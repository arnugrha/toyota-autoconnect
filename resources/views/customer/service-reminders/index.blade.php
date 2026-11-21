@extends('layouts.public')

@section('title', 'Pengingat Servis Berkala — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Pengingat Perawatan Berkala Toyota</h1>
                <p class="text-xs text-slate-500 mt-1">Jadwal estimasi servis rutin agar kendaraan Anda selalu prima, bergaransi, dan mempertahankan nilai jual optimal.</p>
            </div>
            <div>
                <a href="{{ route('customer.service-bookings.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Booking Servis Sekarang
                </a>
            </div>
        </div>

        <!-- Reminders List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($reminders as $reminder)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-5 hover:border-red-300 transition">
                    <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-100">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $reminder->status_badge_class }}">
                            {{ $reminder->status_label }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium">
                            Diperbarui: {{ $reminder->updated_at->format('d M Y') }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Unit Kendaraan</span>
                        <h3 class="text-base font-extrabold text-slate-900 mt-0.5">{{ $reminder->vehicle_name }}</h3>
                        <span class="inline-block mt-1 px-2.5 py-0.5 bg-slate-100 text-slate-800 font-mono text-xs font-bold rounded border border-slate-200">
                            {{ $reminder->license_plate }}
                        </span>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-[11px]">Rekomendasi Servis:</span>
                            <span class="font-bold text-red-600 text-right">{{ $reminder->recommended_service }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                            <span class="text-slate-500 text-[11px]">Estimasi Tanggal:</span>
                            <span class="font-bold text-slate-900">{{ $reminder->next_service_date ? \Carbon\Carbon::parse($reminder->next_service_date)->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                        @if($reminder->next_service_mileage)
                            <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                                <span class="text-slate-500 text-[11px]">Target Odometer:</span>
                                <span class="font-bold text-slate-900 font-mono">{{ number_format($reminder->next_service_mileage, 0, ',', '.') }} KM</span>
                            </div>
                        @endif
                        @if($reminder->last_service_date)
                            <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 text-[10px] text-slate-400">
                                <span>Servis Terakhir:</span>
                                <span>{{ \Carbon\Carbon::parse($reminder->last_service_date)->format('d/m/Y') }}</span>
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('customer.service-bookings.create', ['vehicle_name' => $reminder->vehicle_name, 'license_plate' => $reminder->license_plate]) }}"
                       class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-red-600 py-2.5 text-xs font-bold text-white hover:bg-red-700 shadow-2xs transition">
                        <span>Booking Jadwal Ini</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-2xs">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </div>
                    <h3 class="text-xs font-bold text-slate-800">Belum ada pengingat perawatan berkala aktif</h3>
                    <p class="text-[11px] text-slate-500 mt-1 max-w-sm mx-auto">Pengingat servis akan dibuat secara otomatis saat kendaraan Anda menyelesaikan servis di bengkel resmi Toyota atau setelah serah terima unit baru.</p>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection

