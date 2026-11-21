<?php

namespace App\Http\Controllers;

use App\Models\VehicleBooking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CustomerVehicleBookingController extends Controller
{
    /**
     * Display listing of customer's vehicle bookings.
     */
    public function index(Request $request): View
    {
        $bookings = VehicleBooking::with(['vehicle.images', 'branch'])
            ->where('customer_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('customer.bookings.index', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Display details of a specific vehicle booking.
     */
    public function show(VehicleBooking $booking): View
    {
        Gate::authorize('view', $booking);

        $booking->load(['vehicle.categoryRelation', 'branch', 'salesOpportunity.assignedSales', 'salesOffer']);

        return view('customer.bookings.show', [
            'booking' => $booking,
        ]);
    }
}

