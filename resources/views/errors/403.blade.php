@extends('layouts.public')

@section('title', 'Akses Ditolak (403) — Toyota AutoConnect')
@section('meta_description', 'Anda tidak memiliki hak akses yang memadai untuk membuka halaman ini di Toyota AutoConnect.')

@section('content')
<div class="min-h-[65vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-md w-full text-center bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="inline-flex h-14 w-14 items-center justify-center rounded-xl bg-red-50 text-red-600 mb-4 font-extrabold text-xl border border-red-100">
            403
        </div>
        
        <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Akses Ditolak (Forbidden)</h1>
        
        <p class="mt-2 text-xs text-slate-500 leading-relaxed">
            Akun Anda tidak memiliki izin (permission) yang memadai untuk membuka halaman atau melakukan tindakan ini. Sistem membatasi akses berdasarkan kebijakan Role-Based Access Control (RBAC).
        </p>

        <div class="mt-6 flex flex-col sm:flex-row gap-2.5 justify-center">
            @auth
                @if(auth()->user()->isCustomer())
                    <a href="{{ route('home') }}" 
                       class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Kembali ke Beranda
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}" 
                       class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Kembali ke Dashboard
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" 
                   class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Masuk ke Akun
                </a>
            @endauth

            <a href="{{ route('home') }}" 
               class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Beranda
            </a>
        </div>
    </div>
</div>
@endsection
