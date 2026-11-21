<?php

namespace App\Http\Controllers;

use App\Models\SalesOffer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CustomerOfferController extends Controller
{
    /**
     * Display listing of vehicle offers sent to customer.
     */
    public function index(Request $request): View
    {
        $offers = SalesOffer::with(['vehicle.images', 'salesOpportunity.branch'])
            ->where('customer_id', Auth::id())
            ->whereIn('status', ['sent', 'accepted', 'rejected', 'expired'])
            ->latest()
            ->paginate(10);

        return view('customer.offers.index', [
            'offers' => $offers,
        ]);
    }

    /**
     * Display details of a specific offer for customer.
     */
    public function show(SalesOffer $offer): View
    {
        Gate::authorize('view', $offer);

        $offer->load(['vehicle.categoryRelation', 'salesOpportunity.branch', 'createdBy']);

        return view('customer.offers.show', [
            'offer' => $offer,
        ]);
    }
}

