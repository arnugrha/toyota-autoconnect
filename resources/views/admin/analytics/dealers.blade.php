@extends('layouts.dashboard')

@section('title', 'Komparasi Jaringan Dealer — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Komparasi &amp; Peringkat Performa Jaringan Dealer
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Peringkat komparatif cabang dealer berdasarkan penjualan, pendapatan, konversi prospek, kapasitas servis, dan alokasi stok.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold shadow-xs">
                <span>{{ $data['dealers']->count() }} Cabang Dealer Aktif</span>
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    @include('admin.analytics.partials.nav-tabs')

    <!-- Date Range & Branch Filters -->
    @include('admin.analytics.partials.period-filter', ['branches' => $data['branches'] ?? null, 'selectedBranchId' => $selectedBranchId ?? null])

    <!-- Totals Across All Branches -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Penjualan</span>
            <span class="text-xl font-extrabold text-slate-900 mt-1 block">{{ number_format($data['totals']['total_sales']) }} Unit</span>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Pendapatan</span>
            <span class="text-xl font-extrabold text-slate-900 mt-1 block truncate" title="Rp {{ number_format($data['totals']['total_revenue'], 0, ',', '.') }}">
                Rp {{ number_format($data['totals']['total_revenue'] / 1000000, 0, ',', '.') }} Jt
            </span>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total SPK Unit</span>
            <span class="text-xl font-extrabold text-slate-900 mt-1 block">{{ number_format($data['totals']['total_bookings']) }} SPK</span>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Prospek Lead</span>
            <span class="text-xl font-extrabold text-slate-900 mt-1 block">{{ number_format($data['totals']['total_leads']) }}</span>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Stok Tersedia</span>
            <span class="text-xl font-extrabold text-slate-900 mt-1 block">{{ number_format($data['totals']['total_available_stock']) }} Unit</span>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Booking Servis</span>
            <span class="text-xl font-extrabold text-slate-900 mt-1 block">{{ number_format($data['totals']['total_service_bookings']) }} Unit</span>
        </div>
    </div>

    <!-- Ranking Table & Sorting Controls -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Peringkat Komparasi Performa Dealer</h2>
                <p class="text-[11px] text-slate-500">Urutkan dan bandingkan metrik antar cabang dealer</p>
            </div>

            <!-- Sort By Control -->
            <form method="GET" action="{{ route('admin.analytics.dealers') }}" class="flex items-center gap-2 text-xs">
                <input type="hidden" name="period" value="{{ request('period', 'this_month') }}">
                @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                @if(request('branch_id')) <input type="hidden" name="branch_id" value="{{ request('branch_id') }}"> @endif

                <label class="font-bold text-slate-600 whitespace-nowrap">Urutkan Berdasarkan:</label>
                <select name="sort_by" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-800">
                    <option value="revenue" {{ $data['sort_by'] === 'revenue' ? 'selected' : '' }}>Pendapatan Penjualan Tertinggi</option>
                    <option value="sales_count" {{ $data['sort_by'] === 'sales_count' ? 'selected' : '' }}>Unit Terjual Terbanyak</option>
                    <option value="leads_count" {{ $data['sort_by'] === 'leads_count' ? 'selected' : '' }}>Prospek Leads Terbanyak</option>
                    <option value="conversion_rate" {{ $data['sort_by'] === 'conversion_rate' ? 'selected' : '' }}>Rasio Konversi Tertinggi</option>
                    <option value="service_count" {{ $data['sort_by'] === 'service_count' ? 'selected' : '' }}>Booking Servis Terbanyak</option>
                    <option value="available_stock" {{ $data['sort_by'] === 'available_stock' ? 'selected' : '' }}>Sisa Stok Unit Terbanyak</option>
                </select>
            </form>
        </div>

        <!-- Table with Horizontal Scroll Support -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1250px]">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[60px]">Rank</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Cabang Dealer &amp; Lokasi</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[100px]">Unit Terjual</th>
                        <th class="py-2.5 px-3 text-right whitespace-nowrap min-w-[150px]">Pendapatan Penjualan</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[90px]">Total SPK</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[100px]">Leads Masuk</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[110px]">Konversi Deal</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[90px]">Test Drive</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[110px]">Stok Tersedia</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[110px]">Booking Servis</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[110px]">Servis Selesai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                    @forelse($data['dealers'] as $idx => $d)
                        <tr class="hover:bg-slate-50/60 transition {{ $idx === 0 ? 'bg-amber-50/20' : '' }}">
                            <td class="py-2.5 px-3 text-center">
                                @if($idx === 0)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-400 text-slate-900 font-extrabold text-[11px]">1</span>
                                @elseif($idx === 1)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-300 text-slate-900 font-extrabold text-[11px]">2</span>
                                @elseif($idx === 2)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-700/40 text-amber-950 font-extrabold text-[11px]">3</span>
                                @else
                                    <span class="text-slate-400 font-bold text-xs">{{ $idx + 1 }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                <p class="font-bold text-slate-900 text-xs">{{ $d['name'] }}</p>
                                <p class="text-[10px] text-slate-400">{{ $d['city'] }} &bull; <span class="font-mono">{{ $d['code'] }}</span></p>
                            </td>
                            <td class="py-2.5 px-3 text-center font-extrabold text-slate-900 whitespace-nowrap">
                                {{ $d['sales_count'] }} Unit
                            </td>
                            <td class="py-2.5 px-3 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($d['revenue'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-center text-slate-700 whitespace-nowrap">
                                {{ $d['bookings_count'] }}
                            </td>
                            <td class="py-2.5 px-3 text-center text-slate-700 whitespace-nowrap">
                                {{ $d['leads_count'] }}
                            </td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[11px] font-extrabold {{ $d['conversion_rate'] >= 15 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $d['conversion_rate'] }}%
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-center text-slate-700 whitespace-nowrap">
                                {{ $d['test_drive_count'] }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-bold text-slate-800 whitespace-nowrap">
                                {{ $d['available_stock'] }}
                            </td>
                            <td class="py-2.5 px-3 text-center text-slate-700 whitespace-nowrap">
                                {{ $d['service_bookings_count'] }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-bold text-emerald-700 whitespace-nowrap">
                                {{ $d['completed_services_count'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center text-xs text-slate-400 font-medium">
                                Belum ada data cabang dealer yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

