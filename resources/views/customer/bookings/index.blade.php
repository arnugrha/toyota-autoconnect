@extends('layouts.public')

@section('title', 'Pemesanan Mobil Saya (SPK) — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Page Header & Overview -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Pemesanan Mobil (SPK)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantau status alokasi unit kendaraan dan tanda jadi resmi dari cabang dealer Toyota AutoConnect</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vehicles.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Pesan Unit Baru
                </a>
            </div>
        </div>

        <!-- Bookings Table Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[1000px]">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-5 whitespace-nowrap min-w-[140px]">No. Booking SPK</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[180px]">Kendaraan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[150px]">Cabang Pengambilan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[130px]">Nilai Unit Bersih</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[120px]">Tanda Jadi</th>
                            <th class="py-3.5 px-4 whitespace-nowrap text-center min-w-[120px]">Status Alokasi</th>
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
                                <td class="py-4 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($booking->net_price, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-700 whitespace-nowrap">
                                    Rp {{ number_format($booking->booking_fee, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $booking->status_badge_class }}">
                                        {{ $booking->status_label }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-right whitespace-nowrap">
                                    <a href="{{ route('customer.bookings.show', $booking) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 transition">
                                        Detail SPK
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-16 text-center text-xs text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </div>
                                        <p class="font-medium text-slate-600">Belum ada pemesanan unit kendaraan yang terdaftar untuk akun Anda.</p>
                                        <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700 transition shadow-xs">
                                            Pilih mobil Toyota impian Anda di katalog &rarr;
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
