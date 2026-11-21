<?php

namespace App\Http\Controllers;

use App\Models\VehicleCategory;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VehicleCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = VehicleCategory::withCount('vehicles')->orderBy('name');

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        $categories = $query->paginate(10)->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'filters' => [
                'q' => $request->input('q', ''),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('vehicle_categories', 'slug')],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['slug'] = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $category = VehicleCategory::create($validated);

        app(AuditTrailService::class)->logCreated($category, 'categories', "Penambahan kategori kendaraan baru: {$category->name}");

        return back()->with('success', "Kategori '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, VehicleCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('vehicle_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['slug'] = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $category->getAttributes();
        $category->update($validated);

        app(AuditTrailService::class)->logUpdated($category, 'categories', $oldValues, $category->getAttributes(), "Pembaruan informasi kategori kendaraan: {$category->name}");

        return back()->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    public function toggleStatus(VehicleCategory $category): RedirectResponse
    {
        $oldStatus = $category->is_active ? 'active' : 'inactive';
        $category->update(['is_active' => ! $category->is_active]);
        $newStatus = $category->is_active ? 'active' : 'inactive';
        $statusText = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

        app(AuditTrailService::class)->logStatusChanged($category, 'categories', $oldStatus, $newStatus, "Perubahan status publikasi kategori kendaraan.");

        return back()->with('success', "Status kategori '{$category->name}' berhasil {$statusText}.");
    }

    public function destroy(VehicleCategory $category): RedirectResponse
    {
        // Enforce referential integrity: do not delete categories with vehicles attached
        if ($category->vehicles()->exists()) {
            return back()->with('error', "Kategori '{$category->name}' tidak dapat dihapus karena masih digunakan oleh unit kendaraan. Anda disarankan menonaktifkan status kategori sebagai gantinya.");
        }

        $categoryName = $category->name;
        app(AuditTrailService::class)->logDeleted($category, 'categories', "Penghapusan kategori kendaraan: {$categoryName}");
        $category->delete();

        return back()->with('success', "Kategori '{$categoryName}' berhasil dihapus.");
    }
}
