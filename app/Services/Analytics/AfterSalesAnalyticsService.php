<?php

namespace App\Services\Analytics;

use App\Models\Branch;
use App\Models\ServiceBooking;
use App\Models\ServiceOrder;
use App\Models\ServiceType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AfterSalesAnalyticsService
{
    /**
     * Compute after-sales workshop analytics dataset.
     */
    public function getAfterSalesAnalytics(User $user, Request $request, array $dateFilter): array
    {
        $branchId = ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id'));
        $start = $dateFilter['start_date'];
        $end = $dateFilter['end_date'];

        // Bookings
        $bookingBaseQuery = ServiceBooking::whereDate('booking_date', '>=', $start->toDateString())
            ->whereDate('booking_date', '<=', $end->toDateString());
        if ($branchId) {
            $bookingBaseQuery->where('branch_id', $branchId);
        }

        $totalBookings = (clone $bookingBaseQuery)->count();
        $pendingBookings = (clone $bookingBaseQuery)->where('status', 'pending')->count();
        $confirmedBookings = (clone $bookingBaseQuery)->where('status', 'confirmed')->count();
        $completedBookings = (clone $bookingBaseQuery)->where('status', 'completed')->count();
        $cancelledBookings = (clone $bookingBaseQuery)->whereIn('status', ['cancelled', 'rejected'])->count();

        // Orders / Work Orders
        $orderBaseQuery = ServiceOrder::whereBetween('created_at', [$start, $end]);
        if ($branchId) {
            $orderBaseQuery->where('branch_id', $branchId);
        }

        $totalOrders = (clone $orderBaseQuery)->count();
        $activeOrders = (clone $orderBaseQuery)->whereIn('status', ['confirmed', 'vehicle_received', 'inspection', 'in_progress', 'waiting_parts'])->count();
        $completedOrders = (clone $orderBaseQuery)->whereIn('status', ['completed', 'delivered'])->count();
        $workshopRevenue = (float) (clone $orderBaseQuery)->whereIn('status', ['completed', 'delivered'])->sum(DB::raw('COALESCE(final_cost, estimated_cost, 0)'));

        // Popular Service Types
        $popularTypes = DB::table('service_orders')
            ->join('service_types', 'service_orders.service_type_id', '=', 'service_types.id')
            ->whereBetween('service_orders.created_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('service_orders.branch_id', $branchId))
            ->select(
                'service_types.name as service_name',
                'service_types.category as category_name',
                DB::raw('count(*) as count'),
                DB::raw('sum(COALESCE(service_orders.final_cost, service_orders.estimated_cost, 0)) as revenue')
            )
            ->groupBy('service_types.id', 'service_types.name', 'service_types.category')
            ->orderByDesc('count')
            ->take(8)
            ->get();

        // Paginated Service Orders List
        $ordersQuery = ServiceOrder::with(['user', 'branch', 'serviceType', 'serviceAdvisor'])
            ->whereBetween('created_at', [$start, $end]);

        if ($branchId) {
            $ordersQuery->where('branch_id', $branchId);
        }

        if ($status = $request->input('status')) {
            $ordersQuery->where('status', $status);
        }

        if ($typeId = $request->input('service_type_id')) {
            $ordersQuery->where('service_type_id', $typeId);
        }

        if ($search = $request->input('search')) {
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('license_plate', 'like', "%{$search}%")
                  ->orWhere('vehicle_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $ordersQuery->latest()->paginate(15)->withQueryString();

        return [
            'kpis' => [
                'total_bookings' => $totalBookings,
                'pending_bookings' => $pendingBookings,
                'confirmed_bookings' => $confirmedBookings,
                'completed_bookings' => $completedBookings,
                'cancelled_bookings' => $cancelledBookings,
                'total_orders' => $totalOrders,
                'active_orders' => $activeOrders,
                'completed_orders' => $completedOrders,
                'workshop_revenue' => $workshopRevenue,
            ],
            'popular_types' => $popularTypes,
            'orders' => $orders,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'service_types' => ServiceType::where('is_active', true)->orderBy('name')->get(),
        ];
    }
}

