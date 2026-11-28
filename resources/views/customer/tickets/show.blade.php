@extends('layouts.public')

@section('title', "Tiket {$ticket->ticket_number} — Toyota AutoConnect")

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('customer.tickets.index') }}" class="hover:text-red-600 transition">Pusat Bantuan</a>
                    <span>/</span>
                    <span class="text-slate-900 font-bold font-mono">{{ $ticket->ticket_number }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $ticket->subject }}</h1>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold border {{ $ticket->status_badge_class }}">
                        {{ $ticket->status_label }}
                    </span>
                </div>
            </div>

            <a href="{{ route('customer.tickets.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                &larr; Kembali
            </a>
        </div>


        <!-- Ticket Overview Bar -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Kategori</span>
                <span class="font-bold text-slate-800 mt-0.5 block">{{ $ticket->category_label }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Prioritas</span>
                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold border mt-0.5 {{ $ticket->priority_badge_class }}">
                    {{ $ticket->priority_label }}
                </span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Cabang Terkait</span>
                <span class="font-bold text-slate-800 mt-0.5 block">{{ $ticket->branch?->name ?? 'Layanan Pusat' }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Petugas CS</span>
                <span class="font-bold text-slate-800 mt-0.5 block">{{ $ticket->assignedAgent?->name ?? 'Menunggu Ditugaskan' }}</span>
            </div>
        </div>

        <!-- Conversation Messages Thread -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.502 49.188 49.188 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                </svg>
                Diskusi &amp; Riwayat Pesan
            </h3>

            <div class="space-y-4">
                @foreach($ticket->customerMessages as $msg)
                    @php
                        $isCustomer = ($msg->user_id === auth()->id());
                    @endphp
                    <div class="flex {{ $isCustomer ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-2xl rounded-2xl p-5 shadow-2xs {{ $isCustomer ? 'bg-red-600 text-white rounded-tr-none' : 'bg-white border border-slate-200 text-slate-800 rounded-tl-none' }}">
                            <div class="flex items-center justify-between gap-4 pb-2 border-b {{ $isCustomer ? 'border-red-500/50' : 'border-slate-100' }} mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold {{ $isCustomer ? 'text-white' : 'text-slate-900' }}">
                                        {{ $msg->user?->name ?? 'Pengguna' }}
                                    </span>
                                    <span class="text-[10px] px-2 py-0.2 rounded font-bold {{ $isCustomer ? 'bg-red-800 text-red-200' : 'bg-slate-100 text-red-700' }}">
                                        {{ $isCustomer ? 'Anda' : 'Customer Care' }}
                                    </span>
                                </div>
                                <span class="text-[10px] {{ $isCustomer ? 'text-red-200' : 'text-slate-400' }}">
                                    {{ $msg->created_at->format('d M Y, H:i') }}
                                </span>
                            </div>

                            <p class="text-xs leading-relaxed whitespace-pre-line">{{ $msg->message }}</p>

                            @if($msg->attachment_path)
                                <div class="mt-3 pt-3 border-t {{ $isCustomer ? 'border-red-500/50' : 'border-slate-100' }}">
                                    <a href="{{ asset('storage/' . $msg->attachment_path) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 text-xs font-bold underline {{ $isCustomer ? 'text-white hover:text-red-100' : 'text-red-600 hover:text-red-700' }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                        </svg>
                                        Lihat Lampiran Dokumen
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Reply Form (If Not Closed) -->
        @if($ticket->status !== 'closed')
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Kirim Balasan Pesan</h4>

                <form action="{{ route('customer.tickets.reply', $ticket) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <textarea name="message"
                                  rows="4"
                                  required
                                  placeholder="Ketik balasan atau pertanyaan lanjutan Anda di sini..."
                                  class="w-full rounded-xl border border-slate-300 p-3 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <input type="file"
                                   name="attachment"
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   class="text-xs text-slate-600 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                        </div>

                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                            <span>Kirim Balasan</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-slate-100 p-6 text-center text-xs text-slate-600">
                <p class="font-bold text-slate-800">Tiket bantuan ini telah ditutup (Closed).</p>
                <p class="mt-1">Jika Anda membutuhkan bantuan lain, silakan buat tiket baru.</p>
                <a href="{{ route('customer.tickets.create') }}" class="mt-3 inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-700 transition">
                    Buat Tiket Baru &rarr;
                </a>
            </div>
        @endif

    </div>
</div>
@endsection
