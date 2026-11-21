<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerNotificationPreferenceController extends Controller
{
    /**
     * Show customer notification preferences settings form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $preferences = $user->getNotificationPreferences();

        return view('customer.notifications.preferences', compact('user', 'preferences'));
    }

    /**
     * Update customer notification preferences.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $preferences = $user->getNotificationPreferences();

        $validated = $request->validate([
            'in_app' => ['nullable', 'boolean'],
            'email' => ['nullable', 'boolean'],
            'whatsapp' => ['nullable', 'boolean'],
            'service_reminders' => ['nullable', 'boolean'],
            'transactional' => ['nullable', 'boolean'],
            'promotional' => ['nullable', 'boolean'],
            'whatsapp_consent' => ['nullable', 'boolean'],
        ]);

        $whatsappConsent = $request->boolean('whatsapp_consent');
        $consentAt = $preferences->whatsapp_consent_at;

        if ($whatsappConsent && !$preferences->whatsapp_consent) {
            $consentAt = now();
        } elseif (!$whatsappConsent) {
            $consentAt = null;
        }

        $preferences->update([
            'in_app' => $request->boolean('in_app'),
            'email' => $request->boolean('email'),
            'whatsapp' => $request->boolean('whatsapp'),
            'service_reminders' => $request->boolean('service_reminders'),
            'transactional' => $request->boolean('transactional', true),
            'promotional' => $request->boolean('promotional'),
            'whatsapp_consent' => $whatsappConsent,
            'whatsapp_consent_at' => $consentAt,
        ]);

        return back()->with('success', 'Preferensi notifikasi berhasil diperbarui.');
    }
}

