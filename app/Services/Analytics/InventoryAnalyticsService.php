<?php

namespace App\Services\Analytics;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAnalyticsService
{
    /**
     * Compute inventory analytics dataset.
     */
    public function getInventoryAnalytics(User $user, Request $request, array $dateFilter): array
    {
        $branchId = ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id'));
        $start = $dateFilter['start_date'];
        $end = $dateFilter['end_date'];

        $invQuery = VehicleInventory::with(['vehicle.categoryRelation', 'branch']);

        if ($branchId) {
            $invQuery->where('branch_id', $branchId);
        }

        if ($categoryId = $request->input('category_id')) {
            $invQuery->whereHas('vehicle', fn ($vq) => $vq->where('category_id', $categoryId));
        }

        if ($vehicleId = $request->input('vehicle_id')) {
            $invQuery->where('vehicle_id', $vehicleId);
        }

        $allInventories = (clone $invQuery)->get();

        $physicalStock = (int) $allInventories->sum('stock_quantity');
        $reservedStock = (int) $allInventories->sum('reserved_quantity');
        $availableStock = max(0, $physicalStock - $reservedStock);

        // Low stock items (available <= 3 units)
        $lowStockItems = $allInventories->filter(function ($inv) {
            $available = max(0, $inv->stock_quantity - $inv->reserved_quantity);
            return $available <= 3;
        })->values();

        // Distribution by Branch
        $distributionByBranch = DB::table('branches')
            ->where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('id', $branchId))
            ->get()
            ->map(function ($dealer) {
                $inv = VehicleInventory::where('branch_id', $dealer->id)
                    ->selectRaw('sum(stock_quantity) as total_physical, sum(reserved_quantity) as total_reserved')
                    ->first();

                $phys = (int) ($inv->total_physical ?? 0);
                $res = (int) ($inv->total_reserved ?? 0);

                return [
                    'id' => $dealer->id,
                    'name' => $dealer->name,
                    'city' => $dealer->city,
                    'physical' => $phys,
                    'reserved' => $res,
                    'available' => max(0, $phys - $res),
                ];
            })
            ->sortByDesc('physical')
            ->values();

        // Distribution by Model
        $distributionByModel = (clone $invQuery)
            ->join('vehicles', 'vehicle_inventories.vehicle_id', '=', 'vehicles.id')
            ->select(
                'vehicles.name',
                DB::raw('sum(vehicle_inventories.stock_quantity) as physical'),
                DB::raw('sum(vehicle_inventories.reserved_quantity) as reserved'),
                DB::raw('sum(vehicle_inventories.stock_quantity - vehicle_inventories.reserved_quantity) as available')
            )
            ->groupBy('vehicles.id', 'vehicles.name')
            ->orderByDesc('physical')
            ->take(10)
            ->get();

        // Movements History (in/out/reservation/release)
        $movementsQuery = InventoryMovement::with(['vehicleInventory.vehicle', 'vehicleInventory.branch', 'user'])
            ->whereBetween('created_at', [$start, $end]);

        if ($branchId) {
            $movementsQuery->whereHas('vehicleInventory', fn ($iq) => $iq->where('branch_id', $branchId));
        }

        if ($movementType = $request->input('movement_type')) {
            $movementsQuery->where('movement_type', $movementType);
        }

        $movements = $movementsQuery->latest()->paginate(15)->withQueryString();

        return [
            'kpis' => [
                'physical_stock' => $physicalStock,
                'reserved_stock' => $reservedStock,
                'available_stock' => $availableStock,
                'low_stock_count' => $lowStockItems->count(),
            ],
            'low_stock_items' => $lowStockItems->take(10),
            'by_branch' => $distributionByBranch,
            'by_model' => $distributionByModel,
            'movements' => $movements,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'categories' => VehicleCategory::where('is_active', true)->orderBy('name')->get(),
            'vehicles' => Vehicle::orderBy('name')->get(),
        ];
    }
}
