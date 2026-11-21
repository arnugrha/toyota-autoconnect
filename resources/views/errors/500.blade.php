@extends('layouts.public')

@section('title', 'Kendala Server (500) — Toyota AutoConnect')
@section('meta_description', 'Terjadi kesalahan sistem saat memproses permintaan Anda. Tim kami telah mencatat kendala ini.')

@section('content')
<div class="min-h-[65vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-md w-full text-center bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="inline-flex h-14 w-14 items-center justify-center rounded-xl bg-red-100 text-red-700 mb-4 font-extrabold text-xl border border-red-200">
            500
        </div>
        
        <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Terjadi Kendala pada Server</h1>
        
        <p class="mt-2 text-xs text-slate-500 leading-relaxed">
            Mohon maaf atas ketidaknyamanannya. Terjadi kesalahan tak terduga saat memproses permintaan Anda. Tim teknis Toyota AutoConnect telah mencatat insiden ini untuk segera ditangani.
        </p>

        <div class="mt-6 flex flex-col sm:flex-row gap-2.5 justify-center">
            @auth
                @if(auth()->user()->isCustomer())
                    <a href="{{ route('customer.dashboard') }}" 
                       class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Dashboard Pelanggan
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}" 
                       class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Dashboard Petugas
                    </a>
                @endif
            @else
                <a href="{{ route('home') }}" 
                   class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Kembali ke Beranda
                </a>
            @endauth

            <button onclick="window.location.reload();" 
                    type="button"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Coba Muat Ulang
            </button>
        </div>
    </div>
</div>
@endsection

