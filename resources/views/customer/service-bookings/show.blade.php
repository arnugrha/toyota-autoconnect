@extends('layouts.public')

@section('title', "Detail Booking Servis {$booking->booking_code} — Toyota AutoConnect")

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10" x-data="{ showCancelModal: false }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('customer.service-bookings.index') }}" class="hover:text-red-600 transition">Booking Servis</a>
                    <span>/</span>
                    <span class="text-slate-900 font-bold font-mono">{{ $booking->booking_code }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight font-mono">{{ $booking->booking_code }}</h1>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold border {{ $booking->status_badge_class }}">
                        {{ $booking->status_label }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('customer.service-bookings.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    &larr; Kembali
                </a>

                @if($booking->is_cancellable)
                    <button type="button"
                            @click="showCancelModal = true"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-300 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition shadow-2xs">
                        <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        Batalkan Booking
                    </button>
                @endif
            </div>
        </div>


        <!-- Grid Layout: Info Left, Timeline Right -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                <!-- Service Order / Invoice Summary Banner -->
                @if($booking->serviceOrder)
                    @php
                        $wo = $booking->serviceOrder;
                        $isFinished = in_array($wo->status, ['completed', 'delivered']);
                    @endphp
                    <div class="rounded-2xl border {{ $isFinished ? 'border-emerald-200 bg-emerald-50/80' : 'border-blue-200 bg-blue-50/70' }} p-5 shadow-2xs space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded {{ $isFinished ? 'bg-emerald-600' : 'bg-blue-600' }} text-white font-mono text-[10px] font-bold">
                                        {{ $isFinished ? 'INVOICE SERVIS TERSEDIA' : 'WORK ORDER AKTIF' }}
                                    </span>
                                    <span class="text-xs font-bold {{ $isFinished ? 'text-emerald-950' : 'text-blue-900' }} font-mono">
                                        {{ $wo->invoice_number ?? $wo->order_number }}
                                    </span>
                                    @if($isFinished)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $wo->payment_badge_class }}">
                                            {{ $wo->payment_status_label }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs {{ $isFinished ? 'text-emerald-900' : 'text-blue-800' }} mt-1">
                                    Status Pengerjaan Bengkel: <strong class="font-bold">{{ $wo->status_label }}</strong>
                                </p>
                            </div>
                            <a href="{{ route('customer.service-histories.show', $wo) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg {{ $isFinished ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-blue-600 hover:bg-blue-700' }} px-4 py-2 text-xs font-bold text-white shadow-xs transition">
                                {{ $isFinished ? 'Buka & Cetak Invoice Servis' : 'Lihat Rincian & Estimasi Biaya' }} &rarr;
                            </a>
                        </div>

                        <div class="grid grid-cols-3 gap-2 pt-3 border-t {{ $isFinished ? 'border-emerald-200/80' : 'border-blue-200/80' }} text-xs">
                            <div class="rounded-lg bg-white/80 p-2.5 border border-white">
                                <span class="text-[10px] text-slate-500 block">Biaya Jasa Servis</span>
                                <span class="font-mono font-bold text-slate-900">{{ $wo->formatted_labor_cost }}</span>
                            </div>
                            <div class="rounded-lg bg-white/80 p-2.5 border border-white">
                                <span class="text-[10px] text-slate-500 block">Suku Cadang</span>
                                <span class="font-mono font-bold text-slate-900">{{ $wo->formatted_parts_cost }}</span>
                            </div>
                            <div class="rounded-lg bg-white/80 p-2.5 border border-white">
                                <span class="text-[10px] text-slate-500 block">Total Tagihan</span>
                                <span class="font-mono font-black text-red-600">{{ $wo->formatted_final_cost }}</span>
                            </div>
                        </div>
                    </div>

                    @include('customer.partials.spare-part-approval-card', ['order' => $wo])
                @endif

                <!-- Card 1: Data Jadwal & Bengkel -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-5">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
                        <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                        </svg>
                        Informasi Jadwal &amp; Bengkel Resmi
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[11px] text-slate-400 block font-medium">Jadwal Tanggal Kedatangan:</span>
                            <span class="text-sm font-bold text-slate-900 mt-0.5 block">
                                {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('l, d F Y') }}
                            </span>
                            <span class="text-xs font-bold text-red-600 mt-1 block">
                                Sesi Pukul {{ $booking->booking_time }} WIB
                            </span>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[11px] text-slate-400 block font-medium">Bengkel Resmi Toyota:</span>
                            <span class="text-sm font-bold text-slate-900 mt-0.5 block">{{ $booking->branch?->name }}</span>
                            <span class="text-xs text-slate-600 mt-0.5 block">{{ $booking->branch?->address }}, {{ $booking->branch?->city }}</span>
                            @if($booking->branch?->phone)
                                <span class="text-[11px] text-slate-400 block mt-1">Telp: {{ $booking->branch?->phone }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card 2: Kendaraan & Layanan Servis -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-5">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
                        <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27-2.828-2.828 3.27-3.27a4.5 4.5 0 0 0-6.32 4.486c.118.58.094 1.193-.07 1.743" />
                        </svg>
                        Detail Kendaraan &amp; Paket Servis
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-[11px] text-slate-400 block">Unit Kendaraan:</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $booking->vehicle_name }}</span>
                            <span class="inline-block mt-1 px-2.5 py-0.5 bg-slate-100 text-slate-800 font-mono text-xs font-bold rounded border border-slate-200">
                                {{ $booking->license_plate }}
                            </span>
                            @if($booking->current_mileage)
                                <p class="text-[11px] text-slate-500 mt-1">Kilometer Odometer: <strong>{{ number_format($booking->current_mileage, 0, ',', '.') }} KM</strong></p>
                            @endif
                        </div>

                        <div>
                            <span class="text-[11px] text-slate-400 block">Jenis Layanan:</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $booking->serviceType?->name ?? 'Servis Berkala' }}</span>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $booking->serviceType?->description }}</p>
                            <span class="text-xs font-bold text-red-600 mt-1.5 block">Estimasi Biaya: {{ $booking->serviceType?->formatted_price ?? '-' }}</span>
                        </div>
                    </div>

                    @if($booking->customer_complaints)
                        <div class="pt-3 border-t border-slate-100 text-xs">
                            <span class="text-[11px] text-slate-400 block font-semibold mb-1">Keluhan / Catatan Tambahan Anda:</span>
                            <div class="p-3.5 bg-slate-50 rounded-xl text-slate-700 italic border border-slate-100">
                                "{{ $booking->customer_complaints }}"
                            </div>
                        </div>
                    @endif

                    @if($booking->cancellation_reason)
                        <div class="pt-3 border-t border-rose-100 text-xs">
                            <span class="text-[11px] text-rose-500 block font-semibold mb-1">Alasan Pembatalan:</span>
                            <div class="p-3.5 bg-rose-50 rounded-xl text-rose-800 border border-rose-100">
                                {{ $booking->cancellation_reason }}
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Sidebar: Panduan Kedatangan & Bantuan -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                        Prosedur Kedatangan Servis
                    </h3>

                    <ol class="text-xs text-slate-600 space-y-3 list-decimal list-inside leading-relaxed">
                        <li>Datang <strong>15 menit</strong> sebelum jam sesi servis yang Anda pilih.</li>
                        <li>Tunjukkan <strong>Kode Booking ({{ $booking->booking_code }})</strong> atau STNK ke resepsionis bengkel.</li>
                        <li>Service Advisor kami akan melakukan pemeriksaan awal bersama Anda.</li>
                        <li>Anda dapat bersantai di ruang tunggu eksekutif Toyota dengan fasilitas Wi-Fi dan snack gratis.</li>
                    </ol>

                    <div class="pt-3 border-t border-slate-100">
                        <a href="{{ route('customer.tickets.create', ['category' => 'service_booking', 'subject' => 'Pertanyaan Booking ' . $booking->booking_code]) }}"
                           class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.502 49.188 49.188 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>
                            Butuh Bantuan? Buat Tiket
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Modal Cancel Booking -->
    <div x-show="showCancelModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div @click.away="showCancelModal = false"
             class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Batalkan Booking Servis</h3>
                <button type="button" @click="showCancelModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="text-xs text-slate-600">Apakah Anda yakin ingin membatalkan jadwal booking servis ini? Slot waktu Anda akan dialihkan untuk pelanggan lain.</p>

            <form action="{{ route('customer.service-bookings.cancel', $booking) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pembatalan <span class="text-red-600">*</span></label>
                    <textarea name="cancellation_reason"
                              required
                              rows="3"
                              placeholder="Contoh: Ada keperluan mendadak, ingin menjadwalkan ulang di hari lain."
                              class="w-full rounded-xl border border-slate-300 p-3 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button"
                            @click="showCancelModal = false"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition">
                        Konfirmasi Pembatalan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

