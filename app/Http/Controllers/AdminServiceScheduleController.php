<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ServiceSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminServiceScheduleController extends Controller
{
    /**
     * Display service schedules configuration for a branch.
     */
    public function index(Request $request): View
    {
        Gate::authorize('manage', ServiceSchedule::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        // Super Admin can choose branch; other roles default to their branch
        $selectedBranchId = $request->input('branch_id');
        if (! $selectedBranchId || (! $user->hasRole('Super Admin') && $user->branch_id)) {
            $selectedBranchId = $user->branch_id ?? $branches->first()?->id;
        }

        $schedules = ServiceSchedule::where('branch_id', $selectedBranchId)
            ->orderBy('day_of_week')
            ->get();

        $selectedBranch = Branch::find($selectedBranchId);

        return view('admin.service-schedules.index', [
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
            'selectedBranchId' => (int) $selectedBranchId,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Update a specific day's service schedule.
     */
    public function update(Request $request, ServiceSchedule $serviceSchedule): RedirectResponse
    {
        Gate::authorize('manage', ServiceSchedule::class);

        $validated = $request->validate([
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i', 'after:opening_time'],
            'slot_duration_minutes' => ['required', 'integer', 'in:30,45,60,90,120'],
            'max_slot_capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'max_daily_capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'is_operational' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_operational'] = $request->boolean('is_operational');

        $serviceSchedule->update($validated);

        return redirect()->route('admin.service-schedules.index', ['branch_id' => $serviceSchedule->branch_id])
            ->with('success', "Jadwal operasional hari {$serviceSchedule->day_name} berhasil diperbarui.");
    }
}

