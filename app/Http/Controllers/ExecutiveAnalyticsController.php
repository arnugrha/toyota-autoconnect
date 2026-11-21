<?php

namespace App\Http\Controllers;

use App\Models\Branch;
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
    ) {
    }

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
        // Dealer comparison is available to Super Admin, or Dealer Manager to review their own branch
        if (!$user->hasRole(['Super Admin', 'Dealer Manager'])) {
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
}

