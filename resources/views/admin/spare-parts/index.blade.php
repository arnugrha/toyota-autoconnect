@extends('layouts.dashboard')

@section('title', 'Kelola Sparepart — Toyota AutoConnect')
@section('header_title', 'Manajemen Master & Stok Sparepart')
@section('header_subtitle', 'Kelola katalog Toyota Genuine Parts, standar harga jual, batas minimum, dan pantauan stok gudang bengkel')

@section('content')
<div class="space-y-6"
     x-data="{
         showCreateModal: {{ $errors->any() && old('_form_mode') === 'create' ? 'true' : 'false' }},
         showEditModal: false,
         editUrl: '',
         editForm: {
             part_number: '',
             name: '',
             category: 'oil_fluids',
             unit: 'Pcs',
             purchase_price: 0,
             selling_price: 0,
             min_stock: 5,
             description: '',
             is_active: true
         },
         openEdit(part, url) {
             this.editUrl = url;
             this.editForm = {
                 part_number: part.part_number,
                 name: part.name,
                 category: part.category,
                 unit: part.unit,
                 purchase_price: parseFloat(part.purchase_price),
                 selling_price: parseFloat(part.selling_price),
                 min_stock: parseInt(part.min_stock),
                 description: part.description || '',
                 is_active: Boolean(part.is_active)
             };
             this.showEditModal = true;
         }
     }">

    <!-- Validation Error Alert -->
    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800 shadow-2xs">
            <div class="font-bold mb-1">Terdapat kesalahan input:</div>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Jenis SKU</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($stats['total_sku']) }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Katalog suku cadang terdaftar</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Stok Gudang</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($stats['total_stock']) }}</div>
            <p class="text-[11px] text-slate-500 mt-0.5">Total fisik unit/pcs siap pakai</p>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Stok Menipis</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-amber-700">{{ number_format($stats['low_stock']) }}</div>
            <p class="text-[11px] text-amber-700/80 mt-0.5">Perlu segera restock / pengadaan</p>
        </div>

        <div class="rounded-2xl border border-rose-200 bg-rose-50/40 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Stok Habis</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-rose-700">{{ number_format($stats['out_of_stock']) }}</div>
            <p class="text-[11px] text-rose-700/80 mt-0.5">Tidak tersedia untuk Work Order</p>
        </div>
    </div>

    <!-- Main Catalog Table Card -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden">
        <!-- Header & Actions -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">Daftar Master Sparepart (Toyota Genuine Parts)</h2>
                <p class="text-[11px] text-slate-500 mt-0.5">Data sparepart ini menjadi acuan otomatis dropdown pada Work Order Servis dan Pengadaan.</p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                @can('spare-parts.manage')
                    <a href="{{ route('admin.spare-part-procurements.create') }}"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition whitespace-nowrap">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        Buat Pengadaan (Restock)
                    </a>

                    <button type="button"
                            @click="showCreateModal = true"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition whitespace-nowrap">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Sparepart Baru
                    </button>
                @endcan
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="p-4 bg-slate-50/70 border-b border-slate-100">
            <form method="GET" action="{{ route('admin.spare-parts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5">
                <input type="text"
                       name="q"
                       value="{{ $filters['q'] }}"
                       placeholder="Cari SKU / nama sparepart..."
                       class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">

                <select name="category"
                        class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $catKey => $catLabel)
                        <option value="{{ $catKey }}" {{ $filters['category'] === $catKey ? 'selected' : '' }}>
                            {{ $catLabel }}
                        </option>
                    @endforeach
                </select>

                <select name="stock_status"
                        class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Kondisi Stok</option>
                    <option value="in_stock" {{ $filters['stock_status'] === 'in_stock' ? 'selected' : '' }}>Tersedia Aman</option>
                    <option value="low_stock" {{ $filters['stock_status'] === 'low_stock' ? 'selected' : '' }}>Stok Menipis</option>
                    <option value="out_of_stock" {{ $filters['stock_status'] === 'out_of_stock' ? 'selected' : '' }}>Stok Habis</option>
                </select>

                <select name="status"
                        class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Status Aktif</option>
                    <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $filters['status'] === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white hover:bg-slate-800 transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['q', 'category', 'stock_status', 'status']))
                        <a href="{{ route('admin.spare-parts.index') }}"
                           class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Responsive Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[960px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3.5 whitespace-nowrap min-w-[260px]">Kode Part (SKU) &amp; Nama</th>
                        <th class="px-4 py-3.5 whitespace-nowrap min-w-[140px]">Kategori</th>
                        <th class="px-4 py-3.5 text-right whitespace-nowrap min-w-[125px]">Harga Beli (HPP)</th>
                        <th class="px-4 py-3.5 text-right whitespace-nowrap min-w-[125px]">Harga Jual</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap min-w-[140px]">Stok Gudang</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap min-w-[90px]">Status</th>
                        <th class="px-4 py-3.5 text-right whitespace-nowrap min-w-[180px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($spareParts as $part)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900">{{ $part->name }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-mono text-[10px] font-bold text-red-700 bg-red-50 px-1.5 py-0.5 rounded border border-red-100">
                                        {{ $part->part_number }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">Satuan: {{ $part->unit }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700 border border-slate-200">
                                    {{ $part->category_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-slate-600 whitespace-nowrap">
                                {{ $part->formatted_purchase_price }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $part->formatted_selling_price }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="font-extrabold text-sm text-slate-900">
                                    {{ number_format($part->stock_quantity) }} <span class="text-[10px] font-semibold text-slate-500">{{ $part->unit }}</span>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold border mt-1 {{ $part->stock_badge_class }}">
                                    {{ $part->stock_status_label }} (Min: {{ $part->min_stock }})
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($part->is_active)
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-500 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.spare-parts.movements', $part) }}"
                                       title="Riwayat Mutasi Stok"
                                       class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition">
                                        Log Stok
                                    </a>

                                    @can('spare-parts.manage')
                                        <button type="button"
                                                @click='openEdit(@json($part), "{{ route('admin.spare-parts.update', $part) }}")'
                                                class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-200 transition">
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('admin.spare-parts.toggle-status', $part) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-[11px] font-semibold {{ $part->is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }} transition">
                                                {{ $part->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        @if($part->service_order_items_count === 0 && $part->procurement_items_count === 0)
                                            <form method="POST"
                                                  action="{{ route('admin.spare-parts.destroy', $part) }}"
                                                  onsubmit="return confirm('Hapus sparepart {{ $part->name }}?')"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        title="Hapus Sparepart"
                                                        class="rounded-lg p-1.5 text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                                Belum ada data sparepart yang sesuai filter. Klik <strong>Tambah Sparepart Baru</strong> untuk menambahkan katalog.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($spareParts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $spareParts->links() }}
            </div>
        @endif
    </div>

    @can('spare-parts.manage')
        <!-- Modal Tambah Sparepart Baru -->
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
            <div @click.away="showCreateModal = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-slate-200 max-h-[90vh] overflow-y-auto space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Tambah Master Sparepart Baru</h3>
                        <p class="text-[11px] text-slate-500">Masukkan spesifikasi &amp; standar harga suku cadang Toyota</p>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.spare-parts.store') }}" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="_form_mode" value="create">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Part / SKU <span class="text-red-600">*</span></label>
                            <input type="text" name="part_number" value="{{ old('part_number') }}" required
                                   placeholder="Contoh: 08880-83389"
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs uppercase font-mono text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori <span class="text-red-600">*</span></label>
                            <select name="category" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                @foreach($categories as $catKey => $catLabel)
                                    <option value="{{ $catKey }}" {{ old('category') === $catKey ? 'selected' : '' }}>{{ $catLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sparepart <span class="text-red-600">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               placeholder="Contoh: Toyota Motor Oil (TMO) 5W-30 4L"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Satuan <span class="text-red-600">*</span></label>
                            <select name="unit" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                @foreach($units as $unit)
                                    <option value="{{ $unit }}" {{ old('unit', 'Pcs') === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Stok Awal <span class="text-red-600">*</span></label>
                            <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" required
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Min. Stok Alert <span class="text-red-600">*</span></label>
                            <input type="number" name="min_stock" value="{{ old('min_stock', 5) }}" min="0" required
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga Beli / HPP (Rp) <span class="text-red-600">*</span></label>
                            <input type="number" name="purchase_price" value="{{ old('purchase_price', 0) }}" min="0" step="1000" required
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual Resmi (Rp) <span class="text-red-600">*</span></label>
                            <input type="number" name="selling_price" value="{{ old('selling_price', 0) }}" min="0" step="1000" required
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi / Kompatibilitas Model</label>
                        <textarea name="description" rows="2" placeholder="Contoh: Cocok untuk Avanza, Veloz, Rush, dan Yaris."
                                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('description') }}</textarea>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                        <span class="text-xs font-semibold text-slate-700">Aktif (tersedia di dropdown servis &amp; pengadaan)</span>
                    </label>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showCreateModal = false" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                            Simpan Sparepart
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Sparepart -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
            <div @click.away="showEditModal = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-slate-200 max-h-[90vh] overflow-y-auto space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Edit Master Sparepart</h3>
                        <p class="text-[11px] text-slate-500">Untuk penambahan stok fisik, gunakan fitur Pengadaan Sparepart.</p>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="editUrl" class="space-y-3.5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Part / SKU <span class="text-red-600">*</span></label>
                            <input type="text" name="part_number" x-model="editForm.part_number" required
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs uppercase font-mono text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori <span class="text-red-600">*</span></label>
                            <select name="category" x-model="editForm.category" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                @foreach($categories as $catKey => $catLabel)
                                    <option value="{{ $catKey }}">{{ $catLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sparepart <span class="text-red-600">*</span></label>
                        <input type="text" name="name" x-model="editForm.name" required
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Satuan <span class="text-red-600">*</span></label>
                            <select name="unit" x-model="editForm.unit" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                @foreach($units as $unit)
                                    <option value="{{ $unit }}">{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Batas Minimum Stok <span class="text-red-600">*</span></label>
                            <input type="number" name="min_stock" x-model="editForm.min_stock" min="0" required
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga Beli / HPP (Rp) <span class="text-red-600">*</span></label>
                            <input type="number" name="purchase_price" x-model="editForm.purchase_price" min="0" step="1000" required
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual Resmi (Rp) <span class="text-red-600">*</span></label>
                            <input type="number" name="selling_price" x-model="editForm.selling_price" min="0" step="1000" required
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi</label>
                        <textarea name="description" x-model="editForm.description" rows="2"
                                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" x-model="editForm.is_active" class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                        <span class="text-xs font-semibold text-slate-700">Status Aktif</span>
                    </label>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showEditModal = false" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

</div>
@endsection
