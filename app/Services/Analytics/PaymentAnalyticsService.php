<?php

namespace App\Services\Analytics;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentAnalyticsService
{
    /**
     * Compute payment analytics dataset.
     */
    public function getPaymentAnalytics(User $user, Request $request, array $dateFilter): array
    {
        $branchId = ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id'));
        $start = $dateFilter['start_date'];
        $end = $dateFilter['end_date'];

        $baseQuery = Payment::with(['vehicleBooking.vehicle', 'vehicleBooking.branch', 'user'])
            ->whereBetween('created_at', [$start, $end]);

        if ($branchId) {
            $baseQuery->whereHas('vehicleBooking', fn ($bq) => $bq->where('branch_id', $branchId));
        }

        $totalTransactions = (clone $baseQuery)->count();

        // Successful payments
        $paidPayments = (clone $baseQuery)->where('status', 'paid');
        $paidCount = (clone $paidPayments)->count();
        $totalReceived = (float) (clone $paidPayments)->sum('amount');
        $avgTransaction = $paidCount > 0 ? round($totalReceived / $paidCount, 0) : 0;

        // Pending payments
        $pendingPayments = (clone $baseQuery)->where('status', 'pending');
        $pendingCount = (clone $pendingPayments)->count();
        $totalPending = (float) (clone $pendingPayments)->sum('amount');

        // Failed / Expired payments
        $failedPayments = (clone $baseQuery)->whereIn('status', ['failed', 'expired', 'cancelled']);
        $failedCount = (clone $failedPayments)->count();
        $totalFailed = (float) (clone $failedPayments)->sum('amount');

        // Payment Methods Breakdown
        $methodsBreakdown = (clone $paidPayments)
            ->select(DB::raw('COALESCE(payment_method, "Midtrans Snap") as method_name'), DB::raw('count(*) as count'), DB::raw('sum(amount) as amount'))
            ->groupBy('method_name')
            ->orderByDesc('amount')
            ->get();

        // Paginated Transactions List with Filters
        $transactionsQuery = Payment::with(['vehicleBooking.vehicle', 'vehicleBooking.branch', 'user'])
            ->whereBetween('created_at', [$start, $end]);

        if ($branchId) {
            $transactionsQuery->whereHas('vehicleBooking', fn ($bq) => $bq->where('branch_id', $branchId));
        }

        if ($status = $request->input('status')) {
            $transactionsQuery->where('status', $status);
        }

        if ($method = $request->input('payment_method')) {
            $transactionsQuery->where('payment_method', $method);
        }

        if ($search = $request->input('search')) {
            $transactionsQuery->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                  ->orWhere('gateway_transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                  ->orWhereHas('vehicleBooking', fn ($bq) => $bq->where('code', 'like', "%{$search}%"));
            });
        }

        $transactions = $transactionsQuery->latest()->paginate(15)->withQueryString();

        return [
            'kpis' => [
                'total_transactions' => $totalTransactions,
                'paid_count' => $paidCount,
                'total_received' => $totalReceived,
                'avg_transaction' => $avgTransaction,
                'pending_count' => $pendingCount,
                'total_pending' => $totalPending,
                'failed_count' => $failedCount,
                'total_failed' => $totalFailed,
            ],
            'methods' => $methodsBreakdown,
            'transactions' => $transactions,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ];
    }
}

