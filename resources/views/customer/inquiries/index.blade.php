@extends('layouts.public')

@section('title', 'Riwayat Pengajuan Test Drive — Toyota AutoConnect')
@section('meta_description', 'Pantau status permohonan test drive dan konsultasi unit kendaraan Toyota Anda secara transparan.')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Breadcrumb & Header Title -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-6">
            <div>
                <nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-2 font-medium">
                    <a href="{{ route('home') }}" class="hover:text-red-600 transition">Beranda</a>
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                    <span class="text-slate-800 font-semibold">Riwayat Pengajuan Test Drive</span>
                </nav>
                <div class="flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-600 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat Pengajuan Test Drive</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar permohonan sesi uji kendara dan konsultasi unit kendaraan Anda di Toyota AutoConnect</p>
                    </div>
                </div>
            </div>

            <!-- Quick Action Links -->
            <div class="flex items-center gap-2.5 flex-shrink-0">
                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-red-600 transition">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    Profil Akun
                </a>
                <a href="{{ route('vehicles.index') }}" 
                   class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Ajukan Test Drive Baru
                </a>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="rounded-2xl bg-white p-4 border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
            <form method="GET" action="{{ route('customer.inquiries.index') }}" class="flex items-center gap-2.5 w-full sm:w-auto">
                <label for="status" class="text-xs font-bold text-slate-700 whitespace-nowrap">Filter Status:</label>
                <select id="status" name="status" onchange="this.form.submit()"
                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 bg-white focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Status Pengajuan</option>
                    <option value="new" {{ $currentStatus === 'new' ? 'selected' : '' }}>Baru Dikirim</option>
                    <option value="in_progress" {{ $currentStatus === 'in_progress' ? 'selected' : '' }}>Sedang Diproses Staf</option>
                    <option value="answered" {{ $currentStatus === 'answered' ? 'selected' : '' }}>Telah Dikonfirmasi / Dijawab</option>
                    <option value="closed" {{ $currentStatus === 'closed' ? 'selected' : '' }}>Selesai</option>
                </select>
                @if ($currentStatus)
                    <a href="{{ route('customer.inquiries.index') }}" class="text-xs text-slate-400 hover:text-red-600 font-semibold px-1">
                        Reset
                    </a>
                @endif
            </form>

            <span class="text-xs font-medium text-slate-500 self-end sm:self-center">
                Total: <strong class="text-slate-900 font-bold">{{ $inquiries->total() }}</strong> pengajuan
            </span>
        </div>

        <!-- Inquiries Table Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[850px]">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-5 whitespace-nowrap min-w-[180px]">Model Kendaraan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[220px]">Topik Permintaan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[160px]">Waktu Pengiriman</th>
                            <th class="py-3.5 px-4 whitespace-nowrap text-center min-w-[110px]">Status</th>
                            <th class="py-3.5 px-5 whitespace-nowrap text-right min-w-[90px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($inquiries as $inq)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-5">
                                    @if ($inq->vehicle)
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $inq->vehicle->primary_image_url }}" alt="{{ $inq->vehicle->name }}" class="h-10 w-16 object-cover rounded-lg bg-slate-100 border border-slate-200 flex-shrink-0">
                                            <div>
                                                <div class="font-bold text-slate-900">{{ $inq->vehicle->name }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $inq->vehicle->formatted_price }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="font-bold text-slate-800">Pertanyaan Umum Showroom</div>
                                        <div class="text-[10px] text-slate-400">Non-Unit Kendaraan</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900">{{ $inq->subject }}</div>
                                    <div class="text-[11px] text-slate-500 truncate max-w-xs sm:max-w-md mt-0.5">{{ Str::limit($inq->message, 90) }}</div>
                                </td>
                                <td class="py-4 px-4 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                    {{ $inq->submitted_at->format('d M Y, H:i') }} WIB
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $inq->status_badge_class }}">
                                        {{ $inq->status_label }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-right whitespace-nowrap">
                                    <a href="{{ route('customer.inquiries.show', $inq) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 transition shadow-2xs">
                                        Detail
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center text-xs text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                        </div>
                                        <p class="font-medium text-slate-600">Belum ada permohonan test drive atau konsultasi yang ditemukan.</p>
                                        <a href="{{ route('vehicles.index') }}" 
                                           class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700 transition shadow-xs">
                                            Jelajahi Katalog Mobil Toyota &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($inquiries->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $inquiries->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
