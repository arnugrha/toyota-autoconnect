<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100 text-slate-800 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard — Toyota AutoConnect')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full flex flex-col font-sans text-xs sm:text-sm bg-slate-100" x-data="{ sidebarOpen: false, userMenuOpen: false }">

    <div class="flex h-screen overflow-hidden">

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             x-cloak 
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden transition-opacity"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:z-auto border-r border-slate-800 flex-shrink-0">
            
            <!-- Sidebar Header -->
            <div class="h-16 flex items-center justify-between px-5 bg-slate-950 border-b border-slate-800/80">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-600 text-white font-extrabold text-sm shadow-sm">
                        T
                    </div>
                    <div>
                        <span class="block text-xs font-extrabold tracking-tight text-white">TOYOTA <span class="font-normal text-slate-400">AutoConnect</span></span>
                        <span class="block text-[9px] font-semibold text-red-400 tracking-wider uppercase">
                            Internal Dealer
                        </span>
                    </div>
                </a>
                <button type="button" @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Sidebar Navigation Links -->
            <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">

                <!-- Main Section -->
                <div>
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Utama</p>
                    <nav class="space-y-1">
                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                            <span>Ringkasan Dashboard</span>
                        </a>
                    </nav>
                </div>

                <!-- Admin Section (Only for Super Admin / users.view) -->
                @can('users.view')
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Administrasi Sistem</p>
                        <nav class="space-y-1">
                            <a href="{{ route('admin.users.index') }}"
                               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                <span>Manajemen Pengguna</span>
                            </a>

                            @can('notifications.view')
                                <a href="{{ route('admin.notifications.monitoring') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.notifications.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                        </svg>
                                        <span>Monitoring Notifikasi</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.notifications.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-amber-400' }}">Log</span>
                                </a>
                            @endcan


                        </nav>
                    </div>
                @endcan

                <!-- Operational Section for Internal Roles -->
                <div>
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Penjualan &amp; Test Drive</p>
                        <nav class="space-y-1">
                            @can('test-drives.view')
                                <a href="{{ route('admin.test-drives.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.test-drives.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span>Jadwal Test Drive</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.test-drives.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-purple-400' }}">Kalender</span>
                                </a>
                            @endcan



                            @can('sales.view')
                                <a href="{{ route('admin.sales.opportunities.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.sales.opportunities.*') || request()->routeIs('admin.sales.offers.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                        </svg>
                                        <span>Pipeline Penjualan</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.sales.opportunities.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-amber-400' }}">Pipeline</span>
                                </a>

                                <a href="{{ route('admin.sales.bookings.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.sales.bookings.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span>Pemesanan Unit (SPK)</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.sales.bookings.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-emerald-400' }}">Alokasi</span>
                                </a>
                            @endcan


                        </nav>
                    </div>

                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Keuangan &amp; Pembiayaan</p>
                        <nav class="space-y-1">
                            @can('financing.view')
                                <a href="{{ route('admin.leasing.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.leasing.index') || request()->routeIs('admin.leasing.show') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span>Pembiayaan &amp; Leasing</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.leasing.index') || request()->routeIs('admin.leasing.show') ? 'bg-red-800 text-white' : 'bg-slate-800 text-blue-400' }}">Kredit</span>
                                </a>

                                <a href="{{ route('admin.leasing.providers') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.leasing.providers*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                    </svg>
                                    <span>Mitra Lembaga Leasing</span>
                                </a>
                            @endcan



                            @can('payments.view')
                                <a href="{{ route('admin.payments.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.payments.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                        </svg>
                                        <span>Transaksi Pembayaran</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.payments.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-emerald-400' }}">Midtrans</span>
                                </a>
                            @endcan


                        </nav>
                    </div>

                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Operasional Dealer</p>
                        <nav class="space-y-1">
                            @can('vehicles.view')
                                <a href="{{ route('admin.vehicles.index') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.vehicles.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75h-6m6 0a3.75 3.75 0 0 1 3.75 3.75v1.5m-9.75-5.25V3.75m0 3.75a3.75 3.75 0 0 0-3.75 3.75v1.5m0 0H18.75" />
                                    </svg>
                                    <span>Master Kendaraan</span>
                                </a>

                                <a href="{{ route('admin.categories.index') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.categories.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                                    </svg>
                                    <span>Kategori Mobil</span>
                                </a>
                            @endcan



                            @can('inventory.view')
                                <a href="{{ route('admin.inventory.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.inventory.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                        </svg>
                                        <span>Stok &amp; Inventory</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.inventory.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-emerald-400' }}">Cabang</span>
                                </a>
                            @endcan



                            @can('settings.manage')
                                <a href="{{ route('admin.branches.index') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.branches.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72m-13.5 0c.315.159.66.26 1.02.298m0 0c.36-.038.705-.139 1.02-.298" />
                                    </svg>
                                    <span>Cabang Dealer</span>
                                </a>

                                <a href="{{ route('admin.service-types.index') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.service-types.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27-2.828-2.828 3.27-3.27a4.5 4.5 0 0 0-6.32 4.486c.118.58.094 1.193-.07 1.743" />
                                    </svg>
                                    <span>Layanan Servis</span>
                                </a>

                                <a href="{{ route('admin.lead-sources.index') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.lead-sources.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
                                    </svg>
                                    <span>Sumber Lead CRM</span>
                                </a>

                                <a href="{{ route('admin.payment-methods.index') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.payment-methods.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                    </svg>
                                    <span>Metode Pembayaran</span>
                                </a>
                            @endcan



                            @can('leads.view')
                                <a href="{{ route('admin.crm.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.crm.*') || request()->routeIs('admin.leads.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.059 2.772m0 0a5.972 5.972 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                        </svg>
                                        <span>CRM &amp; Prospek Lead</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.crm.*') || request()->routeIs('admin.leads.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-emerald-400' }}">Aktif</span>
                                </a>
                            @endcan


                        </nav>
                    </div>

                    <!-- Bengkel & After-Sales (Tahap 8) -->
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Bengkel &amp; After-Sales</p>
                        <nav class="space-y-1">
                            @can('service-bookings.view')
                                <a href="{{ route('admin.service-bookings.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.service-bookings.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span>Pemesanan Servis</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.service-bookings.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-amber-400' }}">Booking</span>
                                </a>
                            @endcan



                            @can('service-orders.view')
                                <a href="{{ route('admin.service-orders.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.service-orders.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27-2.828-2.828 3.27-3.27a4.5 4.5 0 0 0-6.32 4.486c.118.58.094 1.193-.07 1.743" />
                                        </svg>
                                        <span>Perintah Kerja (WO)</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.service-orders.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-emerald-400' }}">9 Tahap</span>
                                </a>
                            @endcan



                            @can('service-schedules.manage')
                                <a href="{{ route('admin.service-schedules.index') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.service-schedules.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                    </svg>
                                    <span>Jadwal &amp; Kapasitas</span>
                                </a>
                            @endcan



                            @can('service-orders.manage')
                                <a href="{{ route('admin.service-reminders.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.service-reminders.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                        </svg>
                                        <span>Pengingat Servis</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.service-reminders.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-blue-400' }}">Otomatis</span>
                                </a>
                            @endcan

                            @can('spare-parts.view')
                                <a href="{{ route('admin.spare-parts.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.spare-parts.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                        <span>Kelola Sparepart</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.spare-parts.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-sky-400' }}">Stok</span>
                                </a>

                                <a href="{{ route('admin.spare-part-procurements.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.spare-part-procurements.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                        </svg>
                                        <span>Pengadaan Sparepart</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.spare-part-procurements.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-emerald-400' }}">+Stok</span>
                                </a>
                            @endcan


                        </nav>
                    </div>

                    <!-- Layanan Pelanggan (CS) (Tahap 8) -->
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Layanan Pelanggan (CS)</p>
                        <nav class="space-y-1">
                            @can('support-tickets.view')
                                <a href="{{ route('admin.tickets.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.tickets.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.502 49.188 49.188 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                        </svg>
                                        <span>Pusat Tiket CS</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.tickets.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-purple-400' }}">Helpdesk</span>
                                </a>
                            @endcan


                        </nav>
                    </div>

                    <!-- Analytics, BI & Pelaporan (Tahap 10) -->
                    @can('analytics.view')
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Analytics &amp; Business Intelligence</p>
                        <nav class="space-y-1">
                            @if(auth()->user()->hasRole(['Super Admin', 'Dealer Manager', 'Owner']))
                                <a href="{{ route('admin.owner.revenue') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.owner.revenue') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span>Monitoring Pendapatan</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.owner.revenue') ? 'bg-red-800 text-white' : 'bg-slate-800 text-emerald-400' }}">Omzet</span>
                                </a>
                            @endif

                            <a href="{{ route('admin.analytics.dashboard') }}"
                               class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.analytics.dashboard') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center gap-2.5">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                                    </svg>
                                    <span>Dashboard Eksekutif</span>
                                </div>
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.analytics.dashboard') ? 'bg-red-800 text-white' : 'bg-slate-800 text-red-400' }}">BI</span>
                            </a>

                            @if(!auth()->user()->hasRole(['Inventory Officer', 'Service Advisor']))
                                <a href="{{ route('admin.analytics.sales') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.analytics.sales') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                                    </svg>
                                    <span>Analisis Penjualan</span>
                                </a>
                            @endif

                            @if(!auth()->user()->hasRole(['Inventory Officer', 'Service Advisor', 'Finance Officer']))
                                <a href="{{ route('admin.analytics.crm') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.analytics.crm') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.41A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                                    </svg>
                                    <span>CRM &amp; Funnel Leads</span>
                                </a>
                            @endif

                            @if(auth()->user()->hasRole(['Super Admin', 'Dealer Manager', 'Owner']))
                                <a href="{{ route('admin.analytics.dealers') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.analytics.dealers') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5" />
                                    </svg>
                                    <span>Komparasi Dealer</span>
                                </a>
                            @endif

                            @if(!auth()->user()->hasRole(['Service Advisor', 'CRM Officer']))
                                <a href="{{ route('admin.analytics.inventory') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.analytics.inventory') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                                    </svg>
                                    <span>Analisis Inventaris</span>
                                </a>
                            @endif

                            @if(!auth()->user()->hasRole(['Service Advisor', 'Inventory Officer', 'Sales Executive']))
                                <a href="{{ route('admin.analytics.payments') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.analytics.payments') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                    </svg>
                                    <span>Analisis Pembayaran</span>
                                </a>
                            @endif

                            @if(!auth()->user()->hasRole(['Sales Executive', 'Inventory Officer']))
                                <a href="{{ route('admin.analytics.after-sales') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.analytics.after-sales') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27-2.828-2.828 3.27-3.27a4.5 4.5 0 0 0-6.32 4.486c.118.58.094 1.193-.07 1.743" />
                                    </svg>
                                    <span>Analisis Bengkel</span>
                                </a>
                            @endif

                            @can('reports.view')
                                <a href="{{ route('admin.reports.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.reports.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        <span>Pusat Laporan (CSV)</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.reports.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-emerald-400' }}">Ekspor</span>
                                </a>
                            @endcan


                        </nav>
                    </div>
                    @endcan

                    <!-- Pengaturan & Audit Trail (Tahap 11) -->
                    @if(auth()->user()->can('settings.manage') || auth()->user()->can('audit.view'))
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Sistem &amp; Keamanan</p>
                        <nav class="space-y-1">
                            @can('settings.manage')
                                <a href="{{ route('admin.settings.index') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.settings.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <span>Pengaturan Sistem</span>
                                </a>
                            @endcan

                            @can('audit.view')
                                <a href="{{ route('admin.audit-logs.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.audit-logs.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                        </svg>
                                        <span>Catatan Audit Trail</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ request()->routeIs('admin.audit-logs.*') ? 'bg-red-800 text-white' : 'bg-slate-800 text-amber-400' }}">Log</span>
                                </a>
                            @endcan
                        </nav>
                    </div>
                    @endif

                <!-- Navigation to Public Website -->
                <div>
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Akses Cepat</p>
                    <nav class="space-y-1">
                        <a href="{{ route('home') }}" target="_blank"
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition">
                            <div class="flex items-center gap-2.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                <span>Lihat Website Publik</span>
                            </div>
                            <svg class="h-3 w-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Sidebar User Quick Info & Logout -->
            <div class="p-3 bg-slate-950 border-t border-slate-800">
                <div class="flex items-center justify-between">
                    <a href="{{ route('profile.edit') }}" title="Pengaturan Profil" class="flex items-center gap-2.5 overflow-hidden hover:opacity-80 transition flex-1">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-slate-800 text-xs font-bold text-white border border-slate-700">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="truncate">
                            <p class="truncate text-xs font-bold text-white">{{ auth()->user()->name }}</p>
                            <p class="truncate text-[10px] text-red-400 font-medium">{{ auth()->user()->role_name }}</p>
                        </div>
                    </a>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('profile.edit') }}#security" title="Ubah Kata Sandi"
                           class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Keluar dari Akun"
                                    class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-red-400 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            
            <!-- Top Navbar -->
            <header class="h-16 flex items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6 z-10 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <button type="button" @click="sidebarOpen = true" class="lg:hidden text-slate-600 hover:text-slate-900 p-1.5 rounded-md hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">@yield('header_title', 'Dashboard')</h1>
                        <p class="text-[11px] text-slate-500 hidden sm:block">@yield('header_subtitle', 'Selamat datang di sistem manajemen Toyota AutoConnect')</p>
                    </div>
                </div>

                <!-- Top Navbar Right -->
                <div class="flex items-center gap-3">
                    <!-- Notification Bell Component -->
                    <x-notification-bell />

                    <!-- Role Badge -->
                    @if (auth()->user()->hasRole('Owner'))
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-extrabold bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Executive Owner
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                            {{ auth()->user()->role_name }}
                        </span>
                    @endif

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open" @click.outside="open = false"
                                class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition focus:outline-none">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-[11px] font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <span class="text-xs font-semibold text-slate-800 hidden md:inline">{{ auth()->user()->name }}</span>
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div x-show="open" x-cloak
                             class="absolute right-0 mt-2 w-48 rounded-xl bg-white p-1.5 shadow-lg border border-slate-200 z-50 text-xs">
                            <div class="px-3 py-2 border-b border-slate-100">
                                <p class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                Pengaturan Profil
                            </a>
                            <a href="{{ route('profile.edit') }}#security"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 transition">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                                Ubah Kata Sandi
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 transition text-left">
                                    <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Page Content Scrollable -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                
                <!-- Flash Notification Banners -->
                @if (session('success') || session('status'))
                    <div x-data="{ show: true }" x-show="show" x-transition.duration.200ms
                         class="mb-5 flex items-center justify-between gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-3.5 text-xs text-emerald-800 shadow-2xs">
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
                @endif

                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition.duration.200ms
                         class="mb-5 flex items-center justify-between gap-2.5 rounded-xl bg-red-50 border border-red-200 p-3.5 text-xs text-red-800 shadow-2xs">
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
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
