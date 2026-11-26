<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceBookingRequest;
use App\Models\Branch;
use App\Models\ServiceBooking;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Services\ServiceBooking\ServiceBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class CustomerServiceBookingController extends Controller
{
    public function __construct(
        protected ServiceBookingService $bookingService
    ) {}

    /**
     * Display a listing of customer's service bookings.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ServiceBooking::class);

        /** @var User $user */
        $user = Auth::user();

        $query = ServiceBooking::with(['branch', 'serviceType', 'vehicle', 'serviceOrder.items'])
            ->where('user_id', $user->id)
            ->latest('booking_date');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->paginate(10)->withQueryString();

        return view('customer.service-bookings.index', [
            'bookings' => $bookings,
            'currentStatus' => $request->input('status'),
        ]);
    }

    /**
     * Show form to create a new service booking.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', ServiceBooking::class);

        /** @var User $user */
        $user = Auth::user();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $serviceTypes = ServiceType::active()->orderBy('name')->get();

        // Customer's purchased vehicles from completed bookings
        $purchasedVehicles = VehicleBooking::with('vehicle')
            ->where('customer_id', $user->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->get();

        // All catalog vehicles for selection if customer owns other Toyota
        $catalogVehicles = Vehicle::published()->orderBy('name')->get();

        return view('customer.service-bookings.create', [
            'branches' => $branches,
            'serviceTypes' => $serviceTypes,
            'purchasedVehicles' => $purchasedVehicles,
            'catalogVehicles' => $catalogVehicles,
            'preselectedBranchId' => $request->input('branch_id'),
            'preselectedTypeId' => $request->input('service_type_id'),
        ]);
    }

    /**
     * AJAX endpoint to get available time slots for a branch on a given date.
     */
    public function availableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'date' => ['required', 'date'],
        ]);

        $branch = Branch::findOrFail($request->input('branch_id'));
        $slots = $this->bookingService->getAvailableSlotsForDate($branch, $request->input('date'));

        return response()->json([
            'success' => true,
            'slots' => $slots,
        ]);
    }

    /**
     * Store a newly created service booking.
     */
    public function store(StoreServiceBookingRequest $request): RedirectResponse
    {
        Gate::authorize('create', ServiceBooking::class);

        /** @var User $user */
        $user = Auth::user();

        try {
            $booking = $this->bookingService->createBooking($user, $request->validated());

            return redirect()->route('customer.service-bookings.show', $booking)
                ->with('success', "Permintaan booking servis {$booking->booking_code} berhasil dikirimkan. Tim bengkel resmi kami akan segera mengonfirmasi jadwal Anda.");
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['booking_time' => $e->getMessage()]);
        }
    }

    /**
     * Display a specific service booking.
     */
    public function show(ServiceBooking $booking): View
    {
        Gate::authorize('view', $booking);

        $booking->load(['branch', 'serviceType', 'vehicle', 'serviceOrder.items.sparePart', 'serviceOrder.items.serviceType', 'serviceOrder.logs.user']);

        return view('customer.service-bookings.show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Cancel a booking by customer.
     */
    public function cancel(Request $request, ServiceBooking $booking): RedirectResponse
    {
        Gate::authorize('cancel', $booking);

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        try {
            $this->bookingService->cancelBooking($booking, $request->input('cancellation_reason'), $user);

            return redirect()->route('customer.service-bookings.show', $booking)
                ->with('success', 'Jadwal booking servis Anda berhasil dibatalkan.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
