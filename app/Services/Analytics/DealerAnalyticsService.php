<?php

namespace App\Services\Analytics;

use App\Models\Branch;
use App\Models\Lead;
use App\Models\ServiceBooking;
use App\Models\ServiceOrder;
use App\Models\TestDriveBooking;
use App\Models\User;
use App\Models\VehicleBooking;
use App\Models\VehicleInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DealerAnalyticsService
{
    /**
     * Compute comparative dealer performance ranking dataset.
     */
    public function getDealerAnalytics(User $user, Request $request, array $dateFilter): array
    {
        $branchId = ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id'));
        $start = $dateFilter['start_date'];
        $end = $dateFilter['end_date'];
        $sortBy = $request->input('sort_by', 'revenue'); // revenue, sales_count, leads_count, conversion_rate, service_count

        $dealersQuery = Branch::where('is_active', true);
        if ($branchId) {
            $dealersQuery->where('id', $branchId);
        }

        $dealers = $dealersQuery->get()->map(function (Branch $dealer) use ($start, $end) {
            // 1. Sales & Revenue
            $sales = VehicleBooking::where('branch_id', $dealer->id)
                ->whereBetween('created_at', [$start, $end])
                ->where(function ($q) {
                    $q->whereIn('status', ['confirmed', 'completed'])
                      ->where('payment_status', 'paid')
                      ->orWhere('status', 'completed');
                })
                ->selectRaw('count(*) as total_sales, sum(vehicle_price) as revenue')
                ->first();

            $salesCount = (int) ($sales->total_sales ?? 0);
            $revenue = (float) ($sales->revenue ?? 0);

            // 2. Bookings (all new SPK)
            $bookingsCount = VehicleBooking::where('branch_id', $dealer->id)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            // 3. Leads & Conversion
            $leadsCount = Lead::whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $dealer->id))
                ->whereBetween('created_at', [$start, $end])
                ->count();
            $dealsCount = Lead::whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $dealer->id))
                ->whereBetween('created_at', [$start, $end])
                ->where('status', 'deal')
                ->count();
            $conversionRate = $leadsCount > 0 ? round(($dealsCount / $leadsCount) * 100, 1) : 0.0;

            // 4. Test Drives
            $testDriveCount = TestDriveBooking::where('branch_id', $dealer->id)
                ->whereBetween('scheduled_at', [$start, $end])
                ->count();

            // 5. Available Inventory
            $availableStock = (int) VehicleInventory::where('branch_id', $dealer->id)
                ->selectRaw('sum(stock_quantity - reserved_quantity) as total_available')
                ->value('total_available') ?? 0;

            // 6. Service Bookings & Completed Services
            $serviceBookingsCount = ServiceBooking::where('branch_id', $dealer->id)
                ->whereDate('booking_date', '>=', $start->toDateString())
                ->whereDate('booking_date', '<=', $end->toDateString())
                ->count();

            $completedServicesCount = ServiceOrder::where('branch_id', $dealer->id)
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('status', ['completed', 'delivered'])
                ->count();

            return [
                'id' => $dealer->id,
                'name' => $dealer->name,
                'city' => $dealer->city,
                'code' => $dealer->code,
                'phone' => $dealer->phone,
                'sales_count' => $salesCount,
                'revenue' => $revenue,
                'bookings_count' => $bookingsCount,
                'leads_count' => $leadsCount,
                'deals_count' => $dealsCount,
                'conversion_rate' => $conversionRate,
                'test_drive_count' => $testDriveCount,
                'available_stock' => max(0, $availableStock),
                'service_bookings_count' => $serviceBookingsCount,
                'completed_services_count' => $completedServicesCount,
            ];
        });

        // Apply sorting
        $sortedDealers = (match ($sortBy) {
            'sales_count' => $dealers->sortByDesc('sales_count'),
            'leads_count' => $dealers->sortByDesc('leads_count'),
            'conversion_rate' => $dealers->sortByDesc('conversion_rate'),
            'service_count' => $dealers->sortByDesc('service_bookings_count'),
            'available_stock' => $dealers->sortByDesc('available_stock'),
            default => $dealers->sortByDesc('revenue'),
        })->values();

        // Totals across all inspected dealers
        $totals = [
            'total_sales' => $sortedDealers->sum('sales_count'),
            'total_revenue' => $sortedDealers->sum('revenue'),
            'total_bookings' => $sortedDealers->sum('bookings_count'),
            'total_leads' => $sortedDealers->sum('leads_count'),
            'total_test_drives' => $sortedDealers->sum('test_drive_count'),
            'total_available_stock' => $sortedDealers->sum('available_stock'),
            'total_service_bookings' => $sortedDealers->sum('service_bookings_count'),
            'total_completed_services' => $sortedDealers->sum('completed_services_count'),
        ];

        return [
            'dealers' => $sortedDealers,
            'totals' => $totals,
            'sort_by' => $sortBy,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
