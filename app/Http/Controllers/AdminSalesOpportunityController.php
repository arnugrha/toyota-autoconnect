<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalesOpportunityRequest;
use App\Http\Requests\UpdateSalesOpportunityRequest;
use App\Models\Branch;
use App\Models\Lead;
use App\Models\SalesOpportunity;
use App\Models\TestDriveBooking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminSalesOpportunityController extends Controller
{
    /**
     * Display a listing of sales opportunities and sales pipeline metrics.
     */
    public function index(Request $request): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $canViewAll = $currentUser->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager', 'CRM Officer'])
            || $currentUser->can('leads.view-team');

        $baseQuery = SalesOpportunity::query();

        if (! $canViewAll) {
            $baseQuery->where('assigned_to', $currentUser->id);
        }

        // Live pipeline metrics per stage
        $stages = [
            'prospecting' => 'Prospek Awal',
            'qualification' => 'Kualifikasi Kebutuhan',
            'presentation' => 'Presentasi & Penawaran',
            'test_drive' => 'Uji Kemudi',
            'negotiation' => 'Negosiasi Harga',
            'booking' => 'Booking SPK',
            'closed_won' => 'Closed Won',
            'closed_lost' => 'Closed Lost',
        ];

        $stageCounts = [];
        $totalEstimatedValue = (clone $baseQuery)->whereNotIn('stage', ['closed_lost'])->sum('estimated_value');

        foreach (array_keys($stages) as $st) {
            $stageCounts[$st] = (clone $baseQuery)->where('stage', $st)->count();
        }

        // Filterable query
        $query = (clone $baseQuery)->with(['customer', 'vehicle', 'branch', 'assignedSales'])->latest('updated_at');

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('code', 'like', "%{$keyword}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$keyword}%")->orWhere('email', 'like', "%{$keyword}%"))
                    ->orWhereHas('vehicle', fn ($vq) => $vq->where('name', 'like', "%{$keyword}%"));
            });
        }

        if ($request->filled('stage') && $request->input('stage') !== 'all') {
            $query->where('stage', $request->input('stage'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->input('assigned_to'));
        }

        $opportunities = $query->paginate(12)->withQueryString();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $salesConsultants = User::role(['Sales Executive', 'Sales Manager'])->where('is_active', true)->orderBy('name')->get();

        return view('admin.sales.opportunities.index', [
            'opportunities' => $opportunities,
            'stageCounts' => $stageCounts,
            'stages' => $stages,
            'totalEstimatedValue' => $totalEstimatedValue,
            'branches' => $branches,
            'salesConsultants' => $salesConsultants,
            'filters' => [
                'q' => $request->input('q', ''),
                'stage' => $request->input('stage', 'all'),
                'branch_id' => $request->input('branch_id', ''),
                'assigned_to' => $request->input('assigned_to', ''),
            ],
        ]);
    }

    /**
     * Show form to create a new opportunity.
     */
    public function create(): View
    {
        Gate::authorize('create', SalesOpportunity::class);

        $vehicles = Vehicle::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $customers = User::role('Customer')->orderBy('name')->get();
        $salesConsultants = User::role(['Sales Executive', 'Sales Manager'])->where('is_active', true)->orderBy('name')->get();

        return view('admin.sales.opportunities.create', [
            'vehicles' => $vehicles,
            'branches' => $branches,
            'customers' => $customers,
            'salesConsultants' => $salesConsultants,
        ]);
    }

    /**
     * Store a newly created opportunity.
     */
    public function store(StoreSalesOpportunityRequest $request): RedirectResponse
    {
        Gate::authorize('create', SalesOpportunity::class);

        $validated = $request->validated();
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        $opportunity = DB::transaction(function () use ($validated, $vehicle) {
            $estValue = ! empty($validated['estimated_value']) ? $validated['estimated_value'] : ($vehicle->price ?? 0);

            $opportunity = SalesOpportunity::create([
                'code' => SalesOpportunity::generateCode(),
                'lead_id' => $validated['lead_id'] ?? null,
                'user_id' => $validated['user_id'],
                'vehicle_id' => $vehicle->id,
                'branch_id' => $validated['branch_id'],
                'assigned_to' => $validated['assigned_to'] ?? Auth::id(),
                'stage' => $validated['stage'] ?? 'prospecting',
                'estimated_value' => $estValue,
                'notes' => $validated['notes'] ?? null,
            ]);

            $opportunity->recordActivity(
                'opportunity_created',
                "Peluang penjualan dibuka dengan nilai estimasi Rp ".number_format($estValue, 0, ',', '.').".",
                ['stage' => $opportunity->stage],
                Auth::id()
            );

            return $opportunity;
        });

        return redirect()->route('admin.sales.opportunities.show', $opportunity)
            ->with('success', "Sales Opportunity {$opportunity->code} berhasil dibuat.");
    }

    /**
     * Display the specified opportunity.
     */
    public function show(SalesOpportunity $opportunity): View
    {
        Gate::authorize('view', $opportunity);

        $opportunity->load([
            'customer',
            'vehicle.categoryRelation',
            'branch',
            'lead.activities',
            'assignedSales',
            'activities.user',
            'offers.createdBy',
            'bookings.inventory',
        ]);

        // Related test drives for this customer and vehicle
        $testDrives = TestDriveBooking::where('user_id', $opportunity->user_id)
            ->where('vehicle_id', $opportunity->vehicle_id)
            ->latest('scheduled_at')
            ->get();

        $salesConsultants = User::role(['Sales Executive', 'Sales Manager'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.sales.opportunities.show', [
            'opportunity' => $opportunity,
            'testDrives' => $testDrives,
            'salesConsultants' => $salesConsultants,
        ]);
    }

    /**
     * Update opportunity stage, value, or assignment.
     */
    public function update(UpdateSalesOpportunityRequest $request, SalesOpportunity $opportunity): RedirectResponse
    {
        Gate::authorize('update', $opportunity);

        $validated = $request->validated();
        $userId = Auth::id();

        DB::transaction(function () use ($opportunity, $validated, $userId) {
            $prevStage = $opportunity->stage;
            $newStage = $validated['stage'] ?? $prevStage;

            $updateData = [];

            if (isset($validated['estimated_value'])) {
                $updateData['estimated_value'] = (int) $validated['estimated_value'];
            }

            if (array_key_exists('assigned_to', $validated)) {
                $updateData['assigned_to'] = $validated['assigned_to'];
            }

            if (isset($validated['notes'])) {
                $updateData['notes'] = $validated['notes'];
            }

            if ($newStage !== $prevStage) {
                $updateData['stage'] = $newStage;

                if ($newStage === 'closed_won') {
                    $updateData['closed_at'] = now();
                } elseif ($newStage === 'closed_lost') {
                    $updateData['closed_at'] = now();
                    $updateData['lost_reason'] = $validated['lost_reason'] ?? 'Dibatalkan oleh calon pembeli';
                }

                $opportunity->update($updateData);

                $opportunity->recordActivity(
                    'stage_changed',
                    "Tahap penjualan diubah dari [{$opportunity->stage_label}] ke [".match($newStage) {
                        'prospecting' => 'Prospek Awal',
                        'qualification' => 'Kualifikasi Kebutuhan',
                        'presentation' => 'Presentasi & Penawaran',
                        'test_drive' => 'Uji Kemudi',
                        'negotiation' => 'Negosiasi Harga',
                        'booking' => 'Booking Unit (SPK)',
                        'closed_won' => 'Penjualan Berhasil (Won)',
                        'closed_lost' => 'Batal / Kalah (Lost)',
                        default => $newStage,
                    }."].",
                    [
                        'previous_stage' => $prevStage,
                        'new_stage' => $newStage,
                        'lost_reason' => $validated['lost_reason'] ?? null,
                    ],
                    $userId
                );
            } else {
                $opportunity->update($updateData);

                $opportunity->recordActivity(
                    'details_updated',
                    "Informasi peluang penjualan diperbarui.",
                    $updateData,
                    $userId
                );
            }
        });

        return redirect()->route('admin.sales.opportunities.show', $opportunity)
            ->with('success', "Peluang penjualan {$opportunity->code} berhasil diperbarui.");
    }

    /**
     * Add internal note to opportunity audit log.
     */
    public function addNote(Request $request, SalesOpportunity $opportunity): RedirectResponse
    {
        Gate::authorize('update', $opportunity);

        $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $opportunity->recordActivity(
            'internal_note',
            $request->input('note'),
            null,
            Auth::id()
        );

        return redirect()->route('admin.sales.opportunities.show', $opportunity)
            ->with('success', 'Catatan aktivitas penjualan berhasil ditambahkan.');
    }
}

