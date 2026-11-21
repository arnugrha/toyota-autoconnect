@extends('layouts.public')

@section('title', 'Surat Penawaran ' . $offer->code . ' — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('customer.offers.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Penawaran
            </a>

            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $offer->status_badge_class }}">
                {{ $offer->status_label }}
            </span>
        </div>

        <!-- Official Quotation Document Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-6 sm:p-8 space-y-6">
            
            <!-- Header Document -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-red-600 text-white font-extrabold text-xs px-2 py-0.5">TOYOTA</span>
                        <span class="font-bold text-slate-900 text-sm">AutoConnect Official Quotation</span>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900 font-mono mt-2">{{ $offer->code }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Cabang: <strong class="text-slate-800">{{ $offer->salesOpportunity->branch->name }}</strong></p>
                </div>
                <div class="text-left sm:text-right space-y-0.5">
                    <span class="text-[10px] text-slate-400 block uppercase tracking-wider font-bold">Masa Berlaku Penawaran</span>
                    <span class="text-xs font-extrabold text-red-600 font-mono block">s/d {{ $offer->valid_until->format('d F Y') }}</span>
                    <span class="text-[10px] text-slate-400 block">Diterbitkan: {{ $offer->created_at->format('d M Y') }}</span>
                </div>
            </div>

            <!-- Vehicle Summary -->
            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 flex items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Model Kendaraan</span>
                    <h3 class="text-base font-extrabold text-slate-900 mt-0.5">{{ $offer->vehicle->name }}</h3>
                    <p class="text-xs text-slate-500">{{ $offer->vehicle->variant ?? 'Varian Standar' }} &bull; {{ $offer->vehicle->category }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Harga OTR Terkini</span>
                    <p class="text-sm font-extrabold text-slate-900 mt-0.5 font-mono">
                        Rp {{ number_format($offer->vehicle_price, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Price Breakdown Table -->
            <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">Rincian Perhitungan Harga Resmi</h4>
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs min-w-[340px]">
                            <tbody class="divide-y divide-slate-100">
                                <tr class="bg-white">
                                    <td class="py-3 px-4 text-slate-600 whitespace-nowrap">Harga Dasar Kendaraan (Snapshot OTR)</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                        Rp {{ number_format($offer->vehicle_price, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @if ($offer->discount_amount > 0)
                                    <tr class="bg-emerald-50/50">
                                        <td class="py-3 px-4 text-emerald-800 font-semibold whitespace-nowrap">
                                            Diskon Dealer Khusus
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700 whitespace-nowrap">
                                            - Rp {{ number_format($offer->discount_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                                @if ($offer->additional_fees > 0)
                                    <tr class="bg-white">
                                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap">Biaya Tambahan / Asesoris Resmi</td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-900 whitespace-nowrap">
                                            + Rp {{ number_format($offer->additional_fees, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                                <tr class="bg-slate-50 font-bold border-t-2 border-slate-200">
                                    <td class="py-3.5 px-4 text-slate-900 text-sm whitespace-nowrap">Total Nilai Penawaran OTR</td>
                                    <td class="py-3.5 px-4 text-right font-mono text-base text-red-600 whitespace-nowrap">
                                        Rp {{ number_format($offer->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($offer->notes)
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Catatan &amp; Ketentuan Konsultan:</span>
                    <p class="text-xs text-slate-700 mt-1 leading-relaxed">{{ $offer->notes }}</p>
                </div>
            @endif

            <div class="rounded-xl bg-blue-50/60 p-4 border border-blue-100 text-[11px] text-blue-900 leading-relaxed">
                <span class="font-bold">Informasi Pelanggan:</span> Penawaran ini dibuat khusus untuk Anda oleh konsultan dealer resmi Toyota AutoConnect. Jika Anda menyetujui penawaran ini, silakan hubungi konsultan sales Anda untuk melanjutkan ke proses penerbitan Surat Pesanan Kendaraan (SPK) dan alokasi unit.
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Diterbitkan Oleh: <strong>{{ $offer->salesOpportunity->assignedSales->name ?? 'Sales Executive Toyota' }}</strong></span>
                <span>Toyota AutoConnect Digital Dealership</span>
            </div>
        </div>

    </div>
</div>
@endsection
