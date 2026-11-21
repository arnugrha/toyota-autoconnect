<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LeadSourceController extends Controller
{
    public function index(Request $request): View
    {
        $query = LeadSource::withCount('leads')->orderBy('name');

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $leadSources = $query->paginate(10)->withQueryString();

        return view('admin.master-data.lead-sources.index', [
            'leadSources' => $leadSources,
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
            'code' => ['required', 'string', 'max:50', Rule::unique('lead_sources', 'code')],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = Str::slug($validated['code'], '_');
        $validated['is_active'] = $request->boolean('is_active', true);

        $leadSource = LeadSource::create($validated);

        app(AuditTrailService::class)->logCreated($leadSource, 'lead_sources', "Penambahan kanal sumber lead baru: {$leadSource->name} ({$leadSource->code})");

        return back()->with('success', "Sumber prospek lead '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, LeadSource $leadSource): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('lead_sources', 'code')->ignore($leadSource->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = Str::slug($validated['code'], '_');
        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $leadSource->getAttributes();
        $leadSource->update($validated);

        app(AuditTrailService::class)->logUpdated($leadSource, 'lead_sources', $oldValues, $leadSource->getAttributes(), "Pembaruan informasi sumber lead: {$leadSource->name}");

        return back()->with('success', "Sumber prospek lead '{$leadSource->name}' berhasil diperbarui.");
    }

    public function toggleStatus(LeadSource $leadSource): RedirectResponse
    {
        $oldStatus = $leadSource->is_active ? 'active' : 'inactive';
        $leadSource->update(['is_active' => ! $leadSource->is_active]);
        $newStatus = $leadSource->is_active ? 'active' : 'inactive';
        $statusText = $leadSource->is_active ? 'diaktifkan' : 'dinonaktifkan';

        app(AuditTrailService::class)->logStatusChanged($leadSource, 'lead_sources', $oldStatus, $newStatus, "Perubahan status operasional kanal lead.");

        return back()->with('success', "Status sumber lead '{$leadSource->name}' berhasil {$statusText}.");
    }

    public function destroy(LeadSource $leadSource): RedirectResponse
    {
        // Enforce referential integrity: do not delete if used by leads
        $hasLeads = Lead::where('source', $leadSource->code)->exists();

        if ($hasLeads) {
            return back()->with('error', "Sumber lead '{$leadSource->name}' tidak dapat dihapus karena sudah tercatat dalam riwayat prospek CRM. Anda disarankan menonaktifkan status kanal ini.");
        }

        $name = $leadSource->name;
        app(AuditTrailService::class)->logDeleted($leadSource, 'lead_sources', "Penghapusan kanal sumber lead: {$name}");
        $leadSource->delete();

        return back()->with('success', "Sumber prospek lead '{$name}' berhasil dihapus.");
    }
}

