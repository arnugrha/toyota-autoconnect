<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InquiryController extends Controller
{
    /**
     * Store a newly created inquiry and generate a corresponding CRM lead.
     */
    public function store(Request $request): RedirectResponse
    {
        // Rate limiting to prevent spam / accidental double-submission (max 5 per minute per IP/User)
        $rateLimitKey = 'inquiry-submit:'.(Auth::id() ?? $request->ip());
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return back()->with('error', "Terlalu banyak permintaan dalam waktu singkat. Silakan tunggu {$seconds} detik sebelum mengirim kembali.");
        }

        $validated = $request->validate([
            'vehicle_id' => ['nullable', 'integer', Rule::exists('vehicles', 'id')],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:25'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
            'consent' => ['accepted'],
        ], [
            'customer_name.required' => 'Nama lengkap wajib diisi.',
            'customer_email.required' => 'Alamat email wajib diisi.',
            'subject.required' => 'Topik pertanyaan wajib dipilih.',
            'message.required' => 'Pesan atau pertanyaan tidak boleh kosong.',
            'consent.accepted' => 'Anda harus menyetujui izin komunikasi dari konsultan dealer Toyota AutoConnect.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Database transaction: Inquiry + Lead + Initial LeadActivity created atomically
        try {
            DB::transaction(function () use ($validated, $user, &$inquiry, &$lead) {
                // 1. Create Inquiry
                $inquiry = Inquiry::create([
                    'user_id' => $user->id,
                    'vehicle_id' => $validated['vehicle_id'] ?? null,
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_phone' => $validated['customer_phone'] ?? $user->phone,
                    'subject' => $validated['subject'],
                    'message' => $validated['message'],
                    'status' => 'new',
                    'submitted_at' => now(),
                ]);

                // 2. Create CRM Lead
                $vehicleName = $inquiry->vehicle_id ? Vehicle::find($inquiry->vehicle_id)?->name : 'Umum / Belum Ditentukan';
                $lead = Lead::create([
                    'lead_number' => Lead::generateLeadNumber(),
                    'customer_id' => $user->id,
                    'inquiry_id' => $inquiry->id,
                    'vehicle_id' => $inquiry->vehicle_id,
                    'assigned_to' => null,
                    'status' => 'new',
                    'source' => 'website_inquiry',
                    'notes' => "Inquiry website: {$inquiry->subject}. Pesan awal: ".Str::limit($inquiry->message, 150),
                    'next_follow_up_at' => now()->addDay(),
                    'last_activity_at' => now(),
                ]);

                // 3. Create initial append-only activity
                LeadActivity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $user->id,
                    'activity_type' => 'created',
                    'description' => "Prospek baru dibuat otomatis dari inquiry portal untuk model: {$vehicleName}.",
                    'previous_status' => null,
                    'new_status' => 'new',
                    'created_at' => now(),
                ]);
            });

            RateLimiter::hit($rateLimitKey, 60);

            return back()->with('success', 'Inquiry Anda berhasil dikirim! Konsultan penjualan kami akan segera menghubungi Anda melalui kontak yang terdaftar.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan inquiry. Mohon coba beberapa saat lagi.');
        }
    }
}
