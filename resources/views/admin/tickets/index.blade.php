@extends('layouts.dashboard')

@section('title', 'Manajemen Tiket Layanan Pelanggan — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Manajemen Tiket Layanan Pelanggan (Helpdesk CS)</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola tiket aduan, pertanyaan seputar servis kendaraan, penawaran, garansi resmi, dan eskalasi penanganan keluhan.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3.5">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Tiket</span>
            <p class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 block">Tiket Baru (Open)</span>
            <p class="text-xl font-black text-blue-900 mt-1">{{ number_format($stats['open']) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block">Sedang Ditangani</span>
            <p class="text-xl font-black text-amber-900 mt-1">{{ number_format($stats['in_progress']) }}</p>
        </div>
        <div class="rounded-2xl border border-purple-200 bg-purple-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 block">Tunggu Pelanggan</span>
            <p class="text-xl font-black text-purple-900 mt-1">{{ number_format($stats['waiting_customer']) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Selesai (Resolved)</span>
            <p class="text-xl font-black text-emerald-900 mt-1">{{ number_format($stats['resolved']) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-100 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 block">Ditutup (Closed)</span>
            <p class="text-xl font-black text-slate-800 mt-1">{{ number_format($stats['closed']) }}</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
        <form action="{{ route('admin.tickets.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-4 relative">
                <input type="text"
                       name="q"
                       value="{{ $filters['q'] }}"
                       placeholder="Cari nomor tiket, subjek, nama pelanggan..."
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="open" {{ $filters['status'] === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ $filters['status'] === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="waiting_customer" {{ $filters['status'] === 'waiting_customer' ? 'selected' : '' }}>Waiting Customer</option>
                    <option value="resolved" {{ $filters['status'] === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ $filters['status'] === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="priority" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="all">Semua Prioritas</option>
                    <option value="urgent" {{ $filters['priority'] === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ $filters['priority'] === 'high' ? 'selected' : '' }}>Tinggi</option>
                    <option value="medium" {{ $filters['priority'] === 'medium' ? 'selected' : '' }}>Sedang</option>
                    <option value="low" {{ $filters['priority'] === 'low' ? 'selected' : '' }}>Rendah</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="category" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="all">Semua Kategori</option>
                    <option value="service_booking" {{ $filters['category'] === 'service_booking' ? 'selected' : '' }}>Booking Servis</option>
                    <option value="service_complaint" {{ $filters['category'] === 'service_complaint' ? 'selected' : '' }}>Keluhan Bengkel</option>
                    <option value="vehicle_info" {{ $filters['category'] === 'vehicle_info' ? 'selected' : '' }}>Info Kendaraan</option>
                    <option value="payment" {{ $filters['category'] === 'payment' ? 'selected' : '' }}>Pembayaran</option>
                    <option value="warranty" {{ $filters['category'] === 'warranty' ? 'selected' : '' }}>Garansi</option>
                    <option value="general_inquiry" {{ $filters['category'] === 'general_inquiry' ? 'selected' : '' }}>Umum</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-slate-900 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Filter
                </button>
                @if($filters['q'] || $filters['status'] !== 'all' || $filters['priority'] !== 'all' || $filters['category'] !== 'all')
                    <a href="{{ route('admin.tickets.index') }}" class="p-2 text-slate-500 hover:text-red-600 text-xs font-bold">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card with Horizontal Scroll -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 min-w-[1100px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[140px]">No. Tiket</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[180px]">Pelanggan</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[220px]">Subjek / Masalah</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[140px]">Kategori</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[110px]">Prioritas</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[160px]">Petugas CS</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center min-w-[130px]">Status</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-right min-w-[90px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $ticket->ticket_number }}
                                <span class="block font-sans text-[10px] text-slate-400 font-normal mt-0.5">
                                    {{ $ticket->created_at->format('d/m/Y H:i') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $ticket->user?->name }}</span>
                                <span class="text-[11px] text-slate-500 block">{{ $ticket->user?->phone ?? $ticket->user?->email }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-bold text-slate-900 block max-w-xs truncate">{{ $ticket->subject }}</span>
                                <span class="text-[11px] text-slate-500 line-clamp-1 mt-0.5 max-w-xs truncate">{{ $ticket->description }}</span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $ticket->category_label }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold border {{ $ticket->priority_badge_class }}">
                                    {{ $ticket->priority_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $ticket->assignedAgent?->name ?? 'Belum Ditugaskan' }}
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $ticket->status_badge_class }}">
                                    {{ $ticket->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.tickets.show', $ticket) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-slate-50 hover:text-red-600 transition shadow-2xs">
                                    Buka Tiket
                                    <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-500 text-xs">
                                Tidak ada tiket layanan yang sesuai dengan filter.
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
@endsection

