<?php

namespace App\Http\Controllers;

use App\Models\ServiceReminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminServiceReminderController extends Controller
{
    /**
     * Display a listing of after-sales service reminders.
     */
    public function index(Request $request): View
    {
        Gate::authorize('manage', ServiceReminder::class);

        $query = ServiceReminder::with(['user', 'vehicle'])->latest('next_service_date');

        $stats = [
            'total' => ServiceReminder::count(),
            'pending' => ServiceReminder::where('status', 'pending')->count(),
            'sent' => ServiceReminder::where('status', 'sent')->count(),
            'booked' => ServiceReminder::where('status', 'booked')->count(),
        ];

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('license_plate', 'like', "%{$keyword}%")
                    ->orWhere('vehicle_name', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $reminders = $query->paginate(15)->withQueryString();

        return view('admin.service-reminders.index', [
            'reminders' => $reminders,
            'stats' => $stats,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', 'all'),
            ],
        ]);
    }

    /**
     * Manually trigger the service reminder command.
     */
    public function trigger(): RedirectResponse
    {
        Gate::authorize('manage', ServiceReminder::class);

        Artisan::call('toyota:send-service-reminders');

        return redirect()->route('admin.service-reminders.index')
            ->with('success', 'Pemeriksaan pengingat servis berkala telah dijalankan.');
    }
}

