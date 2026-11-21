<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeasingApplicationRequest;
use App\Models\CreditSimulation;
use App\Models\LeasingApplication;
use App\Models\LeasingProvider;
use App\Models\SalesOpportunity;
use App\Models\Vehicle;
use App\Services\CreditSimulationService;
use App\Services\Leasing\LeasingApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerLeasingApplicationController extends Controller
{
    public function __construct(
        protected LeasingApplicationService $leasingService,
        protected CreditSimulationService $simulationService
    ) {}

    /**
     * Display a listing of customer's leasing applications.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', LeasingApplication::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = LeasingApplication::with(['vehicle', 'leasingProvider'])
            ->where('user_id', $user->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $applications = $query->paginate(10)->withQueryString();

        return view('customer.leasing.index', [
            'applications' => $applications,
            'currentStatus' => $request->input('status'),
        ]);
    }

    /**
     * Show form to create a new leasing application.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', LeasingApplication::class);

        $selectedSimulation = null;
        if ($request->filled('simulation_id')) {
            $selectedSimulation = CreditSimulation::with(['vehicle', 'leasingProvider'])
                ->where('user_id', Auth::id())
                ->find($request->input('simulation_id'));
        }

        $selectedVehicle = null;
        if ($selectedSimulation) {
            $selectedVehicle = $selectedSimulation->vehicle;
        } elseif ($request->filled('vehicle_id')) {
            $selectedVehicle = Vehicle::find($request->input('vehicle_id'));
        }

        $vehicles = Vehicle::published()->orderBy('name')->get();
        if ($selectedVehicle && ! $vehicles->contains('id', $selectedVehicle->id)) {
            $vehicles->prepend($selectedVehicle);
        }

        $providers = LeasingProvider::active()->orderBy('name')->get();

        return view('customer.leasing.create', [
            'vehicles' => $vehicles,
            'providers' => $providers,
            'selectedSimulation' => $selectedSimulation,
            'selectedVehicle' => $selectedVehicle,
        ]);
    }

    /**
     * Store and submit a newly created leasing application.
     */
    public function store(StoreLeasingApplicationRequest $request): RedirectResponse
    {
        Gate::authorize('create', LeasingApplication::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $vehicle = Vehicle::findOrFail($request->input('vehicle_id'));
        $provider = LeasingProvider::findOrFail($request->input('leasing_provider_id'));

        $dp = (int) $request->input('down_payment');
        $tenor = (int) $request->input('tenor_months');

        // Calculate accurate financing figures
        $calculation = $this->simulationService->calculate(
            vehiclePrice: $vehicle->price,
            downPayment: $dp,
            tenorMonths: $tenor,
            interestRate: $provider->base_interest_rate,
        );

        // Find active sales opportunity if exists
        $salesOpportunity = SalesOpportunity::where('user_id', $user->id)
            ->where('vehicle_id', $vehicle->id)
            ->whereNotIn('stage', ['closed_won', 'closed_lost'])
            ->latest()
            ->first();

        // Create Application record
        $application = LeasingApplication::create([
            'code' => LeasingApplication::generateCode(),
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'credit_simulation_id' => $request->input('credit_simulation_id'),
            'leasing_provider_id' => $provider->id,
            'sales_opportunity_id' => $salesOpportunity?->id,
            'status' => 'draft',
            'vehicle_price' => $calculation['vehicle_price'],
            'down_payment' => $calculation['down_payment'],
            'tenor_months' => $calculation['tenor_months'],
            'monthly_installment' => $calculation['monthly_installment'],
            'applicant_data' => [
                'employment_type' => $request->input('employment_type'),
                'monthly_income' => (int) $request->input('monthly_income'),
                'company_name' => $request->input('company_name'),
                'id_card_number' => $request->input('id_card_number'),
                'npwp_number' => $request->input('npwp_number'),
                'marital_status' => $request->input('marital_status'),
            ],
            'notes' => $request->input('notes'),
        ]);

        // Transmit to provider via adapter
        $result = $this->leasingService->submit($application, $user);

        $msg = $result['success']
            ? 'Pengajuan pembiayaan leasing berhasil dikirimkan ke ' . $provider->name . '.'
            : 'Pengajuan leasing tersimpan sebagai draft (server leasing belum merespons: ' . $result['message'] . '). Tim dealer akan membantu follow-up.';

        return redirect()->route('customer.leasing.show', $application)
            ->with('success', $msg);
    }

    /**
     * Display a specific leasing application and its timeline.
     */
    public function show(LeasingApplication $application): View
    {
        Gate::authorize('view', $application);

        $application->load(['vehicle', 'leasingProvider', 'salesOpportunity.assignedSales', 'logs.user']);

        return view('customer.leasing.show', [
            'application' => $application,
        ]);
    }

    /**
     * Cancel an application.
     */
    public function cancel(Request $request, LeasingApplication $application): RedirectResponse
    {
        Gate::authorize('cancel', $application);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->leasingService->cancel(
            application: $application,
            actor: $user,
            reason: $request->input('cancellation_reason')
        );

        return redirect()->route('customer.leasing.show', $application)
            ->with('success', 'Pengajuan pembiayaan leasing berhasil dibatalkan.');
    }
}

