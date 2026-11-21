@extends('layouts.public')

@section('title', 'Detail Pengajuan: ' . $inquiry->subject . ' — Toyota AutoConnect')
@section('meta_description', 'Detail status permohonan test drive dan informasi konsultasi unit Toyota AutoConnect.')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Navigation & Status -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-4">
            <a href="{{ route('customer.inquiries.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-red-600 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Riwayat Pengajuan
            </a>

            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $inquiry->status_badge_class }} self-start sm:self-auto">
                Status: {{ $inquiry->status_label }}
            </span>
        </div>

        <!-- Inquiry Main Overview Card -->
        <div class="rounded-2xl bg-white p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-red-600 font-mono">
                    #INQ-{{ str_pad($inquiry->id, 5, '0', STR_PAD_LEFT) }}
                </span>
                <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 mt-1">
                    {{ $inquiry->subject }}
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Diajukan pada: <span class="font-medium text-slate-700">{{ $inquiry->submitted_at->format('d F Y, H:i') }} WIB</span>
                </p>
            </div>

            <!-- Vehicle Details Card (if vehicle linked) -->
            @if ($inquiry->vehicle)
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ $inquiry->vehicle->primary_image_url }}" alt="{{ $inquiry->vehicle->name }}" class="h-16 w-24 object-cover rounded-lg bg-slate-200 border border-slate-300 flex-shrink-0">
                        <div>
                            <span class="inline-block rounded bg-red-100 text-red-700 px-1.5 py-0.5 text-[9px] font-bold uppercase">
                                {{ $inquiry->vehicle->category }}
                            </span>
                            <h2 class="font-bold text-slate-900 text-sm sm:text-base mt-1">{{ $inquiry->vehicle->name }}</h2>
                            <p class="text-xs font-semibold text-slate-600">{{ $inquiry->vehicle->formatted_price }}</p>
                        </div>
                    </div>

                    <a href="{{ route('vehicles.show', $inquiry->vehicle->slug) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:border-red-600 hover:text-red-600 transition flex-shrink-0 shadow-2xs">
                        Lihat Katalog Unit &rarr;
                    </a>
                </div>
            @endif

            <!-- Customer Inquiry Message Content -->
            <div>
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Rincian Permintaan / Catatan Anda:</h3>
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-200 text-xs text-slate-800 leading-relaxed whitespace-pre-line">
                    {{ $inquiry->message }}
                </div>
            </div>

            <!-- Snapshot Contact Info -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100 text-xs">
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Nama Lengkap Pemohon:</span>
                    <span class="font-semibold text-slate-900 mt-0.5 block">{{ $inquiry->customer_name }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Alamat Email:</span>
                    <span class="font-semibold text-slate-900 mt-0.5 block">{{ $inquiry->customer_email }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Nomor Telepon / WhatsApp:</span>
                    <span class="font-semibold text-slate-900 mt-0.5 block">{{ $inquiry->customer_phone ?? '—' }}</span>
                </div>
            </div>
        </div>

        <!-- Dealer Service Advice Card -->
        <div class="rounded-2xl bg-blue-50/70 p-5 sm:p-6 border border-blue-200/80 text-xs text-blue-900">
            <div class="flex items-start gap-3.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600 flex-shrink-0">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-blue-950">Informasi Layanan Showroom Toyota AutoConnect</h4>
                    <p class="mt-1 leading-relaxed text-blue-800">
                        Pengajuan Anda telah tercatat dan ditugaskan ke konsultan penjualan resmi. Tim konsultan kami akan menghubungi Anda melalui kontak di atas untuk konfirmasi tanggal sesi test drive di dealer atau pengantaran unit konsultasi.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
