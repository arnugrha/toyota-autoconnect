@extends('layouts.public')

@section('title', 'Program Promo & Penawaran Dealer — Toyota AutoConnect')
@section('meta_description', 'Informasi program promosi resmi, skema pembiayaan bunga spesial, dan paket purnajual dealer Toyota AutoConnect.')

@section('content')
<div class="bg-slate-50 min-h-screen py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="border-b border-slate-200 pb-6 mb-8 text-center max-w-2xl mx-auto">
            <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Program Penjualan</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-0.5">Promo &amp; Paket Pembiayaan Resmi</h1>
            <p class="text-xs text-slate-500 mt-1.5">
                Dapatkan penawaran terbaik untuk pembelian unit Toyota baru, program cicilan ringan, serta bonus paket perawatan berkala di bengkel resmi.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="inline-block rounded-md bg-red-50 text-red-600 px-2 py-0.5 text-[10px] font-bold mb-2.5">Paket Spesial MPV</span>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Program All New Avanza &amp; Veloz</h2>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Nikmati program uang muka ringan mulai dari 15% serta paket bebas biaya jasa dan suku cadang untuk servis berkala hingga 50.000 km.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[11px] text-slate-400">Periode Berjalan</span>
                    <a href="{{ route('vehicles.index', ['category' => 'MPV']) }}" class="text-xs font-bold text-red-600 hover:underline flex items-center gap-1">
                        Lihat Model
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="inline-block rounded-md bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[10px] font-bold mb-2.5">Elektrifikasi &amp; Hybrid</span>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Garansi Baterai HEV 8 Tahun</h2>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Setiap pembelian unit Toyota Hybrid (Innova Zenix HEV, Yaris Cross HEV, Camry Hybrid) mendapatkan garansi baterai resmi pabrikan hingga 8 tahun atau 160.000 km.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[11px] text-slate-400">Garansi Resmi</span>
                    <a href="{{ route('vehicles.index', ['q' => 'Hybrid']) }}" class="text-xs font-bold text-red-600 hover:underline flex items-center gap-1">
                        Lihat Lini Hybrid
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="inline-block rounded-md bg-blue-50 text-blue-700 px-2 py-0.5 text-[10px] font-bold mb-2.5">Trade-In Prioritas</span>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Tukar Tambah Unit Lama</h2>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Apresiasi unit lama dari seluruh merek dengan penaksiran harga transparan dan subsidi tukar tambah ke unit Toyota impian Anda.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[11px] text-slate-400">Inspeksi Showroom</span>
                    <a href="{{ route('contact') }}" class="text-xs font-bold text-red-600 hover:underline flex items-center gap-1">
                        Hubungi Kami
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-5 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-red-600 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                Kembali ke Katalog Kendaraan
            </a>
        </div>
    </div>
</div>
@endsection
