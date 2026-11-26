<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\User;
use App\Services\ServiceOrder\ServiceOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class CustomerServiceHistoryController extends Controller
{
    public function __construct(
        protected ServiceOrderService $serviceOrderService
    ) {}

    /**
     * Display a listing of completed service orders for customer's vehicles.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $query = ServiceOrder::with(['branch', 'serviceType', 'vehicle', 'items'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->latest('delivered_at')
            ->latest('completed_at')
            ->latest('id');

        if ($request->filled('license_plate')) {
            $query->where('license_plate', 'like', '%'.$request->input('license_plate').'%');
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('customer.service-histories.index', [
            'orders' => $orders,
            'currentPlate' => $request->input('license_plate'),
        ]);
    }

    /**
     * Display detail of a specific service history record / invoice.
     */
    public function show(ServiceOrder $serviceOrder): View
    {
        Gate::authorize('view', $serviceOrder);

        $serviceOrder->load([
            'branch',
            'serviceType',
            'vehicle',
            'serviceAdvisor',
            'serviceBooking',
            'items.sparePart',
            'items.serviceType',
            'logs.user',
        ]);

        return view('customer.service-histories.show', [
            'order' => $serviceOrder,
        ]);
    }

    /**
     * Customer approves a pending additional spare part item.
     */
    public function approveItem(Request $request, ServiceOrder $serviceOrder, ServiceOrderItem $item): RedirectResponse
    {
        if ($serviceOrder->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berwenang mengonfirmasi Work Order ini.');
        }

        if ($item->service_order_id !== $serviceOrder->id) {
            abort(404);
        }

        $validated = $request->validate([
            'customer_response_note' => ['nullable', 'string', 'max:300'],
        ]);

        /** @var User $customer */
        $customer = Auth::user();

        try {
            $this->serviceOrderService->approveItem(
                order: $serviceOrder,
                item: $item,
                customer: $customer,
                note: $validated['customer_response_note'] ?? null
            );

            return back()->with('success', "Tambahan suku cadang '{$item->name}' telah Anda setujui dan akan segera dipasang oleh teknisi kami.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Customer rejects a pending additional spare part item.
     */
    public function rejectItem(Request $request, ServiceOrder $serviceOrder, ServiceOrderItem $item): RedirectResponse
    {
        if ($serviceOrder->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berwenang mengonfirmasi Work Order ini.');
        }

        if ($item->service_order_id !== $serviceOrder->id) {
            abort(404);
        }

        $validated = $request->validate([
            'customer_response_note' => ['nullable', 'string', 'max:300'],
        ]);

        /** @var User $customer */
        $customer = Auth::user();

        try {
            $this->serviceOrderService->rejectItem(
                order: $serviceOrder,
                item: $item,
                customer: $customer,
                reason: $validated['customer_response_note'] ?? null
            );

            return back()->with('success', "Tambahan suku cadang '{$item->name}' telah ditolak dan tidak akan ditagihkan ke faktur Anda.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Customer approves all pending additional spare part items at once.
     */
    public function approveAllItems(ServiceOrder $serviceOrder): RedirectResponse
    {
        if ($serviceOrder->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berwenang mengonfirmasi Work Order ini.');
        }

        /** @var User $customer */
        $customer = Auth::user();

        try {
            $count = $this->serviceOrderService->approveAllPendingItems($serviceOrder, $customer);

            return back()->with('success', "Seluruh {$count} item suku cadang tambahan telah Anda setujui dan akan segera dikerjakan oleh teknisi.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
