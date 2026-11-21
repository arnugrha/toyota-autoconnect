@extends('layouts.public')

@section('title', 'Perbandingan Simulasi Kredit — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('customer.simulations.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Riwayat Simulasi
            </a>

            <span class="text-xs text-slate-500">
                Membandingkan <strong class="text-slate-900 font-bold">{{ $simulations->count() }}</strong> skema simulasi
            </span>
        </div>

        <div>
            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Komparasi Simulasi Kredit</h2>
            <p class="text-xs text-slate-500 mt-0.5">Bandingkan opsi uang muka, jangka waktu tenor, dan estimasi angsuran bulanan secara berdampingan</p>
        </div>

        <!-- Comparison Table Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="py-4 px-5 text-slate-500 font-bold uppercase tracking-wider text-[11px] w-48 border-r border-slate-200 whitespace-nowrap">
                                Parameter / Opsi
                            </th>
                            @foreach ($simulations as $sim)
                                <th class="py-4 px-5 text-center min-w-56 border-r border-slate-200 last:border-r-0 whitespace-nowrap">
                                    <div class="font-extrabold text-slate-900 text-sm">{{ $sim->vehicle->name }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $sim->leasingProvider?->name ?? 'Estimasi Standar' }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-5 font-semibold text-slate-700 bg-slate-50/70 border-r border-slate-200">
                                Harga Acuan Kendaraan
                            </td>
                            @foreach ($simulations as $sim)
                                <td class="py-3.5 px-5 text-center font-mono font-bold text-slate-900 border-r border-slate-200 last:border-r-0">
                                    {{ $sim->formatted_vehicle_price }}
                                </td>
                            @endforeach
                        </tr>
                        <tr class="hover:bg-slate-50/50 bg-emerald-50/30">
                            <td class="py-3.5 px-5 font-semibold text-emerald-900 bg-emerald-50/50 border-r border-slate-200">
                                Uang Muka (DP)
                            </td>
                            @foreach ($simulations as $sim)
                                <td class="py-3.5 px-5 text-center border-r border-slate-200 last:border-r-0">
                                    <div class="font-mono font-bold text-emerald-700">{{ $sim->formatted_down_payment }}</div>
                                    <span class="inline-block rounded bg-emerald-100 px-1.5 py-0.2 text-[10px] font-bold text-emerald-800 mt-0.5">
                                        {{ $sim->down_payment_percentage }}%
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-5 font-semibold text-slate-700 bg-slate-50/70 border-r border-slate-200">
                                Pokok Pembiayaan
                            </td>
                            @foreach ($simulations as $sim)
                                <td class="py-3.5 px-5 text-center font-mono font-bold text-slate-800 border-r border-slate-200 last:border-r-0">
                                    {{ $sim->formatted_financing_amount }}
                                </td>
                            @endforeach
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-5 font-semibold text-slate-700 bg-slate-50/70 border-r border-slate-200">
                                Jangka Waktu Tenor
                            </td>
                            @foreach ($simulations as $sim)
                                <td class="py-3.5 px-5 text-center border-r border-slate-200 last:border-r-0">
                                    <span class="font-bold text-slate-900">{{ $sim->tenor_months }} Bulan</span>
                                    <div class="text-[10px] text-slate-400">({{ $sim->tenor_years }} Tahun)</div>
                                </td>
                            @endforeach
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-5 font-semibold text-slate-700 bg-slate-50/70 border-r border-slate-200">
                                Suku Bunga Acuan (Flat)
                            </td>
                            @foreach ($simulations as $sim)
                                <td class="py-3.5 px-5 text-center font-mono text-slate-800 border-r border-slate-200 last:border-r-0">
                                    {{ $sim->interest_rate }}% / tahun
                                </td>
                            @endforeach
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-5 font-semibold text-slate-700 bg-slate-50/70 border-r border-slate-200">
                                Total Bunga
                            </td>
                            @foreach ($simulations as $sim)
                                <td class="py-3.5 px-5 text-center font-mono text-slate-700 border-r border-slate-200 last:border-r-0">
                                    {{ $sim->formatted_total_interest }}
                                </td>
                            @endforeach
                        </tr>
                        <tr class="bg-red-50/30 border-t-2 border-red-200">
                            <td class="py-4 px-5 font-extrabold text-red-900 bg-red-50/50 border-r border-slate-200">
                                Estimasi Cicilan Bulanan
                            </td>
                            @foreach ($simulations as $sim)
                                <td class="py-4 px-5 text-center border-r border-slate-200 last:border-r-0">
                                    <div class="text-base font-extrabold text-red-600 font-mono">
                                        {{ $sim->formatted_monthly_installment }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">per bulan</div>
                                </td>
                            @endforeach
                        </tr>
                        <tr class="bg-slate-50">
                            <td class="py-4 px-5 bg-slate-50 border-r border-slate-200">
                                Aksi Pengajuan
                            </td>
                            @foreach ($simulations as $sim)
                                <td class="py-4 px-5 text-center border-r border-slate-200 last:border-r-0">
                                    <a href="{{ route('customer.leasing.create', ['simulation_id' => $sim->id]) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                                        Pilih Opsi Ini
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

