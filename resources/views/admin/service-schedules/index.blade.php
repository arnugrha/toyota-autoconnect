@extends('layouts.dashboard')

@section('title', 'Jadwal & Kapasitas Bengkel — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.service-bookings.index') }}" class="hover:text-red-600 transition">Booking Servis</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Jadwal &amp; Kapasitas Operasional</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Pengaturan Jam &amp; Kapasitas Bengkel</h1>
            <p class="text-xs text-slate-500 mt-1">Konfigurasikan hari operasional, jam buka-tutup, durasi slot waktu, batas kapasitas per slot, dan batas kuota kendaraan harian.</p>
        </div>
        <div>
            <a href="{{ route('admin.service-bookings.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                &larr; Kembali ke Booking
            </a>
        </div>
    </div>


    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-2xs">
            <div class="flex items-center gap-2 font-bold mb-1">
                <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Terdapat kesalahan pada konfigurasi:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-[11px] font-normal text-rose-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Branch Selector for Super Admin -->
    @if(auth()->user()->hasRole('Super Admin'))
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
            <form action="{{ route('admin.service-schedules.index') }}" method="GET" class="flex flex-wrap items-center gap-3 text-xs">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Pilih Cabang Dealer yang Dikelola</label>
                    <select name="branch_id" class="rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->city }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="pt-5">
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 text-xs font-bold text-white hover:bg-slate-800 transition">
                        Ganti Cabang
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Schedules List per Day -->
    <div class="space-y-4">
        @forelse($schedules as $schedule)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs">
                <form action="{{ route('admin.service-schedules.update', $schedule) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl font-mono text-xs font-bold {{ $schedule->is_operational ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-slate-100 text-slate-400' }}">
                                {{ substr($schedule->day_name, 0, 3) }}
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $schedule->day_name }}</h3>
                                <p class="text-[11px] text-slate-500">Cabang: {{ $schedule->branch?->name }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                                <input type="checkbox"
                                       name="is_operational"
                                       value="1"
                                       {{ $schedule->is_operational ? 'checked' : '' }}
                                       class="rounded text-red-600 focus:ring-red-500 h-4 w-4">
                                <span>Operasional (Bengkel Buka)</span>
                            </label>

                            <button type="submit"
                                    class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 transition shadow-2xs">
                                Simpan Hari Ini
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-xs">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Jam Buka (WIB)</label>
                            <input type="text"
                                   name="opening_time"
                                   value="{{ substr($schedule->opening_time, 0, 5) }}"
                                   required
                                   placeholder="08:00"
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Jam Tutup (WIB)</label>
                            <input type="text"
                                   name="closing_time"
                                   value="{{ substr($schedule->closing_time, 0, 5) }}"
                                   required
                                   placeholder="17:00"
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Durasi Slot Waktu</label>
                            <select name="slot_duration_minutes" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                <option value="30" {{ $schedule->slot_duration_minutes == 30 ? 'selected' : '' }}>30 Menit</option>
                                <option value="45" {{ $schedule->slot_duration_minutes == 45 ? 'selected' : '' }}>45 Menit</option>
                                <option value="60" {{ $schedule->slot_duration_minutes == 60 ? 'selected' : '' }}>60 Menit (1 Jam)</option>
                                <option value="90" {{ $schedule->slot_duration_minutes == 90 ? 'selected' : '' }}>90 Menit</option>
                                <option value="120" {{ $schedule->slot_duration_minutes == 120 ? 'selected' : '' }}>120 Menit (2 Jam)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Kapasitas / Slot</label>
                            <input type="number"
                                   name="max_slot_capacity"
                                   value="{{ $schedule->max_slot_capacity }}"
                                   min="1"
                                   max="20"
                                   required
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <span class="text-[10px] text-slate-400">Mobil per jam</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Kuota Harian Total</label>
                            <input type="number"
                                   name="max_daily_capacity"
                                   value="{{ $schedule->max_daily_capacity }}"
                                   min="1"
                                   max="100"
                                   required
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <span class="text-[10px] text-slate-400">Maksimal mobil / hari</span>
                        </div>
                    </div>
                </form>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center text-xs text-slate-500 shadow-2xs">
                Tidak ada data jadwal operasional untuk cabang yang dipilih.
            </div>
        @endforelse
    </div>

</div>
@endsection

