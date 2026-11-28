@extends('layouts.public')

@section('title', 'Toyota AutoConnect — Dealer Resmi & Katalog Kendaraan Toyota')
@section('meta_description', 'Temukan kendaraan Toyota impian Anda. Bandingkan spesifikasi, harga OTR, dan ketersediaan unit MPV, SUV, Hatchback, Sedan, hingga Commercial di dealer kami.')

@section('content')
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-slate-900 bg-gradient-to-b from-slate-900 via-slate-950 to-slate-900 text-white" style="background-color: #0f172a;">
        <!-- Background Lighting Texture -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_var(--tw-gradient-stops))] from-red-600/15 via-transparent to-transparent opacity-70"></div>
        <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-red-600/10 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-12 lg:gap-8">
                <!-- Hero Text -->
                <div class="lg:col-span-7 space-y-4 sm:space-y-5">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-700 bg-slate-800/80 px-3 py-1 text-[11px] font-semibold text-slate-300 backdrop-blur-md">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse"></span>
                        KATALOG DIGITAL TOYOTA AUTOCONNECT
                    </div>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                        Temukan Kendaraan yang Tepat untuk Perjalanan Anda
                    </h1>

                    <p class="max-w-xl text-xs sm:text-sm leading-relaxed text-slate-300">
                        Jelajahi lini kendaraan Toyota terlengkap, pelajari spesifikasi mendalam, dan temukan informasi yang Anda butuhkan sebelum mengunjungi showroom dealer kami.
                    </p>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <a href="{{ route('vehicles.index') }}" 
                           class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-md shadow-red-600/30 transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                            Jelajahi Katalog Kendaraan
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                        <a href="#layanan" 
                           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-700 bg-slate-800/60 px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-200 backdrop-blur-sm transition hover:border-slate-500 hover:bg-slate-800 hover:text-white">
                            Keunggulan Layanan
                        </a>
                    </div>

                    <!-- Quick Facts Bar -->
                    <div class="grid grid-cols-3 gap-3 pt-5 border-t border-slate-800/80 text-left">
                        <div>
                            <div class="text-lg sm:text-xl font-bold text-white">5 Kategori</div>
                            <div class="text-[11px] text-slate-400">MPV, SUV, Sedan, dll</div>
                        </div>
                        <div>
                            <div class="text-lg sm:text-xl font-bold text-white">100% Resmi</div>
                            <div class="text-[11px] text-slate-400">Garansi Pabrikan Toyota</div>
                        </div>
                        <div>
                            <div class="text-lg sm:text-xl font-bold text-white">Transparan</div>
                            <div class="text-[11px] text-slate-400">Spesifikasi Detail</div>
                        </div>
                    </div>
                </div>

                <!-- Hero Spotlight Vehicle Image -->
                <div class="lg:col-span-5 relative">
                    <div class="relative mx-auto overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-b from-slate-800/60 to-slate-900/90 p-3.5 shadow-xl backdrop-blur-sm">
                        <div class="relative aspect-video w-full overflow-hidden rounded-xl bg-slate-950">
                            <img src="{{ asset('images/vehicles/all-new-kijang-innova-zenix-exterior.jpg') }}" 
                                 alt="All New Kijang Innova Zenix Showroom Spotlight" 
                                 class="h-full w-full object-cover object-center transition duration-500 hover:scale-105"
                                 width="800"
                                 height="500">
                            <div class="absolute bottom-2.5 left-2.5 rounded-lg bg-black/70 px-2.5 py-1 backdrop-blur-md">
                                <p class="text-[11px] font-semibold text-white">Innova Zenix 2.0 V HEV</p>
                                <p class="text-[10px] text-red-400">Generasi Baru Hybrid System</p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between px-1 text-[11px]">
                            <span class="text-slate-400">Unit Unggulan Bulan Ini</span>
                            <a href="{{ route('vehicles.show', 'all-new-kijang-innova-zenix') }}" class="font-semibold text-red-400 hover:text-red-300 flex items-center gap-1">
                                Lihat Spesifikasi
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Integrated Search Bar Section -->
    <section class="relative z-10 -mt-8 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-lg lg:p-6">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Pencarian Kendaraan Dealer</h2>
                    <p class="text-[11px] text-slate-500">Saring pilihan berdasarkan model, tipe bodi, atau anggaran Anda</p>
                </div>
                <a href="{{ route('vehicles.index') }}" class="text-[11px] font-semibold text-red-600 hover:text-red-700 flex items-center gap-1">
                    Buka Pencarian Lanjutan
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

            <form action="{{ route('vehicles.index') }}" method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Keyword input -->
                <div>
                    <label for="search-model" class="block text-[11px] font-semibold text-slate-700 mb-1">Model / Kata Kunci</label>
                    <input type="text" 
                           id="search-model" 
                           name="q" 
                           placeholder="Contoh: Innova, Fortuner, Yaris..." 
                           class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 transition focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                </div>

                <!-- Category selector -->
                <div>
                    <label for="search-category" class="block text-[11px] font-semibold text-slate-700 mb-1">Kategori Bodi</label>
                    <select id="search-category" 
                            name="category" 
                            class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 transition focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat['name'] }}">{{ $cat['name'] }} ({{ $cat['count'] }} unit)</option>
                        @endforeach
                    </select>
                </div>

                <!-- Max budget selector -->
                <div>
                    <label for="search-price" class="block text-[11px] font-semibold text-slate-700 mb-1">Estimasi Anggaran Maksimum</label>
                    <select id="search-price" 
                            name="max_price" 
                            class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 transition focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                        <option value="">Semua Rentang Harga</option>
                        <option value="300000000">&lt; Rp 300 Juta</option>
                        <option value="500000000">&lt; Rp 500 Juta</option>
                        <option value="800000000">&lt; Rp 800 Juta</option>
                        <option value="1500000000">&lt; Rp 1,5 Miliar</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-1.5 rounded-lg bg-slate-900 py-2 px-4 text-xs font-semibold text-white shadow-xs transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        Tampilkan Kendaraan
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-8 gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Lini Produk</span>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">Pilihan Kategori Kendaraan</h2>
                    <p class="text-xs text-slate-500 mt-1">Sesuaikan tipe kendaraan dengan kebutuhan perjalanan keluarga atau mobilitas bisnis Anda</p>
                </div>
                <a href="{{ route('vehicles.index') }}" class="text-xs font-semibold text-slate-700 hover:text-red-600 transition flex items-center gap-1">
                    Lihat Semua Model
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ($categories as $cat)
                    <a href="{{ route('vehicles.index', ['category' => $cat['name']]) }}" 
                       class="group relative flex flex-col justify-between rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition duration-200 hover:-translate-y-1 hover:border-red-200 hover:shadow-md">
                        <div>
                            <!-- Clean SVG Vehicle Icon for each category -->
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-700 transition group-hover:bg-red-600 group-hover:text-white">
                                @if ($cat['name'] === 'MPV')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.999-3.199a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                    </svg>
                                @elseif ($cat['name'] === 'SUV')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                    </svg>
                                @elseif ($cat['name'] === 'Hatchback')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                    </svg>
                                @elseif ($cat['name'] === 'Sedan')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25" />
                                    </svg>
                                @endif
                            </div>
                            <h3 class="mt-3 text-sm font-bold text-slate-900 group-hover:text-red-600 transition">
                                {{ $cat['name'] }}
                            </h3>
                            <p class="mt-1 text-[11px] leading-relaxed text-slate-500">
                                {{ $cat['description'] }}
                            </p>
                        </div>
                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-2.5 text-[11px]">
                            <span class="font-semibold text-slate-700">{{ $cat['count'] }} Unit Terdaftar</span>
                            <svg class="h-3.5 w-3.5 text-red-600 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Featured Vehicles Section -->
    <section class="bg-slate-100/70 py-12 sm:py-16 border-y border-slate-200/80">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-8 gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Pilihan Teratas</span>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">Kendaraan Unggulan Showroom</h2>
                    <p class="text-xs text-slate-500 mt-1">Unit terpopuler dan terdepan yang siap menemani setiap momen mobilitas Anda</p>
                </div>
                <a href="{{ route('vehicles.index') }}" 
                   class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-800 border border-slate-200 shadow-xs hover:border-slate-300 hover:bg-slate-50 transition">
                    Lihat Seluruh Katalog ({{ \App\Models\Vehicle::published()->count() }} Unit)
                </a>
            </div>

            @if ($featuredVehicles->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                    <p class="text-xs font-medium text-slate-600">Belum ada kendaraan yang ditandai sebagai unggulan.</p>
                    <a href="{{ route('vehicles.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:underline">
                        Buka Semua Kendaraan
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($featuredVehicles as $vehicle)
                        <div class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                            <!-- Image Card -->
                            <div class="relative aspect-16/10 w-full overflow-hidden bg-slate-900">
                                <img src="{{ $vehicle->primary_image_url }}" 
                                     alt="{{ $vehicle->name }} - {{ $vehicle->variant }}"
                                     loading="lazy" 
                                     class="h-full w-full object-cover object-center transition duration-300 hover:scale-105"
                                     width="600"
                                     height="375">
                                
                                <!-- Category & Status Badges -->
                                <div class="absolute top-2.5 left-2.5 flex items-center gap-2">
                                    <span class="rounded-md bg-slate-950/80 px-2 py-0.5 text-[10px] font-bold text-white backdrop-blur-md">
                                        {{ $vehicle->category }}
                                    </span>
                                </div>

                                <div class="absolute top-2.5 right-2.5">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold backdrop-blur-md {{ $vehicle->status_badge['class'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $vehicle->status_badge['dot'] }}"></span>
                                        {{ $vehicle->status_badge['label'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Content Details -->
                            <div class="flex flex-1 flex-col justify-between p-4 sm:p-5">
                                <div>
                                    <h3 class="text-sm sm:text-base font-bold text-slate-900 hover:text-red-600 transition">
                                        <a href="{{ route('vehicles.show', $vehicle->slug) }}">
                                            {{ $vehicle->name }}
                                        </a>
                                    </h3>
                                    <p class="text-[11px] font-medium text-slate-500 mt-0.5">
                                        {{ $vehicle->variant ?? 'Standard Model' }}
                                    </p>

                                    <!-- Quick Specification Pills -->
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

                                <!-- Price & Action -->
                                <div class="mt-4 flex items-center justify-between pt-1">
                                    <div>
                                        <span class="text-[10px] font-medium text-slate-400">Mulai dari</span>
                                        <div class="text-xs sm:text-sm font-extrabold text-slate-900">
                                            {{ $vehicle->formatted_price }}
                                        </div>
                                    </div>
                                    <a href="{{ route('vehicles.show', $vehicle->slug) }}" 
                                       class="inline-flex items-center gap-1 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white shadow-xs transition hover:bg-red-600">
                                        Lihat Detail
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- Dealership Excellence Section -->
    <section id="layanan" class="py-12 sm:py-16 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
                <!-- Left Editorial Description -->
                <div class="lg:col-span-6 space-y-4">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Keunggulan Layanan</span>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-tight">
                        Pengalaman Memilih Kendaraan yang Jelas, Nyaman, dan Terpercaya
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Toyota AutoConnect dibangun untuk menghubungkan Anda secara langsung dengan informasi kendaraan yang akurat. Kami memastikan calon pelanggan memperoleh kejelasan spesifikasi teknis, varian, dan panduan harga sebelum berkunjung ke showroom.
                    </p>

                    <div class="space-y-3 pt-2">
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900">Informasi Spesifikasi Lengkap</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Seluruh parameter transmisi, mesin, dimensi, hingga kapasitas penumpang disajikan secara terstruktur.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900">Transparansi Ketersediaan Unit</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Status unit (Tersedia, Segera Hadir, Terpesan) terhubung dengan database katalog resmi.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900">Layanan Digital Terpadu</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Nikmati kemudahan simulasi kredit multi-leasing resmi, pengajuan booking test drive, hingga pemesanan servis berkala secara langsung.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Visual Section -->
                <div class="lg:col-span-6">
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 p-6 text-white shadow-lg">
                        <div class="absolute -right-10 -bottom-10 h-48 w-48 rounded-full bg-red-600/20 blur-3xl"></div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-0.5 text-[10px] font-semibold text-slate-200">
                            STANDAR LAYANAN DEALER
                        </span>
                        <h3 class="mt-3 text-lg sm:text-xl font-bold">Kunjungi Showroom Kami</h3>
                        <p class="mt-1.5 text-xs text-slate-300 leading-relaxed">
                            Konsultan otomotif kami siap mendampingi Anda melihat langsung unit display, mendiskusikan opsi pembiayaan resmi, serta menjadwalkan sesi test drive.
                        </p>

                        <div class="mt-5 rounded-xl bg-white/5 p-3.5 border border-white/10 backdrop-blur-sm">
                            <div class="text-[10px] text-slate-400">Alamat Showroom Utama:</div>
                            <div class="text-xs font-semibold text-white mt-0.5">Toyota AutoConnect Hub — Jl. Raya Otomotif No. 88, Jakarta</div>
                            <div class="text-[10px] text-slate-300 mt-1">Buka Setiap Hari: 08:30 – 17:00 WIB</div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2.5">
                            <a href="{{ route('contact') }}" class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-slate-900 shadow-xs hover:bg-slate-100 transition">
                                Rute &amp; Kontak Showroom
                            </a>
                            <a href="{{ route('vehicles.index') }}" class="rounded-lg border border-white/20 bg-transparent px-4 py-2 text-xs font-semibold text-white hover:bg-white/10 transition">
                                Cari Model Lain
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Banner -->
    <section class="border-t border-slate-200 bg-slate-900 text-white py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-xl sm:text-2xl font-extrabold text-white">Siap Memilih Toyota yang Tepat?</h2>
            <p class="mx-auto mt-2 max-w-xl text-xs sm:text-sm text-slate-400">
                Akses katalog lengkap sekarang untuk melihat seluruh model, filter berdasarkan kategori, atau konsultasikan kebutuhan Anda dengan tim kami.
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('vehicles.index') }}" 
                   class="rounded-lg bg-red-600 px-5 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-red-700">
                    Buka Katalog Kendaraan Sekarang
                </a>
                <a href="{{ route('contact') }}" 
                   class="rounded-lg border border-slate-700 bg-slate-800 px-5 py-2.5 text-xs font-semibold text-slate-200 hover:bg-slate-700 transition">
                    Informasi Kontak Showroom
                </a>
            </div>
        </div>
    </section>
@endsection
