@extends('layouts.dashboard')

@section('title', 'Manajemen Booking Servis — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Manajemen Booking Servis Bengkel</h1>
            <p class="text-xs text-slate-500 mt-1">Pantau dan kelola jadwal permohonan servis kendaraan, alokasi waktu, serta penerbitan Work Order resmi.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.service-bookings.calendar') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-red-600 transition">
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                </svg>
                Tampilan Kalender
            </a>
            <a href="{{ route('admin.service-schedules.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                </svg>
                Atur Kapasitas Cabang
            </a>
        </div>
    </div>


    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Booking</span>
            <p class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block">Menunggu Konfirmasi</span>
            <p class="text-xl font-black text-amber-900 mt-1">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 block">Dikonfirmasi</span>
            <p class="text-xl font-black text-blue-900 mt-1">{{ number_format($stats['confirmed']) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Selesai Servis</span>
            <p class="text-xl font-black text-emerald-900 mt-1">{{ number_format($stats['completed']) }}</p>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 block">Batal / Ditolak</span>
            <p class="text-xl font-black text-rose-900 mt-1">{{ number_format($stats['cancelled']) }}</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
        <form action="{{ route('admin.service-bookings.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-4 relative">
                <input type="text"
                       name="q"
                       value="{{ $filters['q'] }}"
                       placeholder="Cari kode booking, nama pemohon, plat mobil..."
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            <div class="sm:col-span-3">
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="confirmed" {{ $filters['status'] === 'confirmed' ? 'selected' : '' }}>Confirmed (Dikonfirmasi)</option>
                    <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                    <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>Cancelled (Dibatalkan)</option>
                    <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                </select>
            </div>

            @if(auth()->user()->hasRole('Super Admin'))
                <div class="sm:col-span-3">
                    <select name="branch_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">Semua Cabang Dealer</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $filters['branch_id'] == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-slate-900 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Filter
                </button>
                @if($filters['q'] || $filters['status'] !== 'all' || $filters['branch_id'] || $filters['date'])
                    <a href="{{ route('admin.service-bookings.index') }}" class="p-2 text-slate-500 hover:text-red-600 text-xs font-bold">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card with Horizontal Scroll -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 min-w-[1200px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[150px]">Kode &amp; Tanggal Masuk</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[170px]">Pemohon Pelanggan</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[180px]">Unit Mobil &amp; Plat</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[180px]">Jenis Servis</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[170px]">Jadwal Sesi Servis</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[160px]">Cabang</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center min-w-[130px]">Status</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-right min-w-[120px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $booking->booking_code }}
                                <span class="block font-sans text-[10px] text-slate-400 font-normal mt-0.5">
                                    {{ $booking->created_at->format('d/m/Y H:i') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $booking->user?->name }}</span>
                                <span class="text-[11px] text-slate-500 block">{{ $booking->user?->phone ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $booking->vehicle_name }}</span>
                                <span class="inline-block mt-0.5 px-2 py-0.5 bg-slate-100 text-slate-700 rounded font-mono text-[10px] font-bold border border-slate-200">
                                    {{ $booking->license_plate }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-semibold text-slate-900 block whitespace-nowrap">{{ $booking->serviceType?->name ?? 'Servis Berkala' }}</span>
                                <span class="text-[10px] text-slate-500">{{ $booking->serviceType?->formatted_price ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">
                                    {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}
                                </span>
                                <span class="text-[11px] font-semibold text-red-600 block">
                                    Pukul {{ $booking->booking_time }} WIB
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                                {{ $booking->branch?->name }}
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $booking->status_badge_class }}">
                                    {{ $booking->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.service-bookings.show', $booking) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-slate-50 hover:text-red-600 transition shadow-2xs">
                                    Detail &amp; Tindakan
                                    <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-500 text-xs">
                                Tidak ada data permohonan booking servis yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

