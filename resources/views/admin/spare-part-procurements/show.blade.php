@extends('layouts.dashboard')

@section('title', "Dokumen Pengadaan {$procurement->procurement_number} — Toyota AutoConnect")
@section('header_title', "Dokumen Pengadaan {$procurement->procurement_number}")
@section('header_subtitle', "Penerimaan suku cadang dari {$procurement->supplier_name}")

@section('content')
<div class="max-w-4xl space-y-6">

    <!-- Top Actions -->
    <div class="flex items-center justify-between gap-3 print:hidden">
        <a href="{{ route('admin.spare-part-procurements.index') }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
            &larr; Kembali ke Riwayat Pengadaan
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.spare-parts.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition">
                Cek Stok Sparepart &rarr;
            </a>
            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition">
                Cetak Dokumen
            </button>
        </div>
    </div>

    <!-- Document Card -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-2xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-5 border-b-2 border-slate-900">
            <div>
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-red-600 flex items-center justify-center text-white font-black text-sm">T</div>
                    <span class="text-base font-black tracking-tight text-slate-900">TOYOTA AUTOCONNECT</span>
                </div>
                <p class="text-xs font-bold text-slate-700 mt-2">Dokumen Pengadaan &amp; Penerimaan Sparepart</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Gudang: {{ $procurement->branch?->name ?? 'Gudang Pusat Toyota AutoConnect' }}</p>
            </div>

            <div class="text-left sm:text-right">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $procurement->status_badge_class }}">
                    {{ $procurement->status_label }}
                </span>
                <p class="font-mono text-base font-black text-slate-900 mt-1.5">{{ $procurement->procurement_number }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Tanggal: {{ $procurement->procurement_date->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Supplier / Distributor</span>
                <p class="font-extrabold text-slate-900 text-sm">{{ $procurement->supplier_name }}</p>
                @if($procurement->notes)
                    <p class="text-slate-600 mt-1">Catatan: {{ $procurement->notes }}</p>
                @endif
            </div>
            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Petugas Penerima</span>
                <p class="font-extrabold text-slate-900 text-sm">{{ $procurement->creator?->name ?? 'Administrator' }}</p>
                <p class="text-slate-500">Dicatat pada: {{ $procurement->created_at->format('d/m/Y H:i') }} WIB</p>
            </div>
        </div>

        <!-- Item Table -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs min-w-[680px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Kode Part (SKU)</th>
                        <th class="px-4 py-3">Nama Sparepart</th>
                        <th class="px-4 py-3 text-center">Qty Masuk</th>
                        <th class="px-4 py-3 text-right">Harga Beli</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($procurement->items as $idx => $item)
                        <tr>
                            <td class="px-4 py-3 font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3 font-mono font-bold text-red-700">{{ $item->sparePart?->part_number }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                {{ $item->sparePart?->name }}
                                @if($item->sparePart)
                                    <a href="{{ route('admin.spare-parts.movements', $item->sparePart) }}"
                                       class="block text-[10px] text-blue-600 hover:underline print:hidden">
                                        Stok sekarang: {{ $item->sparePart->stock_quantity }} {{ $item->sparePart->unit }} (Lihat log)
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-extrabold text-emerald-700">
                                +{{ number_format($item->quantity) }} {{ $item->sparePart?->unit }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono">{{ $item->formatted_unit_cost }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">{{ $item->formatted_subtotal }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold">
                    <tr>
                        <td colspan="5" class="px-4 py-3.5 text-right uppercase tracking-wider text-slate-800">
                            Total Nilai Pengadaan:
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-base text-red-600">
                            {{ $procurement->formatted_total_cost }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
