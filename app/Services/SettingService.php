<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    /**
     * Default application settings catalog.
     */
    public const DEFAULTS = [
        // A. General Settings
        'app_name' => [
            'group' => 'general',
            'value' => 'Toyota AutoConnect',
            'type' => 'string',
            'description' => 'Nama resmi platform dealer otomotif digital',
            'is_public' => true,
        ],
        'app_description' => [
            'group' => 'general',
            'value' => 'Platform digital terintegrasi dealer resmi Toyota Indonesia: booking kendaraan, simulasi kredit, test drive, dan layanan purna jual.',
            'type' => 'text',
            'description' => 'Deskripsi ringkas platform untuk meta SEO dan branding',
            'is_public' => true,
        ],
        'app_email' => [
            'group' => 'general',
            'value' => 'info@toyota-autoconnect.id',
            'type' => 'string',
            'description' => 'Email korespondensi resmi platform',
            'is_public' => true,
        ],
        'app_phone' => [
            'group' => 'general',
            'value' => '0800-1-TOYOTA (869682)',
            'type' => 'string',
            'description' => 'Hotline call center dan customer service resmi',
            'is_public' => true,
        ],
        'head_office_address' => [
            'group' => 'general',
            'value' => 'Jl. Jenderal Sudirman Kav. 52-53, Senayan, Kebayoran Baru, Jakarta Selatan 12190',
            'type' => 'text',
            'description' => 'Alamat kantor pusat operasional dealer utama',
            'is_public' => true,
        ],
        'timezone' => [
            'group' => 'general',
            'value' => 'Asia/Jakarta',
            'type' => 'string',
            'description' => 'Zona waktu standar sistem (WIB / WITA / WIT)',
            'is_public' => false,
        ],
        'date_format' => [
            'group' => 'general',
            'value' => 'd/m/Y',
            'type' => 'string',
            'description' => 'Format tampilan tanggal pada sistem dan invoice',
            'is_public' => false,
        ],
        'currency_format' => [
            'group' => 'general',
            'value' => 'IDR (Rp)',
            'type' => 'string',
            'description' => 'Format standar mata uang pada transaksi',
            'is_public' => true,
        ],
        'locale' => [
            'group' => 'general',
            'value' => 'id',
            'type' => 'string',
            'description' => 'Bahasa default antarmuka sistem',
            'is_public' => true,
        ],
        'app_logo' => [
            'group' => 'general',
            'value' => null,
            'type' => 'image',
            'description' => 'Logo utama Toyota AutoConnect',
            'is_public' => true,
        ],
        'app_favicon' => [
            'group' => 'general',
            'value' => null,
            'type' => 'image',
            'description' => 'Favicon browser tab',
            'is_public' => true,
        ],

        // B. Business Settings
        'company_name' => [
            'group' => 'business',
            'value' => 'PT Toyota AutoConnect Indonesia',
            'type' => 'string',
            'description' => 'Nama resmi entitas legal perusahaan dealer',
            'is_public' => true,
        ],
        'company_tagline' => [
            'group' => 'business',
            'value' => 'Mobility Happiness for All with Official Toyota Partnership',
            'type' => 'string',
            'description' => 'Slogan dan identitas korporasi',
            'is_public' => true,
        ],
        'company_address' => [
            'group' => 'business',
            'value' => 'Gedung Menara Astra Lt. 18, Jl. Jend. Sudirman Kav. 5-6, Jakarta 10220',
            'type' => 'text',
            'description' => 'Alamat resmi surat-menyurat perusahaan',
            'is_public' => true,
        ],
        'company_phone' => [
            'group' => 'business',
            'value' => '021-50808800',
            'type' => 'string',
            'description' => 'Nomor telepon resmi kantor pusat perusahaan',
            'is_public' => true,
        ],
        'company_email' => [
            'group' => 'business',
            'value' => 'corporate@toyota-autoconnect.id',
            'type' => 'string',
            'description' => 'Email resmi hubungan korporasi dan kemitraan',
            'is_public' => true,
        ],
        'central_dealer_info' => [
            'group' => 'business',
            'value' => 'Toyota AutoConnect Flagship Showroom & Service Center Sudirman',
            'type' => 'string',
            'description' => 'Informasi dealer pusat rujukan utama',
            'is_public' => true,
        ],
        'business_hours' => [
            'group' => 'business',
            'value' => 'Senin - Sabtu: 08:00 - 17:00 WIB | Minggu: 09:00 - 15:00 WIB (Khusus Showroom)',
            'type' => 'string',
            'description' => 'Jam operasional resmi showroom dan bengkel',
            'is_public' => true,
        ],

        // C. Booking Settings
        'default_booking_fee' => [
            'group' => 'booking',
            'value' => 5000000,
            'type' => 'integer',
            'description' => 'Besaran default uang tanda jadi (SPK) pemesanan unit kendaraan',
            'is_public' => true,
        ],
        'reservation_duration_days' => [
            'group' => 'booking',
            'value' => 3,
            'type' => 'integer',
            'description' => 'Durasi reservasi alokasi unit fisik inventory sebelum kadaluarsa (hari)',
            'is_public' => false,
        ],
        'booking_payment_timeout_hours' => [
            'group' => 'booking',
            'value' => 24,
            'type' => 'integer',
            'description' => 'Batas waktu pembayaran uang tanda jadi sebelum dibatalkan otomatis (jam)',
            'is_public' => false,
        ],
        'cancellation_terms' => [
            'group' => 'booking',
            'value' => 'Pembatalan SPK dapat diajukan sebelum alokasi nomor rangka (VIN) diterbitkan oleh dealer. Pengembalian tanda jadi tunduk pada potongan administrasi 10% dan proses verifikasi 7 hari kerja.',
            'type' => 'text',
            'description' => 'Syarat dan ketentuan pembatalan pesanan unit kendaraan',
            'is_public' => true,
        ],
        'test_drive_slot_duration_minutes' => [
            'group' => 'booking',
            'value' => 60,
            'type' => 'integer',
            'description' => 'Durasi per sesi test drive pelanggan (menit)',
            'is_public' => false,
        ],
        'test_drive_max_daily_slots' => [
            'group' => 'booking',
            'value' => 8,
            'type' => 'integer',
            'description' => 'Kapasitas maksimal antrean test drive per unit per hari',
            'is_public' => false,
        ],
        'service_max_daily_capacity' => [
            'group' => 'booking',
            'value' => 12,
            'type' => 'integer',
            'description' => 'Kapasitas penerimaan servis berkala per stall bengkel per hari',
            'is_public' => false,
        ],

        // D. Notification Settings
        'notification_email_enabled' => [
            'group' => 'notification',
            'value' => true,
            'type' => 'boolean',
            'description' => 'Aktifkan pengiriman notifikasi transaksional via Email',
            'is_public' => false,
        ],
        'notification_whatsapp_enabled' => [
            'group' => 'notification',
            'value' => true,
            'type' => 'boolean',
            'description' => 'Aktifkan pengiriman pesan resmi dan reminder via WhatsApp',
            'is_public' => false,
        ],
        'service_reminder_days_before' => [
            'group' => 'notification',
            'value' => 7,
            'type' => 'integer',
            'description' => 'Kirim reminder jadwal servis berkala berapa hari sebelumnya',
            'is_public' => false,
        ],
        'notify_on_booking_created' => [
            'group' => 'notification',
            'value' => true,
            'type' => 'boolean',
            'description' => 'Kirim notifikasi otomatis saat pemesanan SPK baru dibuat',
            'is_public' => false,
        ],
        'notify_on_booking_confirmed' => [
            'group' => 'notification',
            'value' => true,
            'type' => 'boolean',
            'description' => 'Kirim notifikasi saat pemesanan unit telah dikonfirmasi sales',
            'is_public' => false,
        ],
        'notify_on_payment_success' => [
            'group' => 'notification',
            'value' => true,
            'type' => 'boolean',
            'description' => 'Kirim konfirmasi dan kuitansi saat pembayaran terverifikasi lunas',
            'is_public' => false,
        ],

        // E. Payment Settings (Safe display, no secret keys)
        'payment_gateway_mode' => [
            'group' => 'payment',
            'value' => 'sandbox',
            'type' => 'string',
            'description' => 'Mode lingkungan transaksi gateway pembayaran (sandbox / production)',
            'is_public' => false,
        ],
        'payment_bank_transfer_active' => [
            'group' => 'payment',
            'value' => true,
            'type' => 'boolean',
            'description' => 'Status aktif metode Virtual Account Bank (BCA, Mandiri, BNI, BRI, Permata)',
            'is_public' => true,
        ],
        'payment_credit_card_active' => [
            'group' => 'payment',
            'value' => true,
            'type' => 'boolean',
            'description' => 'Status aktif pembayaran Kartu Kredit / Debit Online via Midtrans Snap',
            'is_public' => true,
        ],
        'payment_qris_active' => [
            'group' => 'payment',
            'value' => true,
            'type' => 'boolean',
            'description' => 'Status aktif pembayaran QRIS dan e-Wallet resmi (GoPay)',
            'is_public' => true,
        ],
        'payment_cash_active' => [
            'group' => 'payment',
            'value' => true,
            'type' => 'boolean',
            'description' => 'Status aktif opsi pelunasan tunai langsung di kasir dealer',
            'is_public' => true,
        ],
    ];

    /**
     * Get setting value with default fallback.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = Setting::get($key);
        if ($value !== null) {
            return $value;
        }

        if (array_key_exists($key, self::DEFAULTS)) {
            return self::DEFAULTS[$key]['value'];
        }

        return $default;
    }

    /**
     * Retrieve all settings grouped with values and metadata.
     */
    public function getAllGrouped(): array
    {
        $existing = Setting::all()->keyBy('key');
        $grouped = [
            'general' => [],
            'business' => [],
            'booking' => [],
            'notification' => [],
            'payment' => [],
        ];

        foreach (self::DEFAULTS as $key => $meta) {
            $group = $meta['group'];
            $currentVal = $existing->has($key)
                ? $existing->get($key)->getCastedValue()
                : $meta['value'];

            $grouped[$group][$key] = [
                'key' => $key,
                'group' => $group,
                'value' => $currentVal,
                'type' => $meta['type'],
                'description' => $meta['description'],
                'is_public' => $meta['is_public'],
            ];
        }

        return $grouped;
    }

    /**
     * Update a batch of settings from key-value pairs.
     */
    public function updateBatch(array $settings, ?string $group = null): void
    {
        foreach ($settings as $key => $value) {
            $meta = self::DEFAULTS[$key] ?? null;
            $itemGroup = $group ?? ($meta['group'] ?? 'general');
            $type = $meta['type'] ?? 'string';
            $description = $meta['description'] ?? null;
            $isPublic = $meta['is_public'] ?? false;

            Setting::set($key, $value, $itemGroup, $type, $description, $isPublic);
        }
    }

    /**
     * Handle logo or favicon file upload.
     */
    public function handleFileUpload(string $key, UploadedFile $file, string $group = 'general'): string
    {
        $path = $file->store('settings', 'public');
        $url = Storage::url($path);

        $meta = self::DEFAULTS[$key] ?? null;
        $description = $meta['description'] ?? "Berkas {$key}";

        Setting::set($key, $url, $group, 'image', $description, true);

        return $url;
    }

    /**
     * Seed initial default settings if table is empty.
     */
    public function seedDefaults(): void
    {
        foreach (self::DEFAULTS as $key => $meta) {
            Setting::firstOrCreate(
                ['key' => $key],
                [
                    'group' => $meta['group'],
                    'value' => is_bool($meta['value']) ? ($meta['value'] ? '1' : '0') : (string) $meta['value'],
                    'type' => $meta['type'],
                    'description' => $meta['description'],
                    'is_public' => $meta['is_public'],
                ]
            );
        }
    }
}

