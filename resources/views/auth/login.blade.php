@php
    $isAdmin = ($portalType ?? (request()->routeIs('admin.*') ? 'admin' : 'customer')) === 'admin';
@endphp

@extends('layouts.public')

@section('title', $isAdmin ? 'Masuk ke Portal Petugas & Admin — Toyota AutoConnect' : 'Masuk ke Akun Pelanggan — Toyota AutoConnect')
@section('meta_description', $isAdmin ? 'Masuk ke portal operasional dealer Toyota AutoConnect untuk staf, sales, dan manajemen.' : 'Masuk ke portal Toyota AutoConnect untuk mengakses dashboard dealer dan layanan pelanggan.')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-12 overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm"
         x-data="{ showPassword: false }">
        
        <!-- Left Visual Panel -->
        <div class="lg:col-span-5 bg-slate-900 p-8 text-white flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full {{ $isAdmin ? 'bg-amber-600/20' : 'bg-red-600/20' }} blur-2xl"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $isAdmin ? 'bg-amber-600' : 'bg-red-600' }} text-white font-extrabold text-sm">
                        T
                    </div>
                    <span class="text-sm font-bold tracking-tight">TOYOTA <span class="font-normal text-slate-400">AutoConnect</span></span>
                </div>

                <div class="mt-8">
                    @if($isAdmin)
                        <span class="inline-block rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 text-[10px] font-bold">
                            PORTAL OPERASIONAL &amp; DEALER
                        </span>
                        <h2 class="mt-2 text-xl font-bold leading-snug">Sistem Manajemen Operasional Dealer</h2>
                        <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                            Akses dashboard manajemen untuk Super Admin, Sales Executive, Inventory Officer, Service Advisor, dan Dealer Manager.
                        </p>
                    @else
                        <span class="inline-block rounded-md bg-white/10 px-2 py-0.5 text-[10px] font-bold text-red-400 border border-white/10">
                            PORTAL PELANGGAN
                        </span>
                        <h2 class="mt-2 text-xl font-bold leading-snug">Selamat Datang di Dealer Resmi</h2>
                        <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                            Akses layanan digital untuk pelanggan: pemesanan unit kendaraan, booking servis, simulasi kredit, dan permohonan test drive.
                        </p>
                    @endif
                </div>
            </div>

            <div class="relative z-10 pt-8 border-t border-slate-800/80 text-[11px] text-slate-400">
                <p class="flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Akses Aman Terenkripsi (RBAC Enforced)
                </p>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-6">
                    <div>
                        <h1 class="text-lg sm:text-xl font-extrabold text-slate-900">
                            {{ $isAdmin ? 'Masuk Portal Petugas' : 'Masuk ke Akun Pelanggan' }}
                        </h1>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $isAdmin ? 'Gunakan akun staf terdaftar Anda untuk mengelola dashboard dealer' : 'Masukkan alamat email dan kata sandi akun Anda' }}
                        </p>
                    </div>
                    <a href="{{ route('home') }}" class="text-[11px] font-semibold text-slate-400 hover:text-red-600 transition flex items-center gap-1">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Beranda
                    </a>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-lg bg-red-50 p-3 border border-red-200 text-xs text-red-700">
                        <div class="flex items-center gap-1.5 font-bold mb-1">
                            <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            Gagal Masuk
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-5 rounded-lg bg-emerald-50 p-3 border border-emerald-200 text-xs text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ $isAdmin ? route('admin.login') : route('login') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username"
                               placeholder="{{ $isAdmin ? 'petugas@toyota-autoconnect.id' : 'nama@email.com' }}" 
                               class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-xs font-bold text-slate-700">Kata Sandi</label>
                            <button type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 transition">
                                <span x-show="!showPassword">Tampilkan</span>
                                <span x-show="showPassword" x-cloak>Sembunyikan</span>
                            </button>
                        </div>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   autocomplete="current-password"
                                   placeholder="••••••••" 
                                   class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                            <span class="text-slate-600 text-[11px]">Ingat sesi masuk saya</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full flex items-center justify-center gap-1.5 rounded-lg {{ $isAdmin ? 'bg-slate-900 hover:bg-slate-800 text-white' : 'bg-red-600 hover:bg-red-700 text-white' }} py-2.5 text-xs font-bold shadow-xs transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                            </svg>
                            {{ $isAdmin ? 'Masuk ke Dashboard Petugas' : 'Masuk Sekarang' }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                @if($isAdmin)
                    Bukan petugas atau staf dealer?
                    <a href="{{ route('login') }}" class="font-bold text-red-600 hover:underline ml-1">
                        Masuk sebagai Pelanggan
                    </a>
                @else
                    <div class="space-y-1.5">
                        <div>
                            Belum memiliki akun pelanggan?
                            <a href="{{ route('register') }}" class="font-bold text-red-600 hover:underline ml-1">
                                Daftar di Sini
                            </a>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            Akses Petugas &amp; Staf Dealer?
                            <a href="{{ route('admin.login') }}" class="font-semibold text-slate-700 hover:text-red-600 hover:underline ml-0.5">
                                Masuk ke Portal Petugas
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
