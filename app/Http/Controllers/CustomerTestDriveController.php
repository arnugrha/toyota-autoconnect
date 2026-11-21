<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestDriveBookingRequest;
use App\Models\Branch;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\SalesOpportunity;
use App\Models\TestDriveBooking;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CustomerTestDriveController extends Controller
{
    /**
     * Display customer's test drive bookings list.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $query = TestDriveBooking::with(['vehicle', 'branch'])
            ->where('user_id', $user->id)
            ->latest('scheduled_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->paginate(10)->withQueryString();

        return view('customer.test_drives.index', [
            'user' => $user,
            'bookings' => $bookings,
            'currentStatus' => $request->input('status', ''),
        ]);
    }

    /**
     * Store a new test drive booking submitted by customer.
     */
    public function store(StoreTestDriveBookingRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validated();
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $branch = Branch::findOrFail($validated['branch_id']);

        $scheduledAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['scheduled_date'].' '.$validated['scheduled_time']
        );

        $booking = DB::transaction(function () use ($user, $vehicle, $branch, $scheduledAt, $validated) {
            // 1. Lock branch to prevent race condition on slot capacity
            $branchLocked = Branch::where('id', $branch->id)->lockForUpdate()->first();
            if (! $branchLocked->isSlotAvailable($scheduledAt)) {
                throw new \InvalidArgumentException('Slot waktu pada jam tersebut telah penuh di cabang ini. Silakan pilih slot jam lain.');
            }

            // 2. Integrasi CRM Lead: Cek atau buat Lead untuk customer & kendaraan ini
            $lead = Lead::where('customer_id', $user->id)
                ->where('vehicle_id', $vehicle->id)
                ->whereNotIn('status', ['deal', 'lost'])
                ->latest()
                ->first();

            $isNewLead = false;
            if (! $lead) {
                $isNewLead = true;
                $lead = Lead::create([
                    'lead_number' => Lead::generateLeadNumber(),
                    'customer_id' => $user->id,
                    'vehicle_id' => $vehicle->id,
                    'source' => 'test_drive_booking',
                    'status' => 'test_drive',
                    'notes' => 'Pengajuan test drive mandiri oleh pelanggan dari katalog website.',
                    'last_activity_at' => now(),
                ]);
            } else {
                // Perbarui status lead ke test_drive jika sebelumnya masih prospek baru atau contacted
                if (in_array($lead->status, ['new', 'contacted', 'interested'])) {
                    $prevStatus = $lead->status;
                    $lead->update([
                        'status' => 'test_drive',
                        'last_activity_at' => now(),
                    ]);

                    LeadActivity::create([
                        'lead_id' => $lead->id,
                        'user_id' => $user->id,
                        'activity_type' => 'status_changed',
                        'description' => "Status lead diperbarui otomatis menjadi Uji Kemudi (Test Drive) atas pengajuan jadwal pelanggan.",
                        'previous_status' => $prevStatus,
                        'new_status' => 'test_drive',
                        'created_at' => now(),
                    ]);
                }
            }

            // 3. Catat aktivitas Lead CRM
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'activity_type' => 'test_drive_requested',
                'description' => "Pelanggan mengajukan jadwal test drive kendaraan {$vehicle->name} pada {$scheduledAt->format('d M Y, H:i')} WIB di cabang {$branch->name}.",
                'previous_status' => $lead->status,
                'new_status' => $lead->status,
                'created_at' => now(),
            ]);

            // 4. Buat record TestDriveBooking dengan kode unik
            $booking = TestDriveBooking::create([
                'code' => TestDriveBooking::generateCode(),
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id,
                'branch_id' => $branch->id,
                'lead_id' => $lead->id,
                'assigned_to' => $lead->assigned_to,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => $branch->slot_duration_minutes ?: 60,
                'status' => 'pending',
                'customer_name' => $user->name,
                'customer_phone' => $user->phone ?? 'Belum diisi',
                'customer_email' => $user->email,
                'customer_note' => $validated['customer_note'] ?? null,
            ]);

            // 5. Hubungkan / Buat Sales Opportunity
            $opportunity = SalesOpportunity::where('user_id', $user->id)
                ->where('vehicle_id', $vehicle->id)
                ->where('branch_id', $branch->id)
                ->whereNotIn('stage', ['closed_won', 'closed_lost'])
                ->first();

            if (! $opportunity) {
                $opportunity = SalesOpportunity::create([
                    'code' => SalesOpportunity::generateCode(),
                    'lead_id' => $lead->id,
                    'user_id' => $user->id,
                    'vehicle_id' => $vehicle->id,
                    'branch_id' => $branch->id,
                    'assigned_to' => $lead->assigned_to,
                    'stage' => 'test_drive',
                    'estimated_value' => $vehicle->price ?? 0,
                    'notes' => 'Peluang penjualan dibuka otomatis dari pengajuan test drive online.',
                ]);

                $opportunity->recordActivity(
                    'opportunity_created',
                    "Peluang penjualan dibuat dari booking test drive {$booking->code}.",
                    ['booking_code' => $booking->code],
                    $user->id
                );
            } else {
                $opportunity->recordActivity(
                    'test_drive_linked',
                    "Jadwal test drive {$booking->code} diajukan untuk tanggal {$scheduledAt->format('d M Y, H:i')} WIB.",
                    ['booking_code' => $booking->code],
                    $user->id
                );
            }

            return $booking;
        });

        try {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyUser(
                user: $user,
                title: 'Pengajuan Test Drive Diterima',
                message: "Pengajuan test drive unit {$vehicle->name} (Kode: {$booking->code}) untuk tanggal {$scheduledAt->format('d M Y, H:i')} WIB di cabang {$branch->name} telah berhasil dicatat.",
                category: 'test_drive',
                actionUrl: route('customer.test-drives.show', $booking),
                actionText: 'Lihat Jadwal Test Drive',
                branchId: $branch->id,
                metadata: ['booking_id' => $booking->id, 'booking_code' => $booking->code]
            );

            $notificationService->notifyRole(
                roleName: 'Sales Executive',
                title: 'Permintaan Test Drive Baru',
                message: "Pengajuan test drive baru ({$booking->code}) dari {$user->name} untuk unit {$vehicle->name} pada {$scheduledAt->format('d/m/Y H:i')}.",
                category: 'test_drive',
                branchId: $branch->id,
                actionUrl: route('admin.test-drives.show', $booking)
            );
        } catch (\Throwable $e) {}

        return redirect()->route('customer.test-drives.show', $booking)
            ->with('success', "Pengajuan jadwal test drive Anda ({$booking->code}) berhasil dikirim. Tim kami akan segera memverifikasi jadwal Anda.");
    }

    /**
     * Show detail of customer's test drive booking.
     */
    public function show(TestDriveBooking $booking): View
    {
        Gate::authorize('view', $booking);

        $booking->load(['vehicle.categoryRelation', 'branch', 'assignedSales', 'confirmedByUser']);

        return view('customer.test_drives.show', [
            'booking' => $booking,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Cancel a test drive booking by customer.
     */
    public function cancel(Request $request, TestDriveBooking $booking): RedirectResponse
    {
        Gate::authorize('cancel', $booking);

        $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($booking, $request) {
            $reason = $request->input('cancellation_reason', 'Dibatalkan langsung oleh pelanggan melalui Customer Portal.');

            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancellation_reason' => $reason,
            ]);

            if ($booking->lead) {
                LeadActivity::create([
                    'lead_id' => $booking->lead_id,
                    'user_id' => Auth::id(),
                    'activity_type' => 'test_drive_cancelled',
                    'description' => "Pelanggan membatalkan jadwal test drive {$booking->code}. Alasan: {$reason}",
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()->route('customer.test-drives.show', $booking)
            ->with('success', 'Jadwal test drive berhasil dibatalkan.');
    }
}

