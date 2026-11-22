<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\VehicleBooking;
use App\Services\Analytics\AfterSalesAnalyticsService;
use App\Services\Analytics\CrmAnalyticsService;
use App\Services\Analytics\CustomerAnalyticsService;
use App\Services\Analytics\DateRangeFilter;
use App\Services\Analytics\DealerAnalyticsService;
use App\Services\Analytics\ExecutiveDashboardService;
use App\Services\Analytics\InventoryAnalyticsService;
use App\Services\Analytics\PaymentAnalyticsService;
use App\Services\Analytics\SalesAnalyticsService;
use App\Services\Analytics\ScopeFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExecutiveAnalyticsController extends Controller
{
    public function __construct(
        protected ExecutiveDashboardService $executiveService,
        protected SalesAnalyticsService $salesService,
        protected CrmAnalyticsService $crmService,
        protected DealerAnalyticsService $dealerService,
        protected InventoryAnalyticsService $inventoryService,
        protected PaymentAnalyticsService $paymentService,
        protected AfterSalesAnalyticsService $afterSalesService,
        protected CustomerAnalyticsService $customerService
    ) {}

    /**
     * 1. Executive Dashboard (Super Admin & Management Overview)
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $dateFilter = DateRangeFilter::resolve($request);
        $data = $this->executiveService->getExecutiveMetrics($user, $dateFilter, $request->input('branch_id'));
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.analytics.dashboard', [
            'data' => $data,
            'dateFilter' => $dateFilter,
            'branches' => $branches,
            'selectedBranchId' => ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id')),
        ]);
    }

    /**
     * 2. Sales Analytics
     */
    public function sales(Request $request): View
    {
        $user = $request->user();
        if ($user->hasRole(['Inventory Officer', 'Service Advisor'])) {
            abort(403, 'Akses analisis penjualan tidak diizinkan untuk role Anda.');
        }

        $dateFilter = DateRangeFilter::resolve($request);
        $data = $this->salesService->getSalesAnalytics($user, $request, $dateFilter);

        return view('admin.analytics.sales', [
            'data' => $data,
            'dateFilter' => $dateFilter,
            'selectedBranchId' => ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id')),
        ]);
    }

    /**
     * 3. CRM & Lead Analytics
     */
    public function crm(Request $request): View
    {
        $user = $request->user();
        if ($user->hasRole(['Inventory Officer', 'Service Advisor', 'Finance Officer'])) {
            abort(403, 'Akses analisis CRM tidak diizinkan untuk role Anda.');
        }

        $dateFilter = DateRangeFilter::resolve($request);
        $data = $this->crmService->getCrmAnalytics($user, $request, $dateFilter);

        return view('admin.analytics.crm', [
            'data' => $data,
            'dateFilter' => $dateFilter,
            'selectedBranchId' => ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id')),
        ]);
    }

    /**
     * 4. Dealer Performance Analytics
     */
    public function dealers(Request $request): View
    {
        $user = $request->user();
        // Dealer comparison is available to Super Admin, Dealer Manager, or Owner to review branches
        if (! $user->hasRole(['Super Admin', 'Dealer Manager', 'Owner'])) {
            abort(403, 'Akses komparasi dealer hanya dapat diakses oleh Manajemen.');
        }

        $dateFilter = DateRangeFilter::resolve($request);
        $data = $this->dealerService->getDealerAnalytics($user, $request, $dateFilter);

        return view('admin.analytics.dealers', [
            'data' => $data,
            'dateFilter' => $dateFilter,
            'selectedBranchId' => ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id')),
        ]);
    }

    /**
     * 5. Inventory Analytics
     */
    public function inventory(Request $request): View
    {
        $user = $request->user();
        if ($user->hasRole(['Service Advisor', 'CRM Officer'])) {
            abort(403, 'Akses analisis inventory tidak diizinkan.');
        }

        $dateFilter = DateRangeFilter::resolve($request);
        $data = $this->inventoryService->getInventoryAnalytics($user, $request, $dateFilter);

        return view('admin.analytics.inventory', [
            'data' => $data,
            'dateFilter' => $dateFilter,
            'selectedBranchId' => ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id')),
        ]);
    }

    /**
     * 6. Payment Analytics
     */
    public function payments(Request $request): View
    {
        $user = $request->user();
        if ($user->hasRole(['Service Advisor', 'Inventory Officer', 'Sales Executive'])) {
            abort(403, 'Akses laporan transaksi pembayaran dibatasi.');
        }

        $dateFilter = DateRangeFilter::resolve($request);
        $data = $this->paymentService->getPaymentAnalytics($user, $request, $dateFilter);

        return view('admin.analytics.payments', [
            'data' => $data,
            'dateFilter' => $dateFilter,
            'selectedBranchId' => ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id')),
        ]);
    }

    /**
     * 7. After-Sales Analytics
     */
    public function afterSales(Request $request): View
    {
        $user = $request->user();
        if ($user->hasRole(['Sales Executive', 'Inventory Officer'])) {
            abort(403, 'Akses analisis bengkel dan after-sales dibatasi.');
        }

        $dateFilter = DateRangeFilter::resolve($request);
        $data = $this->afterSalesService->getAfterSalesAnalytics($user, $request, $dateFilter);

        return view('admin.analytics.after-sales', [
            'data' => $data,
            'dateFilter' => $dateFilter,
            'selectedBranchId' => ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id')),
        ]);
    }

    /**
     * 8. Customer Analytics
     */
    public function customers(Request $request): View
    {
        $user = $request->user();
        if ($user->hasRole(['Inventory Officer', 'Service Advisor', 'Sales Executive'])) {
            abort(403, 'Akses analisis pelanggan dibatasi.');
        }

        $dateFilter = DateRangeFilter::resolve($request);
        $data = $this->customerService->getCustomerAnalytics($user, $request, $dateFilter);

        return view('admin.analytics.customers', [
            'data' => $data,
            'dateFilter' => $dateFilter,
        ]);
    }

    /**
     * 9. Owner Executive Revenue Monitoring Hub
     */
    public function revenueMonitoring(Request $request): View
    {
        $user = $request->user();
        $dateFilter = DateRangeFilter::resolve($request);
        $branchId = ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id'));
        $start = $dateFilter['start_date'];
        $end = $dateFilter['end_date'];

        // 1. Vehicle Sales Revenue (SPK Completed / Confirmed)
        $salesQuery = VehicleBooking::with(['vehicle', 'branch', 'customer'])
            ->whereBetween('created_at', [$start, $end]);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }

        $totalSpkCount = (clone $salesQuery)->count();
        $confirmedSpkCount = (clone $salesQuery)->whereIn('status', ['confirmed', 'completed'])->count();
        $totalSalesGross = (float) (clone $salesQuery)->whereIn('status', ['confirmed', 'completed'])->sum('vehicle_price');
        $totalBookingFeeReceived = (float) (clone $salesQuery)->where('payment_status', 'paid')->sum('booking_fee');

        // 2. Realized Cash Inflow via Midtrans / Payments
        $paymentsQuery = Payment::with(['vehicleBooking.vehicle', 'vehicleBooking.branch', 'user'])
            ->whereBetween('created_at', [$start, $end]);
        if ($branchId) {
            $paymentsQuery->whereHas('vehicleBooking', fn ($bq) => $bq->where('branch_id', $branchId));
        }
        $cashInflowReceived = (float) (clone $paymentsQuery)->where('status', 'paid')->sum('amount');
        $pendingCashInflow = (float) (clone $paymentsQuery)->where('status', 'pending')->sum('amount');

        // 3. Service Workshop Revenue (Bengkel & Suku Cadang)
        $serviceQuery = ServiceOrder::whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['completed', 'delivered']);
        if ($branchId) {
            $serviceQuery->where('branch_id', $branchId);
        }
        $totalServiceRevenue = (float) (clone $serviceQuery)->sum(DB::raw('COALESCE(final_cost, estimated_cost, 0)'));
        $serviceOrderCount = (clone $serviceQuery)->count();

        // Combined Total Business Revenue (Unit Sales Gross + Workshop Service)
        $totalCombinedRevenue = $totalSalesGross + $totalServiceRevenue;

        // 4. Branch Revenue Breakdown (Perbandingan Antar Cabang)
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $branchBreakdown = [];
        foreach ($branches as $b) {
            $bSales = (float) VehicleBooking::where('branch_id', $b->id)
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('status', ['confirmed', 'completed'])
                ->sum('vehicle_price');
            $bService = (float) ServiceOrder::where('branch_id', $b->id)
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('status', ['completed', 'delivered'])
                ->sum(DB::raw('COALESCE(final_cost, estimated_cost, 0)'));
            $bCash = (float) Payment::whereHas('vehicleBooking', fn ($bq) => $bq->where('branch_id', $b->id))
                ->whereBetween('created_at', [$start, $end])
                ->where('status', 'paid')
                ->sum('amount');
            $bTotal = $bSales + $bService;

            $branchBreakdown[] = [
                'branch' => $b,
                'sales_revenue' => $bSales,
                'service_revenue' => $bService,
                'cash_inflow' => $bCash,
                'total_revenue' => $bTotal,
            ];
        }

        // Sort by highest revenue
        usort($branchBreakdown, fn ($a, $b) => $b['total_revenue'] <=> $a['total_revenue']);

        // 5. Top Revenue Contributing Vehicle Models
        $topVehicles = VehicleBooking::with('vehicle')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['confirmed', 'completed'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->select('vehicle_id', DB::raw('count(*) as total_units'), DB::raw('sum(vehicle_price) as total_volume'))
            ->groupBy('vehicle_id')
            ->orderByDesc('total_volume')
            ->take(6)
            ->get();

        // 6. Recent Paid Transactions
        $recentTransactions = (clone $paymentsQuery)
            ->where('status', 'paid')
            ->latest()
            ->take(8)
            ->get();

        return view('admin.owner.revenue', [
            'dateFilter' => $dateFilter,
            'branches' => $branches,
            'selectedBranchId' => $branchId,
            'stats' => [
                'total_combined_revenue' => $totalCombinedRevenue,
                'total_sales_gross' => $totalSalesGross,
                'cash_inflow_received' => $cashInflowReceived,
                'pending_cash_inflow' => $pendingCashInflow,
                'total_booking_fee' => $totalBookingFeeReceived,
                'total_service_revenue' => $totalServiceRevenue,
                'service_order_count' => $serviceOrderCount,
                'total_spk_count' => $totalSpkCount,
                'confirmed_spk_count' => $confirmedSpkCount,
            ],
            'branchBreakdown' => $branchBreakdown,
            'topVehicles' => $topVehicles,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
