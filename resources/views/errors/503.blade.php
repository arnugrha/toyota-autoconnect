@extends('layouts.public')

@section('title', 'Pemeliharaan Sistem (503) — Toyota AutoConnect')
@section('meta_description', 'Layanan Toyota AutoConnect sedang dalam peningkatan sistem berkala.')

@section('content')
<div class="min-h-[65vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-md w-full text-center bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="inline-flex h-14 w-14 items-center justify-center rounded-xl bg-blue-50 text-blue-600 mb-4 font-extrabold text-xl border border-blue-100">
            503
        </div>
        
        <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Sistem Sedang Dalam Pemeliharaan</h1>
        
        <p class="mt-2 text-xs text-slate-500 leading-relaxed">
            Platform Toyota AutoConnect sedang menjalani pemeliharaan berkala untuk peningkatan performa dan keamanan layanan. Kami akan segera kembali dalam beberapa saat. Terima kasih atas kesabaran Anda.
        </p>

        <div class="mt-6 flex flex-col sm:flex-row gap-2.5 justify-center">
            <button onclick="window.location.reload();" 
                    type="button"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition cursor-pointer">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Periksa Ketersediaan
            </button>
            <a href="tel:08001234567" 
               class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                </svg>
                Hubungi Call Center
            </a>
        </div>
    </div>
</div>
@endsection

