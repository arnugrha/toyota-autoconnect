<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalesOfferRequest;
use App\Models\SalesOffer;
use App\Models\SalesOpportunity;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminSalesOfferController extends Controller
{
    /**
     * Show form to create a new vehicle offer for an opportunity.
     */
    public function create(SalesOpportunity $opportunity): View
    {
        Gate::authorize('create', SalesOffer::class);

        $opportunity->load(['customer', 'vehicle', 'branch']);

        return view('admin.sales.offers.create', [
            'opportunity' => $opportunity,
        ]);
    }

    /**
     * Store new vehicle offer with price snapshotting and backend calculations.
     */
    public function store(StoreSalesOfferRequest $request): RedirectResponse
    {
        Gate::authorize('create', SalesOffer::class);

        $validated = $request->validated();
        $opportunity = SalesOpportunity::with('vehicle')->findOrFail($validated['sales_opportunity_id']);

        $offer = DB::transaction(function () use ($opportunity, $validated) {
            $vehiclePrice = (int) ($opportunity->vehicle->price ?? 0);
            $discount = (int) ($validated['discount_amount'] ?? 0);
            $additional = (int) ($validated['additional_cost'] ?? 0);
            $total = max(0, $vehiclePrice - $discount + $additional);

            $offer = SalesOffer::create([
                'code' => SalesOffer::generateCode(),
                'sales_opportunity_id' => $opportunity->id,
                'customer_id' => $opportunity->user_id,
                'vehicle_id' => $opportunity->vehicle_id,
                'vehicle_price' => $vehiclePrice, // Price snapshot
                'discount_amount' => $discount,
                'additional_cost' => $additional,
                'total_amount' => $total,
                'valid_until' => $validated['valid_until'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Advance opportunity stage to presentation if still in prospecting/qualification
            if (in_array($opportunity->stage, ['prospecting', 'qualification'])) {
                $opportunity->update(['stage' => 'presentation']);
            }

            $opportunity->recordActivity(
                'offer_created',
                "Draf penawaran resmi {$offer->code} dibuat dengan total penawaran Rp ".number_format($total, 0, ',', '.').".",
                [
                    'offer_id' => $offer->id,
                    'offer_code' => $offer->code,
                    'total_amount' => $total,
                ],
                Auth::id()
            );

            return $offer;
        });

        return redirect()->route('admin.sales.opportunities.show', $opportunity)
            ->with('success', "Penawaran {$offer->code} berhasil dibuat sebagai Draf.");
    }

    /**
     * Display offer details.
     */
    public function show(SalesOffer $offer): View
    {
        Gate::authorize('view', $offer);

        $offer->load(['salesOpportunity.customer', 'salesOpportunity.branch', 'vehicle', 'createdBy']);

        return view('admin.sales.offers.show', [
            'offer' => $offer,
        ]);
    }

    /**
     * Send offer to customer.
     */
    public function send(SalesOffer $offer): RedirectResponse
    {
        Gate::authorize('update', $offer);

        DB::transaction(function () use ($offer) {
            $offer->update(['status' => 'sent']);

            $opportunity = $offer->salesOpportunity;
            if (in_array($opportunity->stage, ['prospecting', 'qualification', 'presentation'])) {
                $opportunity->update(['stage' => 'negotiation']);
            }

            $opportunity->recordActivity(
                'offer_sent',
                "Penawaran resmi {$offer->code} telah dikirimkan kepada pelanggan {$opportunity->customer->name}.",
                ['offer_code' => $offer->code],
                Auth::id()
            );
        });

        try {
            $customer = $offer->salesOpportunity?->customer;
            if ($customer) {
                app(NotificationService::class)->notifyUser(
                    user: $customer,
                    title: "Penawaran Resmi Unit {$offer->vehicle->name}",
                    message: "Kami telah mengirimkan penawaran resmi ({$offer->code}) untuk kendaraan Toyota {$offer->vehicle->name}. Silakan tinjau rincian penawaran unit dan aksesoris Anda.",
                    category: 'sales_offer',
                    actionUrl: route('customer.offers.show', $offer),
                    actionText: 'Tinjau Penawaran',
                    branchId: $offer->salesOpportunity?->branch_id,
                    metadata: ['offer_id' => $offer->id, 'offer_code' => $offer->code]
                );
            }
        } catch (\Throwable $e) {}

        return back()->with('success', "Penawaran {$offer->code} berhasil dikirim ke pelanggan.");
    }

    /**
     * Update offer status (accept / reject).
     */
    public function updateStatus(Request $request, SalesOffer $offer): RedirectResponse
    {
        Gate::authorize('update', $offer);

        $request->validate([
            'status' => ['required', 'string', 'in:accepted,rejected'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $status = $request->input('status');

        DB::transaction(function () use ($offer, $status, $request) {
            $offer->update([
                'status' => $status,
                'notes' => $request->filled('notes') ? $request->input('notes') : $offer->notes,
            ]);

            $opportunity = $offer->salesOpportunity;
            $opportunity->recordActivity(
                'offer_status_updated',
                "Status penawaran {$offer->code} diperbarui menjadi [{$offer->status_label}].",
                ['status' => $status, 'notes' => $request->input('notes')],
                Auth::id()
            );
        });

        return back()->with('success', "Status penawaran {$offer->code} berhasil diperbarui.");
    }
}

