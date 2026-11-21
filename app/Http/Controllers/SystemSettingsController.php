<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AuditTrailService;
use App\Services\SettingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SystemSettingsController extends Controller
{
    public function __construct(
        protected SettingService $settingService,
        protected AuditTrailService $auditTrailService
    ) {}

    /**
     * Display the System Settings configuration dashboard.
     */
    public function index(Request $request): View
    {
        $activeTab = $request->input('tab', 'general');
        $validTabs = ['general', 'business', 'booking', 'notification', 'payment'];
        if (! in_array($activeTab, $validTabs, true)) {
            $activeTab = 'general';
        }

        $allSettings = $this->settingService->getAllGrouped();

        // Safe display data for payment settings (never expose secrets)
        $paymentInfo = [
            'environment' => config('midtrans.is_production') ? 'Production (Live)' : 'Sandbox (Development & Testing)',
            'client_key' => config('midtrans.client_key') ? substr(config('midtrans.client_key'), 0, 8) . '...' . substr(config('midtrans.client_key'), -4) : 'Belum diatur (.env)',
            'is_sanitized' => config('midtrans.is_sanitized', true) ? 'Aktif' : 'Nonaktif',
            'is_3ds' => config('midtrans.is_3ds', true) ? 'Aktif (3D Secure)' : 'Nonaktif',
            'server_key_status' => ! empty(config('midtrans.server_key')) ? 'Tersimpan Aman di Environtment (.env)' : 'Belum dikonfigurasi',
        ];

        return view('admin.settings.index', [
            'activeTab' => $activeTab,
            'groupedSettings' => $allSettings,
            'currentGroup' => $allSettings[$activeTab] ?? [],
            'paymentInfo' => $paymentInfo,
        ]);
    }

    /**
     * Update system settings for a specific group.
     */
    public function update(Request $request): RedirectResponse
    {
        $group = $request->input('group', 'general');

        $oldGroupValues = Setting::getByGroup($group);

        switch ($group) {
            case 'general':
                $validated = $request->validate([
                    'app_name' => ['required', 'string', 'max:100'],
                    'app_description' => ['nullable', 'string', 'max:500'],
                    'app_email' => ['required', 'email', 'max:150'],
                    'app_phone' => ['required', 'string', 'max:50'],
                    'head_office_address' => ['required', 'string', 'max:500'],
                    'timezone' => ['required', 'string', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'UTC'])],
                    'date_format' => ['required', 'string', Rule::in(['d/m/Y', 'Y-m-d', 'd F Y', 'd M Y'])],
                    'currency_format' => ['required', 'string', 'max:20'],
                    'locale' => ['required', 'string', Rule::in(['id', 'en'])],
                    'app_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
                    'app_favicon' => ['nullable', 'image', 'mimes:ico,png,svg', 'max:1024'],
                ]);

                // Handle file uploads if present
                if ($request->hasFile('app_logo')) {
                    $this->settingService->handleFileUpload('app_logo', $request->file('app_logo'), 'general');
                }
                if ($request->hasFile('app_favicon')) {
                    $this->settingService->handleFileUpload('app_favicon', $request->file('app_favicon'), 'general');
                }

                unset($validated['app_logo'], $validated['app_favicon']);
                $this->settingService->updateBatch($validated, 'general');
                break;

            case 'business':
                $validated = $request->validate([
                    'company_name' => ['required', 'string', 'max:150'],
                    'company_tagline' => ['nullable', 'string', 'max:255'],
                    'company_address' => ['required', 'string', 'max:500'],
                    'company_phone' => ['required', 'string', 'max:50'],
                    'company_email' => ['required', 'email', 'max:150'],
                    'central_dealer_info' => ['required', 'string', 'max:255'],
                    'business_hours' => ['required', 'string', 'max:255'],
                ]);

                $this->settingService->updateBatch($validated, 'business');
                break;

            case 'booking':
                $validated = $request->validate([
                    'default_booking_fee' => ['required', 'numeric', 'min:0', 'max:1000000000'],
                    'reservation_duration_days' => ['required', 'integer', 'min:1', 'max:30'],
                    'booking_payment_timeout_hours' => ['required', 'integer', 'min:1', 'max:168'],
                    'cancellation_terms' => ['required', 'string', 'max:2000'],
                    'test_drive_slot_duration_minutes' => ['required', 'integer', 'min:15', 'max:180'],
                    'test_drive_max_daily_slots' => ['required', 'integer', 'min:1', 'max:50'],
                    'service_max_daily_capacity' => ['required', 'integer', 'min:1', 'max:100'],
                ]);

                $this->settingService->updateBatch($validated, 'booking');
                break;

            case 'notification':
                $payload = [
                    'notification_email_enabled' => $request->boolean('notification_email_enabled'),
                    'notification_whatsapp_enabled' => $request->boolean('notification_whatsapp_enabled'),
                    'service_reminder_days_before' => $request->integer('service_reminder_days_before', 7),
                    'notify_on_booking_created' => $request->boolean('notify_on_booking_created'),
                    'notify_on_booking_confirmed' => $request->boolean('notify_on_booking_confirmed'),
                    'notify_on_payment_success' => $request->boolean('notify_on_payment_success'),
                ];

                $request->validate([
                    'service_reminder_days_before' => ['required', 'integer', 'min:1', 'max:30'],
                ]);

                $this->settingService->updateBatch($payload, 'notification');
                break;

            case 'payment':
                $payload = [
                    'payment_gateway_mode' => $request->input('payment_gateway_mode', 'sandbox'),
                    'payment_bank_transfer_active' => $request->boolean('payment_bank_transfer_active'),
                    'payment_credit_card_active' => $request->boolean('payment_credit_card_active'),
                    'payment_qris_active' => $request->boolean('payment_qris_active'),
                    'payment_cash_active' => $request->boolean('payment_cash_active'),
                ];

                $request->validate([
                    'payment_gateway_mode' => ['required', 'string', Rule::in(['sandbox', 'production'])],
                ]);

                $this->settingService->updateBatch($payload, 'payment');
                break;

            default:
                return back()->with('error', 'Kategori pengaturan tidak valid.');
        }

        $newGroupValues = Setting::getByGroup($group);

        // Audit Trail Record
        $this->auditTrailService->logSettings($group, $oldGroupValues, $newGroupValues, $request->user());

        return redirect()->route('admin.settings.index', ['tab' => $group])
            ->with('success', 'Pengaturan sistem berhasil diperbarui dan diterapkan.');
    }
}

