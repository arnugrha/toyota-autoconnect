@extends('layouts.dashboard')

@section('title', 'Analisis Inventaris & Stok — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Analisis Inventaris &amp; Ketersediaan Stok Unit
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Monitoring real-time stok fisik, stok terpesan SPK, alokasi cabang, peringatan stok menipis, dan riwayat mutasi unit.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold shadow-xs">
                <span>Formula: Tersedia = Fisik - Dipesan</span>
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    @include('admin.analytics.partials.nav-tabs')

    <!-- Date Range & Branch Filters -->
    @include('admin.analytics.partials.period-filter', ['branches' => $data['branches'] ?? null, 'selectedBranchId' => $selectedBranchId ?? null])

    <!-- 1. Stock Status KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Stok Fisik</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($data['kpis']['physical_stock']) }}</span>
                <span class="text-xs font-bold text-slate-500">Unit di Dealer</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Unit yang berada di pool showroom/gudang</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Stok Dipesan (Reserved)</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-extrabold text-amber-600">{{ number_format($data['kpis']['reserved_stock']) }}</span>
                <span class="text-xs font-bold text-amber-500">Terkunci SPK</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Unit yang sudah dialokasikan untuk pemesan</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Stok Tersedia (Ready Sale)</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-extrabold text-emerald-600">{{ number_format($data['kpis']['available_stock']) }}</span>
                <span class="text-xs font-bold text-emerald-500">Bisa Dipesan</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Sisa unit yang belum memiliki komitmen SPK</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Peringatan Stok Menipis</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-extrabold {{ $data['kpis']['low_stock_count'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ number_format($data['kpis']['low_stock_count']) }}
                </span>
                @if($data['kpis']['low_stock_count'] > 0)
                    <span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-rose-100 text-rose-800">
                        Perlu Restock
                    </span>
                @else
                    <span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">
                        Aman
                    </span>
                @endif
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Unit dengan sisa stok &le; batas minimum</p>
        </div>
    </div>

    <!-- 2. Low Stock Alerts Table (If Any) -->
    @if($data['low_stock_items']->isNotEmpty())
        <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-xs">
            <div class="flex items-center gap-2 mb-3">
                <div class="h-6 w-6 rounded-lg bg-amber-200 text-amber-900 flex items-center justify-center font-extrabold text-xs">
                    !
                </div>
                <div>
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-amber-950">
                        Daftar Alokasi Stok Menipis (Restock Segera)
                    </h2>
                    <p class="text-[11px] text-amber-800">Alokasi berikut memiliki stok unit tersedia di bawah batas alert minimum</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[750px]">
                    <thead>
                        <tr class="border-b border-amber-200 text-[11px] font-bold uppercase tracking-wider text-amber-900">
                            <th class="py-2 px-3 whitespace-nowrap min-w-[160px]">Unit Model</th>
                            <th class="py-2 px-3 whitespace-nowrap min-w-[150px]">Cabang Dealer</th>
                            <th class="py-2 px-3 text-center whitespace-nowrap min-w-[90px]">Stok Fisik</th>
                            <th class="py-2 px-3 text-center whitespace-nowrap min-w-[100px]">Dipesan (SPK)</th>
                            <th class="py-2 px-3 text-center whitespace-nowrap min-w-[110px]">Sisa Tersedia</th>
                            <th class="py-2 px-3 text-center whitespace-nowrap min-w-[110px]">Batas Minimum</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100 font-semibold text-slate-800">
                        @foreach($data['low_stock_items'] as $inv)
                            @php $avail = max(0, $inv->stock_quantity - $inv->reserved_quantity); @endphp
                            <tr>
                                <td class="py-2 px-3 font-bold text-slate-900">{{ $inv->vehicle?->name ?? 'N/A' }}</td>
                                <td class="py-2 px-3 text-slate-700">{{ $inv->branch?->name ?? 'Semua Cabang' }}</td>
                                <td class="py-2 px-3 text-center font-bold text-slate-900">{{ $inv->stock_quantity }}</td>
                                <td class="py-2 px-3 text-center text-amber-700 font-bold">{{ $inv->reserved_quantity }}</td>
                                <td class="py-2 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-extrabold bg-rose-100 text-rose-800">
                                        {{ $avail }} Unit
                                    </span>
                                </td>
                                <td class="py-2 px-3 text-center text-slate-500">{{ $inv->minimum_stock_alert ?? 3 }} Unit</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- 3. Distribution Grid (By Branch & By Model) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Distribusi Stok per Cabang Dealer -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Distribusi Stok per Cabang Dealer</h2>
            @if($data['by_branch']->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400">Belum ada inventaris di cabang dealer.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[420px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2 whitespace-nowrap min-w-[150px]">Cabang</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[80px]">Stok Fisik</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[80px]">Dipesan</th>
                                <th class="py-2 text-right whitespace-nowrap min-w-[90px]">Tersedia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                            @foreach($data['by_branch'] as $b)
                                <tr>
                                    <td class="py-2 font-bold text-slate-900 whitespace-nowrap">
                                        {{ $b['name'] }}
                                        <span class="block text-[10px] text-slate-400 font-normal">{{ $b['city'] }}</span>
                                    </td>
                                    <td class="py-2 text-center text-slate-800 font-bold whitespace-nowrap">{{ $b['physical'] }}</td>
                                    <td class="py-2 text-center text-amber-600 whitespace-nowrap">{{ $b['reserved'] }}</td>
                                    <td class="py-2 text-right font-extrabold text-emerald-700 whitespace-nowrap">{{ $b['available'] }} unit</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Distribusi Stok per Model Kendaraan -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Ketersediaan Stok per Model Kendaraan</h2>
            @if($data['by_model']->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400">Belum ada stok model kendaraan.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[420px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2 whitespace-nowrap min-w-[150px]">Model Unit</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[80px]">Fisik</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[80px]">Dipesan</th>
                                <th class="py-2 text-right whitespace-nowrap min-w-[90px]">Tersedia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                            @foreach($data['by_model'] as $m)
                                <tr>
                                    <td class="py-2 font-bold text-slate-900 whitespace-nowrap">{{ $m->name }}</td>
                                    <td class="py-2 text-center text-slate-800 font-bold whitespace-nowrap">{{ $m->physical }}</td>
                                    <td class="py-2 text-center text-amber-600 whitespace-nowrap">{{ $m->reserved }}</td>
                                    <td class="py-2 text-right font-extrabold text-emerald-700 whitespace-nowrap">{{ max(0, $m->available) }} unit</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <!-- 4. Riwayat Mutasi Stok Unit (Audit Trail Table) -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Audit Log &amp; Riwayat Mutasi Stok</h2>
                <p class="text-[11px] text-slate-500">Jejak pergerakan unit (masuk, keluar, reservasi SPK, rilis pembatalan)</p>
            </div>

            <!-- Movement Type Filter -->
            <form method="GET" action="{{ route('admin.analytics.inventory') }}" class="flex items-center gap-2 text-xs">
                <input type="hidden" name="period" value="{{ request('period', 'this_month') }}">
                @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                @if(request('branch_id')) <input type="hidden" name="branch_id" value="{{ request('branch_id') }}"> @endif

                <select name="movement_type" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-800">
                    <option value="">Semua Jenis Mutasi</option>
                    <option value="in" {{ request('movement_type') === 'in' ? 'selected' : '' }}>Stok Masuk (+)</option>
                    <option value="out" {{ request('movement_type') === 'out' ? 'selected' : '' }}>Stok Keluar / Terkirim (-)</option>
                    <option value="reservation" {{ request('movement_type') === 'reservation' ? 'selected' : '' }}>Reservasi SPK</option>
                    <option value="release" {{ request('movement_type') === 'release' ? 'selected' : '' }}>Rilis Pembatalan</option>
                    <option value="adjustment" {{ request('movement_type') === 'adjustment' ? 'selected' : '' }}>Penyesuaian Fisik</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[950px]">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Waktu</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Model Kendaraan</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Cabang Dealer</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[110px]">Jenis Mutasi</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[90px]">Jumlah</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Keterangan / Referensi</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[140px]">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                    @forelse($data['movements'] as $mv)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-900">{{ $mv->vehicleInventory?->vehicle?->name ?? 'N/A' }}</td>
                            <td class="py-2.5 px-3 text-slate-700">{{ $mv->vehicleInventory?->branch?->name ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ in_array($mv->movement_type, ['in', 'release']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $mv->movement_type }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-center font-bold text-slate-900 whitespace-nowrap">
                                {{ $mv->quantity > 0 ? '+' : '' }}{{ $mv->quantity }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 max-w-[200px] truncate">{{ $mv->notes ?? $mv->reference_number ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $mv->user?->name ?? 'Sistem' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-xs text-slate-400 font-medium">
                                Belum ada riwayat mutasi stok untuk rentang filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data['movements']->hasPages())
            <div class="mt-4 pt-3 border-t border-slate-100">
                {{ $data['movements']->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

