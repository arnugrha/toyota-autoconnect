@extends('layouts.dashboard')

@section('title', 'Monitoring Pendapatan Eksekutif (Owner) — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Executive Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-[11px] font-bold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>PORTAL MONITORING EKSEKUTIF &amp; OWNER</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Laporan Pendapatan &amp; Arus Kas Bisnis
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Pantau realisasi pendapatan penjualan unit, penerimaan pembayaran online, dan omzet layanan bengkel multi-cabang.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.reports.index', ['type' => 'revenue']) }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span>Pusat Laporan Eksekutif</span>
            </a>

            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition">
                <svg class="h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.03-1.27-2.829H18.55c-.598.799-1.03 1.753-1.27 2.829M6 6.75h12m-12 3h12M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V9a2.25 2.25 0 0 0-2.25-2.25H4.5A2.25 2.25 0 0 0 2.25 9v8.25A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
                <span>Cetak Ringkasan</span>
            </button>
        </div>
    </div>

    <!-- Period & Branch Filter Controls -->
    @include('admin.analytics.partials.period-filter', [
        'branches' => $branches ?? null,
        'selectedBranchId' => $selectedBranchId ?? null,
        'formAction' => route('admin.owner.revenue')
    ])

    <!-- 1. Executive Summary Cards (Hero Metrics: Pendapatan & Laba) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Total Omzet Bruto Bisnis -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-5 text-white shadow-md" style="background-color: #0f172a;">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Omzet Bisnis</span>
                <span class="rounded-lg bg-red-600/30 border border-red-500/30 px-2 py-0.5 text-[10px] font-bold text-red-300">
                    Gross Revenue
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Rp {{ number_format($stats['total_combined_revenue'], 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">
                    Akumulasi penjualan mobil baru + bengkel resmi
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-300">
                <span>Unit: <strong>{{ number_format($stats['confirmed_spk_count']) }} SPK</strong></span>
                <span>Bengkel: <strong>{{ number_format($stats['service_order_count']) }} WO</strong></span>
            </div>
        </div>

        <!-- Card 2: Estimasi Laba Kotor Bisnis (Gross Profit) -->
        <div class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Estimasi Laba Kotor</span>
                <span class="rounded-lg bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                    Margin {{ number_format($stats['gross_profit_margin'], 1) }}%
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold tracking-tight text-emerald-600">
                    Rp {{ number_format($stats['total_gross_profit'], 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Estimasi profit kotor dari unit SPK &amp; purnajual bengkel
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600">
                <span>Laba Unit: <strong>Rp {{ number_format($stats['vehicle_gross_profit'], 0, ',', '.') }}</strong></span>
                <span>Laba Servis: <strong>Rp {{ number_format($stats['service_gross_profit'], 0, ',', '.') }}</strong></span>
            </div>
        </div>

        <!-- Card 3: Proyeksi Laba Bersih (Net Operating Profit) -->
        <div class="rounded-2xl border border-indigo-200 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Proyeksi Laba Bersih</span>
                <span class="rounded-lg bg-indigo-50 border border-indigo-200 px-2 py-0.5 text-[10px] font-bold text-indigo-700">
                    EBT {{ number_format($stats['net_profit_margin'], 1) }}%
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold tracking-tight text-indigo-700">
                    Rp {{ number_format($stats['net_operating_profit'], 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Estimasi laba bersih setelah asumsi beban OPEX dealer
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600">
                <span>Rasio Efisiensi:</span>
                <span class="font-bold text-indigo-700">Sehat (65% GP)</span>
            </div>
        </div>

        <!-- Card 4: Kas Riil Diterima (Realized Cash Inflow) -->
        <div class="rounded-2xl border border-blue-200 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Kas Masuk Terealisasi</span>
                <span class="rounded-lg bg-blue-50 border border-blue-200 px-2 py-0.5 text-[10px] font-bold text-blue-700">
                    Settled
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold tracking-tight text-blue-700">
                    Rp {{ number_format($stats['cash_inflow_received'], 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Dana terkonfirmasi masuk via Midtrans &amp; Kasir
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600">
                <span>Booking Fee:</span>
                <span class="font-bold text-slate-900">Rp {{ number_format($stats['total_booking_fee'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- 1b. Segment Revenue & Profit Overview Banner -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-2xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75h-6m6 0a3.75 3.75 0 0 1 3.75 3.75v1.5m-9.75-5.25V3.75m0 3.75a3.75 3.75 0 0 0-3.75 3.75v1.5m0 0H18.75" />
                    </svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Lini Penjualan Mobil Baru</span>
                    <p class="text-sm font-extrabold text-slate-900">Rp {{ number_format($stats['total_sales_gross'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-500">Estimasi Laba: <span class="font-bold text-emerald-600">Rp {{ number_format($stats['vehicle_gross_profit'], 0, ',', '.') }}</span> (7.0%)</p>
                </div>
            </div>
            <div class="text-right">
                <span class="rounded bg-blue-50 text-blue-700 font-bold text-[10px] px-2 py-0.5">
                    {{ number_format($stats['confirmed_spk_count']) }} SPK Confirmed
                </span>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-2xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27-2.828-2.828 3.27-3.27a4.5 4.5 0 0 0-6.32 4.486c.118.58.094 1.193-.07 1.743" />
                    </svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Lini Bengkel &amp; After-Sales</span>
                    <p class="text-sm font-extrabold text-slate-900">Rp {{ number_format($stats['total_service_revenue'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-500">Estimasi Laba: <span class="font-bold text-emerald-600">Rp {{ number_format($stats['service_gross_profit'], 0, ',', '.') }}</span> (~40%)</p>
                </div>
            </div>
            <div class="text-right">
                <span class="rounded bg-amber-50 text-amber-700 font-bold text-[10px] px-2 py-0.5">
                    {{ number_format($stats['service_order_count']) }} Servis Selesai
                </span>
            </div>
        </div>
    </div>

    <!-- 2. Dual Breakdown Section: Branch Performance vs Top Contributing Models -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Branch Revenue Breakdown Table (Col 7) -->
        <div class="lg:col-span-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Performa Pendapatan Berdasarkan Cabang Dealer</h2>
                    <p class="text-[11px] text-slate-500">Komparasi kontribusi omzet penjualan unit dan pendapatan bengkel antar cabang</p>
                </div>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-600">
                    {{ count($branchBreakdown) }} Cabang Aktif
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 bg-slate-50/50">
                            <th class="py-2.5 px-3">Cabang Dealer</th>
                            <th class="py-2.5 px-3 text-right">Penjualan Unit</th>
                            <th class="py-2.5 px-3 text-right">Jasa Bengkel</th>
                            <th class="py-2.5 px-3 text-right">Total Omzet</th>
                            <th class="py-2.5 px-3 text-right">Estimasi Laba</th>
                            <th class="py-2.5 px-3 text-right">Kas Real</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($branchBreakdown as $bData)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $bData['branch']->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $bData['branch']->city }} &bull; {{ $bData['branch']->code }}</div>
                                </td>
                                <td class="py-3 px-3 text-right text-slate-800">
                                    Rp {{ number_format($bData['sales_revenue'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right text-slate-800">
                                    Rp {{ number_format($bData['service_revenue'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-slate-900">
                                    Rp {{ number_format($bData['total_revenue'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="font-extrabold text-emerald-600">Rp {{ number_format($bData['estimated_profit'], 0, ',', '.') }}</div>
                                    <div class="text-[10px] text-slate-400">Margin {{ number_format($bData['profit_margin'], 1) }}%</div>
                                </td>
                                <td class="py-3 px-3 text-right text-blue-600 font-semibold">
                                    Rp {{ number_format($bData['cash_inflow'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-xs text-slate-400">
                                    Belum ada data transaksi cabang pada periode yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Vehicle Revenue Contributors (Col 5) -->
        <div class="lg:col-span-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Model Kendaraan Terlaris</h2>
                        <p class="text-[11px] text-slate-500">Kontributor volume nominal omzet tertinggi</p>
                    </div>
                    <span class="rounded-full bg-red-50 text-red-600 px-2.5 py-0.5 text-[10px] font-bold border border-red-200">
                        Top Ranking
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($topVehicles as $index => $tv)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 transition">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 shadow-2xs">
                                    #{{ $index + 1 }}
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-slate-900">{{ $tv->vehicle?->name ?? 'Kendaraan' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $tv->vehicle?->variant ?? '-' }} &bull; {{ $tv->total_units }} Unit Terpesan</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-extrabold text-slate-900">
                                    Rp {{ number_format($tv->total_volume, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-emerald-600 font-semibold">Omzet SPK</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            Belum ada unit yang terpesan pada periode yang dipilih.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                <a href="{{ route('admin.analytics.sales') }}" class="text-xs font-bold text-red-600 hover:text-red-700 inline-flex items-center gap-1">
                    <span>Lihat Analisis Penjualan Selengkapnya</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- 3. Recent Verified Cash Inflow Transactions -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Riwayat Arus Kas Masuk Terverifikasi (Midtrans &amp; Kas)</h2>
                <p class="text-[11px] text-slate-500">Penerimaan dana booking fee dan pembayaran transaksi terkini</p>
            </div>
            <a href="{{ route('admin.payments.index') }}" class="text-xs font-bold text-red-600 hover:text-red-700">
                Lihat Seluruh Transaksi &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 bg-slate-50/50">
                        <th class="py-2.5 px-3">Order ID</th>
                        <th class="py-2.5 px-3">Pelanggan</th>
                        <th class="py-2.5 px-3">Unit Kendaraan</th>
                        <th class="py-2.5 px-3">Cabang</th>
                        <th class="py-2.5 px-3">Metode Bayar</th>
                        <th class="py-2.5 px-3 text-right">Nominal</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($recentTransactions as $p)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $p->order_id }}</td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-800">{{ $p->user?->name ?? 'Pelanggan' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $p->user?->email }}</div>
                            </td>
                            <td class="py-2.5 px-3">
                                {{ $p->vehicleBooking?->vehicle?->name ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3">
                                {{ $p->vehicleBooking?->branch?->name ?? 'Kantor Pusat' }}
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                    {{ $p->payment_method ?? 'Midtrans Snap' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-extrabold text-emerald-600">
                                Rp {{ number_format($p->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                    LUNAS
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-xs text-slate-400">
                                Belum ada riwayat transaksi lunas pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
