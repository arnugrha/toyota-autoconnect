@extends('layouts.public')

@section('title', 'Booking Servis Kendaraan — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Booking Servis Bengkel Resmi</h1>
                <p class="text-xs text-slate-500 mt-1">Kelola jadwal perawatan berkala, perbaikan mesin, dan layanan bengkel resmi Toyota Anda.</p>
            </div>
            <div>
                <a href="{{ route('customer.service-bookings.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Booking Servis Baru
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-2xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Filter Status Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none text-xs font-semibold">
            <a href="{{ route('customer.service-bookings.index') }}"
               class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ !request('status') ? 'bg-slate-900 text-white border-slate-900 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Semua Status
            </a>
            <a href="{{ route('customer.service-bookings.index', ['status' => 'pending']) }}"
               class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ request('status') === 'pending' ? 'bg-amber-600 text-white border-amber-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Menunggu Konfirmasi
            </a>
            <a href="{{ route('customer.service-bookings.index', ['status' => 'confirmed']) }}"
               class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ request('status') === 'confirmed' ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Dikonfirmasi Bengkel
            </a>
            <a href="{{ route('customer.service-bookings.index', ['status' => 'completed']) }}"
               class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ request('status') === 'completed' ? 'bg-emerald-600 text-white border-emerald-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Selesai Dikerjakan
            </a>
            <a href="{{ route('customer.service-bookings.index', ['status' => 'cancelled']) }}"
               class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ request('status') === 'cancelled' ? 'bg-rose-600 text-white border-rose-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Dibatalkan
            </a>
        </div>

        <!-- Table Card with Horizontal Scroll Support -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 min-w-[1050px]">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[140px]">Kode Booking</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[180px]">Kendaraan &amp; Plat</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[180px]">Layanan Servis</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[160px]">Bengkel Cabang</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[160px]">Jadwal Tanggal &amp; Sesi</th>
                            <th class="px-5 py-3.5 whitespace-nowrap text-center min-w-[120px]">Status</th>
                            <th class="px-5 py-3.5 whitespace-nowrap text-right min-w-[100px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($bookings as $booking)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ $booking->booking_code }}
                                    <span class="block font-sans text-[10px] text-slate-400 font-normal mt-0.5">
                                        {{ $booking->created_at->format('d M Y, H:i') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-900 block">{{ $booking->vehicle_name }}</span>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 bg-slate-100 text-slate-700 rounded font-mono text-[10px] font-bold border border-slate-200">
                                        {{ $booking->license_plate }}
                                    </span>
                                    @if($booking->current_mileage)
                                        <span class="text-[10px] text-slate-400 ml-1.5">{{ number_format($booking->current_mileage, 0, ',', '.') }} KM</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-900 block">{{ $booking->serviceType?->name ?? 'Servis Berkala' }}</span>
                                    <span class="text-[10px] text-slate-500">Estimasi: {{ $booking->serviceType?->formatted_price ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800 block">{{ $booking->branch?->name ?? 'Cabang Resmi' }}</span>
                                    <span class="text-[10px] text-slate-400 truncate max-w-[180px] block">{{ $booking->branch?->city ?? 'Toyota Dealer' }}</span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 text-slate-800 font-bold">
                                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}
                                    </div>
                                    <span class="text-[11px] font-semibold text-red-600 mt-0.5 block">
                                        Pukul {{ $booking->booking_time }} WIB
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $booking->status_badge_class }}">
                                        {{ $booking->status_label }}
                                    </span>
                                    @php
                                        $pendingPartCount = $booking->serviceOrder
                                            ? $booking->serviceOrder->items->where('approval_status', 'pending_approval')->count()
                                            : 0;
                                    @endphp
                                    @if($pendingPartCount > 0)
                                        <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-300 px-2 py-0.5 text-[10px] font-extrabold text-amber-800 animate-pulse">
                                            Konfirmasi Sparepart ({{ $pendingPartCount }})
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('customer.service-bookings.show', $booking) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-slate-50 hover:text-red-600 transition shadow-2xs">
                                        Detail
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
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27-2.828-2.828 3.27-3.27a4.5 4.5 0 0 0-6.32 4.486c.118.58.094 1.193-.07 1.743" />
                                        </svg>
                                    </div>
                                    <h3 class="text-xs font-bold text-slate-800">Belum ada booking servis kendaraan</h3>
                                    <p class="text-[11px] text-slate-500 mt-1 max-w-sm mx-auto">Jadwalkan servis rutin Toyota Anda tanpa antre dengan memilih bengkel resmi dan jam kedatangan yang fleksibel.</p>
                                    <a href="{{ route('customer.service-bookings.create') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-red-700 transition">
                                        Booking Jadwal Servis Sekarang &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bookings->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

