@extends('layouts.dashboard')

@section('title', 'Manajemen Test Drive — Toyota AutoConnect')
@section('header_title', 'Manajemen Jadwal Test Drive')
@section('header_subtitle', 'Kelola seluruh permohonan dan jadwal uji kemudi kendaraan Toyota multi-cabang')

@section('content')
<div class="space-y-6">


    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Pengajuan</p>
            <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Semua sesi</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-amber-200 shadow-xs bg-amber-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Menunggu Konfirmasi</p>
            <p class="text-xl font-extrabold text-amber-700 mt-1">{{ $stats['pending'] }}</p>
            <p class="text-[10px] text-amber-600/80 mt-0.5">Perlu diverifikasi</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-blue-200 shadow-xs bg-blue-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Jadwal Dikonfirmasi</p>
            <p class="text-xl font-extrabold text-blue-700 mt-1">{{ $stats['confirmed'] }}</p>
            <p class="text-[10px] text-blue-600/80 mt-0.5">Siap uji kemudi</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-emerald-200 shadow-xs bg-emerald-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Test Drive Selesai</p>
            <p class="text-xl font-extrabold text-emerald-700 mt-1">{{ $stats['completed'] }}</p>
            <p class="text-[10px] text-emerald-600/80 mt-0.5">Telah terlaksana</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Batal / Ditolak / No-Show</p>
            <p class="text-xl font-extrabold text-slate-600 mt-1">{{ $stats['cancelled'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Sesi tidak berjalan</p>
        </div>
    </div>

    <!-- Filters & View Toggle Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs space-y-3">
        <form method="GET" action="{{ route('admin.test-drives.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
            <input type="hidden" name="view" value="{{ $viewMode }}">

            <!-- Search -->
            <div class="sm:col-span-3">
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama, kode booking, email..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-2">
                <select name="status" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="confirmed" {{ $filters['status'] === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                    <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="no_show" {{ $filters['status'] === 'no_show' ? 'selected' : '' }}>Pelanggan Tidak Hadir</option>
                </select>
            </div>

            <!-- Branch Filter -->
            <div class="sm:col-span-3">
                <select name="branch_id" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Cabang Dealer</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $filters['branch_id'] == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Vehicle Filter -->
            <div class="sm:col-span-2">
                <select name="vehicle_id" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Model</option>
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}" {{ $filters['vehicle_id'] == $v->id ? 'selected' : '' }}>
                            {{ $v->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Filter -->
            <div class="sm:col-span-2">
                <input type="date" name="date" value="{{ $filters['date'] }}"
                       class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            <!-- Action buttons -->
            <div class="sm:col-span-12 flex items-center justify-between pt-2 border-t border-slate-100">
                <div class="flex items-center gap-1.5">
                    <!-- Toggle View Mode Button -->
                    <a href="{{ route('admin.test-drives.index', array_merge($filters, ['view' => 'list'])) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold {{ $viewMode === 'list' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        Tabel Daftar
                    </a>
                    <a href="{{ route('admin.test-drives.index', array_merge($filters, ['view' => 'calendar'])) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold {{ $viewMode === 'calendar' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                        </svg>
                        Tampilan Kalender
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.test-drives.index') }}" class="text-xs text-slate-500 hover:text-slate-800 px-2 py-1">
                        Reset Filter
                    </a>
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if ($viewMode === 'calendar')
        <!-- Calendar View (Real Bookings from DB) -->
        <div class="rounded-xl bg-white border border-slate-200 shadow-xs p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Agenda Kalender Jadwal Test Drive</h3>
                    <p class="text-[11px] text-slate-500">Menampilkan agenda sesi booking aktif di dealer Toyota AutoConnect</p>
                </div>
                <span class="text-xs font-mono font-bold text-slate-600">Total {{ $calendarBookings->count() }} Jadwal Terdaftar</span>
            </div>

            @if ($calendarBookings->isEmpty())
                <div class="py-12 text-center text-xs text-slate-400">
                    Tidak ada jadwal test drive pada kriteria filter kalender ini.
                </div>
            @else
                <div class="space-y-4">
                    @php
                        $groupedBookings = $calendarBookings->groupBy(fn($b) => $b->scheduled_at->format('Y-m-d'));
                    @endphp

                    @foreach ($groupedBookings as $date => $items)
                        <div class="rounded-xl border border-slate-200 overflow-hidden">
                            <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">
                                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                                </span>
                                <span class="rounded bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                    {{ $items->count() }} Sesi
                                </span>
                            </div>
                            <div class="divide-y divide-slate-100">
                                @foreach ($items as $item)
                                    <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/60 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 text-center bg-slate-100 border border-slate-200 rounded-lg px-2.5 py-1">
                                                <span class="text-xs font-bold font-mono text-slate-900 block">{{ $item->scheduled_at->format('H:i') }}</span>
                                                <span class="text-[9px] text-slate-400 block font-semibold">WIB</span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-900 text-xs">{{ $item->vehicle->name }}</span>
                                                    <span class="text-[10px] text-slate-400 font-mono">{{ $item->code }}</span>
                                                </div>
                                                <div class="text-[11px] text-slate-500 mt-0.5">
                                                    Pelanggan: <strong class="text-slate-700">{{ $item->customer_name }}</strong> &bull; Cabang: {{ $item->branch->name }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 self-end sm:self-auto">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $item->status_badge_class }}">
                                                {{ $item->status_label }}
                                            </span>
                                            <a href="{{ route('admin.test-drives.show', $item) }}"
                                               class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                                                Kelola
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <!-- List View Table -->
        <div class="rounded-xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[1100px]">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[160px]">Kode &amp; Waktu</th>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[170px]">Pelanggan</th>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[180px]">Kendaraan</th>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[160px]">Cabang Dealer</th>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[160px]">Sales Consultant</th>
                            <th class="py-3 px-4 text-center whitespace-nowrap min-w-[130px]">Status</th>
                            <th class="py-3 px-4 text-right whitespace-nowrap min-w-[110px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bookings as $booking)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-slate-900 block">{{ $booking->code }}</span>
                                    <span class="font-mono text-[10px] text-slate-500 block">{{ $booking->scheduled_at->format('d/m/Y H:i') }} WIB</span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $booking->customer_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $booking->customer_phone }}</div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $booking->vehicle->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $booking->vehicle->category }}</div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800">{{ $booking->branch->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $booking->branch->city }}</div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if ($booking->assignedSales)
                                        <span class="font-medium text-slate-800">{{ $booking->assignedSales->name }}</span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Belum Ditugaskan</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $booking->status_badge_class }}">
                                        {{ $booking->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.test-drives.show', $booking) }}"
                                       class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                                        Kelola
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-xs text-slate-400">
                                    Tidak ada data permohonan test drive yang sesuai dengan kriteria pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($bookings->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection

