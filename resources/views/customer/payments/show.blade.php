@extends('layouts.public')

@section('title', 'Tanda Terima Pembayaran ' . $payment->order_id . ' — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Back & Status -->
        <div class="flex items-center justify-between">
            <a href="{{ route('customer.payments.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Riwayat Pembayaran
            </a>

            <div class="flex items-center gap-2">
                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.641-2.074-1.18-2.973m11.908 0c-.54.9-1.04 1.897-1.28 2.973m-9.448 0A13.447 13.447 0 0 1 3 10.5c0-4.142 4.03-7.5 9-7.5s9 3.358 9 7.5c0 1.155-.316 2.25-.88 3.23m-14.24 0a13.447 13.447 0 0 0 5.44 2.871m8.8-2.871a13.447 13.447 0 0 1-5.44 2.871m0 0v3.75m0 0H7.5m4.5 0h4.5" />
                    </svg>
                    Cetak Kuitansi
                </button>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $payment->status_badge_class }}">
                    {{ $payment->status_label }}
                </span>
            </div>
        </div>


        <!-- Receipt Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-6 sm:p-8 space-y-6 print:border-none print:shadow-none">

            <!-- Receipt Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-red-600 px-2 py-0.5 text-[10px] font-black text-white">TOYOTA</span>
                        <span class="text-xs font-extrabold text-slate-900 tracking-tight">AutoConnect</span>
                    </div>
                    <h1 class="text-lg font-black text-slate-900 mt-2">Tanda Terima Pembayaran Digital</h1>
                    <p class="text-xs text-slate-500">Bukti sah pembayaran tanda jadi pemesanan kendaraan (SPK)</p>
                </div>
                <div class="text-left sm:text-right space-y-0.5">
                    <span class="text-[10px] text-slate-400 block uppercase tracking-wider font-bold">ORDER ID MIDTRANS</span>
                    <span class="text-sm font-black font-mono text-slate-900 block">{{ $payment->order_id }}</span>
                    <span class="text-[11px] text-slate-500 font-mono block">Ref: {{ $payment->code }}</span>
                </div>
            </div>

            <!-- Summary Highlight Banner -->
            <div class="rounded-xl bg-slate-900 text-white p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Nominal Dibayar</span>
                    <p class="text-2xl sm:text-3xl font-black font-mono text-white mt-0.5">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1">Biaya Pemesanan Unit Kendaraan Resmi (Tanda Jadi SPK)</p>
                </div>
                <div class="text-left sm:text-right flex-shrink-0">
                    <span class="inline-flex items-center rounded-md px-3 py-1 text-xs font-bold {{ $payment->status === 'paid' ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-200' }}">
                        {{ $payment->status_label }}
                    </span>
                    @if ($payment->paid_at)
                        <span class="block text-[11px] text-slate-400 mt-1">Lunas: {{ $payment->paid_at->format('d M Y, H:i') }} WIB</span>
                    @endif
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">

                <!-- Vehicle and Booking Information -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Informasi Pemesanan (SPK)</h3>
                    
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Nomor SPK:</span>
                            <a href="{{ route('customer.bookings.show', $payment->vehicleBooking) }}"
                               class="font-mono font-bold text-red-600 hover:underline">
                                {{ $payment->vehicleBooking->code }}
                            </a>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Unit Kendaraan:</span>
                            <span class="font-bold text-slate-800 text-right">{{ $payment->vehicleBooking->vehicle->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Cabang Alokasi:</span>
                            <span class="font-semibold text-slate-800 text-right">{{ $payment->vehicleBooking->branch->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Nilai Bersih Mobil:</span>
                            <span class="font-mono font-bold text-slate-800">Rp {{ number_format($payment->vehicleBooking->net_price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Transaction & Gateway Metadata -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Rincian Gateway Midtrans</h3>

                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Waktu Dibuat:</span>
                            <span class="font-medium text-slate-800">{{ $payment->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Metode Bayar:</span>
                            <span class="font-bold text-slate-800 capitalize">{{ str_replace('_', ' ', $payment->payment_method ?? 'Midtrans Snap') }}</span>
                        </div>
                        @if ($payment->gateway_transaction_id)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Gateway Trx ID:</span>
                                <span class="font-mono text-[11px] text-slate-600">{{ $payment->gateway_transaction_id }}</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Customer Details -->
            <div class="rounded-xl border border-slate-200 p-4 space-y-2 text-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Diterbitkan Untuk:</span>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <p class="font-bold text-slate-900 text-sm">{{ $payment->user->name }}</p>
                        <p class="text-slate-500">{{ $payment->user->email }} &bull; {{ $payment->user->phone_number ?? '-' }}</p>
                    </div>
                    <div class="text-left sm:text-right text-[11px] text-slate-400">
                        Dealer Resmi Toyota AutoConnect Indonesia<br>
                        Sistem Terverifikasi Pembayaran Digital
                    </div>
                </div>
            </div>

            <!-- Pending Action Option -->
            @if ($payment->status === 'pending' && $payment->snap_token)
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h4 class="text-xs font-bold text-amber-900">Menunggu Pembayaran</h4>
                        <p class="text-[11px] text-amber-700 mt-0.5">
                            Selesaikan pembayaran sebelum {{ $payment->expired_at ? $payment->expired_at->format('d M Y, H:i') : 'batas waktu habis' }}.
                        </p>
                    </div>
                    <a href="{{ route('customer.bookings.pay', $payment->vehicleBooking) }}"
                       class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700 transition flex-shrink-0">
                        Lanjutkan Pembayaran via Snap
                    </a>
                </div>
            @endif

            <!-- Footer Action Links -->
            <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                <a href="{{ route('customer.bookings.show', $payment->vehicleBooking) }}"
                   class="font-semibold text-red-600 hover:underline">
                    &larr; Lihat Rincian Surat Pemesanan Kendaraan (SPK)
                </a>

                <span class="text-[11px] text-slate-400">
                    ID Transaksi Midtrans: {{ $payment->order_id }}
                </span>
            </div>

        </div>

    </div>
</div>
@endsection
