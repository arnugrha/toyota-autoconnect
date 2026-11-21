@extends('layouts.dashboard')

@section('title', "Detail Booking {$booking->booking_code} — Toyota AutoConnect")

@section('content')
<div class="space-y-6" x-data="{ showConfirmModal: false, showRescheduleModal: false, showRejectModal: false }">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.service-bookings.index') }}" class="hover:text-red-600 transition">Booking Servis</a>
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
            <a href="{{ route('admin.service-bookings.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                &larr; Kembali
            </a>

            @if($booking->status === 'pending')
                <button type="button"
                        @click="showConfirmModal = true"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Konfirmasi Booking &amp; Terbitkan WO
                </button>

                <button type="button"
                        @click="showRescheduleModal = true"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 px-3.5 py-2 text-xs font-bold text-amber-800 hover:bg-amber-100 transition shadow-2xs">
                    <svg class="h-4 w-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Ubah Jadwal (Reschedule)
                </button>

                <button type="button"
                        @click="showRejectModal = true"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-300 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-800 hover:bg-rose-100 transition shadow-2xs">
                    <svg class="h-4 w-4 text-rose-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Tolak Booking
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-2xs flex items-center gap-2">
            <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Linked Service Order Banner if exists -->
    @if($booking->serviceOrder)
        <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-blue-600 text-white font-mono text-[10px] font-bold">SERVICE ORDER RESMI</span>
                    <span class="text-xs font-bold text-blue-900 font-mono">{{ $booking->serviceOrder->order_number }}</span>
                </div>
                <p class="text-xs text-blue-800 mt-1">Status Progres Saat Ini: <strong class="font-bold">{{ $booking->serviceOrder->status_label }}</strong></p>
            </div>
            <a href="{{ route('admin.service-orders.show', $booking->serviceOrder) }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-blue-700 transition">
                Buka Service Order / SPK Servis &rarr;
            </a>
        </div>
    @endif

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <!-- Card 1: Data Pemohon & Kendaraan -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                    Informasi Pelanggan &amp; Kendaraan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Nama Pelanggan:</span>
                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $booking->user?->name }}</span>
                        <span class="text-slate-600 block mt-0.5">{{ $booking->user?->email }}</span>
                        <span class="text-slate-600 block font-semibold">{{ $booking->user?->phone ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Model Kendaraan:</span>
                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $booking->vehicle_name }}</span>
                        <span class="inline-block mt-1 px-2.5 py-0.5 bg-slate-100 text-slate-800 font-mono text-xs font-bold rounded border border-slate-200">
                            {{ $booking->license_plate }}
                        </span>
                        @if($booking->current_mileage)
                            <p class="text-slate-600 mt-1">Odometer Terakhir: <strong>{{ number_format($booking->current_mileage, 0, ',', '.') }} KM</strong></p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card 2: Jadwal & Layanan Servis -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                    Jadwal Kedatangan &amp; Layanan Servis
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] text-slate-400 block font-medium">Jadwal Tanggal &amp; Jam:</span>
                        <span class="text-sm font-bold text-slate-900 mt-0.5 block">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="text-xs font-bold text-red-600 mt-1 block">
                            Pukul {{ $booking->booking_time }} WIB
                        </span>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] text-slate-400 block font-medium">Layanan yang Dipilih:</span>
                        <span class="text-sm font-bold text-slate-900 mt-0.5 block">{{ $booking->serviceType?->name ?? 'Servis Berkala' }}</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Estimasi Biaya: {{ $booking->serviceType?->formatted_price ?? '-' }}</span>
                        <span class="text-xs text-slate-500 block">Durasi Estimasi: {{ $booking->serviceType?->estimated_duration_minutes ?? 60 }} Menit</span>
                    </div>
                </div>

                @if($booking->customer_complaints)
                    <div class="pt-3 border-t border-slate-100 text-xs">
                        <span class="text-[11px] text-slate-400 block font-semibold mb-1">Keluhan Pelanggan:</span>
                        <div class="p-3.5 bg-slate-50 rounded-xl text-slate-700 italic border border-slate-100">
                            "{{ $booking->customer_complaints }}"
                        </div>
                    </div>
                @endif

                @if($booking->rejection_reason)
                    <div class="pt-3 border-t border-rose-100 text-xs">
                        <span class="text-[11px] text-rose-500 block font-semibold mb-1">Alasan Penolakan:</span>
                        <div class="p-3.5 bg-rose-50 rounded-xl text-rose-800 border border-rose-100">
                            {{ $booking->rejection_reason }}
                        </div>
                    </div>
                @endif

                @if($booking->reschedule_reason)
                    <div class="pt-3 border-t border-amber-100 text-xs">
                        <span class="text-[11px] text-amber-700 block font-semibold mb-1">Alasan Penjadwalan Ulang:</span>
                        <div class="p-3.5 bg-amber-50 rounded-xl text-amber-800 border border-amber-100">
                            {{ $booking->reschedule_reason }}
                        </div>
                    </div>
                @endif
            </div>

        </div>

        <!-- Sidebar: Info Cabang & Audit -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                    Cabang Bengkel Resmi
                </h3>

                <div class="text-xs space-y-2 text-slate-700">
                    <p class="font-bold text-slate-900 text-sm">{{ $booking->branch?->name }}</p>
                    <p class="text-slate-500 leading-relaxed">{{ $booking->branch?->address }}, {{ $booking->branch?->city }}</p>
                    @if($booking->branch?->phone)
                        <p class="text-slate-500">Telp: {{ $booking->branch?->phone }}</p>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 text-[11px] space-y-1.5 text-slate-500">
                    <div class="flex justify-between">
                        <span>Diajukan Pada:</span>
                        <span class="font-semibold text-slate-800">{{ $booking->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($booking->confirmedByUser)
                        <div class="flex justify-between">
                            <span>Dikonfirmasi Oleh:</span>
                            <span class="font-semibold text-slate-800">{{ $booking->confirmedByUser->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Konfirmasi Booking -->
    <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div @click.away="showConfirmModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Konfirmasi Booking Servis</h3>
                <button type="button" @click="showConfirmModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="text-xs text-slate-600">Konfirmasi booking akan menerbitkan <strong>Service Order (Work Order)</strong> resmi dan mencatat slot servis pada jadwal bengkel.</p>

            <form action="{{ route('admin.service-bookings.confirm', $booking) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Teknisi / Mekanik yang Ditugaskan (Opsional)</label>
                    <input type="text"
                           name="technician_name"
                           placeholder="Contoh: Budi Santoso (Master Tech)"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showConfirmModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition">
                        Konfirmasi &amp; Terbitkan WO
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reschedule Booking -->
    <div x-show="showRescheduleModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div @click.away="showRescheduleModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Ubah Jadwal Booking (Reschedule)</h3>
                <button type="button" @click="showRescheduleModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.service-bookings.reschedule', $booking) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Baru <span class="text-red-600">*</span></label>
                    <input type="date"
                           name="booking_date"
                           required
                           min="{{ now()->toDateString() }}"
                           value="{{ $booking->booking_date }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Kedatangan Baru <span class="text-red-600">*</span></label>
                    <input type="text"
                           name="booking_time"
                           required
                           placeholder="Contoh: 10:00"
                           value="{{ $booking->booking_time }}"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penjadwalan Ulang <span class="text-red-600">*</span></label>
                    <textarea name="reschedule_reason"
                              required
                              rows="3"
                              placeholder="Contoh: Kapasitas stall servis penuh pada jam tersebut, dialihkan ke sesi berikutnya."
                              class="w-full rounded-xl border border-slate-300 p-3 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showRescheduleModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition">
                        Simpan Jadwal Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reject Booking -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div @click.away="showRejectModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Tolak Permintaan Booking Servis</h3>
                <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.service-bookings.reject', $booking) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penolakan <span class="text-red-600">*</span></label>
                    <textarea name="rejection_reason"
                              required
                              rows="3"
                              placeholder="Contoh: Bengkel tutup untuk maintenance stall darurat / data kendaraan tidak sesuai."
                              class="w-full rounded-xl border border-slate-300 p-3 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition">
                        Tolak Booking
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

