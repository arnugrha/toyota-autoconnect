@extends('layouts.dashboard')

@section('title', 'Manajemen Transaksi Pembayaran — Toyota AutoConnect')
@section('header_title', 'Manajemen Transaksi Pembayaran')
@section('header_subtitle', 'Pantau penerimaan pembayaran tanda jadi (booking fee) online via Payment Gateway Midtrans')

@section('content')
<div class="space-y-6">


    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Transaksi</p>
            <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Semua kanal</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-emerald-200 shadow-xs bg-emerald-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Lunas / Berhasil</p>
            <p class="text-xl font-extrabold text-emerald-700 mt-1">{{ $stats['paid'] }}</p>
            <p class="text-[10px] text-emerald-600 font-mono mt-0.5">Rp {{ number_format($stats['totalRevenue'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-amber-200 shadow-xs bg-amber-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Menunggu Pembayaran</p>
            <p class="text-xl font-extrabold text-amber-700 mt-1">{{ $stats['pending'] }}</p>
            <p class="text-[10px] text-amber-600/80 mt-0.5">Transaksi aktif</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Kedaluwarsa / Batal</p>
            <p class="text-xl font-extrabold text-slate-600 mt-1">{{ $stats['expired'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Melewati batas waktu</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-purple-200 shadow-xs bg-purple-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-purple-700">Dikembalikan (Refund)</p>
            <p class="text-xl font-extrabold text-purple-700 mt-1">{{ $stats['refunded'] }}</p>
            <p class="text-[10px] text-purple-600/80 mt-0.5">Telah dikembalikan</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs space-y-3">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">

            <!-- Search -->
            <div class="sm:col-span-5">
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari Order ID, kode booking, nama nasabah, email..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-3">
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-700 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="all">Semua Status Transaksi</option>
                    <option value="paid" {{ $filters['status'] === 'paid' ? 'selected' : '' }}>Lunas / Settlement</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Menunggu Bayar (Pending)</option>
                    <option value="expired" {{ $filters['status'] === 'expired' ? 'selected' : '' }}>Kedaluwarsa (Expired)</option>
                    <option value="failed" {{ $filters['status'] === 'failed' ? 'selected' : '' }}>Gagal / Deny</option>
                    <option value="refunded" {{ $filters['status'] === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="sm:col-span-4 flex items-center gap-1.5">
                <button type="submit"
                        class="flex-1 rounded-lg bg-red-600 py-1.5 px-3 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    Filter
                </button>
                <a href="{{ route('admin.payments.index') }}"
                   class="rounded-lg border border-slate-300 bg-white py-1.5 px-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Payments Table Card -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-left text-xs">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap">Order ID &amp; Kode Transaksi</th>
                        <th class="py-3 px-4 whitespace-nowrap">Nasabah / Pembayar</th>
                        <th class="py-3 px-4 whitespace-nowrap">Booking SPK &amp; Unit</th>
                        <th class="py-3 px-4 whitespace-nowrap">Nominal Tanda Jadi</th>
                        <th class="py-3 px-4 whitespace-nowrap">Metode Bayar</th>
                        <th class="py-3 px-4 whitespace-nowrap">Waktu Transaksi</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap">Status</th>
                        <th class="py-3 px-4 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($payments as $payment)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono whitespace-nowrap">
                                <a href="{{ route('admin.payments.show', $payment) }}" class="font-bold text-slate-900 hover:text-red-600 block">
                                    {{ $payment->order_id }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $payment->code }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $payment->user->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $payment->user->email ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <a href="{{ route('admin.sales.bookings.show', $payment->vehicleBooking) }}"
                                   class="font-mono font-bold text-red-600 hover:underline">
                                    {{ $payment->vehicleBooking->code ?? '-' }}
                                </a>
                                <div class="font-medium text-slate-800 text-[11px] mt-0.5">
                                    {{ $payment->vehicleBooking->vehicle->name ?? '-' }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $payment->vehicleBooking->branch->name ?? '-' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $payment->formatted_amount }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-800 capitalize">
                                    {{ str_replace('_', ' ', $payment->payment_method ?? 'Midtrans Snap') }}
                                </span>
                                @if ($payment->gateway_transaction_id)
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ substr($payment->gateway_transaction_id, 0, 16) }}...
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-[11px] text-slate-500 whitespace-nowrap">
                                <div>{{ $payment->created_at->format('d M Y, H:i') }} WIB</div>
                                @if ($payment->paid_at)
                                    <div class="text-[10px] text-emerald-600 font-semibold">Lunas: {{ $payment->paid_at->format('d M Y, H:i') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $payment->status_badge_class }}">
                                    {{ $payment->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.payments.show', $payment) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                                    Detail / Bukti
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-slate-400 italic">
                                Belum ada riwayat transaksi pembayaran tanda jadi yang sesuai filter.
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
@endsection

