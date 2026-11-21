<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class VehicleCatalogController extends Controller
{
    /**
     * Display the vehicle catalog with filters and search.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:50'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'in:all,available,incoming,reserved,sold'],
            'sort' => ['nullable', 'string', 'in:latest,price_asc,price_desc,name_asc'],
        ]);

        $query = Vehicle::published()->with(['images', 'inventories.branch']);

        // Search by name, variant, or description
        if (! empty($validated['q'])) {
            $keyword = trim($validated['q']);
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('variant', 'like', "%{$keyword}%")
                    ->orWhere('category', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Filter by category
        if (! empty($validated['category']) && $validated['category'] !== 'all') {
            $query->where(function ($sub) use ($validated) {
                $sub->where('category', $validated['category'])
                    ->orWhereHas('categoryRelation', fn ($cq) => $cq->where('slug', $validated['category']));
            });
        }

        // Filter by branch inventory
        if (! empty($validated['branch_id'])) {
            $query->whereHas('inventories', function ($iq) use ($validated) {
                $iq->where('branch_id', $validated['branch_id'])
                    ->whereRaw('stock_quantity > reserved_quantity');
            });
        }

        // Filter by minimum price
        if (isset($validated['min_price']) && is_numeric($validated['min_price'])) {
            $query->where('price', '>=', (int) $validated['min_price']);
        }

        // Filter by maximum price
        if (isset($validated['max_price']) && is_numeric($validated['max_price'])) {
            $query->where('price', '<=', (int) $validated['max_price']);
        }

        // Filter by status
        if (! empty($validated['status']) && $validated['status'] !== 'all') {
            $query->where('status', $validated['status']);
        }

        // Sorting
        $sort = $validated['sort'] ?? 'latest';
        match ($sort) {
            'price_asc' => $query->orderByRaw('price IS NULL, price ASC'),
            'price_desc' => $query->orderByRaw('price IS NULL, price DESC'),
            'name_asc' => $query->orderBy('name', 'asc')->orderBy('variant', 'asc'),
            default => $query->latest('published_at')->latest('id'),
        };

        $vehicles = $query->paginate(12)->withQueryString();

        // Distinct categories and active branches
        $dbCategories = VehicleCategory::where('is_active', true)->pluck('name')->toArray();
        $availableCategories = ! empty($dbCategories) ? $dbCategories : ['MPV', 'SUV', 'Hatchback', 'Sedan', 'Commercial'];
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('vehicles.index', [
            'vehicles' => $vehicles,
            'availableCategories' => $availableCategories,
            'branches' => $branches,
            'filters' => [
                'q' => $validated['q'] ?? '',
                'category' => $validated['category'] ?? '',
                'branch_id' => $validated['branch_id'] ?? '',
                'min_price' => $validated['min_price'] ?? '',
                'max_price' => $validated['max_price'] ?? '',
                'status' => $validated['status'] ?? '',
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Display the vehicle detail page.
     */
    public function show(string $slug): View
    {
        $vehicle = Vehicle::published()
            ->where('slug', $slug)
            ->with([
                'images' => fn ($q) => $q->orderBy('sort_order'),
                'inventories.branch' => fn ($q) => $q->where('is_active', true),
            ])
            ->firstOrFail();

        // Related vehicles in the same category (excluding current vehicle)
        $relatedVehicles = Vehicle::published()
            ->where('category', $vehicle->category)
            ->where('id', '!=', $vehicle->id)
            ->with('images')
            ->take(3)
            ->get();

        // Active dealer branches for test drive booking
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        // Active leasing providers for credit simulation
        $leasingProviders = \App\Models\LeasingProvider::active()->orderBy('name')->get();

        // Published vehicles for dynamic credit simulation selection
        $availableVehicles = Vehicle::published()
            ->orderBy('name')
            ->get(['id', 'name', 'variant', 'price', 'slug', 'category']);

        return view('vehicles.show', [
            'vehicle' => $vehicle,
            'relatedVehicles' => $relatedVehicles,
            'branches' => $branches,
            'leasingProviders' => $leasingProviders,
            'availableVehicles' => $availableVehicles,
        ]);
    }
}
