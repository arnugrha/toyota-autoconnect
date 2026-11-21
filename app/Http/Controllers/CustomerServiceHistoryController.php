<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerServiceHistoryController extends Controller
{
    /**
     * Display a listing of completed service orders for customer's vehicles.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = ServiceOrder::with(['branch', 'serviceType', 'vehicle'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->latest('delivered_at');

        if ($request->filled('license_plate')) {
            $query->where('license_plate', 'like', '%' . $request->input('license_plate') . '%');
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
            'items',
            'logs.user',
        ]);

        return view('customer.service-histories.show', [
            'order' => $serviceOrder,
        ]);
    }
}

