@extends('layouts.public')

@section('title', 'Detail Rincian Simulasi Kredit — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('customer.simulations.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Simulasi
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('customer.leasing.create', ['simulation_id' => $simulation->id]) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    Ajukan Pembiayaan Leasing
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Simulation Document Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-6 sm:p-8 space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ESTIMASI SIMULASI KREDIT KENDARAAN</span>
                    <h2 class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $simulation->vehicle->name }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Mitra Pembiayaan: <strong class="text-slate-800">{{ $simulation->leasingProvider?->name ?? 'Estimasi Bunga Standar' }}</strong></p>
                </div>
                <div class="text-left sm:text-right space-y-0.5">
                    <span class="text-[10px] text-slate-400 block uppercase tracking-wider font-bold">Disimpan pada</span>
                    <span class="text-xs font-medium text-slate-700 block">{{ $simulation->created_at->format('d F Y, H:i') }} WIB</span>
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 border border-blue-200">
                        Skema Bunga Flat
                    </span>
                </div>
            </div>

            <!-- Installment Highlight Hero -->
            <div class="rounded-2xl bg-slate-900 p-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Perkiraan Angsuran Bulanan</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-red-500 font-mono mt-1">
                        {{ $simulation->formatted_monthly_installment }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        Tenor <strong class="text-white">{{ $simulation->tenor_months }} Bulan</strong> ({{ $simulation->tenor_years }} Tahun) &bull; Suku Bunga {{ $simulation->interest_rate }}% per tahun
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('customer.leasing.create', ['simulation_id' => $simulation->id]) }}"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Lanjut ke Pengajuan Leasing &rarr;
                    </a>
                </div>
            </div>

            <!-- Detailed Breakdown Table -->
            <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">Rincian Komponen Perhitungan Kredit</h4>
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs min-w-[340px]">
                            <tbody class="divide-y divide-slate-100">
                                <tr class="bg-white">
                                    <td class="py-3 px-4 text-slate-600 whitespace-nowrap">Harga Acuan OTR Kendaraan</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                        {{ $simulation->formatted_vehicle_price }}
                                    </td>
                                </tr>
                                <tr class="bg-emerald-50/40">
                                    <td class="py-3 px-4 text-emerald-800 font-semibold whitespace-nowrap">
                                        Uang Muka / Down Payment ({{ $simulation->down_payment_percentage }}%)
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700 whitespace-nowrap">
                                        - {{ $simulation->formatted_down_payment }}
                                    </td>
                                </tr>
                                <tr class="bg-white">
                                    <td class="py-3 px-4 text-slate-700 font-semibold whitespace-nowrap">Pokok Pembiayaan Kredit</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                        {{ $simulation->formatted_financing_amount }}
                                    </td>
                                </tr>
                                <tr class="bg-white">
                                    <td class="py-3 px-4 text-slate-600 whitespace-nowrap">Suku Bunga Acuan (per tahun)</td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-800 whitespace-nowrap">
                                        {{ $simulation->interest_rate }}%
                                    </td>
                                </tr>
                                <tr class="bg-white">
                                    <td class="py-3 px-4 text-slate-600 whitespace-nowrap">Total Bunga Selama Masa Tenor</td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-900 whitespace-nowrap">
                                        + {{ $simulation->formatted_total_interest }}
                                    </td>
                                </tr>
                                <tr class="bg-slate-50 font-bold border-t-2 border-slate-200">
                                    <td class="py-3.5 px-4 text-slate-900 whitespace-nowrap">Total Pembayaran Kredit (Pokok + Bunga)</td>
                                    <td class="py-3.5 px-4 text-right font-mono text-slate-900 whitespace-nowrap">
                                        Rp {{ number_format($simulation->financing_amount + $simulation->total_interest, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($simulation->notes)
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 text-xs text-slate-700">
                    <span class="font-bold text-slate-800 block text-[11px] mb-1">Catatan Anda:</span>
                    {{ $simulation->notes }}
                </div>
            @endif

            <!-- Legal Disclaimer -->
            <div class="rounded-xl bg-amber-50/70 p-4 border border-amber-200/60 text-[11px] text-amber-900 leading-relaxed space-y-1">
                <span class="font-bold block">Pemberitahuan Penting:</span>
                <p>
                    Perhitungan di atas merupakan simulasi dan estimasi awal. Besaran suku bunga, biaya provisi, asuransi kendaraan, administrasi, dan total uang muka pertama (TDP) aktual akan dihitung secara resmi oleh pihak lembaga pembiayaan/leasing yang Anda pilih pada tahap persetujuan kredit.
                </p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <form action="{{ route('customer.simulations.destroy', $simulation) }}" method="POST"
                      onsubmit="return confirm('Hapus simulasi ini dari daftar?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-rose-600 hover:underline">Hapus Simulasi Ini</button>
                </form>
                <span>Toyota AutoConnect Credit Simulator</span>
            </div>
        </div>

    </div>
</div>
@endsection

