<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CrmController extends Controller
{
    /**
     * Display a listing of CRM leads and overall pipeline statistics.
     */
    public function index(Request $request): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $canViewTeam = $currentUser->can('leads.view-team');

        // Base query with permission-based scoping
        $baseQuery = Lead::query();
        if (! $canViewTeam) {
            // Sales Executive is strictly scoped to leads assigned to them
            $baseQuery->where('assigned_to', $currentUser->id);
        }

        // Live Pipeline Metrics calculated from database within allowed scope
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'new' => (clone $baseQuery)->where('status', 'new')->count(),
            'in_progress' => (clone $baseQuery)->whereIn('status', ['contacted', 'interested', 'test_drive', 'negotiation', 'booking'])->count(),
            'deal' => (clone $baseQuery)->where('status', 'deal')->count(),
            'lost' => (clone $baseQuery)->where('status', 'lost')->count(),
            'overdue' => (clone $baseQuery)
                ->where('next_follow_up_at', '<', now())
                ->whereNotIn('status', ['deal', 'lost'])
                ->count(),
        ];

        // Filterable query
        $query = (clone $baseQuery)->with(['customer', 'vehicle', 'assignedSales'])->latest('last_activity_at');

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('lead_number', 'like', "%{$keyword}%")
                    ->orWhereHas('customer', function ($cq) use ($keyword) {
                        $cq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('vehicle', function ($vq) use ($keyword) {
                        $vq->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($canViewTeam && $request->filled('assigned_to')) {
            if ($request->input('assigned_to') === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->input('assigned_to'));
            }
        }

        if ($request->input('filter') === 'overdue') {
            $query->where('next_follow_up_at', '<', now())
                ->whereNotIn('status', ['deal', 'lost']);
        }

        $leads = $query->paginate(15)->withQueryString();

        // Eligible sales for team filter
        $salesTeam = $canViewTeam
            ? User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Sales Executive', 'Sales Manager']))->orderBy('name')->get()
            : collect();

        return view('admin.crm.index', [
            'leads' => $leads,
            'stats' => $stats,
            'canViewTeam' => $canViewTeam,
            'salesTeam' => $salesTeam,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', ''),
                'assigned_to' => $request->input('assigned_to', ''),
                'filter' => $request->input('filter', ''),
            ],
        ]);
    }

    /**
     * Display detailed information of a specific lead.
     */
    public function show(Lead $lead): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Scope check: If user cannot view team leads, they must be assigned to this lead
        if (! $currentUser->can('leads.view-team') && $lead->assigned_to !== $currentUser->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka prospek pelanggan ini.');
        }

        $lead->load(['customer', 'vehicle.images', 'inquiry', 'assignedSales', 'activities.user']);

        // Eligible staff members for lead assignment
        $eligibleSales = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Sales Executive', 'Sales Manager']))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.crm.show', [
            'lead' => $lead,
            'eligibleSales' => $eligibleSales,
            'canAssign' => $currentUser->can('leads.assign'),
            'canChangeStatus' => $currentUser->can('leads.change-status'),
            'canAddActivity' => $currentUser->can('leads.add-activity'),
        ]);
    }

    /**
     * Update the pipeline status of a lead.
     */
    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->can('leads.view-team') && $lead->assigned_to !== $currentUser->id) {
            abort(403, 'Aksi dilarang: Anda tidak berwenang memperbarui prospek ini.');
        }

        $validStatuses = ['new', 'contacted', 'interested', 'test_drive', 'negotiation', 'booking', 'deal', 'lost'];

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in($validStatuses)],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $previousStatus = $lead->status;
        $newStatus = $validated['status'];

        if ($previousStatus === $newStatus) {
            return back()->with('info', 'Status prospek tidak mengalami perubahan.');
        }

        // Logical transition check: A previously lost lead cannot be marked as Deal without explanation
        if ($previousStatus === 'lost' && $newStatus === 'deal' && empty($validated['note'])) {
            return back()->withErrors([
                'note' => 'Perubahan dari status Batal/Lost langsung ke Deal wajib menyertakan catatan penjelasan.',
            ]);
        }

        $lead->update([
            'status' => $newStatus,
            'last_activity_at' => now(),
        ]);

        // If lead originates from inquiry and reaches advanced stages, update inquiry status
        if ($lead->inquiry_id && in_array($newStatus, ['booking', 'deal'])) {
            $lead->inquiry?->update(['status' => 'answered']);
        } elseif ($lead->inquiry_id && $lead->inquiry?->status === 'new' && $newStatus !== 'new') {
            $lead->inquiry?->update(['status' => 'in_progress']);
        }

        // Append to activity log
        $desc = 'Status prospek diperbarui dari '.$this->getStatusLabel($previousStatus).' menjadi '.$this->getStatusLabel($newStatus).'.';
        if (! empty($validated['note'])) {
            $desc .= ' Catatan: '.$validated['note'];
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $currentUser->id,
            'activity_type' => 'status_changed',
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'description' => $desc,
            'created_at' => now(),
        ]);

        return back()->with('success', "Status prospek berhasil diperbarui menjadi {$this->getStatusLabel($newStatus)}.");
    }

    /**
     * Assign or reassign lead to a Sales Executive.
     */
    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);

        $targetUser = User::findOrFail($validated['assigned_to']);

        // Security check: Only staff roles can be assigned, NEVER Customers
        if ($targetUser->isCustomer()) {
            return back()->withErrors([
                'assigned_to' => 'Customer tidak dapat ditugaskan sebagai sales penanggung jawab.',
            ]);
        }

        $previousSalesName = $lead->assignedSales?->name ?? 'Belum Ditugaskan';
        $lead->update([
            'assigned_to' => $targetUser->id,
            'last_activity_at' => now(),
        ]);

        // Append to activity log
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'assigned',
            'description' => "Penugasan sales dialihkan dari [{$previousSalesName}] kepada [{$targetUser->name}].",
            'previous_status' => null,
            'new_status' => null,
            'created_at' => now(),
        ]);

        return back()->with('success', "Prospek berhasil ditugaskan kepada Sales Executive: {$targetUser->name}.");
    }

    /**
     * Add a follow-up note or schedule a future follow-up for a lead.
     */
    public function addActivity(Request $request, Lead $lead): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->can('leads.view-team') && $lead->assigned_to !== $currentUser->id) {
            abort(403, 'Aksi dilarang: Anda tidak berwenang menambahkan aktivitas untuk prospek ini.');
        }

        $validated = $request->validate([
            'activity_type' => ['required', 'string', Rule::in(['follow_up_note', 'contacted', 'inquiry_responded'])],
            'description' => ['required', 'string', 'max:3000'],
            'next_follow_up_at' => ['nullable', 'date'],
        ], [
            'description.required' => 'Catatan aktivitas tidak boleh kosong.',
        ]);

        $lead->update([
            'last_activity_at' => now(),
            'next_follow_up_at' => $validated['next_follow_up_at'] ?? $lead->next_follow_up_at,
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $currentUser->id,
            'activity_type' => $validated['activity_type'],
            'description' => $validated['description'],
            'previous_status' => null,
            'new_status' => null,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Catatan aktivitas follow-up berhasil ditambahkan.');
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'new' => 'Prospek Baru',
            'contacted' => 'Sudah Dihubungi',
            'interested' => 'Berminat / Hot Lead',
            'test_drive' => 'Jadwal Test Drive',
            'negotiation' => 'Negosiasi Penawaran',
            'booking' => 'SPK / Booking Fee',
            'deal' => 'Deal (Penjualan)',
            'lost' => 'Batal / Lost',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
