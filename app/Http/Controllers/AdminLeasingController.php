<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLeasingApplicationStatusRequest;
use App\Models\LeasingApplication;
use App\Models\LeasingProvider;
use App\Services\Leasing\LeasingApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminLeasingController extends Controller
{
    public function __construct(
        protected LeasingApplicationService $leasingService
    ) {}

    /**
     * Display a listing of all leasing applications.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', LeasingApplication::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $baseQuery = LeasingApplication::query();

        // Scope to assigned sales if user is a Sales Executive
        if ($user->hasRole('Sales Executive') && ! $user->can('financing.manage')) {
            $baseQuery->whereHas('salesOpportunity', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'submitted' => (clone $baseQuery)->where('status', 'submitted')->count(),
            'processing' => (clone $baseQuery)->where('status', 'processing')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'need_revision' => (clone $baseQuery)->where('status', 'need_revision')->count(),
        ];

        $query = (clone $baseQuery)->with(['user', 'vehicle', 'leasingProvider', 'salesOpportunity.assignedSales'])
            ->latest();

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('code', 'like', "%{$keyword}%")
                    ->orWhere('provider_reference', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('vehicle', function ($vq) use ($keyword) {
                        $vq->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('leasing_provider_id')) {
            $query->where('leasing_provider_id', $request->input('leasing_provider_id'));
        }

        $applications = $query->paginate(12)->withQueryString();
        $providers = LeasingProvider::orderBy('name')->get();

        return view('admin.leasing.index', [
            'applications' => $applications,
            'stats' => $stats,
            'providers' => $providers,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', 'all'),
                'leasing_provider_id' => $request->input('leasing_provider_id', ''),
            ],
        ]);
    }

    /**
     * Show detail of a leasing application.
     */
    public function show(LeasingApplication $application): View
    {
        Gate::authorize('view', $application);

        $application->load([
            'user',
            'vehicle',
            'creditSimulation',
            'leasingProvider',
            'salesOpportunity.assignedSales',
            'logs.user',
        ]);

        return view('admin.leasing.show', [
            'application' => $application,
        ]);
    }

    /**
     * Update the status of a leasing application.
     */
    public function updateStatus(UpdateLeasingApplicationStatusRequest $request, LeasingApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->leasingService->updateStatus(
            application: $application,
            newStatus: $request->input('status'),
            actor: $user,
            notes: $request->input('notes'),
            revisionNotes: $request->input('revision_notes')
        );

        return redirect()->route('admin.leasing.show', $application)
            ->with('success', 'Status pengajuan pembiayaan berhasil diperbarui.');
    }

    /**
     * Force sync status from provider adapter.
     */
    public function syncProvider(LeasingApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $result = $this->leasingService->syncStatus($application, $user);

        $msgType = $result['success'] ? 'success' : 'error';

        return redirect()->route('admin.leasing.show', $application)
            ->with($msgType, $result['message']);
    }

    /**
     * Display master leasing providers.
     */
    public function providers(): View
    {
        $providers = LeasingProvider::withCount('applications')->orderBy('name')->get();

        return view('admin.leasing.providers', [
            'providers' => $providers,
        ]);
    }

    /**
     * Toggle active state of a leasing provider.
     */
    public function toggleProvider(LeasingProvider $provider): RedirectResponse
    {
        Gate::authorize('financing.manage');

        $provider->update([
            'is_active' => ! $provider->is_active,
        ]);

        $statusStr = $provider->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.leasing.providers')
            ->with('success', "Provider leasing {$provider->name} berhasil {$statusStr}.");
    }
}

