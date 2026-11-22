@extends('layouts.public')

@section('title', 'Katalog Kendaraan Toyota — Toyota AutoConnect')
@section('meta_description', 'Lihat katalog lengkap lini kendaraan Toyota baru: MPV, SUV, Hatchback, Sedan, hingga Commercial. Filter harga, transmisi, dan status ketersediaan unit.')

@section('content')
<div class="bg-slate-50 min-h-screen py-6 sm:py-10" x-data="{ mobileFilterOpen: false }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500 mb-5" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-red-600 transition">Beranda</a>
            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            <span class="text-slate-900 font-semibold" aria-current="page">Katalog Kendaraan</span>
        </nav>

        <!-- Catalog Header -->
        <div class="border-b border-slate-200 pb-6 mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Daftar Kendaraan</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-950 mt-0.5 tracking-tight">Katalog Kendaraan Toyota</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                    Temukan model Toyota impian Anda. Dapatkan informasi detail mengenai spesifikasi teknis, transmisi, estimasi harga OTR, dan status ketersediaan showroom.
                </p>
            </div>

            <!-- Mobile Filter Trigger & Counter -->
            <div class="flex items-center justify-between md:justify-end gap-3">
                <button type="button" 
                        @click="mobileFilterOpen = true" 
                        class="md:hidden inline-flex items-center gap-1.5 rounded-lg bg-white border border-slate-200 px-3.5 py-2 text-xs font-bold text-slate-800 shadow-xs hover:bg-slate-50">
                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                    </svg>
                    Filter &amp; Urutkan
                    @if (request()->hasAny(['q', 'category', 'min_price', 'max_price', 'status', 'sort', 'branch_id']))
                        <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                    @endif
                </button>

                <div class="text-xs font-semibold text-slate-500">
                    Menampilkan <span class="text-slate-900 font-bold">{{ $vehicles->total() }}</span> kendaraan
                </div>
            </div>
        </div>

        <!-- 2-Column Catalog Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <!-- Desktop Filter Sidebar -->
            <aside class="hidden lg:block lg:col-span-1">
                <div class="sticky top-24 rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Filter Pencarian</h2>
                        @if (request()->hasAny(['q', 'category', 'min_price', 'max_price', 'status', 'sort', 'branch_id']))
                            <a href="{{ route('vehicles.index') }}" class="text-[11px] font-semibold text-red-600 hover:text-red-700">
                                Reset
                            </a>
                        @endif
                    </div>

                    <form action="{{ route('vehicles.index') }}" method="GET" class="space-y-4">
                        <!-- Search Keyword -->
                        <div>
                            <label for="catalog-q" class="block text-[11px] font-bold text-slate-700 mb-1">Kata Kunci Model</label>
                            <input type="text" 
                                   id="catalog-q" 
                                   name="q" 
                                   value="{{ $filters['q'] }}" 
                                   placeholder="Cari model / varian..." 
                                   class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                        </div>

                        <!-- Sorting Selection -->
                        <div>
                            <label for="catalog-sort" class="block text-[11px] font-bold text-slate-700 mb-1">Urutan Tampilan</label>
                            <select id="catalog-sort" 
                                    name="sort" 
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-800 focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                                <option value="latest" {{ $filters['sort'] === 'latest' ? 'selected' : '' }}>Terbaru Diluncurkan</option>
                                <option value="price_asc" {{ $filters['sort'] === 'price_asc' ? 'selected' : '' }}>Harga: Terendah ke Tertinggi</option>
                                <option value="price_desc" {{ $filters['sort'] === 'price_desc' ? 'selected' : '' }}>Harga: Tertinggi ke Terendah</option>
                                <option value="name_asc" {{ $filters['sort'] === 'name_asc' ? 'selected' : '' }}>Nama Model (A - Z)</option>
                            </select>
                        </div>

                        <!-- Category Radio / Selection -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Kategori Kendaraan</label>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                                    <input type="radio" 
                                           name="category" 
                                           value="" 
                                           {{ empty($filters['category']) ? 'checked' : '' }} 
                                           class="text-red-600 focus:ring-red-500">
                                    Semua Kategori
                                </label>
                                @foreach ($availableCategories as $categoryName)
                                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                                        <input type="radio" 
                                               name="category" 
                                               value="{{ $categoryName }}" 
                                               {{ $filters['category'] === $categoryName ? 'checked' : '' }} 
                                               class="text-red-600 focus:ring-red-500">
                                        <span>{{ $categoryName }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Branch Filter -->
                        <div>
                            <label for="catalog-branch" class="block text-[11px] font-bold text-slate-700 mb-1">Pilihan Cabang Dealer</label>
                            <select id="catalog-branch" 
                                    name="branch_id" 
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-800 focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                                <option value="">Semua Lokasi Cabang</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $filters['branch_id'] == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }} ({{ $branch->city }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="catalog-status" class="block text-[11px] font-bold text-slate-700 mb-1">Status Unit</label>
                            <select id="catalog-status" 
                                    name="status" 
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-800 focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                                <option value="all" {{ empty($filters['status']) || $filters['status'] === 'all' ? 'selected' : '' }}>Semua Status Unit</option>
                                <option value="available" {{ $filters['status'] === 'available' ? 'selected' : '' }}>Tersedia (Ready Unit)</option>
                                <option value="incoming" {{ $filters['status'] === 'incoming' ? 'selected' : '' }}>Segera Hadir (Incoming)</option>
                                <option value="reserved" {{ $filters['status'] === 'reserved' ? 'selected' : '' }}>Terpesan (Reserved)</option>
                                <option value="sold" {{ $filters['status'] === 'sold' ? 'selected' : '' }}>Terjual (Sold)</option>
                            </select>
                        </div>

                        <!-- Price Range Filters -->
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-700">Rentang Harga (IDR)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <input type="number" 
                                           name="min_price" 
                                           value="{{ $filters['min_price'] }}" 
                                           min="0" 
                                           placeholder="Min IDR" 
                                           class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-2 py-1 text-xs text-slate-800 placeholder-slate-400 focus:border-red-500 focus:outline-none">
                                </div>
                                <div>
                                    <input type="number" 
                                           name="max_price" 
                                           value="{{ $filters['max_price'] }}" 
                                           min="0" 
                                           placeholder="Maks IDR" 
                                           class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-2 py-1 text-xs text-slate-800 placeholder-slate-400 focus:border-red-500 focus:outline-none">
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-400">Contoh: 300000000 untuk Rp 300 Juta</p>
                        </div>

                        <!-- Apply & Reset Buttons -->
                        <div class="pt-1 flex items-center gap-2">
                            <button type="submit" 
                                    class="flex-1 rounded-lg bg-slate-900 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-red-600 focus:outline-none">
                                Terapkan
                            </button>
                            @if (request()->hasAny(['q', 'category', 'min_price', 'max_price', 'status', 'sort', 'branch_id']))
                                <a href="{{ route('vehicles.index') }}" 
                                   class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition text-center">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </aside>

            <!-- Main Vehicles Grid Area -->
            <div class="lg:col-span-3">

                <!-- Active Filters Pills -->
                @if (request()->hasAny(['q', 'category', 'min_price', 'max_price', 'status', 'sort', 'branch_id']))
                    <div class="mb-5 flex flex-wrap items-center gap-1.5 rounded-lg bg-white p-2.5 border border-slate-200 text-xs">
                        <span class="font-bold text-slate-600 text-[11px]">Filter Aktif:</span>
                        
                        @if (!empty($filters['q']))
                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-slate-700 text-[11px] font-medium">
                                Cari: "{{ $filters['q'] }}"
                            </span>
                        @endif

                        @if (!empty($filters['category']))
                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-slate-700 text-[11px] font-medium">
                                Kategori: {{ $filters['category'] }}
                            </span>
                        @endif

                        @if (!empty($filters['status']) && $filters['status'] !== 'all')
                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-slate-700 text-[11px] font-medium">
                                Status: {{ ucfirst($filters['status']) }}
                            </span>
                        @endif

                        @if (!empty($filters['branch_id']))
                            @php
                                $activeBranch = $branches->firstWhere('id', $filters['branch_id']);
                            @endphp
                            @if ($activeBranch)
                                <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-slate-700 text-[11px] font-medium">
                                    Cabang: {{ $activeBranch->name }}
                                </span>
                            @endif
                        @endif

                        @if (!empty($filters['min_price']))
                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-slate-700 text-[11px] font-medium">
                                Min: Rp {{ number_format($filters['min_price'], 0, ',', '.') }}
                            </span>
                        @endif

                        @if (!empty($filters['max_price']))
                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-slate-700 text-[11px] font-medium">
                                Maks: Rp {{ number_format($filters['max_price'], 0, ',', '.') }}
                            </span>
                        @endif

                        <a href="{{ route('vehicles.index') }}" class="ml-auto font-semibold text-red-600 hover:text-red-700 text-[11px]">
                            Hapus Semua
                        </a>
                    </div>
                @endif

                <!-- Vehicles Grid / Empty State -->
                @if ($vehicles->isEmpty())
                    <!-- Friendly Empty State -->
                    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-xs">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-base font-bold text-slate-900">Kendaraan Tidak Ditemukan</h3>
                        <p class="mx-auto mt-1 max-w-sm text-xs text-slate-500 leading-relaxed">
                            Tidak ada unit yang cocok dengan kriteria pencarian atau filter yang Anda terapkan. Coba kurangi filter atau gunakan kata kunci lain.
                        </p>
                        <div class="mt-5 flex flex-wrap justify-center gap-2.5">
                            <a href="{{ route('vehicles.index') }}" 
                               class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                                Reset Semua Filter
                            </a>
                            <a href="{{ route('home') }}" 
                               class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Vehicle Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach ($vehicles as $vehicle)
                            <div class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                                <!-- Vehicle Photo -->
                                <div class="relative aspect-16/10 w-full overflow-hidden bg-slate-900">
                                    <img src="{{ $vehicle->primary_image_url }}" 
                                         alt="{{ $vehicle->name }} {{ $vehicle->variant }}" 
                                         loading="lazy"
                                         class="h-full w-full object-cover object-center transition duration-300 hover:scale-105"
                                         width="600"
                                         height="375">

                                    <!-- Category Pill -->
                                    <div class="absolute top-2.5 left-2.5">
                                        <span class="rounded-md bg-slate-950/80 px-2 py-0.5 text-[10px] font-bold text-white backdrop-blur-md">
                                            {{ $vehicle->category }}
                                        </span>
                                    </div>

                                    <!-- Status Tag -->
                                    <div class="absolute top-2.5 right-2.5">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold backdrop-blur-md {{ $vehicle->status_badge['class'] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $vehicle->status_badge['dot'] }}"></span>
                                            {{ $vehicle->status_badge['label'] }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="flex flex-1 flex-col justify-between p-4 sm:p-5">
                                    <div>
                                        <h2 class="text-sm sm:text-base font-bold text-slate-950 hover:text-red-600 transition leading-snug">
                                            <a href="{{ route('vehicles.show', $vehicle->slug) }}">
                                                {{ $vehicle->name }}
                                            </a>
                                        </h2>
                                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">
                                            {{ $vehicle->variant ?? 'Model Standar' }}
                                        </p>

                                        <!-- Specifications Highlights -->
                                        <div class="mt-3 grid grid-cols-3 gap-2 border-y border-slate-100 py-2 text-center text-[10px] text-slate-600">
                                            <div>
                                                <span class="block text-slate-400 font-medium">Transmisi</span>
                                                <span class="font-semibold text-slate-800 truncate block">{{ $vehicle->transmission ?? 'N/A' }}</span>
                                            </div>
                                            <div>
                                                <span class="block text-slate-400 font-medium">Bahan Bakar</span>
                                                <span class="font-semibold text-slate-800 truncate block">{{ $vehicle->fuel_type ?? 'N/A' }}</span>
                                            </div>
                                            <div>
                                                <span class="block text-slate-400 font-medium">Kapasitas</span>
                                                <span class="font-semibold text-slate-800 block">{{ $vehicle->passenger_capacity ? $vehicle->passenger_capacity . ' Kursi' : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Price & Details Link -->
                                    <div class="mt-4 flex items-center justify-between pt-1">
                                        <div>
                                            <span class="text-[9px] font-medium text-slate-400 uppercase tracking-wider">Estimasi Harga</span>
                                            <div class="text-xs sm:text-sm font-extrabold text-slate-950">
                                                {{ $vehicle->formatted_price }}
                                            </div>
                                        </div>

                                        <a href="{{ route('vehicles.show', $vehicle->slug) }}" 
                                           class="inline-flex items-center gap-1 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white shadow-xs transition hover:bg-red-600">
                                            Detail
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8 border-t border-slate-200 pt-5 text-xs">
                        {{ $vehicles->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Mobile Drawer for Filters -->
    <div x-show="mobileFilterOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex justify-end"
         role="dialog" 
         aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
             @click="mobileFilterOpen = false"></div>

        <!-- Sliding Panel -->
        <div class="relative w-full max-w-xs bg-white p-5 shadow-2xl flex flex-col justify-between overflow-y-auto">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Filter Kendaraan</h3>
                    <button type="button" @click="mobileFilterOpen = false" class="text-slate-400 hover:text-slate-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('vehicles.index') }}" method="GET" class="space-y-4">
                    <div>
                        <label for="mobile-q" class="block text-[11px] font-bold text-slate-700 mb-1">Model</label>
                        <input type="text" id="mobile-q" name="q" value="{{ $filters['q'] }}" placeholder="Model / varian..." class="w-full rounded-lg border border-slate-200 p-2 text-xs">
                    </div>

                    <div>
                        <label for="mobile-sort" class="block text-[11px] font-bold text-slate-700 mb-1">Urutan</label>
                        <select id="mobile-sort" name="sort" class="w-full rounded-lg border border-slate-200 p-2 text-xs">
                            <option value="latest" {{ $filters['sort'] === 'latest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="price_asc" {{ $filters['sort'] === 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                            <option value="price_desc" {{ $filters['sort'] === 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                            <option value="name_asc" {{ $filters['sort'] === 'name_asc' ? 'selected' : '' }}>Nama (A - Z)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Kategori</label>
                        <div class="space-y-1">
                            <label class="flex items-center gap-2 text-xs text-slate-700">
                                <input type="radio" name="category" value="" {{ empty($filters['category']) ? 'checked' : '' }}>
                                Semua Kategori
                            </label>
                            @foreach ($availableCategories as $categoryName)
                                <label class="flex items-center gap-2 text-xs text-slate-700">
                                    <input type="radio" name="category" value="{{ $categoryName }}" {{ $filters['category'] === $categoryName ? 'checked' : '' }}>
                                    {{ $categoryName }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="mobile-branch" class="block text-[11px] font-bold text-slate-700 mb-1">Cabang Dealer</label>
                        <select id="mobile-branch" name="branch_id" class="w-full rounded-lg border border-slate-200 p-2 text-xs">
                            <option value="">Semua Lokasi Cabang</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $filters['branch_id'] == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }} ({{ $branch->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="mobile-status" class="block text-[11px] font-bold text-slate-700 mb-1">Status</label>
                        <select id="mobile-status" name="status" class="w-full rounded-lg border border-slate-200 p-2 text-xs">
                            <option value="all">Semua Status</option>
                            <option value="available" {{ $filters['status'] === 'available' ? 'selected' : '' }}>Tersedia</option>
                            <option value="incoming" {{ $filters['status'] === 'incoming' ? 'selected' : '' }}>Segera Hadir</option>
                            <option value="reserved" {{ $filters['status'] === 'reserved' ? 'selected' : '' }}>Terpesan</option>
                            <option value="sold" {{ $filters['status'] === 'sold' ? 'selected' : '' }}>Terjual</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-slate-700">Rentang Harga</label>
                        <input type="number" name="min_price" value="{{ $filters['min_price'] }}" placeholder="Min IDR" class="w-full rounded-lg border border-slate-200 p-1.5 text-xs">
                        <input type="number" name="max_price" value="{{ $filters['max_price'] }}" placeholder="Maks IDR" class="w-full rounded-lg border border-slate-200 p-1.5 text-xs">
                    </div>

                    <div class="pt-3 flex flex-col gap-2">
                        <button type="submit" class="w-full rounded-lg bg-red-600 py-2 text-xs font-bold text-white shadow-xs">
                            Terapkan Filter
                        </button>
                        <a href="{{ route('vehicles.index') }}" class="w-full text-center rounded-lg border border-slate-200 py-1.5 text-xs font-semibold text-slate-700">
                            Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
