<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\VehicleBooking;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerPaymentController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

    /**
     * Display a listing of customer's payments.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Payment::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Payment::with(['vehicleBooking.vehicle', 'vehicleBooking.branch'])
            ->where('user_id', $user->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $payments = $query->paginate(10)->withQueryString();

        return view('customer.payments.index', [
            'payments' => $payments,
            'currentStatus' => $request->input('status'),
        ]);
    }

    /**
     * Initiate or retrieve Midtrans Snap checkout session for a vehicle booking fee.
     */
    public function checkout(Request $request, VehicleBooking $booking): JsonResponse|RedirectResponse|View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Customer can only pay for their own booking
        if ($booking->customer_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk membayar pemesanan ini.');
        }

        // Prevent paying for already paid booking
        if ($booking->payment_status === 'paid') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanda jadi untuk pemesanan ini telah lunas dibayar.',
                ], 422);
            }

            return redirect()->route('customer.bookings.show', $booking)
                ->with('info', 'Tanda jadi untuk pemesanan ini telah lunas dibayar.');
        }

        // Prevent paying for inactive / cancelled booking
        if (in_array($booking->status, ['cancelled', 'rejected', 'expired'])) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pemesanan ini sudah tidak aktif atau dibatalkan.',
                ], 422);
            }

            return redirect()->route('customer.bookings.show', $booking)
                ->with('error', 'Pemesanan ini sudah tidak aktif atau dibatalkan.');
        }

        // Check if there is already an active pending payment with valid snap token
        $payment = Payment::where('vehicle_booking_id', $booking->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->latest()
            ->first();

        if (! $payment) {
            $payment = $this->midtransService->createSnapTransaction($booking, $user);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'snap_token' => $payment->snap_token,
                'redirect_url' => $payment->snap_redirect_url,
                'payment_code' => $payment->code,
                'amount' => $payment->amount,
                'client_key' => config('midtrans.client_key'),
            ]);
        }

        if ($request->input('mode') === 'redirect' && $payment->snap_redirect_url) {
            return redirect($payment->snap_redirect_url);
        }

        return view('customer.payments.checkout', [
            'booking' => $booking,
            'payment' => $payment,
            'clientKey' => config('midtrans.client_key'),
            'snapJsUrl' => config('midtrans.snap_js_url'),
        ]);
    }

    /**
     * Display payment receipt / details.
     */
    public function show(Payment $payment): View
    {
        Gate::authorize('view', $payment);

        $payment->load(['vehicleBooking.vehicle', 'vehicleBooking.branch', 'user']);

        return view('customer.payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Landing page after customer finishes payment on Midtrans.
     */
    public function finish(Request $request): RedirectResponse
    {
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $transactionStatus = $request->input('transaction_status');

        $payment = Payment::where('order_id', $orderId)->first();

        if ($payment) {
            // If Midtrans redirect provides settled status, we can prompt success
            $msg = ($transactionStatus === 'settlement' || $statusCode === '200')
                ? 'Pembayaran tanda jadi (booking fee) Anda telah berhasil diterima.'
                : 'Transaksi pembayaran sedang diproses atau menunggu konfirmasi.';

            return redirect()->route('customer.payments.show', $payment)
                ->with('success', $msg);
        }

        return redirect()->route('customer.payments.index')
            ->with('info', 'Status pembayaran sedang diperbarui oleh sistem payment gateway.');
    }
}

