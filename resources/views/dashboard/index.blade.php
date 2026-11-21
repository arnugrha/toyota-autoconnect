@extends('layouts.dashboard')

@section('title', 'Dashboard Operasional — Toyota AutoConnect')
@section('header_title', 'Ringkasan Operasional')
@section('header_subtitle', 'Selamat datang kembali, ' . $user->name . ' (' . $role . ')')

@section('content')
<div class="space-y-6">

    <!-- Welcome Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-slate-900 p-6 text-white shadow-sm border border-slate-800">
        <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-red-600/20 blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-md bg-red-600/20 px-2.5 py-0.5 text-[10px] font-bold text-red-400 border border-red-500/30">
                        {{ strtoupper($role) }}
                    </span>
                    <span class="text-xs text-slate-400">Portal Operasional Toyota AutoConnect</span>
                </div>
                <h2 class="mt-2 text-lg sm:text-xl font-extrabold text-white">Halo, {{ $user->name }}</h2>
                <p class="mt-1 text-xs text-slate-400 max-w-2xl leading-relaxed">
                    Sistem siap digunakan untuk koordinasi operasional dealer, pengelolaan akun staf, dan katalog digital kendaraan resmi Toyota.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-shrink-0">
                @can('users.view')
                    <a href="{{ route('admin.users.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.059 2.772m0 0a5.972 5.972 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        Kelola Pengguna
                    </a>
                @endcan

                <a href="{{ route('vehicles.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 px-3.5 py-2 text-xs font-semibold text-slate-200 border border-slate-700 hover:bg-slate-700 hover:text-white transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Katalog Publik
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Stat 1 -->
        <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Model Mobil</p>
                <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $stats['totalVehicles'] }}</p>
                <p class="text-[10px] text-emerald-600 font-medium mt-0.5 flex items-center gap-1">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Terdaftar di sistem
                </p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75h-6m6 0a3.75 3.75 0 0 1 3.75 3.75v1.5m-9.75-5.25V3.75m0 3.75a3.75 3.75 0 0 0-3.75 3.75v1.5m0 0H18.75" />
                </svg>
            </div>
        </div>

        <!-- Stat 2 -->
        <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Unit Terpublikasi</p>
                <p class="text-xl font-extrabold text-emerald-600 mt-1">{{ $stats['publishedVehicles'] }}</p>
                <p class="text-[10px] text-slate-500 font-medium mt-0.5">Tayang di katalog publik</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
        </div>

        <!-- Stat 3 -->
        <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Staf Internal</p>
                <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $stats['totalStaff'] }}</p>
                <p class="text-[10px] text-slate-500 font-medium mt-0.5">10 Role manajemen staf</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
        </div>

        <!-- Stat 4 -->
        <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pelanggan Terdaftar</p>
                <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $stats['totalCustomers'] }}</p>
                <p class="text-[10px] text-slate-500 font-medium mt-0.5">Akun customer aktif</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.059 2.772m0 0a5.972 5.972 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- After-Sales & Service Metrics (Tahap 8) -->
    @can('service-bookings.view')
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('admin.service-bookings.index', ['status' => 'pending']) }}"
               class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm flex items-center justify-between hover:border-amber-300 transition group">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Booking Servis Menunggu</p>
                    <p class="text-xl font-extrabold text-amber-600 mt-1">{{ $stats['pendingServiceBookings'] ?? 0 }}</p>
                    <p class="text-[10px] text-slate-500 font-medium mt-0.5">Perlu konfirmasi jadwal</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </a>

            <a href="{{ route('admin.service-orders.index') }}"
               class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm flex items-center justify-between hover:border-blue-300 transition group">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Work Order Bengkel Aktif</p>
                    <p class="text-xl font-extrabold text-blue-600 mt-1">{{ $stats['activeServiceOrders'] ?? 0 }}</p>
                    <p class="text-[10px] text-slate-500 font-medium mt-0.5">Sedang dalam pengerjaan teknisi</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27-2.828-2.828 3.27-3.27a4.5 4.5 0 0 0-6.32 4.486c.118.58.094 1.193-.07 1.743" />
                    </svg>
                </div>
            </a>

            @can('support-tickets.view')
                <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}"
                   class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm flex items-center justify-between hover:border-red-300 transition group">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tiket CS Aktif</p>
                        <p class="text-xl font-extrabold text-red-600 mt-1">{{ $stats['openSupportTickets'] ?? 0 }}</p>
                        <p class="text-[10px] text-slate-500 font-medium mt-0.5">Pertanyaan &amp; keluhan pelanggan</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-600 group-hover:bg-red-600 group-hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.502 49.188 49.188 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                        </svg>
                    </div>
                </a>
            @endcan
        </div>
    @endcan

    <!-- Operational Navigation & Quick Action Hub -->
    <div class="rounded-2xl bg-white p-5 sm:p-6 border border-slate-200 shadow-2xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-red-50 text-red-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                    </span>
                    Pusat Navigasi &amp; Pintasan Operasional
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Akses cepat alur kerja harian dealer resmi Toyota AutoConnect untuk showroom, bengkel, dan manajemen transaksi.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Sesi Aktif: {{ $role }}
                </span>
            </div>
        </div>

        <!-- 6 Core Operational Modules Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- 1. Penjualan & SPK Mobil -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 hover:border-red-300 hover:bg-white transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-100 text-red-600 group-hover:bg-red-600 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sales &amp; Deals</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">Penjualan &amp; SPK Mobil</h4>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Kelola alokasi unit kendaraan, tanda jadi resmi, dan surat penawaran OTR pelanggan.
                    </p>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('admin.sales.bookings.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-lg bg-slate-900 text-[11px] font-bold text-white hover:bg-slate-800 transition">
                        Kelola SPK
                    </a>
                    <a href="{{ route('admin.sales.opportunities.index') }}" class="py-1.5 px-2.5 rounded-lg border border-slate-200 bg-white text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Peluang
                    </a>
                </div>
            </div>

            <!-- 2. Bengkel & Layanan Servis -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 hover:border-blue-300 hover:bg-white transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27-2.828-2.828 3.27-3.27a4.5 4.5 0 0 0-6.32 4.486c.118.58.094 1.193-.07 1.743" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">After-Sales</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">Bengkel &amp; Perawatan</h4>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Konfirmasi jadwal servis berkala, alokasi stall mekanik, dan perintah kerja (WO).
                    </p>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('admin.service-bookings.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-lg bg-slate-900 text-[11px] font-bold text-white hover:bg-slate-800 transition">
                        Booking Masuk
                    </a>
                    <a href="{{ route('admin.service-orders.index') }}" class="py-1.5 px-2.5 rounded-lg border border-slate-200 bg-white text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Work Orders
                    </a>
                </div>
            </div>

            <!-- 3. Inventaris & Stok Unit -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 hover:border-amber-300 hover:bg-white transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Inventory</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">Stok &amp; Inventaris Dealer</h4>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Pantau ketersediaan unit fisik di cabang dealer, mutasi masuk/keluar, dan minimum alert.
                    </p>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('admin.inventory.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-lg bg-slate-900 text-[11px] font-bold text-white hover:bg-slate-800 transition">
                        Cek Inventaris
                    </a>
                    <a href="{{ route('admin.vehicles.index') }}" class="py-1.5 px-2.5 rounded-lg border border-slate-200 bg-white text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Katalog Unit
                    </a>
                </div>
            </div>

            <!-- 4. CRM & Prospek Leads -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 hover:border-purple-300 hover:bg-white transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-100 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Customer Care</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">CRM &amp; Layanan Pelanggan</h4>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Tindak lanjuti prospek calon pembeli potensial dan tangani tiket bantuan pelanggan.
                    </p>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('admin.crm.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-lg bg-slate-900 text-[11px] font-bold text-white hover:bg-slate-800 transition">
                        Pipeline Prospek
                    </a>
                    <a href="{{ route('admin.tickets.index') }}" class="py-1.5 px-2.5 rounded-lg border border-slate-200 bg-white text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Tiket CS
                    </a>
                </div>
            </div>

            <!-- 5. Analytics & Laporan Bisnis -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 hover:border-emerald-300 hover:bg-white transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Intelligence</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">Analytics &amp; Laporan Bisnis</h4>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Pantau grafik tren penjualan, rasio konversi dealer, dan unduh laporan transaksi CSV.
                    </p>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('admin.analytics.dashboard') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-lg bg-slate-900 text-[11px] font-bold text-white hover:bg-slate-800 transition">
                        Dashboard BI
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="py-1.5 px-2.5 rounded-lg border border-slate-200 bg-white text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Unduh Laporan
                    </a>
                </div>
            </div>

            <!-- 6. Pengaturan & Master Data -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 hover:border-slate-400 hover:bg-white transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-200 text-slate-700 group-hover:bg-slate-800 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Settings</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">Sistem &amp; Master Data</h4>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Kelola parameter sistem, cabang dealer, saluran Midtrans, dan catatan log audit.
                    </p>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('admin.settings.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-lg bg-slate-900 text-[11px] font-bold text-white hover:bg-slate-800 transition">
                        Pengaturan
                    </a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="py-1.5 px-2.5 rounded-lg border border-slate-200 bg-white text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Audit Logs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-Time Activity Grid (Incoming SPK & Service Bookings) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 1. Pemesanan Mobil (SPK) Terbaru -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-900">Pemesanan Mobil (SPK) Terbaru</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Transaksi pemesanan unit kendaraan yang baru masuk</p>
                </div>
                <a href="{{ route('admin.sales.bookings.index') }}" class="text-[11px] font-semibold text-red-600 hover:text-red-700 transition flex items-center gap-1">
                    Semua SPK &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[560px]">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[130px]">No. SPK</th>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Pembeli</th>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Unit Mobil</th>
                            <th class="py-3 px-4 whitespace-nowrap text-center min-w-[100px]">Status</th>
                            <th class="py-3 px-4 whitespace-nowrap text-right min-w-[60px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentBookings as $booking)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 whitespace-nowrap font-mono font-bold text-slate-900">
                                    {{ $booking->code }}
                                    <span class="block font-sans text-[10px] text-slate-400 font-normal">{{ $booking->created_at->format('d/m/Y') }}</span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-900 block">{{ $booking->customer?->name ?? 'N/A' }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $booking->branch?->name ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800 block">{{ $booking->vehicle?->name ?? '-' }}</span>
                                    <span class="font-mono text-[11px] text-slate-900 block font-bold">Rp {{ number_format($booking->net_price, 0, ',', '.') }}</span>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $booking->status_badge_class }}">
                                        {{ $booking->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.sales.bookings.show', $booking) }}" class="inline-flex items-center gap-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 text-[11px] font-bold transition">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-xs text-slate-400 italic">
                                    Belum ada pemesanan unit kendaraan terbaru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Booking Servis Bengkel Terbaru -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-900">Booking Servis Bengkel Terbaru</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Reservasi servis pelanggan yang baru diajukan</p>
                </div>
                <a href="{{ route('admin.service-bookings.index') }}" class="text-[11px] font-semibold text-red-600 hover:text-red-700 transition flex items-center gap-1">
                    Semua Booking &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[560px]">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[130px]">Kode Booking</th>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Pelanggan &amp; Plat</th>
                            <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Layanan &amp; Jadwal</th>
                            <th class="py-3 px-4 whitespace-nowrap text-center min-w-[100px]">Status</th>
                            <th class="py-3 px-4 whitespace-nowrap text-right min-w-[60px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentServiceBookings as $sb)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 whitespace-nowrap font-mono font-bold text-slate-900">
                                    {{ $sb->booking_code }}
                                    <span class="block font-sans text-[10px] text-slate-400 font-normal">{{ $sb->created_at->format('d/m/Y') }}</span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-900 block">{{ $sb->user?->name ?? 'N/A' }}</span>
                                    <span class="text-[10px] text-slate-500 font-mono block">{{ $sb->license_plate }}</span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800 block">{{ $sb->serviceType?->name ?? 'Servis Berkala' }}</span>
                                    <span class="text-[10px] text-slate-500 block">{{ \Carbon\Carbon::parse($sb->booking_date)->translatedFormat('d M Y') }} &bull; {{ $sb->booking_time }} WIB</span>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $sb->status_badge_class }}">
                                        {{ $sb->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.service-bookings.show', $sb) }}" class="inline-flex items-center gap-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 text-[11px] font-bold transition">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-xs text-slate-400 italic">
                                    Belum ada booking servis bengkel terbaru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Operational Tables / Recent Catalog Items -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-900">Kendaraan Terbaru dalam Database</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Daftar model kendaraan resmi yang tersimpan dalam sistem katalog</p>
            </div>
            <a href="{{ route('vehicles.index') }}" class="text-[11px] font-semibold text-red-600 hover:text-red-700 transition flex items-center gap-1">
                Katalog Lengkap
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[850px]">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[180px]">Model Kendaraan</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Kategori &amp; Segmen</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[140px]">Rentang Harga OTR</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[140px]">Transmisi</th>
                        <th class="py-3 px-4 whitespace-nowrap text-center min-w-[100px]">Status</th>
                        <th class="py-3 px-4 whitespace-nowrap text-right min-w-[90px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentVehicles as $vehicle)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $vehicle->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $vehicle->slug }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-block rounded bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-700">
                                    {{ $vehicle->category }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($vehicle->price_min, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                {{ $vehicle->transmission ?? 'Manual / Otomatis' }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if ($vehicle->is_published)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 border border-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('vehicles.show', $vehicle->slug) }}" target="_blank"
                                   class="text-[11px] font-semibold text-slate-600 hover:text-red-600 transition">
                                    Pratinjau &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-xs text-slate-400">
                                Belum ada data kendaraan dalam database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
