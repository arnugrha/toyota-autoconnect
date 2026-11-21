@extends('layouts.public')

@section('title', $vehicle->name . ' ' . ($vehicle->variant ?? '') . ' — Toyota AutoConnect')
@section('meta_description', 'Spesifikasi lengkap, estimasi harga OTR, dan ketersediaan unit ' . $vehicle->name . ' varian ' . ($vehicle->variant ?? '') . ' di Toyota AutoConnect.')

@section('content')
<div class="bg-slate-50 min-h-screen py-6 sm:py-10" 
     x-data="{ 
         activeImage: '{{ $vehicle->images->first()?->url ?? $vehicle->primary_image_url }}',
         activeAlt: '{{ $vehicle->images->first()?->alt_text ?? $vehicle->name }}',
         testDriveModalOpen: false,
         inquiryModalOpen: false,
         inquirySubject: 'Penawaran Harga OTR & Promo',
         submitting: false,
         authRequiredModalOpen: false
     }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-5 flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-3.5 text-xs text-emerald-800">
                <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 flex items-center gap-2.5 rounded-xl bg-red-50 border border-red-200 p-3.5 text-xs text-red-800">
                <svg class="h-4 w-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500 mb-5" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-red-600 transition">Beranda</a>
            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            <a href="{{ route('vehicles.index') }}" class="hover:text-red-600 transition">Katalog Kendaraan</a>
            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            <span class="text-slate-900 font-semibold truncate" aria-current="page">{{ $vehicle->name }}</span>
        </nav>

        <!-- Main Product Hero Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 bg-white rounded-2xl p-5 sm:p-7 border border-slate-200/90 shadow-xs">
            
            <!-- Left Column: Interactive Image Gallery -->
            <div class="lg:col-span-7 flex flex-col gap-3">
                <!-- Large Stage Image Preview -->
                <div class="relative aspect-16/10 w-full overflow-hidden rounded-xl bg-slate-950 border border-slate-800 shadow-inner">
                    <img :src="activeImage" 
                         :alt="activeAlt" 
                         class="h-full w-full object-cover object-center transition duration-300"
                         width="900"
                         height="560">

                    <!-- Overlay Badges -->
                    <div class="absolute top-3 left-3 flex items-center gap-1.5">
                        <span class="rounded-md bg-slate-950/80 px-2 py-0.5 text-[10px] font-bold text-white backdrop-blur-md">
                            {{ $vehicle->category }}
                        </span>
                        @if ($vehicle->is_featured)
                            <span class="rounded-md bg-red-600/90 px-2 py-0.5 text-[10px] font-bold text-white backdrop-blur-md">
                                Unggulan
                            </span>
                        @endif
                    </div>

                    <div class="absolute top-3 right-3">
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold backdrop-blur-md {{ $vehicle->status_badge['class'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $vehicle->status_badge['dot'] }}"></span>
                            {{ $vehicle->status_badge['label'] }}
                        </span>
                    </div>
                </div>

                <!-- Gallery Thumbnails -->
                @if ($vehicle->images->count() > 1)
                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                        @foreach ($vehicle->images as $index => $img)
                            <button type="button" 
                                    @click="activeImage = '{{ $img->url }}'; activeAlt = '{{ $img->alt_text ?? $vehicle->name }}'"
                                    :class="activeImage === '{{ $img->url }}' ? 'ring-2 ring-red-600 border-transparent' : 'border-slate-200 hover:border-slate-400'"
                                    class="relative aspect-video w-full overflow-hidden rounded-lg border bg-slate-900 transition focus:outline-none">
                                <img src="{{ $img->url }}" 
                                     alt="{{ $img->alt_text ?? $vehicle->name }}" 
                                     class="h-full w-full object-cover object-center">
                            </button>
                        @endforeach
                    </div>
                @endif

                <!-- Pricing Notice Disclaimer -->
                <div class="mt-1 rounded-lg bg-slate-50 p-3 border border-slate-200/60 text-[11px] text-slate-500 leading-relaxed">
                    <strong class="font-semibold text-slate-700">Catatan Estimasi Harga:</strong> Nilai yang tertera merupakan estimasi On The Road (OTR) acuan dan dapat bervariasi bergantung pada wilayah penyerahan unit, pilihan warna khusus, opsi paket pembiayaan/leasing, serta program promo yang sedang berlangsung. Silakan konfirmasikan dengan konsultan dealer kami.
                </div>
            </div>

            <!-- Right Column: Vehicle Overview & Actions -->
            <div class="lg:col-span-5 flex flex-col justify-between space-y-5">
                <div>
                    <!-- Category Tag -->
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">
                            Toyota {{ $vehicle->category }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono">#TYT-{{ str_pad($vehicle->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    <!-- Vehicle Name & Variant -->
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-950 mt-0.5 tracking-tight">
                        {{ $vehicle->name }}
                    </h1>
                    <p class="text-xs sm:text-sm font-semibold text-slate-600 mt-0.5">
                        {{ $vehicle->variant ?? 'Varian Standar' }}
                    </p>

                    <!-- Price Card -->
                    <div class="mt-4 rounded-xl bg-slate-900 p-4 text-white shadow-xs">
                        <div class="text-[11px] font-medium text-slate-400">Estimasi Harga OTR Mulai Dari</div>
                        <div class="text-xl sm:text-2xl font-extrabold text-white mt-0.5">
                            {{ $vehicle->formatted_price }}
                        </div>
                        <div class="mt-1.5 flex items-center gap-1.5 text-[11px] text-slate-300">
                            <span class="h-1.5 w-1.5 rounded-full {{ $vehicle->status_badge['dot'] }}"></span>
                            Status: <strong class="text-white font-semibold">{{ $vehicle->status_badge['label'] }}</strong>
                        </div>
                    </div>

                    <!-- Short Summary Highlights -->
                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-2.5">
                            <span class="text-slate-400 font-medium text-[10px] block">Transmisi</span>
                            <span class="font-bold text-slate-800 text-xs mt-0.5 block truncate">{{ $vehicle->transmission ?? 'N/A' }}</span>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-2.5">
                            <span class="text-slate-400 font-medium text-[10px] block">Tipe Bahan Bakar</span>
                            <span class="font-bold text-slate-800 text-xs mt-0.5 block truncate">{{ $vehicle->fuel_type ?? 'N/A' }}</span>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-2.5">
                            <span class="text-slate-400 font-medium text-[10px] block">Kapasitas Mesin</span>
                            <span class="font-bold text-slate-800 text-xs mt-0.5 block truncate">{{ $vehicle->engine_capacity ?? 'N/A' }}</span>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-2.5">
                            <span class="text-slate-400 font-medium text-[10px] block">Kapasitas Kursi</span>
                            <span class="font-bold text-slate-800 text-xs mt-0.5 block truncate">{{ $vehicle->passenger_capacity ? $vehicle->passenger_capacity . ' Penumpang' : 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Multi-Branch Availability Card -->
                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3.5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Ketersediaan Stok Cabang:</span>
                            <span class="text-xs font-bold text-slate-900">Total: {{ $vehicle->total_available_stock }} Unit</span>
                        </div>

                        <div class="space-y-1.5 text-xs">
                            @forelse ($vehicle->inventories as $inv)
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/60 last:border-0">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $inv->available_stock > 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        <span class="font-medium text-slate-800 text-[11px] truncate">{{ $inv->branch->name }}</span>
                                    </div>
                                    <span class="font-bold text-[11px] flex-shrink-0 {{ $inv->available_stock > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                        {{ $inv->available_stock > 0 ? $inv->available_stock . ' Unit Siap' : 'Indent' }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-[11px] text-slate-400 italic">Informasi ketersediaan cabang sedang diperbarui.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Description Preview -->
                    @if ($vehicle->description)
                        <div class="mt-4 text-xs leading-relaxed text-slate-600 border-t border-slate-100 pt-3">
                            {{ $vehicle->description }}
                        </div>
                    @endif
                </div>

                <!-- Call To Action Buttons -->
                <div class="space-y-2 pt-4 border-t border-slate-200">
                    <button type="button" 
                            @click="@auth testDriveModalOpen = true; @else authRequiredModalOpen = true; @endauth"
                            class="w-full flex items-center justify-center gap-1.5 rounded-lg bg-red-600 py-2.5 px-4 text-xs font-bold text-white shadow-xs transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Ajukan Test Drive
                    </button>

                    <a href="#simulasi-kredit"
                       class="w-full flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 py-2 px-4 text-xs font-bold text-red-700 shadow-2xs transition hover:bg-red-100 hover:border-red-300 focus:outline-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v-.75C2.25 4.56 2.81 4 3.5 4h17c.69 0 1.25.56 1.25 1.25v.75m-19.5 0c0 .414.336.75.75.75h18a.75.75 0 0 0 .75-.75m-19.5 0v12.75c0 .621.504 1.125 1.125 1.125H18M3.75 6H18m0 0v12.75c0 .621-.504 1.125-1.125 1.125H3.75" />
                        </svg>
                        Hitung Simulasi Kredit
                    </a>

                    <button type="button" 
                            @click="@auth inquiryModalOpen = true; inquirySubject = 'Penawaran Harga OTR & Promo'; @else authRequiredModalOpen = true; @endauth"
                            class="w-full flex items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white py-2 px-4 text-xs font-bold text-slate-700 shadow-xs transition hover:bg-slate-50 focus:outline-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.84-.84c.15-.658.334-1.304.544-1.933A7.472 7.472 0 0 1 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                        </svg>
                        Tanya Sales Consultant
                    </button>

                    <div class="text-center pt-1">
                        <a href="{{ route('vehicles.index') }}" class="text-[11px] font-semibold text-slate-500 hover:text-red-600 transition inline-flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                            Kembali ke Katalog Kendaraan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Technical Specifications Table -->
        <div class="mt-8 rounded-2xl bg-white p-5 sm:p-7 border border-slate-200/90 shadow-xs">
            <div class="border-b border-slate-100 pb-3 mb-5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Lembar Data Kendaraan</span>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mt-0.5">Spesifikasi Teknis Mendalam</h2>
                <p class="text-[11px] text-slate-500 mt-0.5">Informasi spesifikasi resmi dari lini kendaraan {{ $vehicle->name }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-xs">
                <!-- Dimensi & Ukuran -->
                <div class="space-y-2">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] border-b border-slate-100 pb-1.5">Dimensi &amp; Kapasitas</h3>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-500">Panjang x Lebar x Tinggi</span>
                        <span class="font-semibold text-slate-900 text-right">{{ $vehicle->dimensions ?? 'Data belum dirilis' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-500">Jumlah Tempat Duduk</span>
                        <span class="font-semibold text-slate-900 text-right">{{ $vehicle->passenger_capacity ? $vehicle->passenger_capacity . ' Kursi' : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-500">Kategori Bodi</span>
                        <span class="font-semibold text-slate-900 text-right">{{ $vehicle->category }}</span>
                    </div>
                </div>

                <!-- Dapur Pacu & Transmisi -->
                <div class="space-y-2">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] border-b border-slate-100 pb-1.5">Performa &amp; Mesin</h3>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-500">Kapasitas / Tipe Mesin</span>
                        <span class="font-semibold text-slate-900 text-right">{{ $vehicle->engine_capacity ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-500">Jenis Bahan Bakar</span>
                        <span class="font-semibold text-slate-900 text-right">{{ $vehicle->fuel_type ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-500">Sistem Transmisi</span>
                        <span class="font-semibold text-slate-900 text-right">{{ $vehicle->transmission ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-500">Varian Model</span>
                        <span class="font-semibold text-slate-900 text-right">{{ $vehicle->variant ?? 'Standard' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Credit Simulation Section (Tahap 7) -->
        <div id="simulasi-kredit"
             class="mt-8 rounded-2xl bg-white p-5 sm:p-7 border border-slate-200/90 shadow-xs scroll-mt-6"
             x-data="creditCalculator({
                 vehicleId: {{ $vehicle->id }},
                 vehiclePrice: {{ (int) ($vehicle->price ?? 1190000000) }},
                 vehicles: {{ ($availableVehicles ?? collect([$vehicle]))->keyBy('id')->toJson() }},
                 providers: {{ $leasingProviders->keyBy('id')->toJson() }},
                 defaultProviderId: {{ $leasingProviders->first()?->id ?? 1 }}
             })">
            
            <div class="border-b border-slate-100 pb-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Simulasi Pembiayaan Resmi</span>
                    <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mt-0.5">Kalkulator Simulasi Kredit & Angsuran</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Hitung estimasi uang muka, suku bunga, dan cicilan bulanan untuk unit <strong class="text-slate-800" x-text="currentVehicleName"></strong>
                    </p>
                </div>
                @auth
                    <a href="{{ route('customer.simulations.index') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-red-600 transition">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Riwayat Simulasi Saya
                    </a>
                @endauth
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Calculator Inputs (Left Column - 7 Cols) -->
                <div class="lg:col-span-7 space-y-5">

                    <!-- Pilihan Model Mobil Toyota -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-1.5">
                            <label>Pilih Model Mobil Toyota</label>
                            <span class="text-[10px] text-slate-400 font-normal" x-text="currentVehicleVariant"></span>
                        </div>
                        <select x-model.number="selectedVehicleId"
                                @change="onVehicleChange()"
                                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-3.5 text-xs font-bold text-slate-900 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                            <template x-for="veh in Object.values(vehicles)" :key="veh.id">
                                <option :value="veh.id"
                                        x-text="veh.name + (veh.variant ? ' — ' + veh.variant : '') + ' (Rp ' + formatRupiah(veh.price) + ')'">
                                </option>
                            </template>
                        </select>
                    </div>
                    
                    <!-- Harga Kendaraan OTR (Sesuai Mobil Pilihan) -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-1.5">
                            <label>Harga Kendaraan OTR Resmi</label>
                            <span class="text-[10px] text-emerald-600 font-semibold" x-text="currentVehicleName"></span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="text"
                                   :value="formatRupiah(vehiclePrice)"
                                   readonly
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-xs font-mono font-bold text-slate-900 cursor-not-allowed">
                        </div>
                    </div>

                    <!-- Leasing Provider Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Pilih Mitra Lembaga Pembiayaan (Leasing)
                        </label>
                        <select x-model.number="selectedProviderId"
                                @change="onProviderChange()"
                                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-3.5 text-xs font-medium text-slate-900 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                            <template x-for="provider in Object.values(providers)" :key="provider.id">
                                <option :value="provider.id"
                                        x-text="provider.name + ' (' + provider.code + ') — Suku Bunga Mulai ' + provider.base_interest_rate + '%/thn'">
                                </option>
                            </template>
                        </select>
                    </div>

                    <!-- Down Payment (DP) -->
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <label class="font-bold text-slate-700">Uang Muka (Down Payment)</label>
                            <span class="font-mono font-bold text-red-600" x-text="dpPercentage + '%'"></span>
                        </div>

                        <!-- Quick Percentage Chips -->
                        <div class="flex flex-wrap items-center gap-2">
                            <template x-for="p in [20, 25, 30, 40, 50]" :key="p">
                                <button type="button"
                                        @click="setPercentage(p)"
                                        :class="dpPercentage == p ? 'bg-red-600 text-white border-red-600' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200'"
                                        class="rounded-lg border px-3 py-1 text-xs font-bold transition"
                                        x-text="p + '%'">
                                </button>
                            </template>
                        </div>

                        <!-- DP Amount Input & Slider -->
                        <div class="relative pt-1">
                            <span class="absolute top-3 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number"
                                   x-model.number="dpAmount"
                                   @input="onDpAmountChange()"
                                   :min="minDp"
                                   :max="vehiclePrice - 1000000"
                                   class="w-full rounded-xl border border-slate-200 py-2.5 pl-10 pr-4 text-xs font-mono font-bold text-slate-900 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                        </div>

                        <input type="range"
                               min="20"
                               max="80"
                               step="1"
                               x-model.number="dpPercentage"
                               @input="onSliderChange()"
                               class="w-full accent-red-600 cursor-pointer">
                    </div>

                    <!-- Tenor Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            Jangka Waktu Pembiayaan (Tenor)
                        </label>
                        <div class="grid grid-cols-5 gap-2">
                            <template x-for="t in [12, 24, 36, 48, 60]" :key="t">
                                <button type="button"
                                        @click="tenorMonths = t"
                                        :class="tenorMonths == t ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="rounded-xl border py-2 text-center transition">
                                    <span class="block text-xs font-extrabold" x-text="t + ' Bln'"></span>
                                    <span class="block text-[10px] opacity-75" x-text="(t/12) + ' Thn'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Interest Rate Input -->
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <label class="font-bold text-slate-700">Estimasi Suku Bunga Pertahun (% Flat)</label>
                            <span class="text-[10px] text-slate-400">Bisa disesuaikan dengan penawaran</span>
                        </div>
                        <div class="relative">
                            <input type="number"
                                   step="0.05"
                                   min="0"
                                   max="50"
                                   x-model.number="interestRate"
                                   class="w-full rounded-xl border border-slate-200 py-2.5 pl-3.5 pr-8 text-xs font-mono font-bold text-slate-900 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-bold text-slate-400">%</span>
                        </div>
                    </div>

                </div>

                <!-- Calculator Output / Simulation Summary (Right Column - 5 Cols) -->
                <div class="lg:col-span-5 flex flex-col justify-between rounded-2xl bg-slate-900 text-white p-6 shadow-sm space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-red-400">Hasil Simulasi Angsuran</span>
                            <span class="rounded bg-slate-800 px-2 py-0.5 text-[10px] font-mono text-slate-300" x-text="currentProviderName"></span>
                        </div>

                        <!-- Big Installment Display -->
                        <div>
                            <span class="text-[11px] text-slate-400 block">Perkiraan Cicilan Bulanan</span>
                            <div class="text-2xl sm:text-3xl font-black font-mono text-red-400 mt-0.5">
                                <span x-text="'Rp ' + formatRupiah(monthlyInstallment)"></span>
                                <span class="text-xs font-sans text-slate-400 font-normal">/ bln</span>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block" x-text="'Tenor ' + tenorMonths + ' Bulan (' + (tenorMonths/12) + ' Tahun)'"></span>
                        </div>

                        <!-- Financial Breakdown Details -->
                        <div class="rounded-xl bg-slate-800/80 p-3.5 space-y-2 text-xs border border-slate-700/60 font-mono">
                            <div class="flex justify-between text-slate-300">
                                <span class="font-sans">Uang Muka (DP):</span>
                                <span class="font-bold text-white" x-text="'Rp ' + formatRupiah(dpAmount) + ' (' + dpPercentage + '%)'"></span>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span class="font-sans">Pokok Pembiayaan:</span>
                                <span class="font-bold text-white" x-text="'Rp ' + formatRupiah(loanAmount)"></span>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span class="font-sans">Total Bunga (<span x-text="interestRate + '%'"></span>):</span>
                                <span class="font-bold text-white" x-text="'Rp ' + formatRupiah(totalInterest)"></span>
                            </div>
                            <div class="pt-2 border-t border-slate-700 flex justify-between font-bold text-white">
                                <span class="font-sans">Total Kredit:</span>
                                <span class="text-red-400" x-text="'Rp ' + formatRupiah(totalCreditPayment)"></span>
                            </div>
                        </div>

                        <p class="text-[10px] text-slate-400 leading-relaxed italic">
                            * Simulasi menggunakan metode suku bunga flat. Nilai akhir bergantung pada persetujuan kredit resmi, biaya provisi, asuransi kendaraan komprehensif, dan fidusia lembaga leasing.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2.5 pt-2 border-t border-slate-800">
                        @auth
                            <!-- Save Simulation Form -->
                            <form action="{{ route('customer.simulations.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="vehicle_id" :value="selectedVehicleId">
                                <input type="hidden" name="leasing_provider_id" :value="selectedProviderId">
                                <input type="hidden" name="down_payment" :value="dpAmount">
                                <input type="hidden" name="tenor_months" :value="tenorMonths">
                                <input type="hidden" name="interest_rate" :value="interestRate">

                                <button type="submit"
                                        class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-slate-800 py-2.5 px-4 text-xs font-bold text-white hover:bg-slate-700 border border-slate-700 transition">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
                                    </svg>
                                    Simpan Hasil Simulasi
                                </button>
                            </form>

                            <!-- Direct Apply Button -->
                            <a :href="'{{ route('customer.leasing.create') }}?vehicle_id=' + selectedVehicleId + '&leasing_provider_id=' + selectedProviderId + '&down_payment_percentage=' + dpPercentage + '&tenor_months=' + tenorMonths"
                               class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-red-600 py-2.5 px-4 text-xs font-bold text-white hover:bg-red-700 transition shadow-xs">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                </svg>
                                Ajukan Pembiayaan Leasing
                            </a>
                        @else
                            <button type="button"
                                    @click="authRequiredModalOpen = true"
                                    class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-slate-800 py-2.5 px-4 text-xs font-bold text-white hover:bg-slate-700 border border-slate-700 transition">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
                                </svg>
                                Simpan Hasil Simulasi
                            </button>

                            <button type="button"
                                    @click="authRequiredModalOpen = true"
                                    class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-red-600 py-2.5 px-4 text-xs font-bold text-white hover:bg-red-700 transition shadow-xs">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                </svg>
                                Ajukan Pembiayaan Leasing
                            </button>
                        @endauth
                    </div>
                </div>
            </div>

        </div>

        <!-- Related Vehicles Section -->
        @if ($relatedVehicles->isNotEmpty())
            <div class="mt-10">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Model Serupa</span>
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mt-0.5">Kendaraan Terkait Kategori {{ $vehicle->category }}</h2>
                    </div>
                    <a href="{{ route('vehicles.index', ['category' => $vehicle->category]) }}" class="text-xs font-bold text-red-600 hover:text-red-700 flex items-center gap-1">
                        Lihat Semua {{ $vehicle->category }}
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @foreach ($relatedVehicles as $related)
                        <div class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs transition duration-200 hover:-translate-y-1 hover:shadow-md">
                            <div class="relative aspect-16/10 w-full overflow-hidden bg-slate-900">
                                <img src="{{ $related->primary_image_url }}" 
                                     alt="{{ $related->name }}" 
                                     class="h-full w-full object-cover object-center"
                                     loading="lazy">
                                <div class="absolute top-2.5 left-2.5">
                                    <span class="rounded-md bg-slate-950/80 px-2 py-0.5 text-[9px] font-bold text-white">
                                        {{ $related->category }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col justify-between p-4">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 hover:text-red-600 transition">
                                        <a href="{{ route('vehicles.show', $related->slug) }}">
                                            {{ $related->name }}
                                        </a>
                                    </h3>
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $related->variant }}</p>
                                </div>
                                <div class="mt-3 flex items-center justify-between pt-2 border-t border-slate-100">
                                    <div class="text-xs font-bold text-slate-900">
                                        {{ $related->formatted_price }}
                                    </div>
                                    <a href="{{ route('vehicles.show', $related->slug) }}" class="text-[11px] font-semibold text-red-600 hover:underline flex items-center gap-1">
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
            </div>
        @endif

    <!-- Modal 1: Formulir Kirim Inquiry & Konsultasi Kendaraan -->
    @auth
        <div x-show="inquiryModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.outside="inquiryModalOpen = false" 
                 class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl border border-slate-200">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-900 px-5 py-4 text-white">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-600 text-white font-extrabold text-xs">
                            T
                        </div>
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold text-white">Konsultasi Unit &amp; Jadwal Test Drive</h3>
                            <p class="text-[10px] text-slate-400">{{ $vehicle->name }} — Toyota AutoConnect</p>
                        </div>
                    </div>
                    <button type="button" @click="inquiryModalOpen = false" class="text-slate-400 hover:text-white transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form method="POST" action="{{ route('inquiries.store') }}" @submit="submitting = true" class="p-5 space-y-3.5 text-xs">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                    <!-- Vehicle Summary Snippet -->
                    <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-2.5 border border-slate-200/80">
                        <img src="{{ $vehicle->primary_image_url }}" alt="{{ $vehicle->name }}" class="h-10 w-16 object-cover rounded-lg flex-shrink-0 bg-slate-200">
                        <div class="overflow-hidden">
                            <span class="text-[9px] font-bold uppercase text-red-600 tracking-wider">{{ $vehicle->category }}</span>
                            <h4 class="font-bold text-slate-900 truncate text-xs">{{ $vehicle->name }}</h4>
                            <p class="text-[10px] font-semibold text-slate-600">{{ $vehicle->formatted_price }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Pemohon <span class="text-red-600">*</span></label>
                            <input type="text" name="customer_name" required value="{{ auth()->user()->name }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Email <span class="text-red-600">*</span></label>
                            <input type="email" name="customer_email" required value="{{ auth()->user()->email }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Nomor Telepon / WA <span class="text-red-600">*</span></label>
                            <input type="tel" name="customer_phone" required value="{{ auth()->user()->phone ?? old('customer_phone') }}"
                                   placeholder="081234567890"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Topik Kebutuhan <span class="text-red-600">*</span></label>
                            <select name="subject" x-model="inquirySubject" required
                                    class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                <option value="Penawaran Harga OTR & Promo">Penawaran Harga OTR &amp; Promo</option>
                                <option value="Pemesanan Jadwal Test Drive">Pemesanan Jadwal Test Drive</option>
                                <option value="Konsultasi Simulasi Kredit">Konsultasi Simulasi Kredit</option>
                                <option value="Ketersediaan Unit & Pilihan Warna">Ketersediaan Unit &amp; Warna</option>
                                <option value="Pertanyaan Spesifikasi Lainnya">Pertanyaan Spesifikasi Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Pesan / Pertanyaan Tambahan <span class="text-red-600">*</span></label>
                        <textarea name="message" rows="3" required
                                  placeholder="Tuliskan jadwal uji kemudi yang diinginkan atau detail informasi pemesanan unit..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" name="consent" value="1" required checked
                                   class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-600">
                            <span class="text-[10px] text-slate-600 leading-snug">
                                Saya menyetujui data kontak ini digunakan oleh tim sales Toyota AutoConnect untuk menghubungi saya perihal penawaran unit dan jadwal dealer.
                            </span>
                        </label>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="inquiryModalOpen = false"
                                class="rounded-lg bg-slate-100 px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="submitting"
                                class="rounded-lg bg-red-600 px-4 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition flex items-center gap-1.5 disabled:opacity-50">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                            <span x-text="submitting ? 'Mengirim...' : 'Kirim Permintaan'">Kirim Permintaan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 1.5: Ajukan Test Drive Resmi Toyota -->
        <div x-show="testDriveModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.outside="testDriveModalOpen = false" 
                 class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white p-6 shadow-xl border border-slate-200">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-red-600">Formulir Resmi Uji Kemudi</span>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 mt-0.5">Ajukan Jadwal Test Drive</h3>
                        <p class="text-[11px] text-slate-500">Unit: <strong class="text-slate-800">{{ $vehicle->name }} {{ $vehicle->variant ?? '' }}</strong></p>
                    </div>
                    <button type="button" @click="testDriveModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('test-drives.store') }}" method="POST" @submit="submitting = true" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                    <!-- Info Pelanggan Terverifikasi (Diambil dari akun login) -->
                    <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-3 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Data Pemohon (Terverifikasi Akun)</span>
                            <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                Profil Login
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-slate-400 text-[10px] block">Nama Lengkap</span>
                                <span class="font-bold text-slate-800">{{ auth()->user()->name }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] block">Email &amp; No. Telepon</span>
                                <span class="font-semibold text-slate-800 truncate block">{{ auth()->user()->email }}</span>
                                <span class="text-slate-500 text-[11px]">{{ auth()->user()->phone ?? 'No. Telp belum diisi' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pilih Cabang Dealer -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                            Pilih Cabang Dealer Toyota <span class="text-red-600">*</span>
                        </label>
                        <select name="branch_id" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <option value="">-- Pilih Cabang Terdekat --</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">
                                    {{ $branch->name }} ({{ $branch->city }}) — {{ $branch->opening_time }} - {{ $branch->closing_time }} WIB
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Tanggal & Slot Jam -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Tanggal Test Drive <span class="text-red-600">*</span>
                            </label>
                            <input type="date" name="scheduled_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Pilihan Jam Sesi <span class="text-red-600">*</span>
                            </label>
                            <select name="scheduled_time" required
                                    class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                <option value="09:00">09:00 WIB (Pagi)</option>
                                <option value="10:00">10:00 WIB (Pagi)</option>
                                <option value="11:00">11:00 WIB (Pagi)</option>
                                <option value="13:30">13:30 WIB (Siang)</option>
                                <option value="14:30">14:30 WIB (Siang)</option>
                                <option value="15:30">15:30 WIB (Sore)</option>
                                <option value="16:30">16:30 WIB (Sore)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Catatan Tambahan (Opsional) -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Tambahan untuk Sales Consultant (Opsional)</label>
                        <textarea name="customer_note" rows="2"
                                  placeholder="Contoh: Ingin mencoba fitur Toyota Safety Sense, atau membawa keluarga..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                    </div>

                    <!-- Syarat & Ketentuan Singkat -->
                    <div class="rounded-lg bg-amber-50/70 border border-amber-200/60 p-2.5 text-[10px] text-amber-800 leading-snug">
                        <strong>Persyaratan Uji Kemudi:</strong> Memiliki SIM A aktif yang masih berlaku dan hadir 10 menit sebelum jadwal sesi di showroom cabang yang dipilih.
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="testDriveModalOpen = false"
                                class="rounded-lg bg-slate-100 px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="submitting"
                                class="rounded-lg bg-red-600 px-4 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition flex items-center gap-1.5 disabled:opacity-50">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span x-text="submitting ? 'Memproses Jadwal...' : 'Ajukan Jadwal Sekarang'">Ajukan Jadwal Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endauth

    <!-- Modal 2: Notifikasi Perlu Login untuk Pengunjung Tamu -->
    @guest
        <div x-show="authRequiredModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.outside="authRequiredModalOpen = false" 
                 class="relative w-full max-w-sm overflow-hidden rounded-2xl bg-white p-6 shadow-xl border border-slate-200 text-center">
                
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600 mb-3">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>

                <h3 class="text-sm font-bold text-slate-900">Masuk untuk Mengirimkan Permintaan</h3>
                <p class="mt-1.5 text-xs text-slate-500 leading-relaxed">
                    Untuk menjadwalkan test drive atau berkonsultasi mengenai unit <strong class="text-slate-800">{{ $vehicle->name }}</strong>, silakan masuk ke akun pelanggan Anda terlebih dahulu.
                </p>

                <div class="mt-5 flex flex-col gap-2">
                    <a href="{{ route('login') }}" 
                       class="w-full rounded-lg bg-red-600 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Masuk ke Akun
                    </a>
                    <a href="{{ route('register') }}" 
                       class="w-full rounded-lg border border-slate-300 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Daftar Akun Pelanggan Baru
                    </a>
                    <button type="button" @click="authRequiredModalOpen = false"
                            class="text-[11px] text-slate-400 hover:text-slate-600 pt-1">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endguest

    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('creditCalculator', (config) => ({
            vehicles: config.vehicles || {},
            selectedVehicleId: config.vehicleId,
            vehiclePrice: config.vehiclePrice > 0 ? config.vehiclePrice : 1190000000,
            providers: config.providers || {},
            selectedProviderId: config.defaultProviderId,
            dpPercentage: 20,
            dpAmount: 0,
            tenorMonths: 36,
            interestRate: 6.00,

            init() {
                if (this.currentVehicle && this.currentVehicle.price > 0) {
                    this.vehiclePrice = Number(this.currentVehicle.price);
                } else if (!this.vehiclePrice || this.vehiclePrice <= 0) {
                    this.vehiclePrice = 1190000000;
                }
                this.dpAmount = Math.round(this.vehiclePrice * (this.dpPercentage / 100));
                this.onProviderChange();
            },

            get currentVehicle() {
                return this.vehicles[this.selectedVehicleId] || null;
            },

            get currentVehicleName() {
                return this.currentVehicle ? this.currentVehicle.name : '{{ $vehicle->name }}';
            },

            get currentVehicleVariant() {
                return this.currentVehicle && this.currentVehicle.variant ? this.currentVehicle.variant : 'Varian Resmi';
            },

            onVehicleChange() {
                if (this.currentVehicle && this.currentVehicle.price > 0) {
                    this.vehiclePrice = Number(this.currentVehicle.price);
                }
                this.dpAmount = Math.round(this.vehiclePrice * (this.dpPercentage / 100));
            },

            get minDp() {
                return Math.round(this.vehiclePrice * 0.20);
            },

            get currentProvider() {
                return this.providers[this.selectedProviderId] || null;
            },

            get currentProviderName() {
                return this.currentProvider ? this.currentProvider.name : 'Estimasi Standar';
            },

            onProviderChange() {
                if (this.currentProvider && this.currentProvider.base_interest_rate) {
                    this.interestRate = parseFloat(this.currentProvider.base_interest_rate);
                }
            },

            setPercentage(p) {
                this.dpPercentage = p;
                this.dpAmount = Math.round(this.vehiclePrice * (p / 100));
            },

            onSliderChange() {
                this.dpAmount = Math.round(this.vehiclePrice * (this.dpPercentage / 100));
            },

            onDpAmountChange() {
                if (this.dpAmount > 0 && this.vehiclePrice > 0) {
                    this.dpPercentage = Math.min(80, Math.max(20, Math.round((this.dpAmount / this.vehiclePrice) * 100)));
                }
            },

            get loanAmount() {
                return Math.max(0, this.vehiclePrice - this.dpAmount);
            },

            get totalInterest() {
                const years = this.tenorMonths / 12;
                return Math.round(this.loanAmount * (this.interestRate / 100) * years);
            },

            get totalCreditPayment() {
                return this.loanAmount + this.totalInterest;
            },

            get monthlyInstallment() {
                return this.tenorMonths > 0 ? Math.round(this.totalCreditPayment / this.tenorMonths) : 0;
            },

            formatRupiah(num) {
                return new Intl.NumberFormat('id-ID').format(num || 0);
            }
        }));
    });
</script>
@endsection
