<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleInventory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display the inventory summary dashboard and stock listing.
     */
    public function index(Request $request): View
    {
        // 1. Calculate live statistics across whole inventory
        $totalVehiclesCount = Vehicle::count();
        $totalStockPhysical = (int) VehicleInventory::sum('stock_quantity');
        $totalStockReserved = (int) VehicleInventory::sum('reserved_quantity');
        $totalStockAvailable = max(0, $totalStockPhysical - $totalStockReserved);

        // Vehicles completely out of stock across all branches
        $outOfStockVehiclesCount = Vehicle::whereDoesntHave('inventories', function ($q) {
            $q->whereRaw('stock_quantity > reserved_quantity');
        })->count();

        // Low stock count (inventory items with <= 2 available units)
        $lowStockCount = VehicleInventory::whereRaw('(stock_quantity - reserved_quantity) <= 2')
            ->whereRaw('(stock_quantity - reserved_quantity) > 0')
            ->count();

        // 2. Query filterable inventory items
        $query = VehicleInventory::with(['vehicle.categoryRelation', 'vehicle.images', 'branch'])
            ->join('vehicles', 'vehicle_inventories.vehicle_id', '=', 'vehicles.id')
            ->select('vehicle_inventories.*');

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('vehicles.name', 'like', "%{$keyword}%")
                    ->orWhere('vehicles.variant', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('vehicle_inventories.branch_id', $request->input('branch_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('vehicles.category_id', $request->input('category_id'));
        }

        if ($request->filled('stock_status')) {
            $status = $request->input('stock_status');
            if ($status === 'out_of_stock') {
                $query->whereRaw('(vehicle_inventories.stock_quantity - vehicle_inventories.reserved_quantity) <= 0');
            } elseif ($status === 'low_stock') {
                $query->whereRaw('(vehicle_inventories.stock_quantity - vehicle_inventories.reserved_quantity) BETWEEN 1 AND 2');
            } elseif ($status === 'in_stock') {
                $query->whereRaw('(vehicle_inventories.stock_quantity - vehicle_inventories.reserved_quantity) > 2');
            }
        }

        $inventories = $query->latest('vehicle_inventories.updated_at')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $categories = VehicleCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.inventory.index', [
            'inventories' => $inventories,
            'branches' => $branches,
            'categories' => $categories,
            'stats' => [
                'totalVehicles' => $totalVehiclesCount,
                'totalStockPhysical' => $totalStockPhysical,
                'totalStockReserved' => $totalStockReserved,
                'totalStockAvailable' => $totalStockAvailable,
                'outOfStockVehicles' => $outOfStockVehiclesCount,
                'lowStockCount' => $lowStockCount,
            ],
            'filters' => [
                'q' => $request->input('q', ''),
                'branch_id' => $request->input('branch_id', ''),
                'category_id' => $request->input('category_id', ''),
                'stock_status' => $request->input('stock_status', ''),
            ],
        ]);
    }

    /**
     * Show the stock adjustment modal/page.
     */
    public function showAdjustForm(Request $request): View
    {
        $selectedVehicleId = $request->input('vehicle_id');
        $selectedBranchId = $request->input('branch_id');

        $vehicles = Vehicle::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.inventory.adjust', [
            'vehicles' => $vehicles,
            'branches' => $branches,
            'selectedVehicleId' => $selectedVehicleId,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    /**
     * Process stock adjustment with pessimistic locking & audit logging.
     */
    public function adjustStock(StockAdjustmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userId = Auth::id();

        try {
            DB::transaction(function () use ($validated, $userId) {
                // Gunakan lockForUpdate untuk mencegah race condition
                $inventory = VehicleInventory::where('vehicle_id', $validated['vehicle_id'])
                    ->where('branch_id', $validated['branch_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $inventory) {
                    $inventory = VehicleInventory::create([
                        'vehicle_id' => $validated['vehicle_id'],
                        'branch_id' => $validated['branch_id'],
                        'stock_quantity' => 0,
                        'reserved_quantity' => 0,
                    ]);
                }

                $stockBefore = $inventory->stock_quantity;
                $changeQty = (int) $validated['quantity'];

                if ($validated['type'] === 'add') {
                    $stockAfter = $stockBefore + $changeQty;
                    $movementType = 'adjustment_in';
                    $delta = $changeQty;
                } else {
                    // Pengecekan pengurangan: stok fisik tidak boleh lebih rendah daripada stok yang dialokasikan (reserved)
                    if ($stockBefore - $changeQty < $inventory->reserved_quantity) {
                        throw new \InvalidArgumentException(
                            "Pengurangan gagal: Stok fisik ({$stockBefore}) dikurangi {$changeQty} akan lebih kecil dari jumlah unit yang telah dialokasikan/dipesan ({$inventory->reserved_quantity})."
                        );
                    }

                    $stockAfter = max(0, $stockBefore - $changeQty);
                    $movementType = 'adjustment_out';
                    $delta = -$changeQty;
                }

                // Update inventory
                $inventory->update([
                    'stock_quantity' => $stockAfter,
                ]);

                // Record audit log
                InventoryMovement::create([
                    'vehicle_inventory_id' => $inventory->id,
                    'user_id' => $userId,
                    'movement_type' => $movementType,
                    'quantity' => $delta,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reason' => $validated['reason'],
                    'created_at' => now(),
                ]);
            });

            return redirect()->route('admin.inventory.index')
                ->with('success', 'Penyesuaian stok berhasil disimpan dan dicatat dalam riwayat audit trail.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat memproses penyesuaian stok.');
        }
    }

    /**
     * Display stock movement history / audit trail.
     */
    public function movements(Request $request): View
    {
        $query = InventoryMovement::with(['inventory.vehicle', 'inventory.branch', 'user'])
            ->latest('id');

        if ($request->filled('branch_id')) {
            $branchId = $request->input('branch_id');
            $query->whereHas('inventory', fn ($q) => $q->where('branch_id', $branchId));
        }

        if ($request->filled('vehicle_id')) {
            $vehicleId = $request->input('vehicle_id');
            $query->whereHas('inventory', fn ($q) => $q->where('vehicle_id', $vehicleId));
        }

        if ($request->filled('type')) {
            $query->where('movement_type', $request->input('type'));
        }

        $movements = $query->paginate(20)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $vehicles = Vehicle::orderBy('name')->get();

        return view('admin.inventory.movements', [
            'movements' => $movements,
            'branches' => $branches,
            'vehicles' => $vehicles,
            'filters' => [
                'branch_id' => $request->input('branch_id', ''),
                'vehicle_id' => $request->input('vehicle_id', ''),
                'type' => $request->input('type', ''),
            ],
        ]);
    }
}
