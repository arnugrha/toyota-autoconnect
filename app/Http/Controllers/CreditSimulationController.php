<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalculateCreditSimulationRequest;
use App\Http\Requests\StoreCreditSimulationRequest;
use App\Models\CreditSimulation;
use App\Models\LeasingProvider;
use App\Models\Vehicle;
use App\Services\CreditSimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CreditSimulationController extends Controller
{
    public function __construct(
        protected CreditSimulationService $simulationService
    ) {}

    /**
     * AJAX endpoint to calculate credit estimation in real-time.
     */
    public function calculate(CalculateCreditSimulationRequest $request): JsonResponse
    {
        $vehicle = Vehicle::findOrFail($request->input('vehicle_id'));
        $dp = (int) $request->input('down_payment');
        $tenor = (int) $request->input('tenor_months');

        $provider = null;
        if ($request->filled('leasing_provider_id')) {
            $provider = LeasingProvider::find($request->input('leasing_provider_id'));
        }

        $rate = (float) ($request->input('interest_rate') ?? ($provider?->base_interest_rate ?? 6.00));

        $result = $this->simulationService->calculate(
            vehiclePrice: $vehicle->price,
            downPayment: $dp,
            tenorMonths: $tenor,
            interestRate: $rate,
        );

        return response()->json([
            'success' => true,
            'data' => array_merge($result, [
                'formatted_vehicle_price' => 'Rp ' . number_format($result['vehicle_price'], 0, ',', '.'),
                'formatted_down_payment' => 'Rp ' . number_format($result['down_payment'], 0, ',', '.'),
                'formatted_financing_amount' => 'Rp ' . number_format($result['financing_amount'], 0, ',', '.'),
                'formatted_total_interest' => 'Rp ' . number_format($result['total_interest'], 0, ',', '.'),
                'formatted_total_credit_payment' => 'Rp ' . number_format($result['total_credit_payment'], 0, ',', '.'),
                'formatted_monthly_installment' => 'Rp ' . number_format($result['monthly_installment'], 0, ',', '.'),
                'provider_name' => $provider?->name ?? 'Estimasi Standar',
            ]),
        ]);
    }

    /**
     * Store a calculated simulation for the authenticated customer.
     */
    public function store(StoreCreditSimulationRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $vehicle = Vehicle::findOrFail($request->input('vehicle_id'));

        $provider = null;
        if ($request->filled('leasing_provider_id')) {
            $provider = LeasingProvider::find($request->input('leasing_provider_id'));
        }

        $simulation = $this->simulationService->saveSimulation(
            user: $user,
            vehicle: $vehicle,
            downPayment: (int) $request->input('down_payment'),
            tenorMonths: (int) $request->input('tenor_months'),
            interestRate: $request->filled('interest_rate') ? (float) $request->input('interest_rate') : null,
            provider: $provider,
            notes: $request->input('notes'),
        );

        return redirect()->route('customer.simulations.show', $simulation)
            ->with('success', 'Simulasi kredit berhasil disimpan ke akun Anda.');
    }

    /**
     * Display customer's saved credit simulations list.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', CreditSimulation::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = CreditSimulation::with(['vehicle', 'leasingProvider'])
            ->where('user_id', $user->id)
            ->latest();

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->input('vehicle_id'));
        }

        $simulations = $query->paginate(10)->withQueryString();

        return view('customer.simulations.index', [
            'simulations' => $simulations,
        ]);
    }

    /**
     * Display a specific saved simulation.
     */
    public function show(CreditSimulation $simulation): View
    {
        Gate::authorize('view', $simulation);

        $simulation->load(['vehicle', 'leasingProvider']);
        $providers = LeasingProvider::active()->orderBy('name')->get();

        return view('customer.simulations.show', [
            'simulation' => $simulation,
            'providers' => $providers,
        ]);
    }

    /**
     * Compare 2 or more simulations.
     */
    public function compare(Request $request): View|RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids) || count($ids) < 2) {
            return redirect()->route('customer.simulations.index')
                ->with('error', 'Pilih minimal 2 simulasi untuk dibandingkan.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $simulations = CreditSimulation::with(['vehicle', 'leasingProvider'])
            ->where('user_id', $user->id)
            ->whereIn('id', $ids)
            ->take(4)
            ->get();

        if ($simulations->count() < 2) {
            return redirect()->route('customer.simulations.index')
                ->with('error', 'Simulasi yang dipilih tidak valid atau tidak ditemukan.');
        }

        $comparisonData = $this->simulationService->compareSimulations($simulations);

        return view('customer.simulations.compare', [
            'simulations' => $simulations,
            'comparisonData' => $comparisonData,
        ]);
    }

    /**
     * Delete a saved credit simulation.
     */
    public function destroy(CreditSimulation $simulation): RedirectResponse
    {
        Gate::authorize('delete', $simulation);

        $simulation->delete();

        return redirect()->route('customer.simulations.index')
            ->with('success', 'Simulasi kredit berhasil dihapus.');
    }
}

