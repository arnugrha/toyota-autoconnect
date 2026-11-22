<?php

namespace App\Http\Controllers;

use App\Models\ServiceBooking;
use App\Models\ServiceOrder;
use App\Models\ServiceReminder;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display internal staff dashboard.
     */
    public function staffDashboard(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // If a customer accesses staff dashboard URL directly, redirect them to customer portal
        if ($user->hasRole('Customer')) {
            return redirect()->route('customer.dashboard');
        }

        // If an Owner accesses staff dashboard, redirect directly to executive revenue monitoring
        if ($user->hasRole('Owner')) {
            return redirect()->route('admin.owner.revenue');
        }

        $totalVehicles = Vehicle::count();
        $publishedVehicles = Vehicle::published()->count();
        $totalStaff = User::whereHas('roles', fn ($q) => $q->where('name', '!=', 'Customer'))->count();
        $totalCustomers = User::whereHas('roles', fn ($q) => $q->where('name', 'Customer'))->count();

        $pendingServiceBookings = ServiceBooking::where('status', 'pending')->count();
        $activeServiceOrders = ServiceOrder::whereIn('status', ['vehicle_received', 'inspection', 'in_progress', 'waiting_parts'])->count();
        $openSupportTickets = SupportTicket::whereIn('status', ['open', 'in_progress'])->count();

        $recentVehicles = Vehicle::latest()->take(5)->get();
        $recentBookings = VehicleBooking::with(['customer', 'vehicle', 'branch'])->latest()->take(5)->get();
        $recentServiceBookings = ServiceBooking::with(['user', 'serviceType', 'branch'])->latest()->take(5)->get();

        return view('dashboard.index', [
            'user' => $user,
            'role' => $user->role_name,
            'stats' => [
                'totalVehicles' => $totalVehicles,
                'publishedVehicles' => $publishedVehicles,
                'totalStaff' => $totalStaff,
                'totalCustomers' => $totalCustomers,
                'pendingServiceBookings' => $pendingServiceBookings,
                'activeServiceOrders' => $activeServiceOrders,
                'openSupportTickets' => $openSupportTickets,
            ],
            'recentVehicles' => $recentVehicles,
            'recentBookings' => $recentBookings,
            'recentServiceBookings' => $recentServiceBookings,
        ]);
    }

    /**
     * Display customer portal dashboard.
     */
    public function customerDashboard(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $featuredVehicles = Vehicle::published()->featured()->take(3)->get();
        $activeBookingsCount = ServiceBooking::where('user_id', $user->id)->whereIn('status', ['pending', 'confirmed'])->count();
        $serviceRemindersCount = ServiceReminder::where('user_id', $user->id)->where('status', 'pending')->count();
        $openTicketsCount = SupportTicket::where('user_id', $user->id)->whereIn('status', ['open', 'in_progress', 'waiting_customer'])->count();

        return view('customer.dashboard', [
            'user' => $user,
            'featuredVehicles' => $featuredVehicles,
            'activeBookingsCount' => $activeBookingsCount,
            'serviceRemindersCount' => $serviceRemindersCount,
            'openTicketsCount' => $openTicketsCount,
        ]);
    }
}
