@extends('layouts.dashboard')

@section('title', 'Analisis Penjualan & Pendapatan — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Analisis Penjualan Kendaraan &amp; Pendapatan
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Laporan komprehensif performa penjualan unit mobil baru Toyota, tren pendapatan, dan efektivitas sales.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold shadow-xs">
                <span>Unit Terkonfirmasi Lunas</span>
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    @include('admin.analytics.partials.nav-tabs')

    <!-- Date Range & Branch Filters -->
    @include('admin.analytics.partials.period-filter', ['branches' => $data['branches'] ?? null, 'selectedBranchId' => $selectedBranchId ?? null])

    <!-- 1. Key Performance Indicators (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Penjualan Kendaraan</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($data['kpis']['total_sales']) }}</span>
                <span class="text-xs font-bold text-slate-500">Unit Terjual</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Status SPK confirmed / completed lunas</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Pendapatan Unit</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-extrabold text-slate-900 truncate" title="Rp {{ number_format($data['kpis']['total_revenue'], 0, ',', '.') }}">
                    Rp {{ number_format($data['kpis']['total_revenue'], 0, ',', '.') }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Akumulasi net unit price kendaraan</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Rata-Rata Nilai Transaksi (AOV)</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-extrabold text-slate-900 truncate" title="Rp {{ number_format($data['kpis']['average_deal'], 0, ',', '.') }}">
                    Rp {{ number_format($data['kpis']['average_deal'], 0, ',', '.') }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Nilai rata-rata per transaksi unit</p>
        </div>
    </div>

    <!-- 2. Sales Trend Visualization -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Tren Penjualan &amp; Volume Unit</h2>
                <p class="text-[11px] text-slate-500">Aktivitas penjualan harian/bulanan pada rentang periode yang dipilih</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-bold">
                <span class="inline-flex items-center gap-1.5 text-red-600">
                    <span class="w-3 h-3 rounded bg-red-600"></span> Volume Unit
                </span>
            </div>
        </div>

        @php
            $maxCount = !empty($data['chart']['counts']) ? max(max($data['chart']['counts']), 1) : 1;
        @endphp

        <div class="overflow-x-auto pb-2">
            <div class="min-w-[600px] h-48 flex items-end gap-2 pt-6 pb-2 border-b border-slate-200">
                @forelse($data['chart']['labels'] as $idx => $label)
                    @php
                        $count = $data['chart']['counts'][$idx] ?? 0;
                        $rev = $data['chart']['revenues'][$idx] ?? 0;
                        $heightPct = min(100, max(6, round(($count / $maxCount) * 100)));
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 group relative h-full justify-end">
                        <!-- Tooltip -->
                        <div class="absolute bottom-full mb-2 hidden group-hover:flex flex-col items-center z-10 pointer-events-none">
                            <div class="bg-slate-900 text-white text-[10px] font-bold rounded-lg py-1 px-2 shadow-lg whitespace-nowrap text-center">
                                <div>{{ $label }}</div>
                                <div class="text-red-400">{{ $count }} unit terjual</div>
                                <div class="text-slate-300">Rp {{ number_format($rev, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <!-- Bar -->
                        <span class="text-[9px] font-bold text-slate-600 {{ $count > 0 ? '' : 'invisible' }}">{{ $count }}</span>
                        <div class="w-full max-w-[28px] rounded-t-md transition-all duration-300 {{ $count > 0 ? 'bg-red-600 group-hover:bg-red-700' : 'bg-slate-100' }}"
                             style="height: {{ $count > 0 ? $heightPct : 4 }}%;"></div>
                        <span class="text-[10px] font-semibold text-slate-400 truncate w-full text-center block mt-1">{{ $label }}</span>
                    </div>
                @empty
                    <div class="w-full h-full flex items-center justify-center text-xs text-slate-400">
                        Tidak ada data grafik untuk periode ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 3. Four Breakdown Cards (Model, Category, Dealer, Sales Executive) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Breakdown per Model Kendaraan -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Penjualan per Model Kendaraan</h2>
            @if($data['by_model']->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400">Belum ada unit terjual pada periode ini.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[420px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2 whitespace-nowrap min-w-[150px]">Model Unit</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[80px]">Terjual</th>
                                <th class="py-2 text-right whitespace-nowrap min-w-[140px]">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                            @foreach($data['by_model'] as $m)
                                <tr>
                                    <td class="py-2 font-bold text-slate-900 whitespace-nowrap">{{ $m->name }}</td>
                                    <td class="py-2 text-center font-extrabold text-slate-900 whitespace-nowrap">{{ $m->total_units }}</td>
                                    <td class="py-2 text-right text-slate-900 font-bold whitespace-nowrap">Rp {{ number_format($m->total_revenue, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Breakdown per Kategori Kendaraan -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Penjualan per Kategori Mobil</h2>
            @if($data['by_category']->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400">Belum ada kategori terjual pada periode ini.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[420px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2 whitespace-nowrap min-w-[150px]">Kategori</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[80px]">Terjual</th>
                                <th class="py-2 text-right whitespace-nowrap min-w-[140px]">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                            @foreach($data['by_category'] as $c)
                                <tr>
                                    <td class="py-2 font-bold text-slate-900 whitespace-nowrap">{{ $c->category_name }}</td>
                                    <td class="py-2 text-center font-extrabold text-slate-900 whitespace-nowrap">{{ $c->count }}</td>
                                    <td class="py-2 text-right text-slate-900 font-bold whitespace-nowrap">Rp {{ number_format($c->revenue, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Breakdown per Cabang Dealer -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Penjualan per Cabang Dealer</h2>
            @if($data['by_dealer']->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400">Belum ada transaksi di cabang pada periode ini.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[440px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2 whitespace-nowrap min-w-[160px]">Cabang</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[80px]">Terjual</th>
                                <th class="py-2 text-right whitespace-nowrap min-w-[140px]">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                            @foreach($data['by_dealer'] as $d)
                                <tr>
                                    <td class="py-2 whitespace-nowrap">
                                        <p class="font-bold text-slate-900">{{ $d->name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $d->city }}</p>
                                    </td>
                                    <td class="py-2 text-center font-extrabold text-slate-900 whitespace-nowrap">{{ $d->count }} unit</td>
                                    <td class="py-2 text-right text-slate-900 font-bold whitespace-nowrap">Rp {{ number_format($d->revenue, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Breakdown per Sales Executive -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Performa Sales Executive</h2>
            @if($data['by_executive']->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400">Belum ada data sales opportunity terkait pada periode ini.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[420px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2 whitespace-nowrap min-w-[150px]">Nama Sales</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[90px]">Unit Closing</th>
                                <th class="py-2 text-right whitespace-nowrap min-w-[140px]">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                            @foreach($data['by_executive'] as $e)
                                <tr>
                                    <td class="py-2 font-bold text-slate-900 whitespace-nowrap">{{ $e->executive_name }}</td>
                                    <td class="py-2 text-center font-extrabold text-slate-900 whitespace-nowrap">{{ $e->count }} unit</td>
                                    <td class="py-2 text-right text-slate-900 font-bold whitespace-nowrap">Rp {{ number_format($e->revenue, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <!-- 4. Filterable & Paginated Sales Transaction Table -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Daftar Transaksi Pemesanan &amp; Penjualan Unit</h2>
                <p class="text-[11px] text-slate-500">Daftar seluruh transaksi SPK dan kendaraan terjual pada periode ini</p>
            </div>
        </div>

        <!-- Transaction Inline Filters -->
        <form method="GET" action="{{ route('admin.analytics.sales') }}" class="flex flex-wrap items-center gap-2 mb-4 text-xs">
            <input type="hidden" name="period" value="{{ request('period', 'this_month') }}">
            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
            @if(request('branch_id')) <input type="hidden" name="branch_id" value="{{ request('branch_id') }}"> @endif

            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-800">
                <option value="">Semua Status SPK</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed (Dikonfirmasi)</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Selesai/Terkirim)</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>

            <select name="vehicle_id" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-800">
                <option value="">Semua Model Mobil</option>
                @foreach($data['vehicles'] as $v)
                    <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                @endforeach
            </select>

            <select name="category_id" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-800">
                <option value="">Semua Kategori</option>
                @foreach($data['categories'] as $c)
                    <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </form>

        <!-- Table with Horizontal Scroll Support -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1100px]">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">Kode SPK</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Tanggal</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Pelanggan</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Unit Kendaraan</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Cabang Dealer</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Sales PIC</th>
                        <th class="py-2.5 px-3 whitespace-nowrap text-right min-w-[130px]">Harga Unit Net</th>
                        <th class="py-2.5 px-3 whitespace-nowrap text-center min-w-[110px]">Status SPK</th>
                        <th class="py-2.5 px-3 whitespace-nowrap text-center min-w-[110px]">Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                    @forelse($data['transactions'] as $tx)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $tx->code }}</td>
                            <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-900">{{ $tx->customer?->name ?? 'N/A' }}</td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $tx->vehicle?->name ?? 'N/A' }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $tx->branch?->name ?? 'N/A' }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $tx->salesOpportunity?->assignedSales?->name ?? 'Tim Sales' }}</td>
                            <td class="py-2.5 px-3 text-right font-bold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($tx->net_unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ in_array($tx->status, ['confirmed', 'completed']) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $tx->status }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $tx->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $tx->payment_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-xs text-slate-400 font-medium">
                                Tidak ada transaksi penjualan yang sesuai dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($data['transactions']->hasPages())
            <div class="mt-4 pt-3 border-t border-slate-100">
                {{ $data['transactions']->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

