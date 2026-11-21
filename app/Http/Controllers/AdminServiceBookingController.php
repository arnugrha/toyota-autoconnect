<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ServiceBooking;
use App\Models\ServiceType;
use App\Services\ServiceBooking\ServiceBookingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class AdminServiceBookingController extends Controller
{
    public function __construct(
        protected ServiceBookingService $bookingService
    ) {}

    /**
     * Display a listing of all service bookings.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ServiceBooking::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $baseQuery = ServiceBooking::query();

        // Scope to branch if user has branch assigned and is not super admin
        if ($user->branch_id && ! $user->hasRole('Super Admin')) {
            $baseQuery->where('branch_id', $user->branch_id);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->whereIn('status', ['cancelled', 'rejected'])->count(),
        ];

        $query = (clone $baseQuery)->with(['user', 'branch', 'serviceType', 'vehicle', 'serviceOrder'])
            ->latest('booking_date');

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('booking_code', 'like', "%{$keyword}%")
                    ->orWhere('license_plate', 'like', "%{$keyword}%")
                    ->orWhere('vehicle_name', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->input('date'));
        }

        $bookings = $query->paginate(12)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.service-bookings.index', [
            'bookings' => $bookings,
            'stats' => $stats,
            'branches' => $branches,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', 'all'),
                'branch_id' => $request->input('branch_id', ''),
                'date' => $request->input('date', ''),
            ],
        ]);
    }

    /**
     * Display service bookings calendar view.
     */
    public function calendar(Request $request): View
    {
        Gate::authorize('viewAny', ServiceBooking::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $monthStart = Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $query = ServiceBooking::with(['user', 'branch', 'serviceType'])
            ->whereBetween('booking_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereNotIn('status', ['cancelled', 'rejected']);

        if ($user->branch_id && ! $user->hasRole('Super Admin')) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $bookings = $query->orderBy('booking_time')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.service-bookings.calendar', [
            'bookings' => $bookings,
            'branches' => $branches,
            'selectedMonth' => $selectedMonth,
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'selectedBranchId' => $request->input('branch_id', $user->branch_id),
        ]);
    }

    /**
     * Display a specific service booking detail.
     */
    public function show(ServiceBooking $serviceBooking): View
    {
        Gate::authorize('view', $serviceBooking);

        $serviceBooking->load([
            'user',
            'branch',
            'serviceType',
            'vehicle',
            'confirmedByUser',
            'serviceOrder.items',
            'serviceOrder.logs.user',
        ]);

        return view('admin.service-bookings.show', [
            'booking' => $serviceBooking,
        ]);
    }

    /**
     * Confirm a service booking.
     */
    public function confirm(Request $request, ServiceBooking $serviceBooking): RedirectResponse
    {
        Gate::authorize('manage', $serviceBooking);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $order = $this->bookingService->confirmBooking(
                booking: $serviceBooking,
                actor: $user,
                technicianName: $request->input('technician_name')
            );

            return redirect()->route('admin.service-orders.show', $order)
                ->with('success', "Booking servis {$serviceBooking->booking_code} berhasil dikonfirmasi dan Service Order {$order->order_number} telah diterbitkan.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reschedule a service booking.
     */
    public function reschedule(Request $request, ServiceBooking $serviceBooking): RedirectResponse
    {
        Gate::authorize('manage', $serviceBooking);

        $validated = $request->validate([
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'string'],
            'reschedule_reason' => ['required', 'string', 'max:500'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->bookingService->rescheduleBooking(
                booking: $serviceBooking,
                newDate: $validated['booking_date'],
                newTime: $validated['booking_time'],
                reason: $validated['reschedule_reason'],
                actor: $user
            );

            return redirect()->route('admin.service-bookings.show', $serviceBooking)
                ->with('success', "Jadwal booking servis {$serviceBooking->booking_code} berhasil diperbarui.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a service booking.
     */
    public function reject(Request $request, ServiceBooking $serviceBooking): RedirectResponse
    {
        Gate::authorize('manage', $serviceBooking);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->bookingService->rejectBooking(
            booking: $serviceBooking,
            reason: $validated['rejection_reason'],
            actor: $user
        );

        return redirect()->route('admin.service-bookings.show', $serviceBooking)
            ->with('success', "Booking servis {$serviceBooking->booking_code} telah ditolak dengan alasan yang tercatat.");
    }
}

