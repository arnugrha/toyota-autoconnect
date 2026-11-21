@extends('layouts.public')

@section('title', 'Penawaran Resmi Saya — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Page Header & Overview -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Penawaran Resmi Mobil Toyota</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar surat penawaran harga resmi (quotation) yang diterbitkan konsultan sales untuk Anda</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vehicles.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Jelajahi Model Lain
                </a>
            </div>
        </div>

        <!-- Offers Table Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[950px]">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-5 whitespace-nowrap min-w-[140px]">No. Penawaran</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[180px]">Model Mobil</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[160px]">Cabang Showroom</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[140px]">Harga OTR Penawaran</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[130px]">Masa Berlaku</th>
                            <th class="py-3.5 px-4 whitespace-nowrap text-center min-w-[110px]">Status</th>
                            <th class="py-3.5 px-5 whitespace-nowrap text-right min-w-[110px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($offers as $offer)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-5 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ $offer->code }}
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $offer->vehicle->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $offer->vehicle->variant ?? 'Varian Standar' }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800">{{ $offer->salesOpportunity->branch->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $offer->salesOpportunity->branch->city }}</div>
                                </td>
                                <td class="py-4 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($offer->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-slate-600 whitespace-nowrap">
                                    s/d {{ $offer->valid_until->format('d M Y') }}
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $offer->status_badge_class }}">
                                        {{ $offer->status_label }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-right whitespace-nowrap">
                                    <a href="{{ route('customer.offers.show', $offer) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 transition">
                                        Lihat Penawaran
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
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                        </div>
                                        <p class="font-medium text-slate-600">Belum ada surat penawaran resmi yang diterbitkan untuk akun Anda.</p>
                                        <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700 transition shadow-xs">
                                            Pilih mobil dan konsultasikan kebutuhan Anda &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($offers->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $offers->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
