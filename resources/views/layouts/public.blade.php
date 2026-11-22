<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 text-slate-800 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Toyota AutoConnect — Dealer Resmi & Katalog Kendaraan')</title>
    <meta name="description" content="@yield('meta_description', 'Jelajahi lini kendaraan Toyota terlengkap, spesifikasi teknis mendalam, estimasi harga OTR, dan ketersediaan unit di Toyota AutoConnect.')">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('title', 'Toyota AutoConnect — Dealer Resmi & Katalog Kendaraan')">
    <meta property="og:description" content="@yield('meta_description', 'Jelajahi lini kendaraan Toyota terlengkap di Toyota AutoConnect.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="flex min-h-full flex-col font-sans text-xs sm:text-sm" x-data="{ mobileMenuOpen: false, modalInfoOpen: false, modalMessage: '', modalTitle: '' }">

    @unless(request()->routeIs('login', 'register', 'admin.login'))
    <!-- Top Announcement Bar -->
    <div class="border-b border-slate-200 bg-slate-900 px-4 py-1.5 text-[11px] font-medium text-slate-300">
        <div class="mx-auto flex max-w-7xl items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-600/20 px-2 py-0.5 text-[10px] font-bold text-red-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    SHOWROOM RESMI
                </span>
                <span class="hidden sm:inline text-slate-400">Layanan Penjualan &amp; Informasi Unit Toyota Indonesia</span>
            </div>
            <div class="flex items-center gap-3 text-[11px]">
                <a href="{{ route('contact') }}" class="transition hover:text-white">Senin - Sabtu: 08.30 - 17.00 WIB</a>
                <span class="text-slate-600">|</span>
                <a href="{{ route('contact') }}" class="font-semibold text-white transition hover:text-red-400 flex items-center gap-1">
                    <svg class="h-3 w-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                    Hubungi Dealer
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
            <!-- Brand Logotype -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group focus:outline-none">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-600 text-white font-extrabold text-base shadow-xs transition group-hover:bg-red-700">
                    T
                </div>
                <div class="flex flex-col">
                    <span class="text-sm sm:text-base font-bold tracking-tight text-slate-950 group-hover:text-red-600 transition leading-tight">
                        TOYOTA <span class="font-medium text-slate-600">AutoConnect</span>
                    </span>
                    <span class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">Official Dealer Partner</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('home') ? 'text-red-600 bg-red-50/70' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-100/70' }}">
                    Beranda
                </a>
                <a href="{{ route('vehicles.index') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('vehicles.*') ? 'text-red-600 bg-red-50/70' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-100/70' }}">
                    Katalog Kendaraan
                </a>
                <a href="{{ route('promo') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('promo') ? 'text-red-600 bg-red-50/70' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-100/70' }}">
                    Program Promo
                </a>
                <a href="{{ route('about') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('about') ? 'text-red-600 bg-red-50/70' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-100/70' }}">
                    Tentang Kami
                </a>
                <a href="{{ route('contact') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('contact') ? 'text-red-600 bg-red-50/70' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-100/70' }}">
                    Kontak Showroom
                </a>
                @if(auth()->check() && auth()->user()->isCustomer())
                    <a href="{{ route('customer.dashboard') }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('customer.*') ? 'text-red-600 bg-red-50/70 font-bold' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-100/70' }}">
                        Portal Pelanggan
                    </a>
                @endif
            </nav>

            <!-- Header Action Buttons -->
            <div class="hidden sm:flex items-center gap-2">
                @guest
                    <a href="{{ route('login') }}" 
                       class="px-3 py-1.5 text-xs font-semibold text-slate-700 hover:text-red-600 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" 
                       class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-800 hover:border-red-600 hover:text-red-600 transition">
                        Daftar
                    </a>
                @else
                    <!-- Notification Bell Component -->
                    <x-notification-bell />

                    @if(auth()->user()->isCustomer())
                        <!-- Customer Account Dropdown -->
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button type="button" @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900 text-xs font-semibold text-white hover:bg-slate-800 transition focus:outline-none">
                                <div class="flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <span>{{ Str::limit(auth()->user()->name, 15) }}</span>
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div x-show="userMenuOpen" x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 rounded-xl bg-white p-1.5 shadow-xl border border-slate-200 z-50 text-xs">
                                <div class="px-3 py-2 border-b border-slate-100">
                                    <p class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                    <span class="inline-flex items-center gap-1 mt-1 rounded bg-red-50 px-1.5 py-0.5 text-[10px] font-bold text-red-600">
                                        Pelanggan Terdaftar
                                    </span>
                                </div>

                                <div class="py-1 space-y-0.5">
                                    <a href="{{ route('customer.dashboard') }}"
                                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-red-600 transition font-medium">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-13.5 18v-2.25Z" />
                                        </svg>
                                        Ringkasan Akun
                                    </a>

                                    <a href="{{ route('customer.test-drives.index') }}"
                                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-red-600 transition font-medium">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        Jadwal Test Drive
                                    </a>

                                    <a href="{{ route('customer.bookings.index') }}"
                                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-red-600 transition font-medium">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        Pemesanan Mobil (SPK)
                                    </a>

                                    <a href="{{ route('customer.offers.index') }}"
                                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-red-600 transition font-medium">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        Penawaran Unit
                                    </a>

                                    <a href="{{ route('customer.inquiries.index') }}"
                                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-red-600 transition font-medium">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.84-.84c.15-.658.334-1.304.544-1.933A7.472 7.472 0 0 1 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                        </svg>
                                        Inquiry &amp; Konsultasi
                                    </a>

                                    <a href="{{ route('notifications.index') }}"
                                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-red-600 transition font-medium">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                        </svg>
                                        Pusat Notifikasi
                                    </a>

                                    <a href="{{ route('customer.notifications.preferences') }}"
                                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-red-600 transition font-medium">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                        </svg>
                                        Pengaturan Notifikasi
                                    </a>

                                    <a href="{{ route('profile.edit') }}"
                                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-red-600 transition font-medium">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                        Profil Saya
                                    </a>

                                    <a href="{{ route('profile.edit') }}#security"
                                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-red-600 transition font-medium">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                        Ubah Kata Sandi
                                    </a>
                                </div>

                                <div class="pt-1 border-t border-slate-100">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 transition text-left font-medium">
                                            <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                            </svg>
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Staff Dashboard Button -->
                        <a href="{{ route('admin.dashboard') }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 text-xs font-semibold text-white hover:bg-slate-800 transition">
                            <svg class="h-3.5 w-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                            Panel Admin
                        </a>
                    @endif
                @endguest
            </div>

            <!-- Mobile Hamburger Button -->
            <div class="flex md:hidden">
                <button type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="inline-flex items-center justify-center rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-slate-950 focus:outline-none"
                        aria-label="Buka menu navigasi">
                    <svg x-show="!mobileMenuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileMenuOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="border-b border-slate-200 bg-white px-4 pt-2 pb-5 md:hidden max-h-[calc(100vh-4rem)] overflow-y-auto">
            <div class="flex flex-col space-y-1">
                <a href="{{ route('home') }}" 
                   class="rounded-lg px-3 py-2 text-xs font-semibold transition {{ request()->routeIs('home') ? 'bg-red-50 text-red-600' : 'text-slate-800 hover:bg-slate-100' }}">
                    Beranda
                </a>
                <a href="{{ route('vehicles.index') }}" 
                   class="rounded-lg px-3 py-2 text-xs font-semibold transition {{ request()->routeIs('vehicles.*') ? 'bg-red-50 text-red-600' : 'text-slate-800 hover:bg-slate-100' }}">
                    Katalog Kendaraan
                </a>
                <a href="{{ route('promo') }}" 
                   class="rounded-lg px-3 py-2 text-xs font-semibold transition {{ request()->routeIs('promo') ? 'bg-red-50 text-red-600' : 'text-slate-800 hover:bg-slate-100' }}">
                    Program Promo
                </a>
                <a href="{{ route('about') }}" 
                   class="rounded-lg px-3 py-2 text-xs font-semibold transition {{ request()->routeIs('about') ? 'bg-red-50 text-red-600' : 'text-slate-800 hover:bg-slate-100' }}">
                    Tentang Kami
                </a>
                <a href="{{ route('contact') }}" 
                   class="rounded-lg px-3 py-2 text-xs font-semibold transition {{ request()->routeIs('contact') ? 'bg-red-50 text-red-600' : 'text-slate-800 hover:bg-slate-100' }}">
                    Kontak Showroom
                </a>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex flex-col gap-2">
                @guest
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="w-full text-center rounded-lg border border-slate-300 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="w-full text-center rounded-lg bg-red-600 py-2 text-xs font-semibold text-white shadow-xs hover:bg-red-700 transition">
                            Daftar Akun
                        </a>
                    </div>
                @else
                    @if(auth()->user()->isCustomer())
                        <div class="rounded-xl bg-slate-50 p-3 border border-slate-200/80 space-y-2">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-200">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-red-600 text-[11px] font-bold text-white">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <div class="truncate">
                                    <p class="font-bold text-slate-900 text-xs truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                            <div class="space-y-1 pt-1">
                                <a href="{{ route('customer.dashboard') }}"
                                   class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 hover:bg-white hover:text-red-600 transition">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-13.5 18v-2.25Z" />
                                    </svg>
                                    Ringkasan Akun
                                </a>
                                <a href="{{ route('customer.test-drives.index') }}"
                                   class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 hover:bg-white hover:text-red-600 transition">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Jadwal Test Drive
                                </a>
                                <a href="{{ route('customer.bookings.index') }}"
                                   class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 hover:bg-white hover:text-red-600 transition">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Pemesanan Mobil (SPK)
                                </a>
                                <a href="{{ route('customer.offers.index') }}"
                                   class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 hover:bg-white hover:text-red-600 transition">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    Penawaran Unit
                                </a>
                                <a href="{{ route('customer.inquiries.index') }}"
                                   class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 hover:bg-white hover:text-red-600 transition">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.84-.84c.15-.658.334-1.304.544-1.933A7.472 7.472 0 0 1 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                    </svg>
                                    Inquiry &amp; Konsultasi
                                </a>
                                <a href="{{ route('profile.edit') }}"
                                   class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 hover:bg-white hover:text-red-600 transition">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    Profil Saya
                                </a>
                                <a href="{{ route('profile.edit') }}#security"
                                   class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 hover:bg-white hover:text-red-600 transition">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                    </svg>
                                    Ubah Kata Sandi
                                </a>
                            </div>
                            <div class="pt-2 border-t border-slate-200">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full text-center rounded-lg bg-red-50 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 transition">
                                        Keluar dari Akun
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('admin.dashboard') }}" class="w-full text-center rounded-lg bg-slate-900 py-2 text-xs font-semibold text-white shadow-xs hover:bg-slate-800 transition">
                            Buka Panel Admin ({{ auth()->user()->role_name }})
                        </a>
                    @endif
                @endguest
            </div>
        </div>
    </header>
    @endunless

    <!-- Main Content Slot -->
    <main class="flex-1">
        @if (session('success') || session('status'))
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-4">
                <div x-data="{ show: true }" x-show="show" x-transition.duration.200ms
                     class="flex items-center justify-between gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-3.5 text-xs text-emerald-800 shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>{{ session('success') ?? session('status') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-800 p-0.5 rounded transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-4">
                <div x-data="{ show: true }" x-show="show" x-transition.duration.200ms
                     class="flex items-center justify-between gap-2.5 rounded-xl bg-red-50 border border-red-200 p-3.5 text-xs text-red-800 shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-red-600 hover:text-red-800 p-0.5 rounded transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Informational Modal -->
    <div x-show="modalInfoOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         aria-modal="true" 
         role="dialog">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
             @click="modalInfoOpen = false"></div>

        <!-- Dialog Card -->
        <div class="relative w-full max-w-sm transform overflow-hidden rounded-2xl bg-white p-5 shadow-xl transition-all border border-slate-100">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900" x-text="modalTitle || 'Informasi Layanan'"></h3>
                    <p class="text-[10px] text-slate-500">Toyota AutoConnect Platform</p>
                </div>
            </div>

            <div class="mt-3 text-xs leading-relaxed text-slate-600" x-text="modalMessage">
            </div>

            <div class="mt-5 flex items-center justify-end gap-2">
                <a href="{{ route('contact') }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Hubungi Showroom
                </a>
                <button type="button" 
                        @click="modalInfoOpen = false" 
                        class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    @unless(request()->routeIs('login', 'register', 'admin.login'))
    <!-- Official Automotive Footer -->
    <footer class="border-t border-slate-200 bg-white text-slate-600">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-5 text-xs">
                <!-- Col 1: Brand Info -->
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-600 text-white font-extrabold text-sm">
                            T
                        </div>
                        <span class="text-sm font-bold text-slate-950">
                            TOYOTA <span class="font-medium text-slate-600">AutoConnect</span>
                        </span>
                    </div>
                    <p class="mt-3 max-w-sm text-xs leading-relaxed text-slate-500">
                        Platform digital dealer resmi Toyota untuk memberikan transparansi informasi kendaraan, kemudahan akses spesifikasi, dan estimasi harga unit di seluruh lini model Toyota.
                    </p>
                    <div class="mt-4 flex items-center gap-4 text-[11px] font-medium text-slate-500">
                        <span class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Unit Bergaransi Resmi
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Jaringan Servis Nasional
                        </span>
                    </div>
                </div>

                <!-- Col 2: Kategori Populer -->
                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-900">Kategori Kendaraan</h3>
                    <ul class="mt-3 space-y-2 text-xs">
                        <li><a href="{{ route('vehicles.index', ['category' => 'MPV']) }}" class="transition hover:text-red-600">MPV Keluarga</a></li>
                        <li><a href="{{ route('vehicles.index', ['category' => 'SUV']) }}" class="transition hover:text-red-600">SUV Tangguh</a></li>
                        <li><a href="{{ route('vehicles.index', ['category' => 'Hatchback']) }}" class="transition hover:text-red-600">Hatchback Urban</a></li>
                        <li><a href="{{ route('vehicles.index', ['category' => 'Sedan']) }}" class="transition hover:text-red-600">Sedan Premium</a></li>
                        <li><a href="{{ route('vehicles.index', ['category' => 'Commercial']) }}" class="transition hover:text-red-600">Komersial &amp; Niaga</a></li>
                    </ul>
                </div>

                <!-- Col 3: Navigasi Cepat -->
                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-900">Informasi Dealer</h3>
                    <ul class="mt-3 space-y-2 text-xs">
                        <li><a href="{{ route('home') }}" class="transition hover:text-red-600">Beranda</a></li>
                        <li><a href="{{ route('vehicles.index') }}" class="transition hover:text-red-600">Katalog Lengkap</a></li>
                        <li><a href="{{ route('promo') }}" class="transition hover:text-red-600">Program Penjualan</a></li>
                        <li><a href="{{ route('about') }}" class="transition hover:text-red-600">Tentang Showroom</a></li>
                        <li><a href="{{ route('contact') }}" class="transition hover:text-red-600">Hubungi Konsultan</a></li>
                        <li><a href="{{ route('admin.login') }}" class="transition hover:text-red-600 font-semibold text-slate-500">Portal Petugas &amp; Staf</a></li>
                    </ul>
                </div>

                <!-- Col 4: Kontak & Jam Operasional -->
                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-900">Showroom &amp; Bengkel</h3>
                    <div class="mt-3 space-y-2 text-xs text-slate-500">
                        <p class="leading-relaxed">
                            <strong class="font-semibold text-slate-700">Toyota AutoConnect Hub</strong><br>
                            Jl. Raya Otomotif No. 88, Jakarta
                        </p>
                        <p class="text-[11px]">
                            <span class="font-semibold text-slate-700">Telepon Layanan:</span><br>
                            (021) 5080-TOYOTA
                        </p>
                        <p class="text-[10px]">
                            <span class="font-semibold text-slate-700">Jam Operasional:</span><br>
                            Senin - Sabtu: 08:30 - 17:00 WIB<br>
                            Minggu: 09:00 - 15:00 WIB
                        </p>
                    </div>
                </div>
            </div>

            <!-- Disclaimer & Copyright -->
            <div class="mt-8 border-t border-slate-200 pt-6 flex flex-col md:flex-row items-center justify-between gap-3 text-[11px] text-slate-400">
                <p>
                    &copy; {{ date('Y') }} Toyota AutoConnect. Seluruh hak cipta dilindungi. Spesifikasi dan estimasi harga sewaktu-waktu dapat disesuaikan.
                </p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('about') }}" class="hover:text-slate-600 transition">Kebijakan Privasi</a>
                    <a href="{{ route('about') }}" class="hover:text-slate-600 transition">Syarat &amp; Ketentuan</a>
                    <a href="{{ route('contact') }}" class="hover:text-slate-600 transition">Bantuan Konsumen</a>
                </div>
            </div>
        </div>
    </footer>
    @endunless

    @stack('scripts')
</body>
</html>
