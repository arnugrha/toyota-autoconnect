<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\SalesOffer;
use App\Models\TestDriveBooking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPortalController extends Controller
{
    /**
     * Display the customer dashboard.
     */
    public function dashboard(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $stats = [
            'totalInquiries' => Inquiry::where('user_id', $user->id)->count(),
            'inProgressInquiries' => Inquiry::where('user_id', $user->id)->whereIn('status', ['new', 'in_progress', 'contacted', 'scheduled'])->count(),
            'resolvedInquiries' => Inquiry::where('user_id', $user->id)->whereIn('status', ['answered', 'cancelled', 'completed', 'deal', 'lost'])->count(),
        ];

        $recentInquiries = Inquiry::with('vehicle')
            ->where('user_id', $user->id)
            ->latest('submitted_at')
            ->take(5)
            ->get();

        $featuredVehicles = Vehicle::published()
            ->featured()
            ->take(3)
            ->get();

        $testDriveCount = TestDriveBooking::where('user_id', $user->id)->count();
        $bookingCount = VehicleBooking::where('customer_id', $user->id)->count();
        $offerCount = SalesOffer::where('customer_id', $user->id)->whereIn('status', ['sent', 'accepted'])->count();
        $serviceBookingCount = \App\Models\ServiceBooking::where('user_id', $user->id)->count();
        $reminderCount = \App\Models\ServiceReminder::where('user_id', $user->id)->where('status', 'pending')->count();
        $ticketCount = \App\Models\SupportTicket::where('user_id', $user->id)->whereIn('status', ['open', 'in_progress', 'waiting_customer'])->count();

        $recentTestDrives = TestDriveBooking::with(['vehicle', 'branch'])
            ->where('user_id', $user->id)
            ->latest('scheduled_at')
            ->take(3)
            ->get();

        return view('customer.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recentInquiries' => $recentInquiries,
            'featuredVehicles' => $featuredVehicles,
            'testDriveCount' => $testDriveCount,
            'bookingCount' => $bookingCount,
            'offerCount' => $offerCount,
            'serviceBookingCount' => $serviceBookingCount,
            'reminderCount' => $reminderCount,
            'ticketCount' => $ticketCount,
            'recentTestDrives' => $recentTestDrives,
        ]);
    }

    /**
     * Display a list of all inquiries submitted by the authenticated customer.
     */
    public function inquiriesIndex(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Inquiry::with('vehicle')
            ->where('user_id', $user->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $inquiries = $query->paginate(10)->withQueryString();

        return view('customer.inquiries.index', [
            'user' => $user,
            'inquiries' => $inquiries,
            'currentStatus' => $request->input('status', ''),
        ]);
    }

    /**
     * Display details of a specific customer inquiry with strict backend authorization.
     */
    public function inquiryShow(Inquiry $inquiry): View
    {
        // Enforce strict backend data authorization (prevent ID tampering)
        if ($inquiry->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat data permintaan ini.');
        }

        $inquiry->load('vehicle');

        return view('customer.inquiries.show', [
            'inquiry' => $inquiry,
            'user' => Auth::user(),
        ]);
    }
}
