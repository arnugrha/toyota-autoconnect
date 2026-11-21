@extends('layouts.dashboard')

@section('title', 'Dashboard Eksekutif & BI — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Dashboard Eksekutif &amp; Business Intelligence
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Gambaran menyeluruh performa bisnis, penjualan unit, prospek CRM, dan operasional dealer Toyota.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Data Aktual Real-time</span>
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    @include('admin.analytics.partials.nav-tabs')

    <!-- Date Range & Branch Filters -->
    @include('admin.analytics.partials.period-filter', ['branches' => $branches ?? null, 'selectedBranchId' => $selectedBranchId ?? null])

    <!-- Dynamic Business Intelligence Insights -->
    @if(!empty($data['insights']))
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <div class="flex items-center gap-2 mb-3">
                <div class="h-7 w-7 rounded-lg bg-red-100 text-red-600 flex items-center justify-center font-bold text-xs">
                    BI
                </div>
                <div>
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-900">
                        Rekomendasi &amp; Insight Bisnis Otomatis
                    </h2>
                    <p class="text-[11px] text-slate-500">Dihasilkan berdasarkan komparasi data aktual transaksi periode ini</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($data['insights'] as $insight)
                    <div class="rounded-xl p-3.5 border text-xs {{ $insight['type'] === 'positive' ? 'bg-emerald-50/60 border-emerald-200 text-emerald-950' : ($insight['type'] === 'warning' ? 'bg-amber-50/60 border-amber-200 text-amber-950' : 'bg-slate-50 border-slate-200 text-slate-900') }}">
                        <div class="flex items-start gap-2">
                            <span class="font-extrabold text-sm">
                                @if($insight['type'] === 'positive') &uarr; @elseif($insight['type'] === 'warning') &excl; @else &bull; @endif
                            </span>
                            <div>
                                <h3 class="font-bold mb-1">{{ $insight['title'] }}</h3>
                                <p class="text-[11px] leading-relaxed text-slate-600">{{ $insight['description'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 1. Executive Business Overview (6 Key Metric Cards) -->
    <div>
        <h2 class="text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-3">1. Ringkasan Performa Bisnis</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3.5">

            <!-- Card 1: Penjualan Unit Kendaraan -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Unit Terjual</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-2xl font-extrabold text-slate-900">{{ number_format($data['overview']['sales_count']['value']) }}</span>
                    <span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded {{ $data['overview']['sales_count']['change']['direction'] === 'up' ? 'bg-emerald-100 text-emerald-700' : ($data['overview']['sales_count']['change']['direction'] === 'down' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                        {{ $data['overview']['sales_count']['change']['formatted'] }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Prev: {{ number_format($data['overview']['sales_count']['prev']) }} unit</p>
            </div>

            <!-- Card 2: Total Pendapatan Penjualan -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pendapatan Penjualan</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-lg font-extrabold text-slate-900 truncate" title="Rp {{ number_format($data['overview']['sales_revenue']['value'], 0, ',', '.') }}">
                        Rp {{ number_format($data['overview']['sales_revenue']['value'] / 1000000, 0, ',', '.') }} Jt
                    </span>
                    <span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded {{ $data['overview']['sales_revenue']['change']['direction'] === 'up' ? 'bg-emerald-100 text-emerald-700' : ($data['overview']['sales_revenue']['change']['direction'] === 'down' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                        {{ $data['overview']['sales_revenue']['change']['formatted'] }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Prev: Rp {{ number_format($data['overview']['sales_revenue']['prev'] / 1000000, 0, ',', '.') }} Jt</p>
            </div>

            <!-- Card 3: Pemesanan Unit (SPK) -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Booking (SPK)</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-2xl font-extrabold text-slate-900">{{ number_format($data['overview']['bookings_count']['value']) }}</span>
                    <span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded {{ $data['overview']['bookings_count']['change']['direction'] === 'up' ? 'bg-emerald-100 text-emerald-700' : ($data['overview']['bookings_count']['change']['direction'] === 'down' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                        {{ $data['overview']['bookings_count']['change']['formatted'] }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Prev: {{ number_format($data['overview']['bookings_count']['prev']) }} SPK</p>
            </div>

            <!-- Card 4: CRM Leads & Konversi -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Leads &amp; Konversi</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-2xl font-extrabold text-slate-900">{{ number_format($data['overview']['leads']['total']) }}</span>
                    <span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">
                        {{ $data['overview']['leads']['conversion_rate'] }}% Deal
                    </span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Prev: {{ number_format($data['overview']['leads']['prev']) }} leads</p>
            </div>

            <!-- Card 5: Transaksi Pembayaran Diterima -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pembayaran Lunas</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-lg font-extrabold text-slate-900 truncate" title="Rp {{ number_format($data['overview']['payments']['amount'], 0, ',', '.') }}">
                        Rp {{ number_format($data['overview']['payments']['amount'] / 1000000, 0, ',', '.') }} Jt
                    </span>
                    <span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700">
                        {{ $data['overview']['payments']['count'] }} tx
                    </span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Gateway Midtrans Snap</p>
            </div>

            <!-- Card 6: Ketersediaan Stok Unit -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Stok Tersedia</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-2xl font-extrabold text-slate-900">{{ number_format($data['overview']['inventory']['available']) }}</span>
                    @if($data['overview']['inventory']['low_stock'] > 0)
                        <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">
                            {{ $data['overview']['inventory']['low_stock'] }} Menipis
                        </span>
                    @else
                        <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">
                            Aman
                        </span>
                    @endif
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Fisik: {{ $data['overview']['inventory']['physical'] }} | Pesan: {{ $data['overview']['inventory']['reserved'] }}</p>
            </div>

        </div>
    </div>

    <!-- 2. Two-Column Analytical Breakdown: CRM Funnel & Sales by Category -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Left: CRM Lead Funnel -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Funnel Konversi CRM &amp; Prospek</h2>
                    <p class="text-[11px] text-slate-500">Distribusi status dari total {{ $data['overview']['leads']['total'] }} prospek lead</p>
                </div>
                <a href="{{ route('admin.analytics.crm') }}" class="text-xs font-bold text-red-600 hover:text-red-700">Detail &rarr;</a>
            </div>

            @php
                $stages = [
                    'new' => ['label' => 'Prospek Baru', 'color' => 'bg-slate-400'],
                    'contacted' => ['label' => 'Sudah Dihubungi', 'color' => 'bg-blue-500'],
                    'interested' => ['label' => 'Tertarik / Follow-up', 'color' => 'bg-cyan-500'],
                    'test_drive' => ['label' => 'Test Drive Dijadwalkan', 'color' => 'bg-purple-500'],
                    'negotiation' => ['label' => 'Negosiasi Penawaran', 'color' => 'bg-amber-500'],
                    'deal' => ['label' => 'Deal / Booking Unit (SPK)', 'color' => 'bg-emerald-500'],
                    'lost' => ['label' => 'Lost / Tidak Tertarik', 'color' => 'bg-rose-500'],
                ];
                $totalL = max(1, $data['overview']['leads']['total']);
            @endphp

            <div class="space-y-3">
                @foreach($stages as $key => $meta)
                    @php
                        $cnt = $data['crm']['counts'][$key] ?? 0;
                        $pct = round(($cnt / $totalL) * 100, 1);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold mb-1">
                            <span class="text-slate-700">{{ $meta['label'] }}</span>
                            <span class="text-slate-900 font-bold">{{ $cnt }} <span class="text-slate-400 font-normal">({{ $pct }}%)</span></span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full {{ $meta['color'] }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Penjualan Berdasarkan Kategori Mobil -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Penjualan per Kategori Mobil</h2>
                    <p class="text-[11px] text-slate-500">Volume dan nilai transaksi per kategori pada periode ini</p>
                </div>
                <a href="{{ route('admin.analytics.sales') }}" class="text-xs font-bold text-red-600 hover:text-red-700">Detail &rarr;</a>
            </div>

            @if($data['sales_by_category']->isEmpty())
                <div class="py-12 text-center text-xs text-slate-400 font-medium">
                    Belum ada data penjualan pada kategori mobil untuk periode ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[420px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2.5 whitespace-nowrap min-w-[140px]">Kategori</th>
                                <th class="py-2.5 text-center whitespace-nowrap min-w-[100px]">Unit Terjual</th>
                                <th class="py-2.5 text-right whitespace-nowrap min-w-[140px]">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                            @foreach($data['sales_by_category'] as $cat)
                                <tr>
                                    <td class="py-2.5 font-bold text-slate-900 whitespace-nowrap">{{ $cat->category_name }}</td>
                                    <td class="py-2.5 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 font-extrabold text-slate-900">{{ $cat->count }}</span>
                                    </td>
                                    <td class="py-2.5 text-right font-extrabold text-slate-900 whitespace-nowrap">Rp {{ number_format($cat->revenue, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <!-- 3. Bottom Grid: Top Selling Models & Dealer Performance Snapshot -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Top Selling Models -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">5 Model Kendaraan Terlaris</h2>
                <a href="{{ route('admin.analytics.sales') }}" class="text-xs font-bold text-red-600 hover:text-red-700">Semua Model &rarr;</a>
            </div>

            @if($data['top_models']->isEmpty())
                <div class="py-10 text-center text-xs text-slate-400 font-medium">
                    Belum ada data unit yang terjual pada rentang periode ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[450px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2 whitespace-nowrap min-w-[40px]">#</th>
                                <th class="py-2 whitespace-nowrap min-w-[160px]">Nama Model</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[80px]">Unit</th>
                                <th class="py-2 text-right whitespace-nowrap min-w-[140px]">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                            @foreach($data['top_models'] as $idx => $m)
                                <tr>
                                    <td class="py-2 text-slate-400 font-bold whitespace-nowrap">{{ $idx + 1 }}</td>
                                    <td class="py-2 font-bold text-slate-900 whitespace-nowrap">{{ $m->name }}</td>
                                    <td class="py-2 text-center font-extrabold text-slate-900 whitespace-nowrap">{{ $m->total_sold }}</td>
                                    <td class="py-2 text-right font-bold text-slate-900 whitespace-nowrap">Rp {{ number_format($m->total_revenue, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Dealer Performance Snapshot -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Performa Jaringan Dealer</h2>
                @if(auth()->user()->hasRole(['Super Admin', 'Dealer Manager']))
                    <a href="{{ route('admin.analytics.dealers') }}" class="text-xs font-bold text-red-600 hover:text-red-700">Komparasi Lengkap &rarr;</a>
                @endif
            </div>

            @if($data['dealer_performance']->isEmpty())
                <div class="py-10 text-center text-xs text-slate-400 font-medium">
                    Belum ada data cabang dealer aktif.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[480px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2 whitespace-nowrap min-w-[160px]">Cabang Dealer</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[100px]">Penjualan</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[90px]">Konversi</th>
                                <th class="py-2 text-right whitespace-nowrap min-w-[120px]">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                            @foreach($data['dealer_performance']->take(5) as $d)
                                <tr>
                                    <td class="py-2 whitespace-nowrap">
                                        <p class="font-bold text-slate-900">{{ $d['name'] }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $d['city'] }}</p>
                                    </td>
                                    <td class="py-2 text-center font-extrabold text-slate-900 whitespace-nowrap">{{ $d['sales_count'] }} unit</td>
                                    <td class="py-2 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $d['conversion_rate'] >= 15 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $d['conversion_rate'] }}%
                                        </span>
                                    </td>
                                    <td class="py-2 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                        Rp {{ number_format($d['revenue'] / 1000000, 0, ',', '.') }} Jt
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection

