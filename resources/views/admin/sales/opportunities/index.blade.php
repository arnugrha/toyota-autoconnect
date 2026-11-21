@extends('layouts.dashboard')

@section('title', 'Pipeline & Peluang Penjualan — Toyota AutoConnect')
@section('header_title', 'Pipeline & Peluang Penjualan')
@section('header_subtitle', 'Pantau kemajuan transaksi dan pipeline sales opportunity dealer')

@section('content')
<div class="space-y-6">

    <!-- Flash message -->
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-xs text-emerald-800">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Pipeline Stages Horizontal Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">SALES PIPELINE OVERVIEW</span>
                <h3 class="text-xs font-bold text-slate-800">Tahapan Penjualan Aktif</h3>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 block font-bold uppercase tracking-wider">Estimasi Nilai Pipeline Aktif</span>
                <span class="text-sm font-extrabold text-red-600 font-mono">Rp {{ number_format($totalEstimatedValue, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- 8-Stage Progress Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2">
            @foreach ($stages as $stageKey => $stageLabel)
                <a href="{{ route('admin.sales.opportunities.index', array_merge($filters, ['stage' => $stageKey])) }}"
                   class="p-2.5 rounded-xl border text-center transition flex flex-col justify-between {{ $filters['stage'] === $stageKey ? 'bg-red-50 border-red-300 ring-2 ring-red-500/20' : 'bg-slate-50 border-slate-200 hover:bg-slate-100' }}">
                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-tight block truncate">{{ $stageLabel }}</span>
                    <span class="text-base font-extrabold font-mono mt-1 {{ $stageCounts[$stageKey] > 0 ? 'text-slate-900' : 'text-slate-400' }}">
                        {{ $stageCounts[$stageKey] }}
                    </span>
                    <span class="text-[9px] text-slate-400 mt-0.5 block">prospek</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Filters & Action Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.sales.opportunities.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-4">
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari kode OPP, nama pelanggan, mobil..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            <div class="sm:col-span-3">
                <select name="stage" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="all">Semua Tahapan Pipeline</option>
                    @foreach ($stages as $key => $lbl)
                        <option value="{{ $key }}" {{ $filters['stage'] === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="branch_id" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Cabang Dealer</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $filters['branch_id'] == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2 justify-end">
                <button type="submit" class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-900 transition">
                    Filter
                </button>
                <a href="{{ route('admin.sales.opportunities.create') }}"
                   class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition flex items-center gap-1 flex-shrink-0">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Opportunity Baru
                </a>
            </div>
        </form>
    </div>

    <!-- Opportunities Table -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1100px]">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[130px]">Kode &amp; Tanggal</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[180px]">Calon Pelanggan</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[170px]">Model Diminati</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Cabang</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[160px]">Sales Executive</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[140px]">Estimasi Nilai</th>
                        <th class="py-3 px-4 whitespace-nowrap text-center min-w-[130px]">Tahap Pipeline</th>
                        <th class="py-3 px-4 whitespace-nowrap text-right min-w-[90px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($opportunities as $opp)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-900 block">{{ $opp->code }}</span>
                                <span class="font-mono text-[10px] text-slate-400 block">{{ $opp->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $opp->customer->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $opp->customer->phone ?? $opp->customer->email }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $opp->vehicle->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $opp->vehicle->category }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ $opp->branch->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $opp->branch->city }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-medium text-slate-800">{{ $opp->assignedSales->name ?? 'Belum Ditugaskan' }}</span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap font-mono font-bold text-slate-900">
                                Rp {{ number_format($opp->estimated_value, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $opp->stage_badge_class }}">
                                    {{ $opp->stage_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-right">
                                <a href="{{ route('admin.sales.opportunities.show', $opp) }}"
                                   class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-xs text-slate-400">
                                Belum ada peluang penjualan yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($opportunities->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $opportunities->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

