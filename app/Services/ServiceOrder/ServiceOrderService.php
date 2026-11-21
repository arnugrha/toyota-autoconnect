<?php

namespace App\Services\ServiceOrder;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\ServiceReminder;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ServiceOrderService
{
    /**
     * Update status of a service order with audit logging and milestone timestamps.
     */
    public function updateStatus(ServiceOrder $order, string $newStatus, ?string $notes = null, ?User $actor = null): ServiceOrder
    {
        $validStatuses = [
            'scheduled',
            'confirmed',
            'vehicle_received',
            'inspection',
            'in_progress',
            'waiting_parts',
            'completed',
            'delivered',
            'cancelled',
        ];

        if (! in_array($newStatus, $validStatuses)) {
            throw new InvalidArgumentException("Status servis '{$newStatus}' tidak valid.");
        }

        return DB::transaction(function () use ($order, $newStatus, $notes, $actor) {
            $oldStatus = $order->status;
            $updates = ['status' => $newStatus];

            if ($newStatus === 'vehicle_received' && ! $order->received_at) {
                $updates['received_at'] = now();
            }

            if ($newStatus === 'completed' && ! $order->completed_at) {
                $updates['completed_at'] = now();
            }

            if ($newStatus === 'delivered' && ! $order->delivered_at) {
                $updates['delivered_at'] = now();
            }

            $order->update($updates);

            // Audit Log
            $order->recordLog(
                action: 'status_transition',
                oldStatus: $oldStatus,
                newStatus: $newStatus,
                notes: $notes ?? "Status diperbarui dari '{$order->getStatusLabelAttribute()}' ke '{$newStatus}'.",
                userId: $actor?->id
            );

            // Sync with parent ServiceBooking if completed
            if (in_array($newStatus, ['completed', 'delivered']) && $order->serviceBooking) {
                $order->serviceBooking->update(['status' => 'completed']);
            }

            // Sync after-sales reminder on completion / delivery
            if (in_array($newStatus, ['completed', 'delivered'])) {
                $this->syncAfterSalesReminder($order);
            }

            try {
                if ($order->user) {
                    $statusTitles = [
                        'in_progress' => 'Pengerjaan Servis Dimulai',
                        'waiting_parts' => 'Servis Menunggu Ketersediaan Suku Cadang',
                        'completed' => 'Servis Selesai - Kendaraan Siap Diambil',
                        'delivered' => 'Kendaraan Telah Diserahkan - Terima Kasih',
                    ];

                    $statusMessages = [
                        'in_progress' => "Kendaraan Toyota Anda ({$order->vehicle_name} - {$order->license_plate}) sedang dalam proses pengerjaan oleh teknisi resmi kami.",
                        'waiting_parts' => "Pengerjaan servis kendaraan Anda ({$order->vehicle_name}) sedang menunggu suku cadang resmi.",
                        'completed' => "Kabar gembira! Servis kendaraan Anda ({$order->vehicle_name} - {$order->license_plate}) telah selesai dan unit siap untuk diambil di bengkel resmi cabang {$order->branch->name}.",
                        'delivered' => "Terima kasih telah melakukan servis kendaraan Anda di Toyota AutoConnect. Semoga perjalanan Anda selalu aman dan nyaman.",
                    ];

                    if (isset($statusTitles[$newStatus])) {
                        app(NotificationService::class)->notifyUser(
                            user: $order->user,
                            title: $statusTitles[$newStatus],
                            message: $statusMessages[$newStatus],
                            category: 'service',
                            actionUrl: route('customer.service-histories.show', $order),
                            actionText: 'Lihat Detail Pengerjaan',
                            branchId: $order->branch_id,
                            metadata: ['order_id' => $order->id, 'order_number' => $order->order_number]
                        );
                    }
                }
            } catch (\Throwable $e) {}

            return $order->fresh();
        });
    }

    /**
     * Record vehicle inspection findings and repair recommendations.
     */
    public function recordInspection(ServiceOrder $order, array $data, ?User $actor = null): ServiceOrder
    {
        return DB::transaction(function () use ($order, $data, $actor) {
            $updates = [
                'inspection_result' => $data['inspection_result'] ?? $order->inspection_result,
                'repair_actions' => $data['repair_actions'] ?? $order->repair_actions,
            ];

            if (isset($data['technician_name'])) {
                $updates['technician_name'] = $data['technician_name'];
            }

            if (isset($data['current_mileage'])) {
                $updates['current_mileage'] = (int) $data['current_mileage'];
            }

            $order->update($updates);

            $order->recordLog(
                action: 'inspection_recorded',
                notes: 'Hasil inspeksi teknisi dan rekomendasi perbaikan telah dicatat.',
                userId: $actor?->id
            );

            return $order->fresh();
        });
    }

    /**
     * Add a spare part or labor service item to the service order.
     */
    public function addItem(ServiceOrder $order, array $itemData): ServiceOrderItem
    {
        return DB::transaction(function () use ($order, $itemData) {
            $quantity = max(1, (int) ($itemData['quantity'] ?? 1));
            $unitPrice = (float) ($itemData['unit_price'] ?? 0);
            $subtotal = $quantity * $unitPrice;

            $item = $order->items()->create([
                'item_type' => $itemData['item_type'] ?? 'part',
                'name' => $itemData['name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);

            $order->recalculateFinalCost();

            $order->recordLog(
                action: 'item_added',
                notes: "Menambahkan {$item->name} (Qty: {$quantity}, Subtotal: Rp " . number_format($subtotal, 0, ',', '.') . ')'
            );

            return $item;
        });
    }

    /**
     * Remove an item from the service order.
     */
    public function removeItem(ServiceOrder $order, ServiceOrderItem $item): void
    {
        DB::transaction(function () use ($order, $item) {
            $itemName = $item->name;
            $item->delete();

            $order->recalculateFinalCost();

            $order->recordLog(
                action: 'item_removed',
                notes: "Menghapus item {$itemName} dari rincian servis."
            );
        });
    }

    /**
     * Sync or create after-sales service reminder based on completed service order.
     */
    protected function syncAfterSalesReminder(ServiceOrder $order): void
    {
        $lastMileage = $order->current_mileage;
        $nextMileage = $lastMileage ? ($lastMileage + 10000) : null;
        $nextDate = now()->addMonths(6)->toDateString();

        $recommended = $nextMileage
            ? "Servis Berkala Resmi {$nextMileage} KM atau 6 Bulan"
            : 'Perawatan Berkala 6 Bulan Berikutnya';

        ServiceReminder::updateOrCreate(
            [
                'user_id' => $order->user_id,
                'license_plate' => $order->license_plate,
            ],
            [
                'vehicle_id' => $order->vehicle_id,
                'vehicle_name' => $order->vehicle_name,
                'last_service_date' => now()->toDateString(),
                'last_mileage' => $lastMileage,
                'next_service_date' => $nextDate,
                'next_service_mileage' => $nextMileage,
                'recommended_service' => $recommended,
                'status' => 'pending',
                'notes' => "Dibuat otomatis berdasarkan penyelesaian Service Order {$order->order_number}.",
            ]
        );
    }
}

