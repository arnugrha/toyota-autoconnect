@extends('layouts.dashboard')

@section('title', 'Pusat Laporan & Ekspor — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Pusat Laporan &amp; Ekspor Data Bisnis
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Unduh dan pratinjau laporan operasional komprehensif dalam format CSV standar RFC 4180 (kompatibel Excel &amp; BI).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-xs transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Unduh Laporan (CSV)</span>
            </a>
        </div>
    </div>

    <!-- Navigation to Analytics Tabs -->
    @include('admin.analytics.partials.nav-tabs')

    <!-- Report Type Selector Bar -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Jenis Laporan Bisnis</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
            @foreach($reportTypes as $typeKey => $typeName)
                @php
                    $isActive = $currentType === $typeKey;
                    $queryParams = array_merge(request()->except('type'), ['type' => $typeKey]);
                @endphp
                <a href="{{ route('admin.reports.index', $queryParams) }}"
                   class="flex items-center justify-between p-3 rounded-xl border text-xs font-bold transition {{ $isActive ? 'bg-red-50 border-red-500 text-red-700 shadow-2xs' : 'bg-slate-50/70 border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-slate-300' }}">
                    <span class="truncate">{{ $typeName }}</span>
                    @if($isActive)
                        <span class="w-2 h-2 rounded-full bg-red-600 flex-shrink-0 ml-2"></span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <!-- Date Range & Branch Filters for Reports -->
    @include('admin.analytics.partials.period-filter', [
        'formAction' => route('admin.reports.index'),
        'branches' => $branches ?? null,
        'selectedBranchId' => $selectedBranchId ?? null
    ])

    <!-- Report Header & Summary Card -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <span class="inline-block px-2.5 py-0.5 rounded-md bg-slate-900 text-white font-mono text-[10px] font-extrabold uppercase mb-1">
                    {{ strtoupper($reportData['type']) }}
                </span>
                <h2 class="text-base font-extrabold text-slate-900">{{ $reportData['title'] }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Periode: <span class="font-bold text-slate-700">{{ $reportData['period_label'] ?? $reportData['period'] ?? '-' }}</span> &bull; 
                    Cakupan Cabang: <span class="font-bold text-slate-700">{{ $reportData['branch_name'] }}</span>
                </p>
            </div>
            <div class="text-left md:text-right text-xs">
                <p class="text-slate-400 text-[11px]">Digenerate: {{ $reportData['generated_at'] ?? now()->format('d/m/Y H:i:s') }}</p>
                <p class="text-slate-900 font-extrabold mt-0.5">{{ count($reportData['rows']) }} Baris Data Transaksi</p>
            </div>
        </div>

        <!-- Summary Metric Pills -->
        @if(!empty($reportData['summary']))
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 pt-1">
                @foreach($reportData['summary'] as $label => $val)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $label }}</span>
                        <span class="text-base font-extrabold text-slate-900 mt-1 block truncate">{{ $val }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Interactive Preview Table -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Pratinjau Data Laporan ({{ count($reportData['rows']) }} Baris)</h3>
                <p class="text-[11px] text-slate-500">Pratinjau struktur kolom dan isi data aktual sebelum diunduh</p>
            </div>

            <a href="{{ route('admin.reports.export', request()->query()) }}"
               class="text-xs font-bold text-red-600 hover:text-red-700 flex items-center gap-1">
                <span>Unduh File Lengkap (.csv)</span>
                <span>&darr;</span>
            </a>
        </div>

        <!-- Table with Horizontal Scroll Support -->
        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs min-w-[950px]">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600 bg-slate-50">
                        @foreach($reportData['headers'] as $header)
                            <th class="py-2.5 px-3 whitespace-nowrap">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse(array_slice($reportData['rows'], 0, 50) as $row)
                        <tr class="hover:bg-slate-50/80 transition">
                            @foreach($row as $colIdx => $cell)
                                <td class="py-2.5 px-3 whitespace-nowrap {{ $colIdx === 0 ? 'font-bold text-slate-500' : '' }}">
                                    {{ $cell }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($reportData['headers']) }}" class="py-14 text-center text-xs text-slate-400">
                                Tidak ada data yang ditemukan untuk jenis laporan dan periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(count($reportData['rows']) > 50)
            <div class="mt-3 text-center text-[11px] text-slate-500 font-medium">
                Menampilkan 50 baris pertama. Seluruh {{ count($reportData['rows']) }} baris tersedia pada file CSV yang diunduh.
            </div>
        @endif
    </div>

</div>
@endsection

