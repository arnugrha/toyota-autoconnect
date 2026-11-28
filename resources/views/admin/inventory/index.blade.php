@extends('layouts.dashboard')

@section('title', 'Manajemen Inventory & Stok — Toyota AutoConnect')
@section('header_title', 'Inventory & Ketersediaan Stok Unit')
@section('header_subtitle', 'Pantau persebaran stok fisik, reservasi unit, dan alokasi stok per cabang dealer')

@section('content')
<div class="space-y-6">


    <!-- Stock Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Card 1 -->
        <div class="rounded-xl bg-white p-3.5 border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Total Model Unit</span>
            <span class="text-xl font-extrabold text-slate-900 mt-1 block">{{ $stats['totalVehicles'] }}</span>
            <span class="text-[9px] text-slate-400 mt-0.5 block">Katalog master</span>
        </div>

        <!-- Card 2 -->
        <div class="rounded-xl bg-white p-3.5 border border-blue-200 shadow-sm bg-blue-50/20">
            <span class="text-[10px] font-bold text-blue-700 uppercase tracking-wider block">Stok Fisik Total</span>
            <span class="text-xl font-extrabold text-blue-700 mt-1 block">{{ $stats['totalStockPhysical'] }}</span>
            <span class="text-[9px] text-blue-600 mt-0.5 block">Seluruh cabang</span>
        </div>

        <!-- Card 3 -->
        <div class="rounded-xl bg-white p-3.5 border border-emerald-200 shadow-sm bg-emerald-50/20">
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Stok Siap Jual</span>
            <span class="text-xl font-extrabold text-emerald-700 mt-1 block">{{ $stats['totalStockAvailable'] }}</span>
            <span class="text-[9px] text-emerald-600 mt-0.5 block">Tersedia dipesan</span>
        </div>

        <!-- Card 4 -->
        <div class="rounded-xl bg-white p-3.5 border border-amber-200 shadow-sm bg-amber-50/20">
            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">Dialokasikan</span>
            <span class="text-xl font-extrabold text-amber-700 mt-1 block">{{ $stats['totalStockReserved'] }}</span>
            <span class="text-[9px] text-amber-600 mt-0.5 block">Booking / SPK</span>
        </div>

        <!-- Card 5 -->
        <div class="rounded-xl bg-white p-3.5 border border-purple-200 shadow-sm bg-purple-50/20">
            <span class="text-[10px] font-bold text-purple-700 uppercase tracking-wider block">Stok Rendah (&le; 2)</span>
            <span class="text-xl font-extrabold text-purple-700 mt-1 block">{{ $stats['lowStockCount'] }}</span>
            <span class="text-[9px] text-purple-600 mt-0.5 block">Perlu restock</span>
        </div>

        <!-- Card 6 -->
        <div class="rounded-xl bg-white p-3.5 border border-rose-200 shadow-sm bg-rose-50/20">
            <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Model Kosong</span>
            <span class="text-xl font-extrabold text-rose-700 mt-1 block">{{ $stats['outOfStockVehicles'] }}</span>
            <span class="text-[9px] text-rose-500 mt-0.5 block">Stok habis total</span>
        </div>
    </div>

    <!-- Action & Filter Bar -->
    <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-3">
            <h3 class="text-sm font-bold text-slate-900">Persebaran Stok per Cabang Dealer</h3>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.inventory.movements') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Riwayat Audit Stok
                </a>

                @can('inventory.manage')
                    <a href="{{ route('admin.inventory.adjust.form') }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Penyesuaian Stok
                    </a>
                @endcan
            </div>
        </div>

        <form method="GET" action="{{ route('admin.inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Search -->
            <div class="sm:col-span-4 relative">
                <input type="text" name="q" value="{{ $filters['q'] }}"
                       placeholder="Cari model mobil atau varian..."
                       class="w-full rounded-lg border border-slate-300 pl-9 pr-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                <svg class="h-4 w-4 text-slate-400 absolute left-3 top-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>

            <!-- Branch Filter -->
            <div class="sm:col-span-3">
                <select name="branch_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                    <option value="">Semua Cabang Dealer</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $filters['branch_id'] == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} ({{ $branch->city }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Status Filter -->
            <div class="sm:col-span-3">
                <select name="stock_status"
                        class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                    <option value="">Semua Status Stok</option>
                    <option value="in_stock" {{ $filters['stock_status'] === 'in_stock' ? 'selected' : '' }}>Tersedia (> 2)</option>
                    <option value="low_stock" {{ $filters['stock_status'] === 'low_stock' ? 'selected' : '' }}>Stok Rendah (&le; 2)</option>
                    <option value="out_of_stock" {{ $filters['stock_status'] === 'out_of_stock' ? 'selected' : '' }}>Habis (0)</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="sm:col-span-2 flex items-center gap-1.5">
                <button type="submit"
                        class="flex-1 rounded-lg bg-slate-900 py-1.5 px-3 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Filter
                </button>
                @if ($filters['q'] || $filters['branch_id'] || $filters['stock_status'])
                    <a href="{{ route('admin.inventory.index') }}"
                       class="rounded-lg bg-slate-100 py-1.5 px-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Inventory Table Card -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1050px]">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[220px]">Kendaraan &amp; Varian</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[180px]">Lokasi Cabang</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap min-w-[110px]">Stok Fisik</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap min-w-[120px]">Dialokasikan</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap min-w-[130px]">Stok Siap Jual</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap min-w-[140px]">Status</th>
                        <th class="py-3 px-4 text-right whitespace-nowrap min-w-[110px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($inventories as $inv)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $inv->vehicle->primary_image_url }}" alt="{{ $inv->vehicle->name }}" class="h-9 w-14 object-cover rounded bg-slate-100 border border-slate-200 flex-shrink-0">
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $inv->vehicle->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $inv->vehicle->variant ?? $inv->vehicle->category }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ $inv->branch->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $inv->branch->city }} &bull; {{ $inv->branch->code }}</div>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-900 whitespace-nowrap">
                                {{ $inv->stock_quantity }}
                            </td>
                            <td class="py-3 px-4 text-center text-amber-700 font-semibold whitespace-nowrap">
                                {{ $inv->reserved_quantity }}
                            </td>
                            <td class="py-3 px-4 text-center font-extrabold text-sm whitespace-nowrap {{ $inv->available_stock > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $inv->available_stock }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $inv->stock_status_badge_class }}">
                                    {{ $inv->stock_status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @can('inventory.manage')
                                    <a href="{{ route('admin.inventory.adjust.form', ['vehicle_id' => $inv->vehicle_id, 'branch_id' => $inv->branch_id]) }}"
                                       class="inline-flex items-center gap-1 text-[11px] font-bold text-red-600 hover:text-red-800 transition">
                                        Sesuaikan
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-xs text-slate-400">
                                Tidak ada data inventory yang memenuhi kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inventories->hasPages())
            <div class="p-3 border-t border-slate-100">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
