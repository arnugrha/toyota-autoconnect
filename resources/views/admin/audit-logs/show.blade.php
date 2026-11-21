@extends('layouts.dashboard')

@section('title', 'Detail Log Audit #' . $log->id . ' — Toyota AutoConnect')
@section('header_title', 'Rincian Catatan Audit Trail #' . $log->id)
@section('header_subtitle', 'Pemeriksaan forensik perubahan data sistem, snapshot nilai sebelum & sesudah, serta jejak sesi')

@section('content')
<div class="space-y-6 max-w-5xl">

    <!-- Back Navigation -->
    <div>
        <a href="{{ route('admin.audit-logs.index') }}"
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-red-600 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Daftar Audit Trail</span>
        </a>
    </div>

    <!-- Metadata Header Card -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <span class="inline-block px-2.5 py-0.5 rounded-md bg-slate-900 text-white font-mono text-[10px] font-extrabold uppercase mb-1">
                    AUDIT RECORD #{{ $log->id }}
                </span>
                <h2 class="text-base font-extrabold text-slate-900">{{ $log->description ?? 'Aktivitas Tercatat' }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">Waktu Perekaman: <span class="font-bold text-slate-700">{{ $log->created_at->format('d F Y, H:i:s') }} WIB</span></p>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-lg text-xs font-extrabold uppercase bg-slate-100 text-slate-800 border border-slate-200">
                    Modul: {{ strtoupper($log->module) }}
                </span>
                <span class="px-3 py-1 rounded-lg text-xs font-extrabold uppercase bg-red-50 text-red-700 border border-red-200">
                    {{ strtoupper($log->action) }}
                </span>
            </div>
        </div>

        <!-- Session & Actor Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Aktor / Pengguna</span>
                <span class="text-xs font-extrabold text-slate-900 mt-1 block">{{ $log->user_name ?? 'Sistem / Otomatis' }}</span>
                <span class="text-[11px] text-slate-500 font-semibold">{{ $log->user_role ?? 'System' }}</span>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat IP Klien</span>
                <span class="text-xs font-mono font-extrabold text-slate-900 mt-1 block">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                <span class="text-[11px] text-slate-500 font-semibold">Terekam Aman</span>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Target Model & ID</span>
                @if($log->auditable_type)
                    <span class="text-xs font-mono font-bold text-slate-900 mt-1 block truncate" title="{{ $log->auditable_type }}">
                        {{ class_basename($log->auditable_type) }}
                    </span>
                    <span class="text-[11px] text-slate-500 font-semibold">ID #{{ $log->auditable_id }}</span>
                @else
                    <span class="text-xs text-slate-500 mt-1 block">Global / Non-Model</span>
                    <span class="text-[11px] text-slate-400">—</span>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Integritas Log</span>
                <span class="text-xs font-extrabold text-emerald-700 mt-1 block">Immutable (Append-Only)</span>
                <span class="text-[11px] text-slate-500 font-semibold">Tidak dapat diubah/dihapus</span>
            </div>
        </div>

        <div>
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">User Agent & Header Klien</span>
            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 font-mono text-[11px] text-slate-700 break-all">
                {{ $log->user_agent ?? 'CLI / Background Process' }}
            </div>
        </div>
    </div>

    <!-- Comparative Diff of Values (Before vs After) -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Perbandingan Nilai Data (Snapshot Perubahan)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Analisis komparatif nilai sebelum (*Old Values*) dan setelah (*New Values*) aksi dieksekusi.</p>
            </div>
            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                Data Sanitized & Masked
            </span>
        </div>

        @php
            $oldVals = $log->old_values ?? [];
            $newVals = $log->new_values ?? [];
            $allKeys = array_unique(array_merge(array_keys($oldVals), array_keys($newVals)));
        @endphp

        @if(empty($allKeys))
            <div class="py-8 text-center text-xs text-slate-400">
                Tidak ada perubahan atribut data langsung yang dicatat pada aktivitas ini (contoh: login/logout).
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs min-w-[700px]">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Atribut / Parameter</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[260px] text-rose-700 bg-rose-50/50">Nilai Sebelum (Old Value)</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[260px] text-emerald-700 bg-emerald-50/50">Nilai Sesudah (New Value)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                        @foreach($allKeys as $key)
                            @php
                                $valOld = $oldVals[$key] ?? null;
                                $valNew = $newVals[$key] ?? null;
                                $isDiff = $valOld !== $valNew;

                                $formatValue = function ($v) {
                                    if ($v === null) return '<span class="text-slate-400 italic font-sans">null</span>';
                                    if (is_bool($v)) return $v ? '<span class="text-emerald-600 font-bold">true</span>' : '<span class="text-rose-600 font-bold">false</span>';
                                    if (is_array($v)) return htmlspecialchars(json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                    if ($v === '[PROTECTED]') return '<span class="px-2 py-0.5 rounded bg-slate-900 text-amber-400 font-sans font-bold text-[10px]">[TERLINDUNGI / MASKED]</span>';
                                    return htmlspecialchars((string) $v);
                                };
                            @endphp
                            <tr class="{{ $isDiff ? 'bg-amber-50/30' : '' }}">
                                <td class="py-3 px-3 font-bold text-slate-800 font-sans">
                                    {{ $key }}
                                    @if($isDiff)
                                        <span class="inline-block ml-1.5 w-1.5 h-1.5 rounded-full bg-amber-500" title="Nilai Berubah"></span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-rose-900 bg-rose-50/20 whitespace-pre-wrap break-all">
                                    {!! $formatValue($valOld) !!}
                                </td>
                                <td class="py-3 px-3 text-emerald-900 bg-emerald-50/20 whitespace-pre-wrap break-all">
                                    {!! $formatValue($valNew) !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

