@extends('layouts.dashboard')

@section('title', 'Pengadaan Sparepart — Toyota AutoConnect')
@section('header_title', 'Pengadaan & Restock Sparepart')
@section('header_subtitle', 'Catat pembelian suku cadang resmi dari supplier untuk penambahan stok gudang bengkel secara otomatis')

@section('content')
<div class="space-y-6">

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Dokumen PO</span>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($stats['total_procurements']) }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Riwayat transaksi pengadaan</p>
        </div>

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 shadow-2xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Total Unit Masuk</span>
            <div class="mt-2 text-2xl font-black text-emerald-700">+{{ number_format($stats['total_units_received']) }}</div>
            <p class="text-[11px] text-emerald-700/80 mt-0.5">Akumulasi stok berhasil ditambah</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Nilai Pengadaan</span>
            <div class="mt-2 text-xl sm:text-2xl font-black text-slate-900 font-mono">
                Rp {{ number_format($stats['total_spend'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-500 mt-0.5">Akumulasi belanja suku cadang</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pengadaan Bulan Ini</span>
            <div class="mt-2 text-2xl font-black text-red-600">{{ number_format($stats['this_month_count']) }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Transaksi pada {{ now()->translatedFormat('F Y') }}</p>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">Riwayat Pengadaan Sparepart</h2>
                <p class="text-[11px] text-slate-500 mt-0.5">Setiap pengadaan baru otomatis menambah saldo stok di halaman Kelola Sparepart.</p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.spare-parts.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Lihat Stok Sparepart
                </a>
                @can('spare-parts.manage')
                    <a href="{{ route('admin.spare-part-procurements.create') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Buat Pengadaan Baru
                    </a>
                @endcan
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="p-4 bg-slate-50/70 border-b border-slate-100">
            <form method="GET" action="{{ route('admin.spare-part-procurements.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <input type="text"
                       name="q"
                       value="{{ $filters['q'] }}"
                       placeholder="Cari nomor PO atau nama supplier..."
                       class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">

                <select name="branch_id"
                        class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Cabang Penerima</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string) $filters['branch_id'] === (string) $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white hover:bg-slate-800 transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['q', 'branch_id']))
                        <a href="{{ route('admin.spare-part-procurements.index') }}"
                           class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[880px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3.5 whitespace-nowrap">Nomor Pengadaan</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Tanggal</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Supplier / Distributor</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap">Item / Total Qty</th>
                        <th class="px-4 py-3.5 text-right whitespace-nowrap">Total Nilai</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap">Status</th>
                        <th class="px-4 py-3.5 text-right whitespace-nowrap">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($procurements as $prc)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <a href="{{ route('admin.spare-part-procurements.show', $prc) }}"
                                   class="font-mono font-extrabold text-slate-900 hover:text-red-600 transition">
                                    {{ $prc->procurement_number }}
                                </a>
                                <div class="text-[10px] text-slate-400 mt-0.5">Oleh: {{ $prc->creator?->name ?? 'Sistem' }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-600 font-medium">
                                {{ $prc->procurement_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900">{{ $prc->supplier_name }}</div>
                                @if($prc->branch)
                                    <div class="text-[10px] text-slate-500 mt-0.5">Gudang: {{ $prc->branch->name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="font-bold text-slate-900">{{ $prc->items_count }} SKU</span>
                                <span class="block text-[10px] font-bold text-emerald-700">+{{ number_format((int) $prc->total_quantity) }} unit masuk</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $prc->formatted_total_cost }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $prc->status_badge_class }}">
                                    {{ $prc->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('admin.spare-part-procurements.show', $prc) }}"
                                   class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-slate-200 transition">
                                    Lihat Dokumen &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                                Belum ada transaksi pengadaan sparepart. Klik <strong>Buat Pengadaan Baru</strong> untuk menambah stok gudang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($procurements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $procurements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
