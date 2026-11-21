<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceTypeController extends Controller
{
    public function index(Request $request): View
    {
        $query = ServiceType::withCount(['serviceBookings', 'serviceOrders'])->latest('id');

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $serviceTypes = $query->paginate(10)->withQueryString();

        $categories = [
            'regular_maintenance' => 'Servis Berkala Rutin',
            'quick_service' => 'Quick Service / Fast Track',
            'repair' => 'Perbaikan Mesin & Mekanikal',
            'ac_service' => 'Perawatan Sistem AC & Sirkulasi',
            'electrical' => 'Kelistrikan & Sistem Hybrid/EV',
            'inspection' => 'Inspeksi & Uji Emisi',
        ];

        return view('admin.master-data.service-types.index', [
            'serviceTypes' => $serviceTypes,
            'categories' => $categories,
            'filters' => [
                'q' => $request->input('q', ''),
                'category' => $request->input('category', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('service_types', 'code')],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'estimated_cost' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);

        $serviceType = ServiceType::create($validated);

        app(AuditTrailService::class)->logCreated($serviceType, 'service_types', "Penambahan jenis paket servis bengkel baru: {$serviceType->name} ({$serviceType->code})");

        return back()->with('success', "Jenis layanan servis '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, ServiceType $serviceType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('service_types', 'code')->ignore($serviceType->id)],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'estimated_cost' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $serviceType->getAttributes();
        $serviceType->update($validated);

        app(AuditTrailService::class)->logUpdated($serviceType, 'service_types', $oldValues, $serviceType->getAttributes(), "Pembaruan informasi paket servis: {$serviceType->name}");

        return back()->with('success', "Jenis layanan servis '{$serviceType->name}' berhasil diperbarui.");
    }

    public function toggleStatus(ServiceType $serviceType): RedirectResponse
    {
        $oldStatus = $serviceType->is_active ? 'active' : 'inactive';
        $serviceType->update(['is_active' => ! $serviceType->is_active]);
        $newStatus = $serviceType->is_active ? 'active' : 'inactive';
        $statusText = $serviceType->is_active ? 'diaktifkan' : 'dinonaktifkan';

        app(AuditTrailService::class)->logStatusChanged($serviceType, 'service_types', $oldStatus, $newStatus, "Perubahan status operasional jenis servis.");

        return back()->with('success', "Status layanan servis '{$serviceType->name}' berhasil {$statusText}.");
    }

    public function destroy(ServiceType $serviceType): RedirectResponse
    {
        // Enforce referential integrity: do not delete if used in bookings or work orders
        $hasBookings = $serviceType->serviceBookings()->exists();
        $hasOrders = $serviceType->serviceOrders()->exists();

        if ($hasBookings || $hasOrders) {
            return back()->with('error', "Layanan servis '{$serviceType->name}' tidak dapat dihapus karena sudah memiliki riwayat booking atau perintah kerja (WO). Anda disarankan menonaktifkan status paket sebagai gantinya.");
        }

        $name = $serviceType->name;
        app(AuditTrailService::class)->logDeleted($serviceType, 'service_types', "Penghapusan jenis paket servis bengkel: {$name}");
        $serviceType->delete();

        return back()->with('success', "Jenis layanan servis '{$name}' berhasil dihapus.");
    }
}

