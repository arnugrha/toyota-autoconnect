@extends('layouts.public')

@section('title', 'Preferensi Notifikasi — Toyota AutoConnect')

@section('content')
<div class="bg-slate-50 min-h-[85vh] py-8 sm:py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Header & Breadcrumb -->
        <div class="border-b border-slate-200/80 pb-6">
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ route('home') }}" class="hover:text-red-600 transition">Beranda</a>
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
                <a href="{{ route('notifications.index') }}" class="hover:text-red-600 transition">Pusat Notifikasi</a>
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
                <span class="text-slate-800 font-semibold">Pengaturan Preferensi</span>
            </nav>

            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-600 text-white shadow-md">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Preferensi Notifikasi &amp; Komunikasi</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Tentukan bagaimana dan melalui saluran apa Toyota AutoConnect menghubungi Anda</p>
                </div>
            </div>
        </div>


        <form action="{{ route('customer.notifications.preferences.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Saluran Notifikasi (Channels) -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                <div>
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">1. Saluran Pengiriman Notifikasi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih media yang Anda kehendaki untuk menerima pemberitahuan</p>
                </div>

                <div class="divide-y divide-slate-100">
                    <!-- In-App Toggle -->
                    <label class="flex items-start justify-between py-3.5 cursor-pointer group">
                        <div class="pr-4">
                            <span class="text-xs font-bold text-slate-900 group-hover:text-red-600 transition">Notifikasi Dalam Aplikasi (In-App)</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Menampilkan lonceng notifikasi dan pembaruan langsung di portal Toyota AutoConnect.</p>
                        </div>
                        <input type="checkbox" name="in_app" value="1" {{ $preferences->in_app ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500 mt-1">
                    </label>

                    <!-- Email Toggle -->
                    <label class="flex items-start justify-between py-3.5 cursor-pointer group">
                        <div class="pr-4">
                            <span class="text-xs font-bold text-slate-900 group-hover:text-red-600 transition">Notifikasi Melalui Email</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Menerima ringkasan transaksi, invoice pembayaran, dan konfirmasi jadwal ke <strong>{{ $user->email }}</strong>.</p>
                        </div>
                        <input type="checkbox" name="email" value="1" {{ $preferences->email ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500 mt-1">
                    </label>

                    <!-- WhatsApp Toggle -->
                    <label class="flex items-start justify-between py-3.5 cursor-pointer group">
                        <div class="pr-4">
                            <span class="text-xs font-bold text-slate-900 group-hover:text-red-600 transition">Notifikasi WhatsApp Resmi</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Menerima pemberitahuan instan resmi melalui pesan WhatsApp ke nomor 
                                <strong>{{ $user->phone ?? 'Belum diisi di profil' }}</strong>.
                            </p>
                        </div>
                        <input type="checkbox" name="whatsapp" value="1" {{ $preferences->whatsapp ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500 mt-1">
                    </label>
                </div>
            </div>

            <!-- Kategori Notifikasi (Topics) -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                <div>
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">2. Kategori Informasi &amp; Layanan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tentukan jenis informasi yang ingin Anda dapatkan</p>
                </div>

                <div class="divide-y divide-slate-100">
                    <!-- Transactional -->
                    <label class="flex items-start justify-between py-3.5 cursor-pointer group">
                        <div class="pr-4">
                            <span class="text-xs font-bold text-slate-900 group-hover:text-red-600 transition">Pemberitahuan Transaksional (Pemesanan SPK, Pembayaran &amp; Servis)</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Informasi terkait kelulusan kredit leasing, verifikasi pembayaran Midtrans, dan perkembangan servis mobil Anda.</p>
                        </div>
                        <input type="checkbox" name="transactional" value="1" {{ $preferences->transactional ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500 mt-1">
                    </label>

                    <!-- Service Reminders -->
                    <label class="flex items-start justify-between py-3.5 cursor-pointer group">
                        <div class="pr-4">
                            <span class="text-xs font-bold text-slate-900 group-hover:text-red-600 transition">Pengingat Servis Berkala &amp; Perawatan (Service Reminder)</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Peringatan otomatis saat kendaraan mendekati waktu atau kilometer jatuh tempo servis berkala.</p>
                        </div>
                        <input type="checkbox" name="service_reminders" value="1" {{ $preferences->service_reminders ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500 mt-1">
                    </label>

                    <!-- Promotional -->
                    <label class="flex items-start justify-between py-3.5 cursor-pointer group">
                        <div class="pr-4">
                            <span class="text-xs font-bold text-slate-900 group-hover:text-red-600 transition">Program Promo, Promo Servis &amp; Diskon Khusus</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Pemberitahuan peluncuran model terbaru Toyota, penawaran bunga rendah leasing, dan promo bengkel resmi.</p>
                        </div>
                        <input type="checkbox" name="promotional" value="1" {{ $preferences->promotional ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500 mt-1">
                    </label>
                </div>
            </div>

            <!-- WhatsApp Consent Box -->
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-6 space-y-3">
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="whatsapp_consent" id="whatsapp_consent" value="1" 
                           {{ $preferences->whatsapp_consent ? 'checked' : '' }}
                           class="h-5 w-5 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500 mt-0.5">
                    
                    <label for="whatsapp_consent" class="text-xs text-slate-800 leading-relaxed cursor-pointer font-medium">
                        <strong class="font-bold text-slate-900 block mb-1">Persetujuan Resmi Pengiriman Pesan WhatsApp (Opt-in Consent)</strong>
                        Saya menyatakan setuju untuk menerima pesan pemberitahuan transaksional, pengingat jadwal, dan informasi resmi dari jaringan dealer <strong>Toyota AutoConnect</strong> melalui platform WhatsApp resmi. Persetujuan ini dapat diubah atau dicabut kapan pun melalui halaman ini.
                        
                        @if($preferences->whatsapp_consent && $preferences->whatsapp_consent_at)
                            <span class="block mt-1 text-[11px] text-emerald-700 font-bold">
                                &check; Persetujuan aktif sejak {{ $preferences->whatsapp_consent_at->format('d M Y, H:i') }} WIB.
                            </span>
                        @endif
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('notifications.index') }}" 
                   class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    Kembali
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-extrabold tracking-wide shadow-md transition">
                    Simpan Pengaturan Preferensi
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

