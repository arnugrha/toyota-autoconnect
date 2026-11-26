<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\ServiceType;
use App\Models\SparePart;
use App\Models\User;
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

        /** @var User $user */
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
            'items.sparePart',
            'items.serviceType',
            'logs.user',
        ]);

        $spareParts = SparePart::active()
            ->orderBy('name')
            ->get(['id', 'part_number', 'name', 'unit', 'selling_price', 'stock_quantity']);

        $serviceTypes = ServiceType::active()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'estimated_cost']);

        return view('admin.service-orders.show', [
            'order' => $serviceOrder,
            'spareParts' => $spareParts,
            'serviceTypes' => $serviceTypes,
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

        /** @var User $user */
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

        /** @var User $user */
        $user = Auth::user();

        $this->serviceOrderService->recordInspection($serviceOrder, $validated, $user);

        return redirect()->route('admin.service-orders.show', $serviceOrder)
            ->with('success', 'Hasil inspeksi teknisi berhasil disimpan.');
    }

    /**
     * Add spare part or labor item to work order strictly from master dropdown.
     */
    public function addItem(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        Gate::authorize('manage', $serviceOrder);

        $validated = $request->validate([
            'item_type' => ['required', 'in:part,labor'],
            'spare_part_id' => ['required_if:item_type,part', 'nullable', 'exists:spare_parts,id'],
            'service_type_id' => ['required_if:item_type,labor', 'nullable', 'exists:service_types,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'requires_approval' => ['boolean'],
        ], [
            'spare_part_id.required_if' => 'Silakan pilih suku cadang dari dropdown master sparepart.',
            'service_type_id.required_if' => 'Silakan pilih pekerjaan jasa dari dropdown master layanan.',
        ]);

        $validated['requires_approval'] = $validated['item_type'] === 'part'
            ? $request->boolean('requires_approval', true)
            : false;

        /** @var User $actor */
        $actor = Auth::user();

        try {
            $item = $this->serviceOrderService->addItem($serviceOrder, $validated, $actor);

            $message = $item->isPendingApproval()
                ? "Tambahan suku cadang '{$item->name}' berhasil ditambahkan dan sedang menunggu konfirmasi dari pelanggan."
                : "Item '{$item->name}' berhasil ditambahkan ke rincian Work Order.";

            return redirect()->route('admin.service-orders.show', $serviceOrder)
                ->with('success', $message);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
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

        /** @var User $actor */
        $actor = Auth::user();

        $this->serviceOrderService->removeItem($serviceOrder, $item, $actor);

        return redirect()->route('admin.service-orders.show', $serviceOrder)
            ->with('success', 'Item berhasil dihapus dari Service Order.');
    }

    /**
     * Mark the service order invoice as paid.
     */
    public function markPaid(ServiceOrder $serviceOrder): RedirectResponse
    {
        Gate::authorize('manage', $serviceOrder);

        /** @var User $actor */
        $actor = Auth::user();

        $order = $this->serviceOrderService->markAsPaid($serviceOrder, $actor);

        return redirect()->route('admin.service-orders.show', $order)
            ->with('success', "Invoice servis {$order->invoice_number} berhasil ditandai LUNAS.");
    }
}
