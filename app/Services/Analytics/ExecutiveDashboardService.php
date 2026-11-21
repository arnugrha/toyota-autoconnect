<?php

namespace App\Services\Analytics;

use App\Models\Branch;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\ServiceBooking;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\VehicleInventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExecutiveDashboardService
{
    /**
     * Compile comprehensive executive analytics metrics.
     *
     * @param array{
     *     start_date: Carbon,
     *     end_date: Carbon,
     *     prev_start_date: Carbon,
     *     prev_end_date: Carbon,
     *     period: string,
     *     label: string
     * } $dateFilter
     */
    public function getExecutiveMetrics(User $user, array $dateFilter, ?int $requestedBranchId = null): array
    {
        $branchId = ScopeFilter::getAuthorizedBranchId($user, $requestedBranchId);
        $start = $dateFilter['start_date'];
        $end = $dateFilter['end_date'];
        $prevStart = $dateFilter['prev_start_date'];
        $prevEnd = $dateFilter['prev_end_date'];

        // --- A. BUSINESS OVERVIEW (Current Period vs Previous Period) ---
        // 1. Sales: vehicle bookings with status confirmed/completed and paid or completed
        $salesQuery = VehicleBooking::whereBetween('created_at', [$start, $end])
            ->where(function ($q) {
                $q->whereIn('status', ['confirmed', 'completed'])
                  ->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            });
        $prevSalesQuery = VehicleBooking::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where(function ($q) {
                $q->whereIn('status', ['confirmed', 'completed'])
                  ->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            });

        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
            $prevSalesQuery->where('branch_id', $branchId);
        }

        $totalSalesCount = (clone $salesQuery)->count();
        $totalSalesRevenue = (float) (clone $salesQuery)->sum('vehicle_price');
        $prevSalesCount = (clone $prevSalesQuery)->count();
        $prevSalesRevenue = (float) (clone $prevSalesQuery)->sum('vehicle_price');

        // 2. Bookings (All new SPK in period)
        $bookingQuery = VehicleBooking::whereBetween('created_at', [$start, $end]);
        $prevBookingQuery = VehicleBooking::whereBetween('created_at', [$prevStart, $prevEnd]);
        if ($branchId) {
            $bookingQuery->where('branch_id', $branchId);
            $prevBookingQuery->where('branch_id', $branchId);
        }
        $totalBookings = (clone $bookingQuery)->count();
        $prevBookings = (clone $prevBookingQuery)->count();

        // 3. Customers (Total & New)
        $totalCustomers = User::role('Customer')->count();
        $newCustomers = User::role('Customer')->whereBetween('created_at', [$start, $end])->count();
        $prevNewCustomers = User::role('Customer')->whereBetween('created_at', [$prevStart, $prevEnd])->count();

        // 4. Leads
        $leadsQuery = Lead::whereBetween('created_at', [$start, $end]);
        $prevLeadsQuery = Lead::whereBetween('created_at', [$prevStart, $prevEnd]);
        if ($branchId) {
            $leadsQuery->whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $branchId));
            $prevLeadsQuery->whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $branchId));
        }
        $totalLeads = (clone $leadsQuery)->count();
        $prevTotalLeads = (clone $prevLeadsQuery)->count();

        // 5. Successful Payments
        $paymentQuery = Payment::where('status', 'paid')->whereBetween('paid_at', [$start, $end]);
        $prevPaymentQuery = Payment::where('status', 'paid')->whereBetween('paid_at', [$prevStart, $prevEnd]);
        if ($branchId) {
            $paymentQuery->whereHas('vehicleBooking', fn ($bq) => $bq->where('branch_id', $branchId));
            $prevPaymentQuery->whereHas('vehicleBooking', fn ($bq) => $bq->where('branch_id', $branchId));
        }
        $successfulPaymentsCount = (clone $paymentQuery)->count();
        $totalPaidAmount = (float) (clone $paymentQuery)->sum('amount');
        $prevSuccessfulPaymentsCount = (clone $prevPaymentQuery)->count();
        $prevTotalPaidAmount = (float) (clone $prevPaymentQuery)->sum('amount');

        // 6. Available Inventory
        $invQuery = VehicleInventory::query();
        if ($branchId) {
            $invQuery->where('branch_id', $branchId);
        }
        $physicalStock = (int) (clone $invQuery)->sum('stock_quantity');
        $reservedStock = (int) (clone $invQuery)->sum('reserved_quantity');
        $availableStock = max(0, $physicalStock - $reservedStock);
        $lowStockCount = (clone $invQuery)->whereRaw('(stock_quantity - reserved_quantity) <= 3')->count();

        // 7. Service Bookings
        $serviceQuery = ServiceBooking::whereDate('booking_date', '>=', $start->toDateString())
            ->whereDate('booking_date', '<=', $end->toDateString());
        $prevServiceQuery = ServiceBooking::whereDate('booking_date', '>=', $prevStart->toDateString())
            ->whereDate('booking_date', '<=', $prevEnd->toDateString());
        if ($branchId) {
            $serviceQuery->where('branch_id', $branchId);
            $prevServiceQuery->where('branch_id', $branchId);
        }
        $totalServiceBookings = (clone $serviceQuery)->count();
        $prevTotalServiceBookings = (clone $prevServiceQuery)->count();

        // --- B. CRM PERFORMANCE BREAKDOWN ---
        $leadStatuses = ['new', 'contacted', 'interested', 'test_drive', 'negotiation', 'deal', 'lost'];
        $crmCounts = [];
        foreach ($leadStatuses as $st) {
            $q = (clone $leadsQuery)->where('status', $st);
            $crmCounts[$st] = $q->count();
        }
        $dealsCount = $crmCounts['deal'];
        $lostCount = $crmCounts['lost'];
        $conversionRate = $totalLeads > 0 ? round(($dealsCount / $totalLeads) * 100, 1) : 0.0;

        // --- C. AFTER-SALES BREAKDOWN ---
        $soQuery = ServiceOrder::whereBetween('created_at', [$start, $end]);
        if ($branchId) {
            $soQuery->where('branch_id', $branchId);
        }
        $activeServicesCount = (clone $soQuery)->whereIn('status', ['confirmed', 'vehicle_received', 'inspection', 'in_progress', 'waiting_parts'])->count();
        $completedServicesCount = (clone $soQuery)->whereIn('status', ['completed', 'delivered'])->count();
        $serviceRevenue = (float) (clone $soQuery)->whereIn('status', ['completed', 'delivered'])->sum(DB::raw('COALESCE(final_cost, estimated_cost, 0)'));

        // --- D. POPULAR MODELS & CATEGORIES ---
        $salesByCategory = DB::table('vehicle_bookings')
            ->join('vehicles', 'vehicle_bookings.vehicle_id', '=', 'vehicles.id')
            ->leftJoin('vehicle_categories', 'vehicles.category_id', '=', 'vehicle_categories.id')
            ->whereBetween('vehicle_bookings.created_at', [$start, $end])
            ->whereIn('vehicle_bookings.status', ['confirmed', 'completed'])
            ->when($branchId, fn ($q) => $q->where('vehicle_bookings.branch_id', $branchId))
            ->select(
                DB::raw('COALESCE(vehicle_categories.name, vehicles.category, "Umum") as category_name'),
                DB::raw('count(*) as count'),
                DB::raw('sum(vehicle_bookings.vehicle_price) as revenue')
            )
            ->groupBy('category_name')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        $topModels = DB::table('vehicle_bookings')
            ->join('vehicles', 'vehicle_bookings.vehicle_id', '=', 'vehicles.id')
            ->whereBetween('vehicle_bookings.created_at', [$start, $end])
            ->whereIn('vehicle_bookings.status', ['confirmed', 'completed'])
            ->when($branchId, fn ($q) => $q->where('vehicle_bookings.branch_id', $branchId))
            ->select('vehicles.name', DB::raw('count(*) as total_sold'), DB::raw('sum(vehicle_bookings.vehicle_price) as total_revenue'))
            ->groupBy('vehicles.id', 'vehicles.name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        // --- E. DEALER COMPARISON (If Super Admin or Dealer Ranking) ---
        $dealerPerformance = DB::table('branches')
            ->where('is_active', true)
            ->select('branches.id', 'branches.name', 'branches.city', 'branches.code')
            ->get()
            ->map(function ($dealer) use ($start, $end) {
                $sales = VehicleBooking::where('branch_id', $dealer->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->selectRaw('count(*) as total_sales, sum(vehicle_price) as revenue')
                    ->first();

                $leads = Lead::whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $dealer->id))->whereBetween('created_at', [$start, $end])->count();
                $deals = Lead::whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $dealer->id))->whereBetween('created_at', [$start, $end])->where('status', 'deal')->count();

                $services = ServiceBooking::where('branch_id', $dealer->id)
                    ->whereDate('booking_date', '>=', $start->toDateString())
                    ->whereDate('booking_date', '<=', $end->toDateString())
                    ->count();

                return [
                    'id' => $dealer->id,
                    'name' => $dealer->name,
                    'city' => $dealer->city,
                    'code' => $dealer->code,
                    'sales_count' => (int) ($sales->total_sales ?? 0),
                    'revenue' => (float) ($sales->revenue ?? 0),
                    'leads_count' => $leads,
                    'deals_count' => $deals,
                    'conversion_rate' => $leads > 0 ? round(($deals / $leads) * 100, 1) : 0.0,
                    'service_count' => $services,
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        // --- F. BUSINESS INTELLIGENCE & INSIGHTS GENERATION ---
        $insights = $this->generateInsights(
            totalSalesCount: $totalSalesCount,
            totalSalesRevenue: $totalSalesRevenue,
            prevSalesRevenue: $prevSalesRevenue,
            topModel: $topModels->first(),
            topDealer: $dealerPerformance->first(),
            conversionRate: $conversionRate,
            lowStockCount: $lowStockCount,
            totalLeads: $totalLeads,
            lostCount: $lostCount
        );

        return [
            'overview' => [
                'sales_count' => [
                    'value' => $totalSalesCount,
                    'prev' => $prevSalesCount,
                    'change' => DateRangeFilter::calcPercentageChange($totalSalesCount, $prevSalesCount),
                ],
                'sales_revenue' => [
                    'value' => $totalSalesRevenue,
                    'prev' => $prevSalesRevenue,
                    'change' => DateRangeFilter::calcPercentageChange($totalSalesRevenue, $prevSalesRevenue),
                ],
                'bookings_count' => [
                    'value' => $totalBookings,
                    'prev' => $prevBookings,
                    'change' => DateRangeFilter::calcPercentageChange($totalBookings, $prevBookings),
                ],
                'customers' => [
                    'total' => $totalCustomers,
                    'new' => $newCustomers,
                    'prev_new' => $prevNewCustomers,
                    'change' => DateRangeFilter::calcPercentageChange($newCustomers, $prevNewCustomers),
                ],
                'leads' => [
                    'total' => $totalLeads,
                    'prev' => $prevTotalLeads,
                    'change' => DateRangeFilter::calcPercentageChange($totalLeads, $prevTotalLeads),
                    'conversion_rate' => $conversionRate,
                ],
                'payments' => [
                    'count' => $successfulPaymentsCount,
                    'amount' => $totalPaidAmount,
                    'change' => DateRangeFilter::calcPercentageChange($totalPaidAmount, $prevTotalPaidAmount),
                ],
                'inventory' => [
                    'physical' => $physicalStock,
                    'reserved' => $reservedStock,
                    'available' => $availableStock,
                    'low_stock' => $lowStockCount,
                ],
                'after_sales' => [
                    'total_bookings' => $totalServiceBookings,
                    'active' => $activeServicesCount,
                    'completed' => $completedServicesCount,
                    'revenue' => $serviceRevenue,
                    'change' => DateRangeFilter::calcPercentageChange($totalServiceBookings, $prevTotalServiceBookings),
                ],
            ],
            'crm' => [
                'counts' => $crmCounts,
                'conversion_rate' => $conversionRate,
                'deals' => $dealsCount,
                'lost' => $lostCount,
            ],
            'sales_by_category' => $salesByCategory,
            'top_models' => $topModels,
            'dealer_performance' => $dealerPerformance,
            'insights' => $insights,
        ];
    }

    /**
     * Generate dynamic, real-data-backed business insights.
     */
    protected function generateInsights(
        int $totalSalesCount,
        float $totalSalesRevenue,
        float $prevSalesRevenue,
        ?object $topModel,
        ?array $topDealer,
        float $conversionRate,
        int $lowStockCount,
        int $totalLeads,
        int $lostCount
    ): array {
        $insights = [];

        // 1. Revenue & Sales Trend
        $revChange = DateRangeFilter::calcPercentageChange($totalSalesRevenue, $prevSalesRevenue);
        if ($totalSalesCount > 0) {
            $dir = $revChange['direction'] === 'up' ? 'peningkatan' : ($revChange['direction'] === 'down' ? 'penurunan' : 'stabil');
            $insights[] = [
                'type' => $revChange['direction'] === 'up' ? 'positive' : 'neutral',
                'title' => "Tren Pendapatan {$revChange['formatted']}",
                'description' => "Pendapatan penjualan unit kendaraan pada periode ini mencapai Rp " . number_format($totalSalesRevenue, 0, ',', '.') . ", mengalami {$dir} dibandingkan periode sebelumnya.",
            ];
        } else {
            $insights[] = [
                'type' => 'neutral',
                'title' => 'Aktivitas Penjualan Belum Tercatat',
                'description' => 'Belum ada transaksi penjualan kendaraan yang terkonfirmasi lunas pada rentang periode ini.',
            ];
        }

        // 2. Best-Selling Model
        if ($topModel && $topModel->total_sold > 0) {
            $insights[] = [
                'type' => 'positive',
                'title' => "Model Terlaris: {$topModel->name}",
                'description' => "Unit {$topModel->name} memimpin penjualan dengan kontribusi {$topModel->total_sold} unit (Rp " . number_format($topModel->total_revenue, 0, ',', '.') . ").",
            ];
        }

        // 3. Top Performing Dealer
        if ($topDealer && $topDealer['sales_count'] > 0) {
            $insights[] = [
                'type' => 'positive',
                'title' => "Dealer Performa Tertinggi: {$topDealer['name']}",
                'description' => "Cabang {$topDealer['name']} mencatatkan performa tertinggi dengan {$topDealer['sales_count']} penjualan dan pendapatan Rp " . number_format($topDealer['revenue'], 0, ',', '.') . ".",
            ];
        }

        // 4. CRM Conversion Insight
        if ($totalLeads > 0) {
            $insights[] = [
                'type' => $conversionRate >= 15.0 ? 'positive' : 'warning',
                'title' => "Rasio Konversi Lead: {$conversionRate}%",
                'description' => "Dari {$totalLeads} prospek pelanggan, {$conversionRate}% berhasil dikonversi menjadi kesepakatan pemesanan mobil.",
            ];
        }

        // 5. Inventory Stock Alert Insight
        if ($lowStockCount > 0) {
            $insights[] = [
                'type' => 'warning',
                'title' => "Peringatan Stok Rendah: {$lowStockCount} Alokasi",
                'description' => "Terdapat {$lowStockCount} alokasi inventaris cabang dengan sisa stok unit yang mendekati atau di bawah batas minimum pengingat.",
            ];
        }

        return $insights;
    }
}
