<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Payment\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

    /**
     * Display a listing of all payments.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Payment::class);

        $baseQuery = Payment::query();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'paid' => (clone $baseQuery)->where('status', 'paid')->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'expired' => (clone $baseQuery)->where('status', 'expired')->count(),
            'refunded' => (clone $baseQuery)->where('status', 'refunded')->count(),
            'totalRevenue' => (clone $baseQuery)->where('status', 'paid')->sum('amount'),
        ];

        $query = (clone $baseQuery)->with(['user', 'vehicleBooking.vehicle', 'vehicleBooking.branch'])
            ->latest();

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('code', 'like', "%{$keyword}%")
                    ->orWhere('order_id', 'like', "%{$keyword}%")
                    ->orWhere('gateway_transaction_id', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('vehicleBooking', function ($bq) use ($keyword) {
                        $bq->where('code', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        $payments = $query->paginate(12)->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'stats' => $stats,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', 'all'),
                'payment_method' => $request->input('payment_method', ''),
            ],
        ]);
    }

    /**
     * Display detail of a payment transaction.
     */
    public function show(Payment $payment): View
    {
        Gate::authorize('view', $payment);

        $payment->load([
            'user',
            'vehicleBooking.vehicle',
            'vehicleBooking.branch',
            'vehicleBooking.salesOpportunity.assignedSales',
            'notifications',
        ]);

        return view('admin.payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Process refund for a paid transaction.
     */
    public function refund(Request $request, Payment $payment): RedirectResponse
    {
        Gate::authorize('manage', $payment);

        $validated = $request->validate([
            'refund_reason' => ['required', 'string', 'max:500'],
            'refund_amount' => ['nullable', 'integer', 'min:1000', 'max:' . $payment->amount],
        ]);

        if ($payment->status !== 'paid') {
            return redirect()->route('admin.payments.show', $payment)
                ->with('error', 'Hanya pembayaran yang berstatus lunas yang dapat dikembalikan (refund).');
        }

        $refundAmount = $validated['refund_amount'] ?? $payment->amount;

        DB::transaction(function () use ($payment, $refundAmount, $validated) {
            $payment->update([
                'status' => 'refunded',
                'refund_amount' => $refundAmount,
                'refund_reason' => $validated['refund_reason'],
                'refunded_at' => now(),
            ]);

            $booking = $payment->vehicleBooking;
            if ($booking) {
                $booking->update([
                    'payment_status' => 'refunded',
                ]);
            }
        });

        return redirect()->route('admin.payments.show', $payment)
            ->with('success', 'Refund sebesar Rp ' . number_format($refundAmount, 0, ',', '.') . ' berhasil dicatat.');
    }
}

