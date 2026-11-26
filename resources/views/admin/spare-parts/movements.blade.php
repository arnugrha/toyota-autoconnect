@extends('layouts.dashboard')

@section('title', "Riwayat Mutasi Stok {$sparePart->part_number} — Toyota AutoConnect")
@section('header_title', 'Riwayat Mutasi Stok Sparepart')
@section('header_subtitle', "{$sparePart->part_number} — {$sparePart->name}")

@section('content')
<div class="space-y-6">

    <!-- Top Back & Summary Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.spare-parts.index') }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs self-start">
            &larr; Kembali ke Master Sparepart
        </a>

        <div class="flex items-center gap-3 rounded-xl bg-white border border-slate-200 px-4 py-2.5 shadow-2xs">
            <div class="text-xs">
                <span class="text-slate-400 block text-[10px] font-bold uppercase">Stok Saat Ini</span>
                <span class="font-black text-base text-slate-900">{{ number_format($sparePart->stock_quantity) }} {{ $sparePart->unit }}</span>
            </div>
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $sparePart->stock_badge_class }}">
                {{ $sparePart->stock_status_label }}
            </span>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900">Jejak Pergerakan Stok (Stock Ledger)</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Mencatat setiap pengadaan masuk, pemakaian servis bengkel, maupun pengembalian stok secara kronologis.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[760px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 whitespace-nowrap">Waktu</th>
                        <th class="px-4 py-3 whitespace-nowrap">Jenis Mutasi</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Perubahan</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Stok Awal &rarr; Akhir</th>
                        <th class="px-4 py-3">Keterangan / Referensi</th>
                        <th class="px-4 py-3 whitespace-nowrap">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($movements as $mv)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $mv->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $mv->type_badge_class }}">
                                    {{ $mv->type_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-extrabold whitespace-nowrap {{ $mv->quantity >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $mv->quantity >= 0 ? '+' . $mv->quantity : $mv->quantity }} {{ $sparePart->unit }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono text-slate-700 whitespace-nowrap">
                                {{ $mv->stock_before }} &rarr; <strong class="text-slate-900">{{ $mv->stock_after }}</strong>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $mv->notes ?: '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                {{ $mv->creator?->name ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                Belum ada riwayat mutasi stok untuk sparepart ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
