@extends('layouts.public')

@section('title', 'Jadwal Test Drive Saya — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Flash message -->
        @if (session('success'))
            <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-xs text-emerald-800">
                <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-2.5 rounded-xl bg-red-50 border border-red-200 p-4 text-xs text-red-800">
                <svg class="h-4 w-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Header Actions & Filters -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
            <!-- Status Filter Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <a href="{{ route('customer.test-drives.index') }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition {{ empty($currentStatus) ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Jadwal
                </a>
                <a href="{{ route('customer.test-drives.index', ['status' => 'pending']) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Menunggu Konfirmasi
                </a>
                <a href="{{ route('customer.test-drives.index', ['status' => 'confirmed']) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'confirmed' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Dikonfirmasi
                </a>
                <a href="{{ route('customer.test-drives.index', ['status' => 'completed']) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'completed' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Selesai
                </a>
            </div>

            <a href="{{ route('vehicles.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition flex-shrink-0">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Ajukan Test Drive Baru
            </a>
        </div>

        <!-- Booking Table Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[950px]">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-5 whitespace-nowrap min-w-[140px]">Kode Booking</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[180px]">Kendaraan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[160px]">Cabang Dealer</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[150px]">Jadwal Sesi</th>
                            <th class="py-3.5 px-4 whitespace-nowrap text-center min-w-[110px]">Status</th>
                            <th class="py-3.5 px-5 whitespace-nowrap text-right min-w-[110px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bookings as $booking)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-5 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ $booking->code }}
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $booking->vehicle->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $booking->vehicle->variant ?? 'Varian Standar' }} &bull; {{ $booking->vehicle->category }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800">{{ $booking->branch->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $booking->branch->city }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $booking->scheduled_date }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">Pukul {{ $booking->scheduled_time }} WIB</div>
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $booking->status_badge_class }}">
                                        {{ $booking->status_label }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-right whitespace-nowrap">
                                    <a href="{{ route('customer.test-drives.show', $booking) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 transition">
                                        Detail Sesi
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center text-xs text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </div>
                                        <p class="font-medium text-slate-600">Belum ada jadwal sesi test drive yang diajukan.</p>
                                        <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700 transition shadow-xs">
                                            Pilih Mobil &amp; Ajukan Test Drive Sekarang &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($bookings->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
