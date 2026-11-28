@extends('layouts.dashboard')

@section('title', 'Master Mitra Lembaga Leasing — Toyota AutoConnect')
@section('header_title', 'Mitra Lembaga Pembiayaan & Leasing')
@section('header_subtitle', 'Kelola konfigurasi lembaga pembiayaan rekanan resmi, suku bunga acuan, dan status integrasi API')

@section('content')
<div class="space-y-6">


    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Daftar Mitra Provider Pembiayaan</h3>
            <p class="text-xs text-slate-500 mt-0.5">Lembaga pembiayaan yang aktif akan tersedia pada kalkulator simulasi kredit dan portal pengajuan nasabah</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.leasing.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                &larr; Berkas Aplikasi Kredit
            </a>
        </div>
    </div>

    <!-- Providers Grid / Table Card -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1000px]">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-5 whitespace-nowrap min-w-[180px]">Kode &amp; Nama Provider</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Suku Bunga Acuan (Flat)</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[180px]">Endpoint Integrasi API</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[200px]">Keterangan / Deskripsi</th>
                        <th class="py-3 px-4 whitespace-nowrap text-center min-w-[120px]">Total Pengajuan</th>
                        <th class="py-3 px-4 whitespace-nowrap text-center min-w-[100px]">Status</th>
                        <th class="py-3 px-5 whitespace-nowrap text-right min-w-[110px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($providers as $provider)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-5 whitespace-nowrap">
                                <div class="font-extrabold text-slate-900 text-sm">{{ $provider->name }}</div>
                                <div class="font-mono text-xs font-bold text-red-600 mt-0.5">{{ $provider->code }}</div>
                            </td>
                            <td class="py-4 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ number_format($provider->base_interest_rate, 2) }}% / tahun
                            </td>
                            <td class="py-4 px-4 font-mono text-[11px] text-slate-600 max-w-xs truncate whitespace-nowrap">
                                {{ $provider->api_endpoint ?: 'Standard Gateway / Mock' }}
                            </td>
                            <td class="py-4 px-4 text-slate-600 max-w-xs text-[11px]">
                                {{ $provider->description ?: '-' }}
                            </td>
                            <td class="py-4 px-4 text-center font-bold text-slate-800 whitespace-nowrap">
                                {{ $provider->applications_count ?? 0 }}
                            </td>
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                @if ($provider->is_active)
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-600 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <form action="{{ route('admin.leasing.providers.toggle', $provider) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $provider->is_active ? 'border-amber-300 text-amber-700 bg-amber-50 hover:bg-amber-100' : 'border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                                        {{ $provider->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-slate-400 italic">
                                Belum ada provider leasing yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

