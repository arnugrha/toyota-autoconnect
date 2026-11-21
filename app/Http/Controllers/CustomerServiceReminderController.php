<?php

namespace App\Http\Controllers;

use App\Models\ServiceReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerServiceReminderController extends Controller
{
    /**
     * Display a listing of customer's after-sales maintenance reminders.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $reminders = ServiceReminder::with('vehicle')
            ->where('user_id', $user->id)
            ->orderBy('next_service_date')
            ->get();

        return view('customer.service-reminders.index', [
            'reminders' => $reminders,
        ]);
    }
}

