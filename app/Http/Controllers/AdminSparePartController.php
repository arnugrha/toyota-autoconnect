<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use App\Models\SparePartStockMovement;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminSparePartController extends Controller
{
    /**
     * Display master spare parts listing & inventory KPIs.
     */
    public function index(Request $request): View
    {
        $stats = [
            'total_sku' => SparePart::count(),
            'total_stock' => (int) SparePart::sum('stock_quantity'),
            'low_stock' => SparePart::where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'min_stock')
                ->count(),
            'out_of_stock' => SparePart::where('stock_quantity', '<=', 0)->count(),
        ];

        $query = SparePart::withCount(['serviceOrderItems', 'procurementItems'])->latest('id');

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('part_number', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('stock_status')) {
            $stockStatus = $request->input('stock_status');
            if ($stockStatus === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($stockStatus === 'low_stock') {
                $query->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '<=', 'min_stock');
            } elseif ($stockStatus === 'in_stock') {
                $query->whereColumn('stock_quantity', '>', 'min_stock');
            }
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $spareParts = $query->paginate(12)->withQueryString();

        return view('admin.spare-parts.index', [
            'spareParts' => $spareParts,
            'stats' => $stats,
            'categories' => SparePart::CATEGORIES,
            'units' => SparePart::UNITS,
            'filters' => [
                'q' => $request->input('q', ''),
                'category' => $request->input('category', ''),
                'stock_status' => $request->input('stock_status', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    /**
     * Store a newly created spare part in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'part_number' => ['required', 'string', 'max:60', Rule::unique('spare_parts', 'part_number')],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(array_keys(SparePart::CATEGORIES))],
            'unit' => ['required', 'string', 'max:30'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['part_number'] = strtoupper(trim($validated['part_number']));
        $validated['is_active'] = $request->boolean('is_active', true);

        $sparePart = DB::transaction(function () use ($validated) {
            $part = SparePart::create($validated);

            if ($part->stock_quantity > 0) {
                SparePartStockMovement::create([
                    'spare_part_id' => $part->id,
                    'type' => 'in',
                    'quantity' => $part->stock_quantity,
                    'stock_before' => 0,
                    'stock_after' => $part->stock_quantity,
                    'reference_type' => 'manual',
                    'created_by' => Auth::id(),
                    'notes' => 'Stok awal saat penambahan master sparepart baru.',
                ]);
            }

            return $part;
        });

        app(AuditTrailService::class)->logCreated(
            $sparePart,
            'spare_parts',
            "Menambahkan master sparepart baru: {$sparePart->name} ({$sparePart->part_number}) dengan stok awal {$sparePart->stock_quantity} {$sparePart->unit}."
        );

        return redirect()->route('admin.spare-parts.index')
            ->with('success', "Sparepart '{$sparePart->name}' ({$sparePart->part_number}) berhasil ditambahkan.");
    }

    /**
     * Update the specified spare part master data.
     */
    public function update(Request $request, SparePart $sparePart): RedirectResponse
    {
        $validated = $request->validate([
            'part_number' => ['required', 'string', 'max:60', Rule::unique('spare_parts', 'part_number')->ignore($sparePart->id)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(array_keys(SparePart::CATEGORIES))],
            'unit' => ['required', 'string', 'max:30'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['part_number'] = strtoupper(trim($validated['part_number']));
        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $sparePart->getAttributes();
        $sparePart->update($validated);

        app(AuditTrailService::class)->logUpdated(
            $sparePart,
            'spare_parts',
            $oldValues,
            $sparePart->getAttributes(),
            "Pembaruan master sparepart: {$sparePart->name} ({$sparePart->part_number})."
        );

        return redirect()->route('admin.spare-parts.index')
            ->with('success', "Data sparepart '{$sparePart->name}' berhasil diperbarui.");
    }

    /**
     * Toggle active status of spare part.
     */
    public function toggleStatus(SparePart $sparePart): RedirectResponse
    {
        $oldStatus = $sparePart->is_active ? 'active' : 'inactive';
        $sparePart->update(['is_active' => ! $sparePart->is_active]);
        $newStatus = $sparePart->is_active ? 'active' : 'inactive';
        $label = $sparePart->is_active ? 'diaktifkan' : 'dinonaktifkan';

        app(AuditTrailService::class)->logStatusChanged(
            $sparePart,
            'spare_parts',
            $oldStatus,
            $newStatus,
            "Status sparepart {$sparePart->part_number} {$label}."
        );

        return back()->with('success', "Sparepart '{$sparePart->name}' berhasil {$label}.");
    }

    /**
     * Remove spare part if not referenced by service orders or procurements.
     */
    public function destroy(SparePart $sparePart): RedirectResponse
    {
        if ($sparePart->serviceOrderItems()->exists() || $sparePart->procurementItems()->exists()) {
            return back()->with('error', "Sparepart '{$sparePart->name}' tidak dapat dihapus karena sudah memiliki riwayat servis atau pengadaan. Gunakan tombol Nonaktifkan saja.");
        }

        $name = $sparePart->name;
        $sku = $sparePart->part_number;
        $sparePart->delete();

        app(AuditTrailService::class)->log(
            action: 'deleted',
            module: 'spare_parts',
            description: "Menghapus master sparepart {$name} ({$sku})."
        );

        return back()->with('success', "Sparepart '{$name}' berhasil dihapus.");
    }

    /**
     * Show stock movement audit trail for a specific spare part.
     */
    public function movements(SparePart $sparePart): View
    {
        $movements = $sparePart->stockMovements()
            ->with('creator')
            ->paginate(20);

        return view('admin.spare-parts.movements', [
            'sparePart' => $sparePart,
            'movements' => $movements,
        ]);
    }
}
