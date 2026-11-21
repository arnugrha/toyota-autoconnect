@extends('layouts.public')

@section('title', 'Kontak & Lokasi Showroom — Toyota AutoConnect')
@section('meta_description', 'Hubungi tim penjualan dan layanan purnajual Toyota AutoConnect. Informasi jam operasional, alamat showroom resmi, dan nomor telepon layanan.')

@section('content')
<div class="bg-slate-50 min-h-screen py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="border-b border-slate-200 pb-6 mb-8 text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider text-red-600">Layanan Pelanggan</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-0.5">Kontak &amp; Lokasi Dealer</h1>
                <p class="text-xs text-slate-500 mt-1.5">
                    Kami siap melayani kebutuhan informasi unit, pemesanan kendaraan baru, hingga perawatan berkala.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Contact Card -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        Informasi Showroom Resmi
                    </h2>
                    
                    <div class="space-y-3.5 text-xs text-slate-600">
                        <div>
                            <span class="block text-[10px] font-bold uppercase text-slate-400">Alamat Showroom Utama</span>
                            <p class="font-semibold text-slate-800 mt-0.5">Toyota AutoConnect Hub</p>
                            <p class="text-slate-500 text-[11px]">Jl. Raya Otomotif No. 88, Kawasan Dealer Terpadu, Jakarta</p>
                        </div>

                        <div>
                            <span class="block text-[10px] font-bold uppercase text-slate-400">Telepon Layanan Resmi</span>
                            <p class="font-semibold text-slate-800 mt-0.5 flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                                (021) 5080-TOYOTA / (021) 5080-8696
                            </p>
                        </div>

                        <div>
                            <span class="block text-[10px] font-bold uppercase text-slate-400">Email Korespondensi</span>
                            <p class="font-semibold text-slate-800 mt-0.5 flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                                layanan@autoconnect-toyota.co.id
                            </p>
                        </div>

                        <div>
                            <span class="block text-[10px] font-bold uppercase text-slate-400">Jam Operasional Showroom</span>
                            <div class="mt-1 space-y-1 text-[11px] text-slate-600">
                                <div class="flex justify-between">
                                    <span>Senin - Jumat:</span>
                                    <span class="font-semibold text-slate-800">08:30 - 17:00 WIB</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Sabtu:</span>
                                    <span class="font-semibold text-slate-800">08:30 - 16:00 WIB</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Minggu / Libur:</span>
                                    <span class="font-semibold text-slate-800">09:00 - 15:00 WIB</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assistance Note -->
                <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-md flex flex-col justify-between">
                    <div>
                        <span class="inline-block rounded-md bg-red-600/30 text-red-400 px-2 py-0.5 text-[10px] font-bold mb-2.5">Customer Support</span>
                        <h2 class="text-base sm:text-lg font-bold">Konsultasi Kendaraan Toyota</h2>
                        <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                            Bagi calon pelanggan yang ingin memeriksa ketersediaan warna, jadwal pengiriman unit, atau rincian simulasi kredit, tim konsultan penjualan kami siap mendampingi Anda di lokasi showroom maupun via konsultasi telepon.
                        </p>
                    </div>

                    <div class="mt-6 pt-5 border-t border-slate-800 space-y-2">
                        <a href="{{ route('vehicles.index') }}" 
                           class="w-full text-center block rounded-lg bg-red-600 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                            Cari Kendaraan di Katalog
                        </a>
                        <a href="{{ route('home') }}" 
                           class="w-full text-center block rounded-lg border border-slate-700 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
