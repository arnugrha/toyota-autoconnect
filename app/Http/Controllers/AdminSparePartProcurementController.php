<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\SparePart;
use App\Models\SparePartProcurement;
use App\Models\SparePartProcurementItem;
use App\Models\User;
use App\Services\SparePart\SparePartProcurementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSparePartProcurementController extends Controller
{
    public function __construct(
        protected SparePartProcurementService $procurementService
    ) {}

    /**
     * Display listing of spare part procurements.
     */
    public function index(Request $request): View
    {
        $stats = [
            'total_procurements' => SparePartProcurement::count(),
            'total_units_received' => (int) SparePartProcurementItem::sum('quantity'),
            'total_spend' => (float) SparePartProcurement::where('status', 'received')->sum('total_cost'),
            'this_month_count' => SparePartProcurement::whereMonth('procurement_date', now()->month)
                ->whereYear('procurement_date', now()->year)
                ->count(),
        ];

        $query = SparePartProcurement::with(['branch', 'creator'])
            ->withCount('items')
            ->withSum('items as total_quantity', 'quantity')
            ->latest('procurement_date')
            ->latest('id');

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('procurement_number', 'like', "%{$keyword}%")
                    ->orWhere('supplier_name', 'like', "%{$keyword}%")
                    ->orWhere('notes', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $procurements = $query->paginate(12)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.spare-part-procurements.index', [
            'procurements' => $procurements,
            'stats' => $stats,
            'branches' => $branches,
            'filters' => [
                'q' => $request->input('q', ''),
                'branch_id' => $request->input('branch_id', ''),
            ],
        ]);
    }

    /**
     * Show form to create a new spare part procurement.
     */
    public function create(Request $request): View
    {
        $spareParts = SparePart::active()
            ->orderBy('name')
            ->get(['id', 'part_number', 'name', 'unit', 'purchase_price', 'stock_quantity', 'min_stock']);

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.spare-part-procurements.create', [
            'spareParts' => $spareParts,
            'branches' => $branches,
            'preselectedPartId' => $request->integer('spare_part_id') ?: null,
        ]);
    }

    /**
     * Store a newly created procurement and automatically increment stock.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'procurement_date' => ['required', 'date'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.spare_part_id' => ['required', 'exists:spare_parts,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Minimal harus memilih 1 item sparepart.',
            'items.*.spare_part_id.required' => 'Sparepart wajib dipilih dari daftar.',
            'items.*.quantity.min' => 'Kuantitas pengadaan minimal 1.',
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        $procurement = $this->procurementService->createProcurement(
            headerData: [
                'procurement_date' => $validated['procurement_date'],
                'supplier_name' => $validated['supplier_name'],
                'branch_id' => $validated['branch_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ],
            items: $validated['items'],
            actor: $actor
        );

        $totalQty = $procurement->items->sum('quantity');

        return redirect()->route('admin.spare-part-procurements.show', $procurement)
            ->with('success', "Pengadaan {$procurement->procurement_number} berhasil dicatat! Stok {$totalQty} unit sparepart telah otomatis ditambahkan ke gudang.");
    }

    /**
     * Display detailed procurement document.
     */
    public function show(SparePartProcurement $procurement): View
    {
        $procurement->load(['items.sparePart', 'branch', 'creator']);

        return view('admin.spare-part-procurements.show', [
            'procurement' => $procurement,
        ]);
    }
}
