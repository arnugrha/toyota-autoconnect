@extends('layouts.public')

@section('title', 'Pemesanan Kendaraan ' . $booking->code . ' — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('customer.bookings.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Pemesanan
            </a>

            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $booking->status_badge_class }}">
                {{ $booking->status_label }}
            </span>
        </div>

        <!-- Booking Document Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-6 sm:p-8 space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NOMOR REGISTER PEMESANAN KENDARAAN (SPK)</span>
                    <h2 class="text-xl font-extrabold text-slate-900 font-mono mt-0.5">{{ $booking->code }}</h2>
                    <p class="text-xs text-slate-500 mt-1">Cabang Alokasi: <strong class="text-slate-800">{{ $booking->branch->name }}</strong></p>
                </div>
                <div class="text-left sm:text-right space-y-0.5">
                    <span class="text-[10px] text-slate-400 block uppercase tracking-wider font-bold">Tanggal Pemesanan</span>
                    <span class="text-xs font-extrabold text-slate-800 block">{{ $booking->booking_date->format('d F Y') }}</span>
                    @if ($booking->expires_at)
                        <span class="text-[10px] text-amber-600 font-medium block">Batas Waktu: {{ $booking->expires_at->format('d M Y') }}</span>
                    @endif
                </div>
            </div>

            <!-- Vehicle Box -->
            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img src="{{ $booking->vehicle->primary_image_url }}" 
                         alt="{{ $booking->vehicle->name }}"
                         class="h-16 w-24 object-cover rounded-lg border border-slate-200 bg-slate-950 flex-shrink-0">
                    <div>
                        <span class="inline-block rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-600">
                            {{ $booking->vehicle->category }}
                        </span>
                        <h3 class="text-sm font-extrabold text-slate-900 mt-1">{{ $booking->vehicle->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $booking->vehicle->variant ?? 'Varian Standar' }}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Harga OTR Unit</span>
                    <p class="text-sm font-extrabold text-slate-900 mt-0.5 font-mono">
                        Rp {{ number_format($booking->vehicle_price, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Price Breakdown -->
            <div class="rounded-xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs min-w-[340px]">
                        <tbody class="divide-y divide-slate-100">
                            <tr class="bg-white">
                                <td class="py-3 px-4 text-slate-600 whitespace-nowrap">Harga Kesepakatan Unit OTR</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($booking->vehicle_price, 0, ',', '.') }}
                                </td>
                            </tr>
                            @if ($booking->discount_amount > 0)
                                <tr class="bg-emerald-50/50">
                                    <td class="py-3 px-4 text-emerald-800 font-semibold whitespace-nowrap">Diskon Disepakati</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700 whitespace-nowrap">
                                        - Rp {{ number_format($booking->discount_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                            <tr class="bg-slate-50 font-bold">
                                <td class="py-3 px-4 text-slate-900 whitespace-nowrap">Harga Bersih Unit Kendaraan</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($booking->net_price, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="bg-blue-50/50 font-bold">
                                <td class="py-3 px-4 text-blue-900 whitespace-nowrap">Tanda Jadi (Booking Fee)</td>
                                <td class="py-3 px-4 text-right font-mono text-blue-900 whitespace-nowrap">
                                    Rp {{ number_format($booking->booking_fee, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Status & Internal Allocation Notice -->
            <!-- Status & Internal Allocation Notice -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Status Alokasi Unit</span>
                    <p class="text-xs font-bold text-slate-800">{{ $booking->status_label }}</p>
                    <p class="text-[11px] text-slate-500">
                        @if ($booking->status === 'confirmed')
                            Unit mobil telah berhasil dialokasikan pada stok cabang {{ $booking->branch->name }}.
                        @elseif ($booking->status === 'pending')
                            Menunggu verifikasi dan alokasi unit oleh tim dealer.
                        @elseif ($booking->status === 'completed')
                            Unit telah selesai diserahterimakan kepada Anda.
                        @else
                            Pemesanan tidak aktif / telah dibatalkan.
                        @endif
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Status Pembayaran Tanda Jadi</span>
                    <p class="text-xs font-bold {{ $booking->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $booking->payment_status_label }}
                    </p>
                    <p class="text-[11px] text-slate-500">
                        @if ($booking->payment_status === 'paid')
                            Pembayaran tanda jadi telah lunas dan terverifikasi otomatis melalui Payment Gateway.
                        @else
                            Silakan lakukan pembayaran tanda jadi secara online untuk mempercepat pemrosesan SPK.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Payment Action / Verification Banner -->
            @if ($booking->payment_status === 'paid')
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-emerald-900">Tanda Jadi (Booking Fee) Telah Lunas Terverifikasi</h4>
                            <p class="text-[11px] text-emerald-700 mt-0.5">
                                Unit Anda telah diprioritaskan untuk proses alokasi dan penyiapan administrasi faktur resmi.
                            </p>
                        </div>
                    </div>
                    @if ($booking->latestPayment)
                        <a href="{{ route('customer.payments.show', $booking->latestPayment) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-3.5 py-2 text-xs font-bold text-emerald-800 shadow-2xs hover:bg-emerald-50 transition flex-shrink-0">
                            Lihat Tanda Terima
                        </a>
                    @endif
                </div>
            @elseif (!in_array($booking->status, ['cancelled', 'rejected']))
                <div class="rounded-xl bg-gradient-to-r from-red-600 to-rose-700 text-white p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-white/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                                Midtrans Gateway
                            </span>
                            <span class="text-xs text-white/80 font-medium">Instan & Terverifikasi Otomatis</span>
                        </div>
                        <h4 class="text-sm sm:text-base font-extrabold text-white">
                            Bayar Tanda Jadi: Rp {{ number_format($booking->booking_fee, 0, ',', '.') }}
                        </h4>
                        <p class="text-xs text-white/90">
                            Gunakan Virtual Account (BCA, Mandiri, BNI, BRI), GoPay, QRIS, atau Kartu Kredit untuk mengamankan pesanan Anda.
                        </p>
                    </div>
                    <a href="{{ route('customer.bookings.pay', $booking) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-xs font-black text-red-600 shadow-md hover:bg-red-50 transition flex-shrink-0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        Bayar via Midtrans
                    </a>
                </div>
            @endif

            @if ($booking->notes)
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Catatan Pemesanan:</span>
                    <p class="text-xs text-slate-700 mt-1">{{ $booking->notes }}</p>
                </div>
            @endif

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Sales Consultant: <strong>{{ $booking->salesOpportunity->assignedSales->name ?? 'Sales Executive Toyota' }}</strong></span>
                <span>Toyota AutoConnect Digital Dealership</span>
            </div>
        </div>

    </div>
</div>
@endsection
