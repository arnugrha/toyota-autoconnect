<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleBookingRequest;
use App\Http\Requests\UpdateVehicleBookingStatusRequest;
use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\SalesOpportunity;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\VehicleInventory;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminVehicleBookingController extends Controller
{
    /**
     * Display a listing of vehicle bookings with filters and metrics.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', VehicleBooking::class);

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $baseQuery = VehicleBooking::with(['customer', 'vehicle', 'branch', 'salesOpportunity.assignedSales', 'inventory']);

        if ($currentUser->hasRole('Sales Executive') && ! $currentUser->can('leads.view-team')) {
            $baseQuery->whereHas('salesOpportunity', function ($q) use ($currentUser) {
                $q->where('assigned_to', $currentUser->id);
            });
        }

        // Metrics
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->whereIn('status', ['cancelled', 'expired'])->count(),
        ];

        $query = (clone $baseQuery)->latest();

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('code', 'like', "%{$keyword}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$keyword}%")->orWhere('email', 'like', "%{$keyword}%"))
                    ->orWhereHas('vehicle', fn ($vq) => $vq->where('name', 'like', "%{$keyword}%"));
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $bookings = $query->paginate(12)->withQueryString();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.sales.bookings.index', [
            'bookings' => $bookings,
            'stats' => $stats,
            'branches' => $branches,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', 'all'),
                'branch_id' => $request->input('branch_id', ''),
            ],
        ]);
    }

    /**
     * Show form to create a booking for an opportunity.
     */
    public function create(SalesOpportunity $opportunity): View
    {
        Gate::authorize('create', VehicleBooking::class);

        $opportunity->load(['customer', 'vehicle', 'branch', 'offers' => fn ($q) => $q->whereIn('status', ['accepted', 'sent'])]);

        // Check inventory availability in this branch
        $inventory = VehicleInventory::where('vehicle_id', $opportunity->vehicle_id)
            ->where('branch_id', $opportunity->branch_id)
            ->first();

        return view('admin.sales.bookings.create', [
            'opportunity' => $opportunity,
            'inventory' => $inventory,
        ]);
    }

    /**
     * Store a pending vehicle booking.
     */
    public function store(StoreVehicleBookingRequest $request): RedirectResponse
    {
        Gate::authorize('create', VehicleBooking::class);

        $validated = $request->validated();
        $opportunity = SalesOpportunity::with(['vehicle', 'branch'])->findOrFail($validated['sales_opportunity_id']);

        $offer = null;
        if (! empty($validated['sales_offer_id'])) {
            $offer = $opportunity->offers()->find($validated['sales_offer_id']);
        }

        $vehiclePrice = $offer ? $offer->vehicle_price : ($opportunity->vehicle->price ?? 0);
        $discountAmount = $offer ? $offer->discount_amount : 0;
        $bookingFee = (int) ($validated['booking_fee'] ?? 5000000); // Default booking fee Rp 5.000.000

        $inventory = VehicleInventory::where('vehicle_id', $opportunity->vehicle_id)
            ->where('branch_id', $opportunity->branch_id)
            ->first();

        $booking = DB::transaction(function () use ($opportunity, $offer, $vehiclePrice, $discountAmount, $bookingFee, $inventory, $validated) {
            $booking = VehicleBooking::create([
                'code' => VehicleBooking::generateCode(),
                'sales_opportunity_id' => $opportunity->id,
                'sales_offer_id' => $offer?->id,
                'customer_id' => $opportunity->user_id,
                'vehicle_id' => $opportunity->vehicle_id,
                'branch_id' => $opportunity->branch_id,
                'inventory_id' => $inventory?->id,
                'vehicle_price' => $vehiclePrice,
                'discount_amount' => $discountAmount,
                'booking_fee' => $bookingFee,
                'booking_date' => now()->toDateString(),
                'expires_at' => $validated['expires_at'] ?? now()->addDays(7),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Advance opportunity stage to booking
            $opportunity->update(['stage' => 'booking']);

            $opportunity->recordActivity(
                'booking_created',
                "Pemesanan unit kendaraan diajukan dengan kode {$booking->code}. Menunggu konfirmasi alokasi stok.",
                ['booking_code' => $booking->code, 'booking_fee' => $bookingFee],
                Auth::id()
            );

            return $booking;
        });

        try {
            $notificationService = app(NotificationService::class);
            if ($booking->customer) {
                $notificationService->notifyUser(
                    user: $booking->customer,
                    title: 'Pemesanan Kendaraan (SPK) Dibuat',
                    message: "Pemesanan kendaraan {$booking->vehicle->name} (SPK: {$booking->code}) telah berhasil dibuat dan saat ini sedang menunggu proses konfirmasi.",
                    category: 'vehicle_booking',
                    actionUrl: route('customer.bookings.show', $booking),
                    actionText: 'Lihat Status Pemesanan',
                    branchId: $booking->branch_id,
                    metadata: ['booking_id' => $booking->id, 'booking_code' => $booking->code]
                );
            }

            $notificationService->notifyRole(
                roleName: 'Sales Manager',
                title: 'Pemesanan Kendaraan (SPK) Baru',
                message: "SPK baru {$booking->code} dibuat untuk {$booking->customer->name} (Unit: {$booking->vehicle->name}).",
                category: 'vehicle_booking',
                branchId: $booking->branch_id,
                actionUrl: route('admin.sales.bookings.show', $booking)
            );
        } catch (\Throwable $e) {}

        return redirect()->route('admin.sales.bookings.show', $booking)
            ->with('success', "Pemesanan unit kendaraan {$booking->code} berhasil dibuat dengan status Pending.");
    }

    /**
     * Display vehicle booking details.
     */
    public function show(VehicleBooking $booking): View
    {
        Gate::authorize('view', $booking);

        $booking->load([
            'customer',
            'vehicle.categoryRelation',
            'branch',
            'salesOpportunity.assignedSales',
            'salesOffer',
            'inventory',
            'createdBy',
            'confirmedByUser',
            'payments',
        ]);

        return view('admin.sales.bookings.show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Confirm vehicle booking with pessimistic locking & stock allocation.
     */
    public function confirm(Request $request, VehicleBooking $booking): RedirectResponse
    {
        Gate::authorize('confirm', $booking);

        if ($booking->status !== 'pending') {
            return back()->with('error', "Pemesanan unit tidak dapat dikonfirmasi karena status saat ini adalah {$booking->status_label}.");
        }

        $userId = Auth::id();

        try {
            DB::transaction(function () use ($booking, $userId, $request) {
                // 1. Lock inventory row to prevent concurrent over-allocation
                $inventory = VehicleInventory::where('vehicle_id', $booking->vehicle_id)
                    ->where('branch_id', $booking->branch_id)
                    ->lockForUpdate()
                    ->first();

                if (! $inventory) {
                    throw new \InvalidArgumentException("Data inventory untuk cabang {$booking->branch->name} tidak ditemukan.");
                }

                // Hitung stok tersedia (stock_quantity - reserved_quantity)
                $available = $inventory->stock_quantity - $inventory->reserved_quantity;
                if ($available < 1) {
                    throw new \InvalidArgumentException(
                        "Alokasi stok gagal: Stok tersedia untuk kendaraan {$booking->vehicle->name} di cabang {$booking->branch->name} tidak mencukupi (Tersedia: {$available} unit)."
                    );
                }

                // 2. Alokasikan stok (naikkan reserved_quantity, fisik tetap)
                $inventory->increment('reserved_quantity', 1);

                // 3. Catat riwayat audit inventory movement
                InventoryMovement::create([
                    'vehicle_inventory_id' => $inventory->id,
                    'user_id' => $userId,
                    'movement_type' => 'reservation',
                    'quantity' => 1,
                    'stock_before' => $inventory->stock_quantity,
                    'stock_after' => $inventory->stock_quantity,
                    'reason' => "Alokasi reservasi unit pemesanan kendaraan SPK {$booking->code}.",
                    'created_at' => now(),
                ]);

                // 4. Update status booking menjadi confirmed
                $paymentStatus = $request->input('payment_status', $booking->payment_status);

                $booking->update([
                    'status' => 'confirmed',
                    'payment_status' => $paymentStatus,
                    'confirmed_at' => now(),
                    'confirmed_by' => $userId,
                    'inventory_id' => $inventory->id,
                ]);

                // 5. Update opportunity activity
                $booking->salesOpportunity->recordActivity(
                    'booking_confirmed',
                    "Pemesanan unit {$booking->code} telah dikonfirmasi dan 1 unit stok berhasil dialokasikan di cabang {$booking->branch->name}.",
                    ['booking_code' => $booking->code, 'inventory_id' => $inventory->id],
                    $userId
                );
            });

            try {
                if ($booking->customer) {
                    app(NotificationService::class)->notifyUser(
                        user: $booking->customer,
                        title: 'Pemesanan Kendaraan (SPK) Dikonfirmasi',
                        message: "Kabar gembira! Pemesanan unit {$booking->vehicle->name} (SPK: {$booking->code}) telah dikonfirmasi dan 1 unit stok telah dialokasikan.",
                        category: 'vehicle_booking',
                        actionUrl: route('customer.bookings.show', $booking),
                        actionText: 'Lihat Detail Pemesanan',
                        branchId: $booking->branch_id,
                        metadata: ['booking_id' => $booking->id, 'booking_code' => $booking->code]
                    );
                }
            } catch (\Throwable $e) {}
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.sales.bookings.show', $booking)
            ->with('success', "Pemesanan kendaraan {$booking->code} berhasil dikonfirmasi dan stok unit telah dialokasikan.");
    }

    /**
     * Cancel vehicle booking & release allocated inventory stock safely.
     */
    public function cancel(Request $request, VehicleBooking $booking): RedirectResponse
    {
        Gate::authorize('cancel', $booking);

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        $reason = $request->input('cancellation_reason');
        $userId = Auth::id();

        DB::transaction(function () use ($booking, $reason, $userId) {
            $wasConfirmed = ($booking->status === 'confirmed');

            // Jika sebelumnya confirmed dan ada alokasi unit pada inventory, lepaskan alokasi stok
            if ($wasConfirmed && $booking->inventory_id) {
                $inventory = VehicleInventory::where('id', $booking->inventory_id)
                    ->lockForUpdate()
                    ->first();

                if ($inventory && $inventory->reserved_quantity > 0) {
                    $inventory->decrement('reserved_quantity', 1);

                    InventoryMovement::create([
                        'vehicle_inventory_id' => $inventory->id,
                        'user_id' => $userId,
                        'movement_type' => 'release',
                        'quantity' => -1,
                        'stock_before' => $inventory->stock_quantity,
                        'stock_after' => $inventory->stock_quantity,
                        'reason' => "Pelepasan alokasi unit pembatalan pemesanan {$booking->code}. Alasan: {$reason}",
                        'created_at' => now(),
                    ]);
                }
            }

            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            $booking->salesOpportunity->recordActivity(
                'booking_cancelled',
                "Pemesanan unit {$booking->code} dibatalkan. Alasan: {$reason}",
                ['booking_code' => $booking->code, 'released_allocation' => $wasConfirmed],
                $userId
            );
        });

        try {
            if ($booking->customer) {
                app(NotificationService::class)->notifyUser(
                    user: $booking->customer,
                    title: 'Pemesanan Kendaraan (SPK) Dibatalkan',
                    message: "Pemesanan unit {$booking->vehicle->name} (SPK: {$booking->code}) telah dibatalkan. Alasan: {$reason}",
                    category: 'vehicle_booking',
                    actionUrl: route('customer.bookings.show', $booking),
                    branchId: $booking->branch_id,
                    metadata: ['booking_id' => $booking->id, 'booking_code' => $booking->code]
                );
            }
        } catch (\Throwable $e) {}

        return redirect()->route('admin.sales.bookings.show', $booking)
            ->with('success', "Pemesanan {$booking->code} berhasil dibatalkan dan alokasi stok telah dilepaskan.");
    }

    /**
     * Complete delivery of vehicle booking unit.
     */
    public function complete(Request $request, VehicleBooking $booking): RedirectResponse
    {
        Gate::authorize('update', $booking);

        if ($booking->status !== 'confirmed') {
            return back()->with('error', 'Hanya pemesanan yang telah dikonfirmasi yang dapat diselesaikan.');
        }

        $userId = Auth::id();

        DB::transaction(function () use ($booking, $userId) {
            if ($booking->inventory_id) {
                $inventory = VehicleInventory::where('id', $booking->inventory_id)
                    ->lockForUpdate()
                    ->first();

                if ($inventory) {
                    $stockBefore = $inventory->stock_quantity;
                    $stockAfter = max(0, $stockBefore - 1);
                    $reservedAfter = max(0, $inventory->reserved_quantity - 1);

                    $inventory->update([
                        'stock_quantity' => $stockAfter,
                        'reserved_quantity' => $reservedAfter,
                    ]);

                    InventoryMovement::create([
                        'vehicle_inventory_id' => $inventory->id,
                        'user_id' => $userId,
                        'movement_type' => 'adjustment_out',
                        'quantity' => -1,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reason' => "Serah terima unit fisik kendaraan pemesanan {$booking->code}.",
                        'created_at' => now(),
                    ]);
                }
            }

            $booking->update([
                'status' => 'completed',
            ]);

            $opportunity = $booking->salesOpportunity;
            $opportunity->update([
                'stage' => 'closed_won',
                'closed_at' => now(),
            ]);

            $opportunity->recordActivity(
                'vehicle_delivered',
                "Unit kendaraan untuk booking {$booking->code} telah diserahterimakan kepada pelanggan. Tahap penjualan Closed Won.",
                ['booking_code' => $booking->code],
                $userId
            );
        });

        return redirect()->route('admin.sales.bookings.show', $booking)
            ->with('success', "Pemesanan {$booking->code} selesai. Unit kendaraan telah diserahkan dan stok fisik diperbarui.");
    }

    /**
     * Update internal payment record status.
     */
    public function updatePaymentStatus(Request $request, VehicleBooking $booking): RedirectResponse
    {
        Gate::authorize('update', $booking);

        $request->validate([
            'payment_status' => ['required', 'string', 'in:unpaid,verified,refunded'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $status = $request->input('payment_status');

        $booking->update([
            'payment_status' => $status,
            'notes' => $request->filled('notes') ? $request->input('notes') : $booking->notes,
        ]);

        $booking->salesOpportunity->recordActivity(
            'payment_status_updated',
            "Status pencatatan tanda jadi booking {$booking->code} diubah menjadi [{$booking->payment_status_label}].",
            ['payment_status' => $status],
            Auth::id()
        );

        return back()->with('success', "Status pembayaran internal untuk {$booking->code} berhasil diperbarui.");
    }
}

