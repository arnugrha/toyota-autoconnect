<?php

namespace App\Services\Payment;

use App\Models\InventoryMovement;
use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Models\User;
use App\Models\VehicleBooking;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class MidtransService
{
    /**
     * Create a Snap payment transaction for a vehicle booking.
     */
    public function createSnapTransaction(VehicleBooking $booking, User $user): Payment
    {
        if ($booking->booking_fee <= 0) {
            throw new InvalidArgumentException('Booking fee harus lebih besar dari 0 untuk membuat transaksi pembayaran.');
        }

        if ($booking->payment_status === 'paid') {
            throw new InvalidArgumentException('Tanda jadi untuk pemesanan ini telah lunas.');
        }

        $serverKey = config('midtrans.server_key') ?? config('services.midtrans.server_key');
        if (empty($serverKey)) {
            Log::warning('Midtrans server key is not configured in environment.');
        }

        $orderId = Payment::generateOrderId($booking);
        $amount = (int) $booking->booking_fee;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '08123456789',
            ],
            'item_details' => [
                [
                    'id' => 'BOOKING-FEE-' . $booking->id,
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => 'Tanda Jadi (SPK) ' . substr($booking->vehicle->name, 0, 30),
                ],
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'days',
                'duration' => 1,
            ],
        ];

        $snapToken = null;
        $snapRedirectUrl = null;
        $gatewayResponse = null;

        try {
            $snapUrl = config('midtrans.snap_url');

            $response = Http::timeout(10)
                ->withBasicAuth($serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($snapUrl, $params);

            if ($response->successful()) {
                $gatewayResponse = $response->json();
                $snapToken = $gatewayResponse['token'] ?? null;
                $snapRedirectUrl = $gatewayResponse['redirect_url'] ?? null;
            } else {
                Log::error('Midtrans Snap Error', [
                    'order_id' => $orderId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Midtrans Connection Exception: ' . $e->getMessage(), [
                'order_id' => $orderId,
            ]);
        }

        // Create Payment record in DB
        return Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $booking->id,
            'user_id' => $user->id,
            'gateway' => 'midtrans',
            'order_id' => $orderId,
            'amount' => $amount,
            'status' => 'pending',
            'snap_token' => $snapToken ?? ('mock-token-' . md5($orderId)),
            'snap_redirect_url' => $snapRedirectUrl ?? ('https://app.sandbox.midtrans.com/snap/v2/vtweb/' . md5($orderId)),
            'gateway_response' => $gatewayResponse,
            'expired_at' => now()->addDay(),
        ]);
    }

    /**
     * Verify Midtrans notification signature key.
     * Formula: SHA512(order_id + status_code + gross_amount + ServerKey)
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $serverKey = config('midtrans.server_key') ?? config('services.midtrans.server_key');

        // Note: gross_amount might be "5000000.00" or "5000000" in Midtrans notification
        $expectedSignature1 = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        // Also test integer-formatted gross amount in case of float string discrepancy
        $intAmount = (string) ((int) round((float) $grossAmount));
        $expectedSignature2 = hash('sha512', $orderId . $statusCode . $intAmount . $serverKey);
        $decimalAmount = number_format((float) $grossAmount, 2, '.', '');
        $expectedSignature3 = hash('sha512', $orderId . $statusCode . $decimalAmount . $serverKey);

        return hash_equals($expectedSignature1, $signatureKey)
            || hash_equals($expectedSignature2, $signatureKey)
            || hash_equals($expectedSignature3, $signatureKey);
    }

    /**
     * Handle incoming webhook notification callback from Midtrans.
     * Enforces signature verification and idempotency.
     */
    public function handleNotification(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';
        $transactionId = $payload['transaction_id'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;

        if (! $orderId || ! $transactionId) {
            return [
                'status' => 'error',
                'message' => 'Parameter order_id atau transaction_id tidak ditemukan.',
                'code' => 400,
            ];
        }

        // Idempotency: Check if this specific notification_id / transaction_id has already been processed
        $existingNotification = PaymentNotification::where('notification_id', $transactionId)
            ->where('processing_status', 'processed')
            ->first();

        if ($existingNotification) {
            Log::info("Midtrans Webhook: Idempotent duplicate notification ignored for order {$orderId}");
            PaymentNotification::create([
                'payment_id' => $existingNotification->payment_id,
                'order_id' => $orderId,
                'notification_id' => $transactionId . '-dup-' . time(),
                'notification_payload' => $payload,
                'processing_status' => 'duplicate_ignored',
                'received_at' => now(),
                'processed_at' => now(),
            ]);

            return [
                'status' => 'ok',
                'message' => 'Notifikasi duplikat diabaikan dengan aman.',
                'code' => 200,
            ];
        }

        // Verify signature
        if (! $this->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning("Midtrans Webhook: Signature verification failed for order {$orderId}");
            PaymentNotification::create([
                'payment_id' => null,
                'order_id' => $orderId,
                'notification_id' => $transactionId,
                'notification_payload' => $payload,
                'processing_status' => 'signature_invalid',
                'received_at' => now(),
                'processed_at' => now(),
            ]);

            return [
                'status' => 'invalid_signature',
                'message' => 'Signature key tidak valid.',
                'code' => 403,
            ];
        }

        // Retrieve Payment record
        $payment = Payment::where('order_id', $orderId)->first();
        if (! $payment) {
            Log::error("Midtrans Webhook: Payment not found for order {$orderId}");
            return [
                'status' => 'not_found',
                'message' => 'Order pembayaran tidak ditemukan di sistem.',
                'code' => 404,
            ];
        }

        // Verify amount
        if ((int) round((float) $grossAmount) !== (int) $payment->amount) {
            Log::error("Midtrans Webhook: Amount mismatch for order {$orderId}. Expected {$payment->amount}, got {$grossAmount}");
            return [
                'status' => 'amount_mismatch',
                'message' => 'Nominal pembayaran tidak sesuai.',
                'code' => 400,
            ];
        }

        // Map Midtrans transaction status to internal payment status
        $mappedPaymentStatus = match ($transactionStatus) {
            'capture' => ($fraudStatus === 'challenge') ? 'pending' : 'paid',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'expired',
            'cancel' => 'cancelled',
            'refund', 'partial_refund' => 'refunded',
            default => 'pending',
        };

        DB::transaction(function () use ($payment, $mappedPaymentStatus, $paymentType, $transactionId, $payload) {
            $updateData = [
                'status' => $mappedPaymentStatus,
                'payment_method' => $paymentType ?? $payment->payment_method,
                'gateway_transaction_id' => $transactionId,
                'gateway_response' => $payload,
            ];

            if ($mappedPaymentStatus === 'paid' && ! $payment->paid_at) {
                $updateData['paid_at'] = now();
            }

            $payment->update($updateData);

            // Connect payment status with VehicleBooking
            $booking = $payment->vehicleBooking;
            if ($booking) {
                if ($mappedPaymentStatus === 'paid') {
                    $booking->update([
                        'payment_status' => 'paid',
                    ]);

                    // If booking was pending and inventory allocated, confirm booking
                    if ($booking->status === 'pending') {
                        $booking->update([
                            'status' => 'confirmed',
                            'confirmed_at' => now(),
                        ]);
                    }
                } elseif ($mappedPaymentStatus === 'expired') {
                    if ($booking->payment_status !== 'paid') {
                        $booking->update([
                            'status' => 'expired',
                        ]);

                        // Release reserved inventory if booking was expired
                        $this->releaseReservedInventory($booking);
                    }
                } elseif ($mappedPaymentStatus === 'refunded') {
                    $booking->update([
                        'payment_status' => 'refunded',
                    ]);
                }
            }

            // Save processed notification record for audit and idempotency
            PaymentNotification::create([
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'notification_id' => $transactionId,
                'notification_payload' => $payload,
                'processing_status' => 'processed',
                'received_at' => now(),
                'processed_at' => now(),
            ]);

            Log::info("Midtrans Webhook: Order {$payment->order_id} processed successfully. New status: {$mappedPaymentStatus}");

            try {
                if ($payment->user) {
                    $notificationService = app(NotificationService::class);
                    if ($mappedPaymentStatus === 'paid') {
                        $notificationService->notifyUser(
                            user: $payment->user,
                            title: 'Pembayaran Berhasil Dikonfirmasi',
                            message: "Pembayaran sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " untuk order {$payment->order_id} telah berhasil diverifikasi oleh sistem.",
                            category: 'payment',
                            actionUrl: route('customer.bookings.index'),
                            actionText: 'Lihat Status Booking',
                            metadata: ['payment_id' => $payment->id, 'order_id' => $payment->order_id]
                        );

                        $notificationService->notifyRole(
                            roleName: 'Finance Officer',
                            title: 'Pembayaran Baru Masuk',
                            message: "Pembayaran lunas sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " untuk order {$payment->order_id} ({$payment->user->name}).",
                            category: 'payment',
                            actionUrl: route('admin.payments.index')
                        );
                    } elseif (in_array($mappedPaymentStatus, ['failed', 'expired'])) {
                        $notificationService->notifyUser(
                            user: $payment->user,
                            title: 'Pembayaran ' . ($mappedPaymentStatus === 'expired' ? 'Kedaluwarsa' : 'Gagal'),
                            message: "Pembayaran untuk order {$payment->order_id} berstatus {$mappedPaymentStatus}. Silakan lakukan pemesanan atau pembayaran ulang.",
                            category: 'payment',
                            actionUrl: route('customer.bookings.index'),
                            metadata: ['payment_id' => $payment->id, 'order_id' => $payment->order_id]
                        );
                    }
                }
            } catch (\Throwable $e) {}
        });

        return [
            'status' => 'ok',
            'message' => 'Notifikasi pembayaran berhasil diproses.',
            'code' => 200,
        ];
    }

    /**
     * Release reserved inventory if booking expires or is cancelled.
     */
    protected function releaseReservedInventory(VehicleBooking $booking): void
    {
        $inventory = $booking->inventory;
        if ($inventory && $inventory->reserved_quantity > 0) {
            $inventory->decrement('reserved_quantity', 1);

            InventoryMovement::create([
                'vehicle_inventory_id' => $inventory->id,
                'user_id' => $booking->customer_id,
                'movement_type' => 'reservation_released',
                'quantity' => 1,
                'stock_before' => $inventory->stock_quantity,
                'stock_after' => $inventory->stock_quantity,
                'reason' => "Pelepasan unit reservasi karena pembayaran booking {$booking->code} kedaluwarsa.",
                'created_at' => now(),
            ]);
        }
    }
}
