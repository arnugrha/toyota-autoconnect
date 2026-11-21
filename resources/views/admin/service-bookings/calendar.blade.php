@extends('layouts.dashboard')

@section('title', 'Kalender Booking Servis — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.service-bookings.index') }}" class="hover:text-red-600 transition">Booking Servis</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Kalender Bulanan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Kalender Jadwal Servis Bengkel</h1>
            <p class="text-xs text-slate-500 mt-1">Pantau kepadatan booking harian dan slot jadwal perawatan kendaraan pelanggan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.service-bookings.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                &larr; Tampilan Tabel
            </a>
        </div>
    </div>

    <!-- Month & Branch Selector Form -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
        <form action="{{ route('admin.service-bookings.calendar') }}" method="GET" class="flex flex-wrap items-center gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Pilih Bulan &amp; Tahun</label>
                <input type="month"
                       name="month"
                       value="{{ $selectedMonth }}"
                       class="rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            @if(auth()->user()->hasRole('Super Admin'))
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Pilih Cabang Dealer</label>
                    <select name="branch_id" class="rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">Semua Cabang Dealer</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="pt-5">
                <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <!-- Grouped Days Calendar Cards -->
    <div class="space-y-4">
        @php
            $groupedBookings = $bookings->groupBy(fn($b) => \Carbon\Carbon::parse($b->booking_date)->toDateString());
            $currentDate = $monthStart->copy();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @while($currentDate->lte($monthEnd))
                @php
                    $dateKey = $currentDate->toDateString();
                    $dayBookings = $groupedBookings->get($dateKey, collect());
                    $isPast = $currentDate->isPast() && !$currentDate->isToday();
                    $isToday = $currentDate->isToday();
                @endphp

                <div class="rounded-2xl border p-4 transition {{ $isToday ? 'border-red-600 bg-red-50/20 ring-1 ring-red-600/30' : ($dayBookings->isNotEmpty() ? 'border-slate-200 bg-white' : 'border-slate-200/60 bg-slate-50/60') }}">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-black {{ $isToday ? 'text-red-600' : 'text-slate-900' }}">
                                {{ $currentDate->translatedFormat('d M Y') }}
                            </span>
                            @if($isToday)
                                <span class="px-2 py-0.5 rounded-full bg-red-600 text-white text-[9px] font-bold">Hari Ini</span>
                            @endif
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $dayBookings->isNotEmpty() ? 'bg-slate-100 text-slate-700' : 'text-slate-400' }}">
                            {{ $dayBookings->count() }} Booking
                        </span>
                    </div>

                    @if($dayBookings->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($dayBookings as $b)
                                <a href="{{ route('admin.service-bookings.show', $b) }}"
                                   class="block p-2.5 rounded-xl border border-slate-200 bg-white hover:border-red-300 hover:shadow-2xs transition text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono font-bold text-red-600">{{ $b->booking_time }} WIB</span>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.2 text-[9px] font-bold border {{ $b->status_badge_class }}">
                                            {{ $b->status_label }}
                                        </span>
                                    </div>
                                    <p class="font-bold text-slate-900 mt-1 truncate">{{ $b->vehicle_name }}</p>
                                    <div class="flex items-center justify-between text-[10px] text-slate-500 mt-0.5">
                                        <span class="font-mono font-semibold">{{ $b->license_plate }}</span>
                                        <span class="truncate max-w-[120px]">{{ $b->user?->name }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[11px] text-slate-400 italic py-2">Tidak ada jadwal booking pada hari ini.</p>
                    @endif
                </div>

                @php $currentDate->addDay(); @endphp
            @endwhile
        </div>
    </div>

</div>
@endsection

