@extends('layouts.public')

@section('title', 'Portal Layanan Pelanggan — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Welcome Hero Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-slate-900 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-6 sm:p-8 text-white shadow-sm border border-slate-800" style="background-color: #0f172a;">
            <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-red-600/20 blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-red-600/20 px-2.5 py-0.5 text-[10px] font-bold text-red-400 border border-red-500/30">
                            TOYOTA AUTOCONNECT
                        </span>
                        <span class="text-xs text-slate-300">Selamat datang kembali</span>
                    </div>
                    <h2 class="mt-2 text-xl sm:text-2xl font-extrabold text-white">Halo, {{ $user->name }}</h2>
                    <p class="mt-1 text-xs text-slate-300 max-w-2xl leading-relaxed">
                        Pantau status pengajuan jadwal uji kemudi (test drive), surat penawaran harga resmi, pemesanan unit (SPK), dan konsultasi dealer langsung dari portal akun pribadi Anda.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 flex-shrink-0">
                    <a href="{{ route('vehicles.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        Jelajahi Katalog Mobil
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Links Grid for Test Drive, SPK, Offers -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('customer.test-drives.index') }}"
               class="rounded-2xl bg-white p-5 border border-slate-200 shadow-2xs hover:border-red-300 hover:shadow-md transition flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Jadwal Uji Kemudi</span>
                    <p class="text-base font-extrabold text-slate-900 mt-0.5 group-hover:text-red-600 transition">Test Drive Saya</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">{{ $testDriveCount ?? 0 }} Sesi Terdaftar</p>
                </div>
                <div class="h-11 w-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center group-hover:bg-red-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </a>

            <a href="{{ route('customer.offers.index') }}"
               class="rounded-2xl bg-white p-5 border border-slate-200 shadow-2xs hover:border-blue-300 hover:shadow-md transition flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Penawaran Resmi</span>
                    <p class="text-base font-extrabold text-slate-900 mt-0.5 group-hover:text-blue-600 transition">Quotation Mobil</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">{{ $offerCount ?? 0 }} Penawaran Aktif</p>
                </div>
                <div class="h-11 w-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
            </a>

            <a href="{{ route('customer.bookings.index') }}"
               class="rounded-2xl bg-white p-5 border border-slate-200 shadow-2xs hover:border-emerald-300 hover:shadow-md transition flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Pemesanan Kendaraan</span>
                    <p class="text-base font-extrabold text-slate-900 mt-0.5 group-hover:text-emerald-600 transition">Pemesanan SPK</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">{{ $bookingCount ?? 0 }} Unit Dipesan</p>
                </div>
                <div class="h-11 w-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </a>
        </div>

        <!-- Quick Links Grid for Service, Reminders, Tickets (Tahap 8) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('customer.service-bookings.index') }}"
               class="rounded-2xl bg-white p-5 border border-slate-200 shadow-2xs hover:border-red-300 hover:shadow-md transition flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Bengkel Resmi Toyota</span>
                    <p class="text-base font-extrabold text-slate-900 mt-0.5 group-hover:text-red-600 transition">Booking Servis</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">{{ $serviceBookingCount ?? 0 }} Jadwal Terdaftar</p>
                </div>
                <div class="h-11 w-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center group-hover:bg-red-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27-2.828-2.828 3.27-3.27a4.5 4.5 0 0 0-6.32 4.486c.118.58.094 1.193-.07 1.743" />
                    </svg>
                </div>
            </a>

            <a href="{{ route('customer.service-reminders.index') }}"
               class="rounded-2xl bg-white p-5 border border-slate-200 shadow-2xs hover:border-amber-300 hover:shadow-md transition flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Jadwal Berkala</span>
                    <p class="text-base font-extrabold text-slate-900 mt-0.5 group-hover:text-amber-600 transition">Pengingat Servis</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">{{ $reminderCount ?? 0 }} Pengingat Mendatang</p>
                </div>
                <div class="h-11 w-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </div>
            </a>

            <a href="{{ route('customer.tickets.index') }}"
               class="rounded-2xl bg-white p-5 border border-slate-200 shadow-2xs hover:border-purple-300 hover:shadow-md transition flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Customer Care</span>
                    <p class="text-base font-extrabold text-slate-900 mt-0.5 group-hover:text-purple-600 transition">Pusat Tiket CS</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">{{ $ticketCount ?? 0 }} Tiket Aktif</p>
                </div>
                <div class="h-11 w-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.502 49.188 49.188 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                    </svg>
                </div>
            </a>
        </div>

        <!-- Live Customer Inquiry Metrics Grid (Strictly from DB) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Stat 1 -->
            <div class="rounded-2xl bg-white p-5 border border-slate-200 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Permintaan Diajukan</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['totalInquiries'] }}</p>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">Inquiry &amp; Konsultasi unit</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
            </div>

            <!-- Stat 2 -->
            <div class="rounded-2xl bg-white p-5 border border-slate-200 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sedang Diproses Staf</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['inProgressInquiries'] }}</p>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">Menunggu follow-up sales</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>

            <!-- Stat 3 -->
            <div class="rounded-2xl bg-white p-5 border border-slate-200 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Telah Direspons / Selesai</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['resolvedInquiries'] }}</p>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">Penawaran &amp; konsultasi selesai</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Recent Inquiries Section -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Permintaan &amp; Inquiry Terbaru Anda</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar permintaan jadwal test drive dan penawaran unit yang Anda kirimkan</p>
                </div>
                <a href="{{ route('customer.inquiries.index') }}" class="text-xs font-semibold text-red-600 hover:text-red-700 transition flex items-center gap-1">
                    Lihat Semua
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[850px]">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-5 whitespace-nowrap min-w-[180px]">Model Kendaraan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[220px]">Topik Permintaan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[160px]">Tanggal Pengiriman</th>
                            <th class="py-3.5 px-4 whitespace-nowrap text-center min-w-[110px]">Status</th>
                            <th class="py-3.5 px-5 whitespace-nowrap text-right min-w-[90px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentInquiries as $inq)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-5 whitespace-nowrap">
                                    @if ($inq->vehicle)
                                        <div class="font-bold text-slate-900">{{ $inq->vehicle->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $inq->vehicle->category }}</div>
                                    @else
                                        <div class="font-bold text-slate-700">Pertanyaan Umum Showroom</div>
                                        <div class="text-[11px] text-slate-400">Non-Unit</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-medium text-slate-900">{{ $inq->subject }}</div>
                                    <div class="text-xs text-slate-500 truncate max-w-xs">{{ Str::limit($inq->message, 60) }}</div>
                                </td>
                                <td class="py-4 px-4 text-slate-500 font-mono text-xs whitespace-nowrap">
                                    {{ $inq->submitted_at->format('d M Y, H:i') }} WIB
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $inq->status_badge_class }}">
                                        {{ $inq->status_label }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-right whitespace-nowrap">
                                    <a href="{{ route('customer.inquiries.show', $inq) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                                        Detail
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-xs text-slate-400">
                                    Anda belum memiliki permintaan inquiry atau konsultasi unit.
                                    <div class="mt-2">
                                        <a href="{{ route('vehicles.index') }}" class="font-bold text-red-600 hover:underline">
                                            Pilih mobil di katalog &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Featured Vehicle Recommendations -->
        @if ($featuredVehicles->count() > 0)
            <div class="rounded-2xl bg-white p-5 border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Rekomendasi Lini Mobil Toyota Pilihan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Model terpopuler siap uji kemudi dan konsultasi pemesanan unit</p>
                    </div>
                    <a href="{{ route('vehicles.index') }}" class="text-xs font-semibold text-red-600 hover:text-red-700 transition flex items-center gap-1">
                        Semua Model
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach ($featuredVehicles as $vehicle)
                        <div class="rounded-xl border border-slate-200 p-4 hover:border-red-300 hover:shadow-xs transition flex flex-col justify-between">
                            <div>
                                <span class="inline-block rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                    {{ $vehicle->category }}
                                </span>
                                <h5 class="text-sm font-bold text-slate-900 mt-1.5">{{ $vehicle->name }}</h5>
                                <p class="text-xs font-semibold text-red-600 mt-1">
                                    {{ $vehicle->formatted_price }}
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <a href="{{ route('vehicles.show', $vehicle->slug) }}"
                                   class="text-xs font-semibold text-slate-700 hover:text-red-600 transition flex items-center justify-between">
                                    Detail Spesifikasi &amp; Konsultasi
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
