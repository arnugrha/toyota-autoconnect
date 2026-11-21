<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use App\Models\VehicleInventory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VehicleManagementController extends Controller
{
    /**
     * Display listing of all vehicles with live inventory summary.
     */
    public function index(Request $request): View
    {
        $query = Vehicle::with(['categoryRelation', 'images', 'inventories.branch'])
            ->latest('id');

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('variant', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category')) {
            $cat = $request->input('category');
            $query->where(function ($sub) use ($cat) {
                $sub->where('category', $cat)
                    ->orWhereHas('categoryRelation', fn ($cq) => $cq->where('slug', $cat)->orWhere('name', $cat));
            });
        }

        if ($request->filled('publication_status')) {
            $query->where('publication_status', $request->input('publication_status'));
        }

        $vehicles = $query->paginate(15)->withQueryString();
        $categories = VehicleCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.vehicles.index', [
            'vehicles' => $vehicles,
            'categories' => $categories,
            'filters' => [
                'q' => $request->input('q', ''),
                'category' => $request->input('category', ''),
                'publication_status' => $request->input('publication_status', ''),
            ],
        ]);
    }

    /**
     * Show vehicle creation form.
     */
    public function create(): View
    {
        $categories = VehicleCategory::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.vehicles.create', [
            'categories' => $categories,
            'branches' => $branches,
        ]);
    }

    /**
     * Store newly created vehicle.
     */
    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name'].'-'.($validated['variant'] ?? 'standard'));

        // Pastikan slug unik
        $originalSlug = $slug;
        $counter = 1;
        while (Vehicle::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $categoryName = $validated['category'] ?? null;
        if (! empty($validated['category_id'])) {
            $cat = VehicleCategory::find($validated['category_id']);
            $categoryName = $cat?->name ?? $categoryName;
        }

        DB::transaction(function () use ($validated, $slug, $categoryName, $request, &$vehicle) {
            $vehicle = Vehicle::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'category' => $categoryName ?? 'Umum',
                'category_id' => $validated['category_id'] ?? null,
                'variant' => $validated['variant'] ?? null,
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'transmission' => $validated['transmission'] ?? null,
                'fuel_type' => $validated['fuel_type'] ?? null,
                'engine_capacity' => $validated['engine_capacity'] ?? null,
                'passenger_capacity' => $validated['passenger_capacity'] ?? null,
                'dimensions' => $validated['dimensions'] ?? null,
                'status' => $validated['status'],
                'publication_status' => $validated['publication_status'],
                'is_featured' => $request->boolean('is_featured'),
                'published_at' => $validated['publication_status'] === 'published' ? now() : null,
            ]);

            // Multi-branch initial inventory initialization (0 if not specified)
            $branches = Branch::all();
            foreach ($branches as $branch) {
                VehicleInventory::firstOrCreate([
                    'vehicle_id' => $vehicle->id,
                    'branch_id' => $branch->id,
                ], [
                    'stock_quantity' => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            // Handle primary image upload
            if ($request->hasFile('primary_image')) {
                $path = $request->file('primary_image')->store('vehicles', 'public');
                VehicleImage::create([
                    'vehicle_id' => $vehicle->id,
                    'image_path' => $path,
                    'alt_text' => $vehicle->name.' Tampak Depan',
                    'sort_order' => 1,
                    'is_primary' => true,
                ]);
            }

            // Handle additional images upload
            if ($request->hasFile('additional_images')) {
                $order = 2;
                foreach ($request->file('additional_images') as $file) {
                    $path = $file->store('vehicles', 'public');
                    VehicleImage::create([
                        'vehicle_id' => $vehicle->id,
                        'image_path' => $path,
                        'alt_text' => $vehicle->name.' Foto '.$order,
                        'sort_order' => $order++,
                        'is_primary' => false,
                    ]);
                }
            }
        });

        return redirect()->route('admin.vehicles.index')
            ->with('success', "Kendaraan '{$vehicle->name}' berhasil ditambahkan ke database.");
    }

    /**
     * Show vehicle edit form.
     */
    public function edit(Vehicle $vehicle): View
    {
        $vehicle->load(['categoryRelation', 'images', 'inventories.branch']);
        $categories = VehicleCategory::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.vehicles.edit', [
            'vehicle' => $vehicle,
            'categories' => $categories,
            'branches' => $branches,
        ]);
    }

    /**
     * Update existing vehicle.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validated();

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : $vehicle->slug;

        $categoryName = $validated['category'] ?? $vehicle->category;
        if (! empty($validated['category_id'])) {
            $cat = VehicleCategory::find($validated['category_id']);
            $categoryName = $cat?->name ?? $categoryName;
        }

        DB::transaction(function () use ($validated, $slug, $categoryName, $request, $vehicle) {
            $vehicle->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'category' => $categoryName,
                'category_id' => $validated['category_id'] ?? $vehicle->category_id,
                'variant' => $validated['variant'] ?? null,
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'transmission' => $validated['transmission'] ?? null,
                'fuel_type' => $validated['fuel_type'] ?? null,
                'engine_capacity' => $validated['engine_capacity'] ?? null,
                'passenger_capacity' => $validated['passenger_capacity'] ?? null,
                'dimensions' => $validated['dimensions'] ?? null,
                'status' => $validated['status'],
                'publication_status' => $validated['publication_status'],
                'is_featured' => $request->boolean('is_featured'),
                'published_at' => $validated['publication_status'] === 'published'
                    ? ($vehicle->published_at ?? now())
                    : null,
            ]);

            // Handle new primary image upload
            if ($request->hasFile('primary_image')) {
                // Reset existing primary flags
                VehicleImage::where('vehicle_id', $vehicle->id)->update(['is_primary' => false]);

                $path = $request->file('primary_image')->store('vehicles', 'public');
                VehicleImage::create([
                    'vehicle_id' => $vehicle->id,
                    'image_path' => $path,
                    'alt_text' => $vehicle->name.' Foto Utama',
                    'sort_order' => 1,
                    'is_primary' => true,
                ]);
            }

            // Handle additional images upload
            if ($request->hasFile('additional_images')) {
                $maxOrder = (int) VehicleImage::where('vehicle_id', $vehicle->id)->max('sort_order') + 1;
                foreach ($request->file('additional_images') as $file) {
                    $path = $file->store('vehicles', 'public');
                    VehicleImage::create([
                        'vehicle_id' => $vehicle->id,
                        'image_path' => $path,
                        'alt_text' => $vehicle->name.' Galeri',
                        'sort_order' => $maxOrder++,
                        'is_primary' => false,
                    ]);
                }
            }
        });

        return redirect()->route('admin.vehicles.index')
            ->with('success', "Informasi kendaraan '{$vehicle->name}' berhasil diperbarui.");
    }

    /**
     * Delete or safely archive a vehicle.
     */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        // Audit check: jika kendaraan memiliki relasi inquiry atau lead, gunakan pengarsipan
        $hasInquiries = DB::table('inquiries')->where('vehicle_id', $vehicle->id)->exists();
        $hasLeads = DB::table('leads')->where('vehicle_id', $vehicle->id)->exists();

        if ($hasInquiries || $hasLeads) {
            $vehicle->update(['publication_status' => 'archived']);

            return back()->with('info', "Kendaraan '{$vehicle->name}' tidak dihapus permanen karena memiliki relasi data prospek/inquiry, melainkan dialihkan ke status 'Diarsipkan (Archived)'.");
        }

        // Hapus file gambar dari storage disk
        foreach ($vehicle->images as $img) {
            if ($img->image_path && Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
        }

        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')
            ->with('success', "Kendaraan '{$vehicle->name}' berhasil dihapus dari sistem.");
    }

    /**
     * Delete an individual vehicle image.
     */
    public function deleteImage(VehicleImage $image): RedirectResponse
    {
        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return back()->with('success', 'Gambar kendaraan berhasil dihapus.');
    }

    /**
     * Set an image as primary.
     */
    public function setPrimaryImage(VehicleImage $image): RedirectResponse
    {
        VehicleImage::where('vehicle_id', $image->vehicle_id)->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Foto utama kendaraan berhasil ditetapkan.');
    }
}
