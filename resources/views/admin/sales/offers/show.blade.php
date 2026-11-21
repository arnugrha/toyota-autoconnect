@extends('layouts.dashboard')

@section('title', 'Detail Penawaran ' . $offer->code . ' — Toyota AutoConnect')
@section('header_title', 'Detail Surat Penawaran Resmi')
@section('header_subtitle', 'Penawaran harga kendaraan resmi untuk pelanggan dealer')

@section('content')
<div class="space-y-6 max-w-4xl">

    <!-- Flash message -->
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-xs text-emerald-800">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.sales.opportunities.show', $offer->salesOpportunity) }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Opportunity ({{ $offer->salesOpportunity->code }})
        </a>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $offer->status_badge_class }}">
                {{ $offer->status_label }}
            </span>
        </div>
    </div>

    <!-- Quotation Card -->
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">KODE RESMI SURAT PENAWARAN</span>
                <h2 class="text-xl font-extrabold text-slate-900 font-mono mt-0.5">{{ $offer->code }}</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Calon Pembeli: <strong class="text-slate-800">{{ $offer->customer->name }}</strong> &bull; Cabang: {{ $offer->salesOpportunity->branch->name }}
                </p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[10px] text-slate-400 block uppercase tracking-wider font-bold">Masa Berlaku</span>
                <span class="text-xs font-extrabold text-red-600 font-mono block">s/d {{ $offer->valid_until->format('d F Y') }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Dibuat oleh: {{ $offer->createdBy->name ?? 'Sales Executive' }}</span>
            </div>
        </div>

        <!-- Vehicle Box -->
        <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Unit Kendaraan</span>
                <h3 class="text-base font-extrabold text-slate-900 mt-0.5">{{ $offer->vehicle->name }}</h3>
                <p class="text-xs text-slate-500">{{ $offer->vehicle->variant ?? 'Varian Standar' }} &bull; {{ $offer->vehicle->category }}</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Snapshot Harga OTR</span>
                <p class="text-sm font-extrabold text-slate-900 mt-0.5 font-mono">
                    Rp {{ number_format($offer->vehicle_price, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Price Breakdown -->
        <div class="rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs min-w-[340px]">
                    <tbody class="divide-y divide-slate-100">
                        <tr class="bg-white">
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">Harga Dasar OTR (Snapshot saat penawaran dibuat)</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($offer->vehicle_price, 0, ',', '.') }}
                            </td>
                        </tr>
                        @if ($offer->discount_amount > 0)
                            <tr class="bg-emerald-50/50">
                                <td class="py-3 px-4 text-emerald-800 font-semibold whitespace-nowrap">Potongan Diskon Dealer yang Disetujui</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700 whitespace-nowrap">
                                    - Rp {{ number_format($offer->discount_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                        @if ($offer->additional_cost > 0)
                            <tr class="bg-white">
                                <td class="py-3 px-4 text-slate-600 whitespace-nowrap">Biaya Tambahan Aksesori / Pengurusan</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                    + Rp {{ number_format($offer->additional_cost, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                        <tr class="bg-slate-900 text-white font-bold">
                            <td class="py-3.5 px-4 text-sm font-extrabold whitespace-nowrap">TOTAL PENAWARAN BERSIH</td>
                            <td class="py-3.5 px-4 text-right text-base font-extrabold font-mono text-red-400 whitespace-nowrap">
                                Rp {{ number_format($offer->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if ($offer->notes)
            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Catatan Paket &amp; Ketentuan:</span>
                <p class="text-xs text-slate-700 mt-1 leading-relaxed">{{ $offer->notes }}</p>
            </div>
        @endif

        <!-- Actions Bar -->
        <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <span class="text-xs text-slate-400">Status saat ini: <strong class="text-slate-700">{{ $offer->status_label }}</strong></span>

            <div class="flex items-center gap-2">
                @if ($offer->status === 'draft')
                    <form action="{{ route('admin.sales.offers.send', $offer) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-blue-700 transition">
                            Kirim ke Pelanggan
                        </button>
                    </form>
                @endif

                @if (in_array($offer->status, ['draft', 'sent']))
                    <form action="{{ route('admin.sales.offers.status.update', $offer) }}" method="POST" class="inline-flex gap-2">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition">
                            Setujui Penawaran
                        </button>
                    </form>

                    <form action="{{ route('admin.sales.offers.status.update', $offer) }}" method="POST" class="inline-flex gap-2">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="rounded-lg bg-rose-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-rose-700 transition">
                            Tolak Penawaran
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

