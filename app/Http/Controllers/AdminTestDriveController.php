<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTestDriveStatusRequest;
use App\Models\Branch;
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

class AdminTestDriveController extends Controller
{
    /**
     * Display a listing of test drive bookings for staff with filters & calendar view.
     */
    public function index(Request $request): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $canManageAll = $currentUser->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager', 'CRM Officer'])
            || $currentUser->can('leads.view-team');

        $baseQuery = TestDriveBooking::with(['vehicle', 'branch', 'assignedSales', 'user']);

        if (! $canManageAll) {
            $baseQuery->where(function ($q) use ($currentUser) {
                $q->where('assigned_to', $currentUser->id)
                  ->orWhereNull('assigned_to');
            });
        }

        // Metrics from DB
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->whereIn('status', ['cancelled', 'rejected', 'no_show'])->count(),
        ];

        // Filters
        $query = (clone $baseQuery);

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('code', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%")
                    ->orWhere('customer_email', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->input('vehicle_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->input('date'));
        }

        $viewMode = $request->input('view', 'list');

        // Fetch calendar entries if calendar view requested
        $calendarBookings = collect();
        if ($viewMode === 'calendar') {
            $calendarBookings = (clone $query)->orderBy('scheduled_at')->get();
        }

        $bookings = $query->latest('scheduled_at')->paginate(12)->withQueryString();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $vehicles = Vehicle::orderBy('name')->get();

        return view('admin.test_drives.index', [
            'bookings' => $bookings,
            'calendarBookings' => $calendarBookings,
            'stats' => $stats,
            'branches' => $branches,
            'vehicles' => $vehicles,
            'viewMode' => $viewMode,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', 'all'),
                'branch_id' => $request->input('branch_id', ''),
                'vehicle_id' => $request->input('vehicle_id', ''),
                'date' => $request->input('date', ''),
            ],
        ]);
    }

    /**
     * Display the specified test drive booking detail.
     */
    public function show(TestDriveBooking $booking): View
    {
        Gate::authorize('view', $booking);

        $booking->load([
            'vehicle.categoryRelation',
            'branch',
            'user',
            'lead.activities',
            'assignedSales',
            'confirmedByUser',
            'cancelledByUser',
        ]);

        // Sales consultants for assignment dropdown
        $salesConsultants = User::role(['Sales Executive', 'Sales Manager'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.test_drives.show', [
            'booking' => $booking,
            'salesConsultants' => $salesConsultants,
        ]);
    }

    /**
     * Update status of test drive booking (confirm, reject, complete, cancel, no_show).
     */
    public function updateStatus(UpdateTestDriveStatusRequest $request, TestDriveBooking $booking): RedirectResponse
    {
        Gate::authorize('manage', $booking);

        $validated = $request->validated();
        $userId = Auth::id();
        $userName = Auth::user()->name;

        DB::transaction(function () use ($booking, $validated, $userId, $userName) {
            $newStatus = $validated['status'];
            $previousStatus = $booking->status;

            $updateData = [
                'status' => $newStatus,
                'staff_note' => $validated['staff_note'] ?? $booking->staff_note,
            ];

            if (! empty($validated['assigned_to'])) {
                $updateData['assigned_to'] = $validated['assigned_to'];
            }

            if ($newStatus === 'confirmed') {
                $updateData['confirmed_at'] = now();
                $updateData['confirmed_by'] = $userId;
            } elseif ($newStatus === 'completed') {
                $updateData['completed_at'] = now();
            } elseif ($newStatus === 'rejected') {
                $updateData['rejection_reason'] = $validated['reason'];
            } elseif ($newStatus === 'cancelled') {
                $updateData['cancelled_at'] = now();
                $updateData['cancelled_by'] = $userId;
                $updateData['cancellation_reason'] = $validated['reason'];
            }

            $booking->update($updateData);

            // Audit Trail & CRM Lead Integration
            if ($booking->lead) {
                $statusLabels = [
                    'confirmed' => 'Jadwal Dikonfirmasi',
                    'completed' => 'Test Drive Selesai',
                    'rejected' => 'Pengajuan Ditolak',
                    'cancelled' => 'Jadwal Dibatalkan',
                    'no_show' => 'Pelanggan Tidak Hadir',
                ];

                $label = $statusLabels[$newStatus] ?? $newStatus;
                $desc = "Status jadwal test drive ({$booking->code}) diubah menjadi [{$label}] oleh {$userName}.";
                if (! empty($validated['reason'])) {
                    $desc .= " Catatan/Alasan: {$validated['reason']}";
                }

                LeadActivity::create([
                    'lead_id' => $booking->lead_id,
                    'user_id' => $userId,
                    'activity_type' => 'test_drive_'.$newStatus,
                    'description' => $desc,
                    'previous_status' => $booking->lead->status,
                    'new_status' => $booking->lead->status,
                    'created_at' => now(),
                ]);

                // Sync assignment to lead if lead was unassigned and booking is assigned
                if (! empty($validated['assigned_to']) && ! $booking->lead->assigned_to) {
                    $booking->lead->update(['assigned_to' => $validated['assigned_to']]);
                }
            }

            // Sync with Sales Opportunity if exists
            $opportunity = SalesOpportunity::where('user_id', $booking->user_id)
                ->where('vehicle_id', $booking->vehicle_id)
                ->whereNotIn('stage', ['closed_won', 'closed_lost'])
                ->first();

            if ($opportunity) {
                $opportunity->recordActivity(
                    'test_drive_status_updated',
                    "Jadwal test drive {$booking->code} diubah menjadi: {$booking->status_label}.",
                    ['booking_id' => $booking->id, 'new_status' => $newStatus],
                    $userId
                );
            }
        });

        try {
            if ($booking->user) {
                $statusLabels = [
                    'confirmed' => 'Jadwal Test Drive Dikonfirmasi',
                    'completed' => 'Test Drive Telah Selesai',
                    'rejected' => 'Pengajuan Test Drive Ditolak',
                    'cancelled' => 'Jadwal Test Drive Dibatalkan',
                ];
                $newStatus = $validated['status'];
                if (isset($statusLabels[$newStatus])) {
                    app(NotificationService::class)->notifyUser(
                        user: $booking->user,
                        title: $statusLabels[$newStatus],
                        message: "Status jadwal test drive unit {$booking->vehicle->name} (Kode: {$booking->code}) diubah menjadi {$newStatus}." . (!empty($validated['reason']) ? " Catatan: {$validated['reason']}" : ''),
                        category: 'test_drive',
                        actionUrl: route('customer.test-drives.show', $booking),
                        actionText: 'Lihat Jadwal Test Drive',
                        branchId: $booking->branch_id,
                        metadata: ['booking_id' => $booking->id, 'booking_code' => $booking->code]
                    );
                }
            }
        } catch (\Throwable $e) {}

        return redirect()->route('admin.test-drives.show', $booking)
            ->with('success', "Status jadwal test drive {$booking->code} berhasil diperbarui.");
    }
}

