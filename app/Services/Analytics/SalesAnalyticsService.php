<?php

namespace App\Services\Analytics;

use App\Models\Branch;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\VehicleCategory;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesAnalyticsService
{
    /**
     * Get detailed sales analytics dataset.
     */
    public function getSalesAnalytics(User $user, Request $request, array $dateFilter): array
    {
        $branchId = ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id'));
        $start = $dateFilter['start_date'];
        $end = $dateFilter['end_date'];

        // Base Query for valid sales
        $baseQuery = VehicleBooking::whereBetween('vehicle_bookings.created_at', [$start, $end])
            ->where(function ($q) {
                $q->whereIn('vehicle_bookings.status', ['confirmed', 'completed'])
                  ->where('vehicle_bookings.payment_status', 'paid')
                  ->orWhere('vehicle_bookings.status', 'completed');
            });

        if ($branchId) {
            $baseQuery->where('vehicle_bookings.branch_id', $branchId);
        }

        // Scope to sales executive if role applies
        if ($user->hasRole('Sales Executive') && !$user->can('leads.view-team')) {
            $baseQuery->whereHas('salesOpportunity', fn ($soq) => $soq->where('assigned_to', $user->id));
        }

        // Summary KPIs
        $totalSales = (clone $baseQuery)->count();
        $totalRevenue = (float) (clone $baseQuery)->sum('vehicle_price');
        $averageDealValue = $totalSales > 0 ? round($totalRevenue / $totalSales, 0) : 0;

        // Sales Trends (Daily or Monthly depending on range)
        $diffDays = $start->diffInDays($end);
        if ($diffDays <= 31) {
            $trends = (clone $baseQuery)
                ->select(
                    DB::raw('DATE(vehicle_bookings.created_at) as date_key'),
                    DB::raw('count(*) as count'),
                    DB::raw('sum(vehicle_price) as revenue')
                )
                ->groupBy('date_key')
                ->orderBy('date_key')
                ->get()
                ->keyBy('date_key');

            $chartLabels = [];
            $chartCounts = [];
            $chartRevenues = [];
            $cursor = $start->copy();
            while ($cursor <= $end) {
                $k = $cursor->toDateString();
                $chartLabels[] = $cursor->format('d M');
                $chartCounts[] = $trends[$k]->count ?? 0;
                $chartRevenues[] = (float) ($trends[$k]->revenue ?? 0);
                $cursor->addDay();
            }
        } else {
            $monthExpr = DB::getDriverName() === 'sqlite'
                ? 'strftime("%Y-%m", vehicle_bookings.created_at)'
                : 'DATE_FORMAT(vehicle_bookings.created_at, "%Y-%m")';

            $trends = (clone $baseQuery)
                ->select(
                    DB::raw("{$monthExpr} as month_key"),
                    DB::raw('count(*) as count'),
                    DB::raw('sum(vehicle_price) as revenue')
                )
                ->groupBy('month_key')
                ->orderBy('month_key')
                ->get()
                ->keyBy('month_key');

            $chartLabels = [];
            $chartCounts = [];
            $chartRevenues = [];
            $cursor = $start->copy()->startOfMonth();
            while ($cursor <= $end) {
                $k = $cursor->format('Y-m');
                $chartLabels[] = $cursor->translatedFormat('M Y');
                $chartCounts[] = $trends[$k]->count ?? 0;
                $chartRevenues[] = (float) ($trends[$k]->revenue ?? 0);
                $cursor->addMonth();
            }
        }

        // Breakdown by Vehicle Model
        $salesByModel = (clone $baseQuery)
            ->join('vehicles', 'vehicle_bookings.vehicle_id', '=', 'vehicles.id')
            ->select('vehicles.name', DB::raw('count(*) as total_units'), DB::raw('sum(vehicle_bookings.vehicle_price) as total_revenue'))
            ->groupBy('vehicles.id', 'vehicles.name')
            ->orderByDesc('total_units')
            ->take(8)
            ->get();

        // Breakdown by Vehicle Category
        $salesByCategory = (clone $baseQuery)
            ->join('vehicles', 'vehicle_bookings.vehicle_id', '=', 'vehicles.id')
            ->leftJoin('vehicle_categories', 'vehicles.category_id', '=', 'vehicle_categories.id')
            ->select(
                DB::raw('COALESCE(vehicle_categories.name, vehicles.category, "Lainnya") as category_name'),
                DB::raw('count(*) as count'),
                DB::raw('sum(vehicle_bookings.vehicle_price) as revenue')
            )
            ->groupBy('category_name')
            ->orderByDesc('count')
            ->get();

        // Breakdown by Dealer Branch
        $salesByDealer = (clone $baseQuery)
            ->join('branches', 'vehicle_bookings.branch_id', '=', 'branches.id')
            ->select('branches.name', 'branches.city', DB::raw('count(*) as count'), DB::raw('sum(vehicle_bookings.vehicle_price) as revenue'))
            ->groupBy('branches.id', 'branches.name', 'branches.city')
            ->orderByDesc('revenue')
            ->get();

        // Breakdown by Sales Executive
        $salesByExecutive = (clone $baseQuery)
            ->join('sales_opportunities', 'vehicle_bookings.sales_opportunity_id', '=', 'sales_opportunities.id')
            ->join('users', 'sales_opportunities.assigned_to', '=', 'users.id')
            ->select('users.name as executive_name', DB::raw('count(*) as count'), DB::raw('sum(vehicle_bookings.vehicle_price) as revenue'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('count')
            ->take(8)
            ->get();

        // Paginated list of recent sales with filters
        $transactionsQuery = VehicleBooking::with(['vehicle', 'customer', 'branch', 'salesOpportunity.assignedSales', 'payments'])
            ->whereBetween('created_at', [$start, $end]);

        if ($branchId) {
            $transactionsQuery->where('branch_id', $branchId);
        }

        if ($user->hasRole('Sales Executive') && !$user->can('leads.view-team')) {
            $transactionsQuery->whereHas('salesOpportunity', fn ($soq) => $soq->where('assigned_to', $user->id));
        }

        if ($status = $request->input('status')) {
            $transactionsQuery->where('status', $status);
        }

        if ($modelId = $request->input('vehicle_id')) {
            $transactionsQuery->where('vehicle_id', $modelId);
        }

        if ($categoryId = $request->input('category_id')) {
            $transactionsQuery->whereHas('vehicle', fn ($vq) => $vq->where('category_id', $categoryId));
        }

        if ($salesId = $request->input('assigned_to')) {
            $transactionsQuery->whereHas('salesOpportunity', fn ($soq) => $soq->where('assigned_to', $salesId));
        }

        $transactions = $transactionsQuery->latest()->paginate(15)->withQueryString();

        return [
            'kpis' => [
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'average_deal' => $averageDealValue,
            ],
            'chart' => [
                'labels' => $chartLabels,
                'counts' => $chartCounts,
                'revenues' => $chartRevenues,
            ],
            'by_model' => $salesByModel,
            'by_category' => $salesByCategory,
            'by_dealer' => $salesByDealer,
            'by_executive' => $salesByExecutive,
            'transactions' => $transactions,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'categories' => VehicleCategory::where('is_active', true)->orderBy('name')->get(),
            'vehicles' => Vehicle::orderBy('name')->get(),
        ];
    }
}
