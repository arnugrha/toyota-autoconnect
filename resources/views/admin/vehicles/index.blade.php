@extends('layouts.dashboard')

@section('title', 'Manajemen Kendaraan — Toyota AutoConnect')
@section('header_title', 'Master Data Kendaraan')
@section('header_subtitle', 'Kelola spesifikasi unit, penetapan harga, foto kendaraan, dan status publikasi')

@section('content')
<div class="space-y-6">

    <!-- Flash Status Messages -->
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-3.5 text-xs text-emerald-800">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('info'))
        <div class="flex items-center gap-2.5 rounded-xl bg-blue-50 border border-blue-200 p-3.5 text-xs text-blue-800">
            <svg class="h-4 w-4 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- Top Action Bar & Filter -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.vehicles.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Search Keyword -->
            <div class="sm:col-span-5 relative">
                <input type="text" name="q" value="{{ $filters['q'] }}"
                       placeholder="Cari nama, varian, atau slug unit..."
                       class="w-full rounded-lg border border-slate-300 pl-9 pr-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                <svg class="h-4 w-4 text-slate-400 absolute left-3 top-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>

            <!-- Category Filter -->
            <div class="sm:col-span-3">
                <select name="category"
                        class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ $filters['category'] === $cat->slug ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Publication Status Filter -->
            <div class="sm:col-span-2">
                <select name="publication_status"
                        class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                    <option value="">Semua Status</option>
                    <option value="published" {{ $filters['publication_status'] === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ $filters['publication_status'] === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="archived" {{ $filters['publication_status'] === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-1.5">
                <button type="submit"
                        class="flex-1 rounded-lg bg-slate-900 py-1.5 px-3 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Filter
                </button>
                @if ($filters['q'] || $filters['category'] || $filters['publication_status'])
                    <a href="{{ route('admin.vehicles.index') }}"
                       class="rounded-lg bg-slate-100 py-1.5 px-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @can('vehicles.create')
            <a href="{{ route('admin.vehicles.create') }}"
               class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition flex-shrink-0">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Kendaraan
            </a>
        @endcan
    </div>

    <!-- Vehicles Table Card -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[950px]">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[220px]">Model Kendaraan</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[170px]">Kategori &amp; Varian</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[140px]">Harga OTR</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap min-w-[120px]">Total Stok</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap min-w-[140px]">Status Publikasi</th>
                        <th class="py-3 px-4 text-right whitespace-nowrap min-w-[120px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($vehicles as $vehicle)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $vehicle->primary_image_url }}" alt="{{ $vehicle->name }}" class="h-10 w-16 object-cover rounded-md bg-slate-100 border border-slate-200 flex-shrink-0">
                                    <div>
                                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                            {{ $vehicle->name }}
                                            @if ($vehicle->is_featured)
                                                <span class="rounded bg-amber-100 text-amber-800 text-[9px] font-bold px-1 py-0.2">Featured</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $vehicle->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-block rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700">
                                    {{ $vehicle->categoryRelation?->name ?? $vehicle->category }}
                                </span>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $vehicle->variant ?? 'Standard' }}</div>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $vehicle->formatted_price }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex flex-col items-center">
                                    <span class="font-bold text-xs {{ $vehicle->total_available_stock > 0 ? 'text-slate-900' : 'text-rose-600' }}">
                                        {{ $vehicle->total_available_stock }} Unit
                                    </span>
                                    <span class="text-[9px] text-slate-400">Total Fisik: {{ $vehicle->total_stock }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if ($vehicle->publication_status === 'published')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                        Published
                                    </span>
                                @elseif ($vehicle->publication_status === 'draft')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Draft
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-600 border border-slate-200">
                                        Archived
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('vehicles.show', $vehicle->slug) }}" target="_blank"
                                       class="rounded p-1 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition" title="Lihat di Katalog">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </a>

                                    @can('vehicles.update')
                                        <a href="{{ route('admin.vehicles.edit', $vehicle) }}"
                                           class="rounded p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition" title="Edit Spesifikasi">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('vehicles.delete')
                                        <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus atau mengarsipkan kendaraan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition" title="Hapus / Arsipkan">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-xs text-slate-400">
                                Tidak ada data kendaraan yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vehicles->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
