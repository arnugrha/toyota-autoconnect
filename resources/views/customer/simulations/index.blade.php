@extends('layouts.public')

@section('title', 'Riwayat Simulasi Kredit — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10" x-data="{ selectedSims: [] }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">


        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Simulasi Kredit Tersimpan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar estimasi perhitungan angsuran kredit mobil Toyota yang telah Anda simpan</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Compare Button (enabled when 2 or more selected) -->
                <button type="button"
                        :disabled="selectedSims.length < 2"
                        @click="if(selectedSims.length >= 2) window.location.href = '{{ route('customer.simulations.compare') }}?ids=' + selectedSims.join(',')"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-bold transition shadow-xs"
                        :class="selectedSims.length >= 2 ? 'bg-slate-900 text-white hover:bg-slate-800' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                    <span>Bandingkan (<span x-text="selectedSims.length"></span>)</span>
                </button>

                <a href="{{ route('vehicles.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Simulasi Mobil Baru
                </a>
            </div>
        </div>

        <!-- Simulations Table Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[1000px]">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-10 whitespace-nowrap">Pilih</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[180px]">Model Kendaraan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[160px]">Mitra Pembiayaan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[160px]">Harga OTR &amp; DP</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[120px]">Tenor</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap min-w-[150px]">Estimasi Cicilan / Bln</th>
                            <th class="py-3.5 px-5 text-right whitespace-nowrap min-w-[110px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($simulations as $sim)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <input type="checkbox"
                                           value="{{ $sim->id }}"
                                           x-model="selectedSims"
                                           class="rounded border-slate-300 text-red-600 focus:ring-red-500 h-4 w-4">
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $sim->vehicle->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $sim->vehicle->variant ?? 'Varian Standar' }} &bull; {{ $sim->vehicle->category }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $sim->leasingProvider?->name ?? 'Estimasi Standar' }}
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Bunga: {{ $sim->interest_rate }}% / thn</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900 font-mono">{{ $sim->formatted_vehicle_price }}</div>
                                    <div class="text-[11px] text-emerald-600 font-medium">DP: {{ $sim->formatted_down_payment }} ({{ $sim->down_payment_percentage }}%)</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">{{ $sim->tenor_months }} Bulan</div>
                                    <div class="text-[10px] text-slate-400 font-mono">({{ $sim->tenor_years }} Tahun)</div>
                                </td>
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    <div class="text-sm font-extrabold text-red-600 font-mono">
                                        {{ $sim->formatted_monthly_installment }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">/ bulan (Flat)</div>
                                </td>
                                <td class="py-4 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('customer.simulations.show', $sim) }}"
                                           class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-slate-800 transition">
                                            Rincian
                                        </a>

                                        <form action="{{ route('customer.simulations.destroy', $sim) }}" method="POST"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus simulasi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-md p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                    title="Hapus Simulasi">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-16 text-center text-xs text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 3h.008v.008H8.25v-.008Zm0 3h.008v.008H8.25v-.008Zm3-6h.008v.008H11.25v-.008Zm0 3h.008v.008H11.25v-.008Zm0 3h.008v.008H11.25v-.008Zm3-6h.008v.008H14.25v-.008Zm0 3h.008v.008H14.25v-.008Zm3-3h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Zm-12-6.75h13.5a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Z" />
                                            </svg>
                                        </div>
                                        <p class="font-medium text-slate-600">Belum ada simulasi kredit yang disimpan.</p>
                                        <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700 transition shadow-xs">
                                            Buka Katalog &amp; Hitung Simulasi Kredit &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($simulations->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $simulations->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

