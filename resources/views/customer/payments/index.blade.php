@extends('layouts.public')

@section('title', 'Riwayat Pembayaran Tanda Jadi (Booking Fee) — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">


        @if (session('info'))
            <div class="flex items-center gap-2.5 rounded-xl bg-blue-50 border border-blue-200 p-4 text-xs text-blue-800">
                <svg class="h-4 w-4 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <!-- Page Header & Overview -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Riwayat Pembayaran Tanda Jadi</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar transaksi pembayaran tanda jadi (booking fee) SPK terverifikasi otomatis melalui Payment Gateway Midtrans</p>
            </div>
            <div>
                <a href="{{ route('customer.bookings.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    Daftar Booking SPK
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="flex flex-wrap items-center gap-1.5 bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs text-xs">
            <a href="{{ route('customer.payments.index') }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ empty($currentStatus) ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Status
            </a>
            <a href="{{ route('customer.payments.index', ['status' => 'paid']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'paid' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Berhasil / Lunas
            </a>
            <a href="{{ route('customer.payments.index', ['status' => 'pending']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Menunggu Pembayaran
            </a>
            <a href="{{ route('customer.payments.index', ['status' => 'expired']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'expired' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Kedaluwarsa
            </a>
            <a href="{{ route('customer.payments.index', ['status' => 'failed']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'failed' ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Gagal
            </a>
        </div>

        <!-- Payments Table Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-5 whitespace-nowrap">Order ID & Kode Transaksi</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Pemesanan (SPK) & Unit</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Nominal Tanda Jadi</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Metode Bayar</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Waktu Transaksi</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Status</th>
                            <th class="py-3.5 px-5 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($payments as $payment)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-5 whitespace-nowrap">
                                    <div class="font-mono font-bold text-slate-900 text-xs">{{ $payment->order_id }}</div>
                                    <div class="font-mono text-[10px] text-slate-400 mt-0.5">{{ $payment->code }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <a href="{{ route('customer.bookings.show', $payment->vehicleBooking) }}"
                                       class="font-mono font-bold text-red-600 hover:underline block text-xs">
                                        {{ $payment->vehicleBooking->code }}
                                    </a>
                                    <div class="font-medium text-slate-800 text-xs mt-0.5">
                                        {{ $payment->vehicleBooking->vehicle->name ?? '-' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $payment->vehicleBooking->branch->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800 capitalize">
                                        {{ str_replace('_', ' ', $payment->payment_method ?? 'Midtrans Snap') }}
                                    </div>
                                    @if ($payment->gateway_transaction_id)
                                        <div class="text-[10px] text-slate-400 font-mono">
                                            ID: {{ substr($payment->gateway_transaction_id, 0, 16) }}...
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-[11px] text-slate-500 whitespace-nowrap">
                                    <div>{{ $payment->created_at->format('d M Y, H:i') }} WIB</div>
                                    @if ($payment->paid_at)
                                        <div class="text-[10px] text-emerald-600 font-medium">Lunas: {{ $payment->paid_at->format('d M Y, H:i') }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $payment->status_badge_class }}">
                                        {{ $payment->status_label }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($payment->status === 'pending' && $payment->snap_token)
                                            <a href="{{ route('customer.bookings.pay', $payment->vehicleBooking) }}"
                                               class="rounded-lg bg-red-600 px-3 py-1.5 text-[11px] font-bold text-white hover:bg-red-700 transition">
                                                Bayar Sekarang
                                            </a>
                                        @endif
                                        <a href="{{ route('customer.payments.show', $payment) }}"
                                           class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition">
                                            Bukti / Rincian
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-4 text-center">
                                    <div class="mx-auto max-w-sm text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v-.75C2.25 4.56 2.81 4 3.5 4h17c.69 0 1.25.56 1.25 1.25v.75m-19.5 0c0 .414.336.75.75.75h18a.75.75 0 0 0 .75-.75m-19.5 0v12.75c0 .621.504 1.125 1.125 1.125H18M3.75 6H18m0 0v12.75c0 .621-.504 1.125-1.125 1.125H3.75" />
                                            </svg>
                                        </div>
                                        <h3 class="mt-3 text-sm font-bold text-slate-900">Belum Ada Transaksi Pembayaran</h3>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Transaksi pembayaran tanda jadi akan muncul di sini setelah Anda melakukan pemesanan kendaraan (SPK).
                                        </p>
                                        <div class="mt-4">
                                            <a href="{{ route('customer.bookings.index') }}"
                                               class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-red-700 transition">
                                                Lihat Pemesanan SPK Saya
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payments->hasPages())
                <div class="border-t border-slate-200 p-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
