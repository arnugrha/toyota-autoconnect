@extends('layouts.public')

@section('title', 'Tentang Kami — Toyota AutoConnect')
@section('meta_description', 'Mengenal Toyota AutoConnect: Dealer resmi partner Toyota yang berfokus pada kemudahan layanan digital, transparansi unit, dan pengalaman purnajual prima.')

@section('content')
<div class="bg-slate-50 min-h-screen py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto bg-white p-6 sm:p-10 rounded-2xl border border-slate-200 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Profil Showroom</span>
                <h1 class="text-2xl font-extrabold text-slate-900 mt-0.5">Tentang Toyota AutoConnect</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Menghubungkan pelanggan dengan kenyamanan memiliki kendaraan Toyota melalui inovasi digital.
                </p>
            </div>

            <div class="space-y-3 text-xs sm:text-sm text-slate-600 leading-relaxed">
                <p>
                    <strong>Toyota AutoConnect</strong> didirikan dengan komitmen untuk menyederhanakan proses pencarian, pembelian, dan kepemilikan kendaraan Toyota. Kami percaya bahwa transparansi data spesifikasi teknis, harga estimasi On The Road (OTR), dan kejelasan status ketersediaan unit di showroom merupakan hak utama setiap calon pemilik kendaraan.
                </p>
                <p>
                    Sebagai mitra dealer resmi, kami menyediakan fasilitas 3S (Sales, Service, Spare Parts) terpadu dengan standar kualitas global Toyota, teknisi bersertifikasi internasional, dan fasilitas ruang tunggu nyaman berkonsep modern.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-100 text-center">
                <div class="rounded-xl bg-slate-50 p-3">
                    <div class="text-lg font-extrabold text-slate-900">100%</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Suku Cadang Asli Toyota</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <div class="text-lg font-extrabold text-slate-900">Resmi</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Garansi Manufaktur</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <div class="text-lg font-extrabold text-slate-900">Modern</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Katalog Terintegrasi</div>
                </div>
            </div>

            <div class="pt-3 text-center">
                <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    Lihat Pilihan Kendaraan
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
