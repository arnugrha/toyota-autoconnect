@extends('layouts.dashboard')

@section('title', 'Pengaturan Sistem — Toyota AutoConnect')
@section('header_title', 'Pengaturan Sistem & Konfigurasi')
@section('header_subtitle', 'Kelola preferensi umum, informasi legal korporasi, aturan booking, notifikasi, dan payment gateway')

@section('content')
<div class="space-y-6">

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-xs font-semibold text-emerald-800 shadow-xs">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-xl bg-red-50 border border-red-200 p-4 text-xs font-semibold text-red-800 shadow-xs">
            <svg class="h-4 w-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Category Tabs Navigation -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200">
        @php
            $tabs = [
                'general' => ['name' => 'General Settings', 'desc' => 'Identitas Aplikasi & Regional', 'icon' => 'cog-6-tooth'],
                'business' => ['name' => 'Business Settings', 'desc' => 'Informasi Legal & Showroom Pusat', 'icon' => 'building-office-2'],
                'booking' => ['name' => 'Booking Settings', 'desc' => 'Uang Muka, Reservasi & Servis', 'icon' => 'calendar-days'],
                'notification' => ['name' => 'Notification Settings', 'desc' => 'Email, WhatsApp & Reminder', 'icon' => 'bell-alert'],
                'payment' => ['name' => 'Payment Settings', 'desc' => 'Metode Bayar & Gateway Midtrans', 'icon' => 'credit-card'],
            ];
        @endphp

        @foreach($tabs as $tabKey => $tab)
            <a href="{{ route('admin.settings.index', ['tab' => $tabKey]) }}"
               class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $activeTab === $tabKey ? 'bg-red-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300' }}">
                <span>{{ $tab['name'] }}</span>
            </a>
        @endforeach
    </div>

    <!-- Active Settings Tab Content -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">

        <!-- ================= A. GENERAL SETTINGS ================= -->
        @if ($activeTab === 'general')
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h3 class="text-base font-extrabold text-slate-900">Pengaturan Umum Aplikasi (General Settings)</h3>
                <p class="text-xs text-slate-500 mt-1">Konfigurasi nama platform, branding, kontak resmi dealer, dan preferensi regional sistem.</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="group" value="general">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Aplikasi <span class="text-red-600">*</span></label>
                        <input type="text" name="app_name" value="{{ old('app_name', $currentGroup['app_name']['value'] ?? '') }}" required
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Nama platform resmi yang ditampilkan pada header dan email sistem.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Resmi Platform <span class="text-red-600">*</span></label>
                        <input type="email" name="app_email" value="{{ old('app_email', $currentGroup['app_email']['value'] ?? '') }}" required
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Telepon Hotline Resmi <span class="text-red-600">*</span></label>
                        <input type="text" name="app_phone" value="{{ old('app_phone', $currentGroup['app_phone']['value'] ?? '') }}" required
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Zona Waktu Sistem <span class="text-red-600">*</span></label>
                        <select name="timezone" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            @php $tz = $currentGroup['timezone']['value'] ?? 'Asia/Jakarta'; @endphp
                            <option value="Asia/Jakarta" {{ $tz === 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Asia/Jakarta - UTC+7)</option>
                            <option value="Asia/Makassar" {{ $tz === 'Asia/Makassar' ? 'selected' : '' }}>WITA (Asia/Makassar - UTC+8)</option>
                            <option value="Asia/Jayapura" {{ $tz === 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Asia/Jayapura - UTC+9)</option>
                            <option value="UTC" {{ $tz === 'UTC' ? 'selected' : '' }}>UTC</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Format Tampilan Tanggal <span class="text-red-600">*</span></label>
                        <select name="date_format" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            @php $df = $currentGroup['date_format']['value'] ?? 'd/m/Y'; @endphp
                            <option value="d/m/Y" {{ $df === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY (Contoh: {{ date('d/m/Y') }})</option>
                            <option value="d F Y" {{ $df === 'd F Y' ? 'selected' : '' }}>DD MMMM YYYY (Contoh: {{ date('d F Y') }})</option>
                            <option value="Y-m-d" {{ $df === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (ISO Format)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Format Mata Uang <span class="text-red-600">*</span></label>
                        <input type="text" name="currency_format" value="{{ old('currency_format', $currentGroup['currency_format']['value'] ?? 'IDR (Rp)') }}" required
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Bahasa Standar Sistem <span class="text-red-600">*</span></label>
                        <select name="locale" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            @php $loc = $currentGroup['locale']['value'] ?? 'id'; @endphp
                            <option value="id" {{ $loc === 'id' ? 'selected' : '' }}>Bahasa Indonesia (ID)</option>
                            <option value="en" {{ $loc === 'en' ? 'selected' : '' }}>English (US)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Unggah Logo Platform (PNG, SVG, WEBP max 2MB)</label>
                        <input type="file" name="app_logo" accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                        @if(!empty($currentGroup['app_logo']['value']))
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-[11px] text-slate-500">Logo saat ini:</span>
                                <img src="{{ $currentGroup['app_logo']['value'] }}" alt="App Logo" class="h-6 object-contain bg-slate-50 p-1 rounded">
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Kantor Pusat <span class="text-red-600">*</span></label>
                    <textarea name="head_office_address" rows="2" required
                              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('head_office_address', $currentGroup['head_office_address']['value'] ?? '') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi Ringkas Platform</label>
                    <textarea name="app_description" rows="2"
                              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('app_description', $currentGroup['app_description']['value'] ?? '') }}</textarea>
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-100">
                    <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Simpan Perubahan General Settings
                    </button>
                </div>
            </form>
        @endif

        <!-- ================= B. BUSINESS SETTINGS ================= -->
        @if ($activeTab === 'business')
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h3 class="text-base font-extrabold text-slate-900">Pengaturan Bisnis & Legal Dealer (Business Settings)</h3>
                <p class="text-xs text-slate-500 mt-1">Kelola entitas badan hukum perusahaan, alamat resmi, nomor izin usaha, dan jam operasional pusat.</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="group" value="business">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Resmi Badan Hukum Perusahaan <span class="text-red-600">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name', $currentGroup['company_name']['value'] ?? '') }}" required
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Slogan / Tagline Korporasi</label>
                        <input type="text" name="company_tagline" value="{{ old('company_tagline', $currentGroup['company_tagline']['value'] ?? '') }}"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Telepon Kantor Perusahaan <span class="text-red-600">*</span></label>
                        <input type="text" name="company_phone" value="{{ old('company_phone', $currentGroup['company_phone']['value'] ?? '') }}" required
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Hubungan Korporasi <span class="text-red-600">*</span></label>
                        <input type="email" name="company_email" value="{{ old('company_email', $currentGroup['company_email']['value'] ?? '') }}" required
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Informasi Showroom & Dealer Utama <span class="text-red-600">*</span></label>
                        <input type="text" name="central_dealer_info" value="{{ old('central_dealer_info', $currentGroup['central_dealer_info']['value'] ?? '') }}" required
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pengaturan Jam Operasional Showroom <span class="text-red-600">*</span></label>
                        <input type="text" name="business_hours" value="{{ old('business_hours', $currentGroup['business_hours']['value'] ?? '') }}" required
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Surat Perusahaan <span class="text-red-600">*</span></label>
                    <textarea name="company_address" rows="2" required
                              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('company_address', $currentGroup['company_address']['value'] ?? '') }}</textarea>
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-100">
                    <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Simpan Perubahan Business Settings
                    </button>
                </div>
            </form>
        @endif

        <!-- ================= C. BOOKING SETTINGS ================= -->
        @if ($activeTab === 'booking')
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h3 class="text-base font-extrabold text-slate-900">Pengaturan Pemesanan Unit & Reservasi (Booking Settings)</h3>
                <p class="text-xs text-slate-500 mt-1">Konfigurasi uang tanda jadi default, masa berlaku reservasi fisik, serta kuota harian bengkel & test drive.</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="group" value="booking">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Besaran Uang Tanda Jadi Default (Rp) <span class="text-red-600">*</span></label>
                        <input type="number" name="default_booking_fee" value="{{ old('default_booking_fee', $currentGroup['default_booking_fee']['value'] ?? 5000000) }}" required min="0" step="100000"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Uang muka tanda jadi untuk pemesanan SPK unit kendaraan baru.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Batas Waktu Pembayaran Tanda Jadi (Jam) <span class="text-red-600">*</span></label>
                        <input type="number" name="booking_payment_timeout_hours" value="{{ old('booking_payment_timeout_hours', $currentGroup['booking_payment_timeout_hours']['value'] ?? 24) }}" required min="1" max="168"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Transaksi kedaluwarsa jika pelanggan tidak menyelesaikan pembayaran Snap.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Durasi Penahanan Stok Unit / Reservasi (Hari) <span class="text-red-600">*</span></label>
                        <input type="number" name="reservation_duration_days" value="{{ old('reservation_duration_days', $currentGroup['reservation_duration_days']['value'] ?? 3) }}" required min="1" max="30"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Durasi status reserved pada inventaris dealer sebelum dilepas kembali.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Durasi Sesi Test Drive (Menit) <span class="text-red-600">*</span></label>
                        <input type="number" name="test_drive_slot_duration_minutes" value="{{ old('test_drive_slot_duration_minutes', $currentGroup['test_drive_slot_duration_minutes']['value'] ?? 60) }}" required min="15" max="180"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kapasitas Maksimal Sesi Test Drive Harian <span class="text-red-600">*</span></label>
                        <input type="number" name="test_drive_max_daily_slots" value="{{ old('test_drive_max_daily_slots', $currentGroup['test_drive_max_daily_slots']['value'] ?? 8) }}" required min="1" max="50"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kapasitas Maksimal Servis Stall per Hari <span class="text-red-600">*</span></label>
                        <input type="number" name="service_max_daily_capacity" value="{{ old('service_max_daily_capacity', $currentGroup['service_max_daily_capacity']['value'] ?? 12) }}" required min="1" max="100"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Syarat & Ketentuan Pembatalan Pemesanan (Cancellation Terms) <span class="text-red-600">*</span></label>
                    <textarea name="cancellation_terms" rows="3" required
                              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('cancellation_terms', $currentGroup['cancellation_terms']['value'] ?? '') }}</textarea>
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-100">
                    <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Simpan Perubahan Booking Settings
                    </button>
                </div>
            </form>
        @endif

        <!-- ================= D. NOTIFICATION SETTINGS ================= -->
        @if ($activeTab === 'notification')
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h3 class="text-base font-extrabold text-slate-900">Pengaturan Notifikasi & Pengingat Terpusat (Notification Settings)</h3>
                <p class="text-xs text-slate-500 mt-1">Atur kanal pengiriman email, WhatsApp, serta pemicu notifikasi otomatis saat pemesanan & perawatan unit.</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="group" value="notification">

                <div class="space-y-4">
                    <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200 bg-slate-50/60">
                        <input type="checkbox" name="notification_email_enabled" value="1" id="notif_email"
                               {{ !empty($currentGroup['notification_email_enabled']['value']) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-600">
                        <div>
                            <label for="notif_email" class="text-xs font-bold text-slate-800 cursor-pointer">Kanal Notifikasi Email Resmi Aktif</label>
                            <p class="text-[11px] text-slate-500 mt-0.5">Kirimkan email bukti SPK, konfirmasi pembayaran Midtrans, dan kuitansi kepada pelanggan.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200 bg-slate-50/60">
                        <input type="checkbox" name="notification_whatsapp_enabled" value="1" id="notif_wa"
                               {{ !empty($currentGroup['notification_whatsapp_enabled']['value']) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-600">
                        <div>
                            <label for="notif_wa" class="text-xs font-bold text-slate-800 cursor-pointer">Kanal Pengingat WhatsApp Resmi Aktif</label>
                            <p class="text-[11px] text-slate-500 mt-0.5">Kirimkan ringkasan jadwal servis dan penawaran SPK langsung ke WhatsApp pelanggan.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200 bg-slate-50/60">
                        <input type="checkbox" name="notify_on_booking_created" value="1" id="notif_bc"
                               {{ !empty($currentGroup['notify_on_booking_created']['value']) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-600">
                        <div>
                            <label for="notif_bc" class="text-xs font-bold text-slate-800 cursor-pointer">Notifikasi Otomatis Pembuatan Booking SPK Baru</label>
                            <p class="text-[11px] text-slate-500 mt-0.5">Beritahu staf sales consultant saat pelanggan menyelesaikan pengajuan pemesanan unit.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200 bg-slate-50/60">
                        <input type="checkbox" name="notify_on_booking_confirmed" value="1" id="notif_bconf"
                               {{ !empty($currentGroup['notify_on_booking_confirmed']['value']) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-600">
                        <div>
                            <label for="notif_bconf" class="text-xs font-bold text-slate-800 cursor-pointer">Notifikasi Konfirmasi Pemesanan SPK</label>
                            <p class="text-[11px] text-slate-500 mt-0.5">Kirim notifikasi kepada customer ketika pemesanan unit telah diverifikasi oleh manajer dealer.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-200 bg-slate-50/60">
                        <input type="checkbox" name="notify_on_payment_success" value="1" id="notif_paysuc"
                               {{ !empty($currentGroup['notify_on_payment_success']['value']) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-600">
                        <div>
                            <label for="notif_paysuc" class="text-xs font-bold text-slate-800 cursor-pointer">Notifikasi Pembayaran Berhasil Terverifikasi</label>
                            <p class="text-[11px] text-slate-500 mt-0.5">Terbitkan kuitansi digital otomatis saat webhook Midtrans mencatat status settlement lunas.</p>
                        </div>
                    </div>
                </div>

                <div class="max-w-md pt-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jadwal Pengingat Servis Berkala (Berapa Hari Sebelumnya) <span class="text-red-600">*</span></label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="service_reminder_days_before" value="{{ old('service_reminder_days_before', $currentGroup['service_reminder_days_before']['value'] ?? 7) }}" required min="1" max="30"
                               class="w-32 rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <span class="text-xs text-slate-600 font-semibold">Hari sebelum jadwal tanggal servis</span>
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-100">
                    <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Simpan Perubahan Notification Settings
                    </button>
                </div>
            </form>
        @endif

        <!-- ================= E. PAYMENT SETTINGS ================= -->
        @if ($activeTab === 'payment')
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h3 class="text-base font-extrabold text-slate-900">Pengaturan Pembayaran & Gateway Midtrans (Payment Settings)</h3>
                <p class="text-xs text-slate-500 mt-1">Konfigurasi opsi pembayaran aktif dan pantau status integrasi gateway pembayaran Midtrans Snap.</p>
            </div>

            <!-- Security & Midtrans Infrastructure Notice -->
            <div class="rounded-xl bg-blue-50/70 border border-blue-200 p-4.5 text-xs text-blue-900 mb-6">
                <div class="flex items-start gap-3">
                    <div class="p-1 rounded-lg bg-blue-600 text-white mt-0.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="font-extrabold text-blue-950">Protokol Keamanan Gateway Pembayaran</h4>
                        <p class="text-[11px] text-blue-800 leading-relaxed">
                            Server Key Midtrans dan kredensial sensitif lainnya disimpan secara terlindungi di layer environment (<code class="bg-blue-100 px-1 py-0.5 rounded font-mono text-blue-950">.env</code>) dan <strong>tidak pernah disimpan maupun ditampilkan secara terbuka</strong> di tabel pengaturan database guna mencegah kebocoran data transaksi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Midtrans Status Summary Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Mode Gateway</span>
                    <span class="text-xs font-extrabold text-slate-800 mt-0.5 block">{{ $paymentInfo['environment'] }}</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Client Key (Publik)</span>
                    <span class="text-xs font-mono font-bold text-slate-800 mt-0.5 block">{{ $paymentInfo['client_key'] }}</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Enkripsi 3DS & Sanitized</span>
                    <span class="text-xs font-extrabold text-emerald-700 mt-0.5 block">{{ $paymentInfo['is_3ds'] }} &bull; {{ $paymentInfo['is_sanitized'] }}</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Server Key Vault</span>
                    <span class="text-xs font-extrabold text-emerald-700 mt-0.5 block">{{ $paymentInfo['server_key_status'] }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="group" value="payment">

                <div class="max-w-md">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Mode Gateway Transaksi <span class="text-red-600">*</span></label>
                    <select name="payment_gateway_mode" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        @php $pm = $currentGroup['payment_gateway_mode']['value'] ?? 'sandbox'; @endphp
                        <option value="sandbox" {{ $pm === 'sandbox' ? 'selected' : '' }}>Mode Sandbox (Pengujian & Simulasi Pembayaran)</option>
                        <option value="production" {{ $pm === 'production' ? 'selected' : '' }}>Mode Production (Transaksi Finansial Nyata)</option>
                    </select>
                </div>

                <div class="space-y-3 pt-2">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Opsi Metode Pembayaran Aktif untuk Konsumen:</h4>

                    <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 bg-white">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Virtual Account Antar-Bank (BCA, Mandiri, BNI, BRI, Permata)</span>
                            <span class="text-[11px] text-slate-400">Pembayaran instan 24 jam dengan verifikasi otomatis via Midtrans.</span>
                        </div>
                        <input type="checkbox" name="payment_bank_transfer_active" value="1"
                               {{ !empty($currentGroup['payment_bank_transfer_active']['value']) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                    </div>

                    <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 bg-white">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Kartu Kredit / Debit Online (Visa, MasterCard, JCB)</span>
                            <span class="text-[11px] text-slate-400">Otorisasi perbankan aman dengan protokol One-Time Password 3D-Secure.</span>
                        </div>
                        <input type="checkbox" name="payment_credit_card_active" value="1"
                               {{ !empty($currentGroup['payment_credit_card_active']['value']) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                    </div>

                    <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 bg-white">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">QRIS Nasional & e-Wallet Resmi (GoPay)</span>
                            <span class="text-[11px] text-slate-400">Pindai kode QRIS dinamis melalui mobile banking atau aplikasi dompet digital.</span>
                        </div>
                        <input type="checkbox" name="payment_qris_active" value="1"
                               {{ !empty($currentGroup['payment_qris_active']['value']) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                    </div>

                    <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 bg-white">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Pelunasan Tunai di Meja Kasir Dealer</span>
                            <span class="text-[11px] text-slate-400">Penyelesaian pembayaran offline dengan verifikasi manual kasir showroom.</span>
                        </div>
                        <input type="checkbox" name="payment_cash_active" value="1"
                               {{ !empty($currentGroup['payment_cash_active']['value']) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-100">
                    <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Simpan Perubahan Payment Settings
                    </button>
                </div>
            </form>
        @endif

    </div>
</div>
@endsection

