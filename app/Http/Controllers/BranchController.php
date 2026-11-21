<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ServiceBooking;
use App\Models\ServiceOrder;
use App\Models\TestDriveBooking;
use App\Models\User;
use App\Models\VehicleBooking;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $query = Branch::withCount('inventories')->latest();

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $branches = $query->paginate(10)->withQueryString();

        return view('admin.branches.index', [
            'branches' => $branches,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('branches', 'code')],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'opening_time' => ['nullable', 'string', 'max:10'],
            'closing_time' => ['nullable', 'string', 'max:10'],
            'slot_duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'slot_capacity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = strtoupper(Str::slug($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['opening_time'] = $validated['opening_time'] ?? '08:30';
        $validated['closing_time'] = $validated['closing_time'] ?? '17:00';
        $validated['slot_duration_minutes'] = $validated['slot_duration_minutes'] ?? 60;
        $validated['slot_capacity'] = $validated['slot_capacity'] ?? 2;

        $branch = Branch::create($validated);

        app(AuditTrailService::class)->logCreated($branch, 'branches', "Penambahan cabang dealer baru: {$branch->name} ({$branch->code})");

        return back()->with('success', "Cabang baru '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('branches', 'code')->ignore($branch->id)],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'opening_time' => ['nullable', 'string', 'max:10'],
            'closing_time' => ['nullable', 'string', 'max:10'],
            'slot_duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'slot_capacity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = strtoupper(Str::slug($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $branch->getAttributes();
        $branch->update($validated);

        app(AuditTrailService::class)->logUpdated($branch, 'branches', $oldValues, $branch->getAttributes(), "Pembaruan informasi data cabang: {$branch->name}");

        return back()->with('success', "Data cabang '{$branch->name}' berhasil diperbarui.");
    }

    public function toggleStatus(Branch $branch): RedirectResponse
    {
        $oldStatus = $branch->is_active ? 'active' : 'inactive';
        $branch->update(['is_active' => ! $branch->is_active]);
        $newStatus = $branch->is_active ? 'active' : 'inactive';
        $statusText = $branch->is_active ? 'diaktifkan' : 'dinonaktifkan';

        app(AuditTrailService::class)->logStatusChanged($branch, 'branches', $oldStatus, $newStatus, "Perubahan status operasional cabang dealer.");

        return back()->with('success', "Status cabang '{$branch->name}' berhasil {$statusText}.");
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        // Enforce Referential Integrity
        $hasInventories = $branch->inventories()->exists();
        $hasBookings = VehicleBooking::where('branch_id', $branch->id)->exists();
        $hasTestDrives = TestDriveBooking::where('branch_id', $branch->id)->exists();
        $hasServices = ServiceBooking::where('branch_id', $branch->id)->exists() || ServiceOrder::where('branch_id', $branch->id)->exists();
        $hasStaff = User::where('branch_id', $branch->id)->exists();

        if ($hasInventories || $hasBookings || $hasTestDrives || $hasServices || $hasStaff) {
            return back()->with('error', "Cabang '{$branch->name}' tidak dapat dihapus karena memiliki data inventaris, transaksi pemesanan, atau staf aktif terkait. Sebaiknya nonaktifkan status operasional cabang.");
        }

        $branchName = $branch->name;
        app(AuditTrailService::class)->logDeleted($branch, 'branches', "Penghapusan cabang dealer: {$branchName}");
        $branch->delete();

        return back()->with('success', "Data cabang '{$branchName}' berhasil dihapus.");
    }
}
