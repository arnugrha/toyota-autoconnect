<?php

namespace App\Services\Analytics;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\ServiceBooking;
use App\Models\TestDriveBooking;
use App\Models\User;
use App\Models\VehicleBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerAnalyticsService
{
    /**
     * Compute customer analytics & behavioral segmentation dataset.
     */
    public function getCustomerAnalytics(User $user, Request $request, array $dateFilter): array
    {
        $start = $dateFilter['start_date'];
        $end = $dateFilter['end_date'];

        $customerQuery = User::role('Customer');

        $totalCustomers = (clone $customerQuery)->count();
        $newCustomers = (clone $customerQuery)->whereBetween('created_at', [$start, $end])->count();

        // Behavioral Segmentation (All-time or active)
        $withBookingsCount = (clone $customerQuery)->whereHas('vehicleBookings')->count();
        $withTestDrivesCount = (clone $customerQuery)->whereHas('testDriveBookings')->count();
        $withServicesCount = (clone $customerQuery)->whereHas('serviceBookings')->count();
        $withPaidTransactionsCount = (clone $customerQuery)->whereHas('payments', fn ($q) => $q->where('status', 'paid'))->count();

        // Repeat customers: having more than 1 vehicle booking OR more than 1 service order
        $repeatCustomersCount = (clone $customerQuery)
            ->where(function ($q) {
                $q->has('vehicleBookings', '>', 1)
                  ->orHas('serviceOrders', '>', 1);
            })
            ->count();

        // Registration Trend (by Month in current year or period)
        $monthExpr = DB::getDriverName() === 'sqlite'
            ? 'strftime("%Y-%m", created_at)'
            : 'DATE_FORMAT(created_at, "%Y-%m")';

        $trendMonths = (clone $customerQuery)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(DB::raw("{$monthExpr} as month_key"), DB::raw('count(*) as count'))
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()
            ->keyBy('month_key');

        $chartLabels = [];
        $chartData = [];
        $cursor = now()->subMonths(11)->startOfMonth();
        $now = now()->endOfMonth();
        while ($cursor <= $now) {
            $key = $cursor->format('Y-m');
            $chartLabels[] = $cursor->translatedFormat('M Y');
            $chartData[] = $trendMonths[$key]->count ?? 0;
            $cursor->addMonth();
        }

        // Paginated Customer Activity List
        $listQuery = User::role('Customer')
            ->withCount(['vehicleBookings', 'testDriveBookings', 'serviceBookings', 'payments as paid_payments_count' => function ($q) {
                $q->where('status', 'paid');
            }]);

        if ($search = $request->input('search')) {
            $listQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($segment = $request->input('segment')) {
            if ($segment === 'has_booking') {
                $listQuery->has('vehicleBookings');
            } elseif ($segment === 'has_service') {
                $listQuery->has('serviceBookings');
            } elseif ($segment === 'has_test_drive') {
                $listQuery->has('testDriveBookings');
            } elseif ($segment === 'repeat') {
                $listQuery->where(function ($q) {
                    $q->has('vehicleBookings', '>', 1)->orHas('serviceOrders', '>', 1);
                });
            }
        }

        $customers = $listQuery->latest()->paginate(15)->withQueryString();

        return [
            'kpis' => [
                'total_customers' => $totalCustomers,
                'new_customers' => $newCustomers,
                'with_bookings' => $withBookingsCount,
                'with_test_drives' => $withTestDrivesCount,
                'with_services' => $withServicesCount,
                'with_paid' => $withPaidTransactionsCount,
                'repeat_customers' => $repeatCustomersCount,
            ],
            'chart' => [
                'labels' => $chartLabels,
                'data' => $chartData,
            ],
            'customers' => $customers,
        ];
    }
}
