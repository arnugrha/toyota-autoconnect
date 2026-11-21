<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Services\ServiceOrder\ServiceOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class AdminServiceOrderController extends Controller
{
    public function __construct(
        protected ServiceOrderService $serviceOrderService
    ) {}

    /**
     * Display a listing of all service work orders.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ServiceOrder::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $baseQuery = ServiceOrder::query();

        if ($user->branch_id && ! $user->hasRole('Super Admin')) {
            $baseQuery->where('branch_id', $user->branch_id);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'in_progress' => (clone $baseQuery)->whereIn('status', ['vehicle_received', 'inspection', 'in_progress', 'waiting_parts'])->count(),
            'inspection' => (clone $baseQuery)->where('status', 'inspection')->count(),
            'waiting_parts' => (clone $baseQuery)->where('status', 'waiting_parts')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'delivered' => (clone $baseQuery)->where('status', 'delivered')->count(),
        ];

        $query = (clone $baseQuery)->with(['user', 'branch', 'serviceType', 'vehicle', 'serviceAdvisor'])
            ->latest();

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('order_number', 'like', "%{$keyword}%")
                    ->orWhere('license_plate', 'like', "%{$keyword}%")
                    ->orWhere('vehicle_name', 'like', "%{$keyword}%")
                    ->orWhere('technician_name', 'like', "%{$keyword}%")
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

        $orders = $query->paginate(12)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.service-orders.index', [
            'orders' => $orders,
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
     * Display service order detail.
     */
    public function show(ServiceOrder $serviceOrder): View
    {
        Gate::authorize('view', $serviceOrder);

        $serviceOrder->load([
            'user',
            'branch',
            'serviceType',
            'vehicle',
            'serviceAdvisor',
            'serviceBooking',
            'items',
            'logs.user',
        ]);

        return view('admin.service-orders.show', [
            'order' => $serviceOrder,
        ]);
    }

    /**
     * Update status of service order.
     */
    public function updateStatus(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        Gate::authorize('manage', $serviceOrder);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:scheduled,confirmed,vehicle_received,inspection,in_progress,waiting_parts,completed,delivered,cancelled'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->serviceOrderService->updateStatus(
                order: $serviceOrder,
                newStatus: $validated['status'],
                notes: $validated['notes'] ?? null,
                actor: $user
            );

            return redirect()->route('admin.service-orders.show', $serviceOrder)
                ->with('success', "Status Service Order {$serviceOrder->order_number} berhasil diperbarui menjadi '{$serviceOrder->fresh()->status_label}'.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Record technician inspection results & recommendations.
     */
    public function recordInspection(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        Gate::authorize('manage', $serviceOrder);

        $validated = $request->validate([
            'inspection_result' => ['required', 'string'],
            'repair_actions' => ['nullable', 'string'],
            'technician_name' => ['nullable', 'string', 'max:100'],
            'current_mileage' => ['nullable', 'integer', 'min:0'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->serviceOrderService->recordInspection($serviceOrder, $validated, $user);

        return redirect()->route('admin.service-orders.show', $serviceOrder)
            ->with('success', 'Hasil inspeksi teknisi berhasil disimpan.');
    }

    /**
     * Add spare part or labor item to work order.
     */
    public function addItem(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        Gate::authorize('manage', $serviceOrder);

        $validated = $request->validate([
            'item_type' => ['required', 'in:part,labor'],
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $this->serviceOrderService->addItem($serviceOrder, $validated);

        return redirect()->route('admin.service-orders.show', $serviceOrder)
            ->with('success', 'Item suku cadang / jasa servis berhasil ditambahkan.');
    }

    /**
     * Remove item from work order.
     */
    public function removeItem(ServiceOrder $serviceOrder, ServiceOrderItem $item): RedirectResponse
    {
        Gate::authorize('manage', $serviceOrder);

        if ($item->service_order_id !== $serviceOrder->id) {
            abort(404);
        }

        $this->serviceOrderService->removeItem($serviceOrder, $item);

        return redirect()->route('admin.service-orders.show', $serviceOrder)
            ->with('success', 'Item berhasil dihapus dari Service Order.');
    }
}

