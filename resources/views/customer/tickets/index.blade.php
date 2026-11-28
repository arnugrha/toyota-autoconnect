@extends('layouts.public')

@section('title', 'Pusat Bantuan & Tiket CS — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Pusat Layanan &amp; Tiket Bantuan Pelanggan</h1>
                <p class="text-xs text-slate-500 mt-1">Sampaikan keluhan, pertanyaan seputar kendaraan, pembiayaan, servis, atau klaim garansi Anda langsung ke tim Customer Care resmi.</p>
            </div>
            <div>
                <a href="{{ route('customer.tickets.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Buat Tiket Bantuan Baru
                </a>
            </div>
        </div>


        <!-- Filter Status Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none text-xs font-semibold">
            <a href="{{ route('customer.tickets.index') }}"
               class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ !request('status') ? 'bg-slate-900 text-white border-slate-900 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Semua Tiket
            </a>
            <a href="{{ route('customer.tickets.index', ['status' => 'open']) }}"
               class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ request('status') === 'open' ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Tiket Baru
            </a>
            <a href="{{ route('customer.tickets.index', ['status' => 'in_progress']) }}"
               class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ request('status') === 'in_progress' ? 'bg-amber-600 text-white border-amber-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Sedang Ditangani
            </a>
            <a href="{{ route('customer.tickets.index', ['status' => 'waiting_customer']) }}"
               class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ request('status') === 'waiting_customer' ? 'bg-purple-600 text-white border-purple-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Menunggu Balasan Anda
            </a>
            <a href="{{ route('customer.tickets.index', ['status' => 'resolved']) }}"
               class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ request('status') === 'resolved' ? 'bg-emerald-600 text-white border-emerald-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Selesai (Resolved)
            </a>
        </div>

        <!-- Table Card with Horizontal Scroll -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 min-w-[1050px]">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[140px]">No. Tiket</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[220px]">Subjek / Topik</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[140px]">Kategori</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[110px]">Prioritas</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[160px]">Cabang Terkait</th>
                            <th class="px-5 py-3.5 whitespace-nowrap text-center min-w-[120px]">Status</th>
                            <th class="px-5 py-3.5 whitespace-nowrap text-right min-w-[100px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ $ticket->ticket_number }}
                                    <span class="block font-sans text-[10px] text-slate-400 font-normal mt-0.5">
                                        {{ $ticket->created_at->format('d M Y, H:i') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-bold text-slate-900 block max-w-xs truncate">{{ $ticket->subject }}</span>
                                    <span class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $ticket->description }}</span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800">{{ $ticket->category_label }}</span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold border {{ $ticket->priority_badge_class }}">
                                        {{ $ticket->priority_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                                    {{ $ticket->branch?->name ?? 'Pusat (Head Office)' }}
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $ticket->status_badge_class }}">
                                        {{ $ticket->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('customer.tickets.show', $ticket) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-slate-50 hover:text-red-600 transition shadow-2xs">
                                        Buka Diskusi
                                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.502 49.188 49.188 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-xs font-bold text-slate-800">Tidak ada tiket bantuan</h3>
                                    <p class="text-[11px] text-slate-500 mt-1 max-w-sm mx-auto">Jika Anda mengalami kendala atau membutuhkan konsultasi, buat tiket bantuan kapan saja.</p>
                                    <a href="{{ route('customer.tickets.create') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-red-700 transition">
                                        Buat Tiket Bantuan &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

