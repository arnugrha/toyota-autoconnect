@extends('layouts.public')

@section('title', 'Terlalu Banyak Permintaan (429) — Toyota AutoConnect')
@section('meta_description', 'Terlalu banyak permintaan dalam waktu singkat. Mohon tunggu beberapa saat sebelum mencoba kembali.')

@section('content')
<div class="min-h-[65vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-md w-full text-center bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="inline-flex h-14 w-14 items-center justify-center rounded-xl bg-orange-50 text-orange-600 mb-4 font-extrabold text-xl border border-orange-100">
            429
        </div>
        
        <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Terlalu Banyak Permintaan</h1>
        
        <p class="mt-2 text-xs text-slate-500 leading-relaxed">
            Sistem mendeteksi tingginya frekuensi permintaan dari perangkat atau jaringan Anda (Rate Limiting). Demi menjaga stabilitas dan keamanan platform, silakan tunggu 1 menit sebelum mencoba kembali.
        </p>

        <div class="mt-6 flex flex-col sm:flex-row gap-2.5 justify-center">
            <a href="{{ route('home') }}" 
               class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Kembali ke Beranda
            </a>
            <a href="{{ route('vehicles.index') }}" 
               class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                Jelajahi Katalog
            </a>
        </div>
    </div>
</div>
@endsection

