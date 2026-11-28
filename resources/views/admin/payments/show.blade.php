@extends('layouts.dashboard')

@section('title', 'Detail Pembayaran ' . $payment->order_id . ' — Toyota AutoConnect')
@section('header_title', 'Detail Transaksi Pembayaran ' . $payment->order_id)
@section('header_subtitle', 'Verifikasi bukti pembayaran digital, respons gateway Midtrans, dan log webhook notifikasi')

@section('content')
<div class="space-y-6" x-data="{ refundModalOpen: false }">

    <!-- Top Action & Status Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('admin.payments.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Transaksi
        </a>

        <div class="flex items-center gap-2">
            @can('manage', $payment)
                @if ($payment->isPaid())
                    <button type="button"
                            @click="refundModalOpen = true"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-purple-300 bg-purple-50 px-3 py-1.5 text-xs font-bold text-purple-700 hover:bg-purple-100 transition shadow-2xs">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                        </svg>
                        Proses Refund
                    </button>
                @endif
            @endcan

            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $payment->status_badge_class }}">
                {{ $payment->status_label }}
            </span>
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Payment Receipt & Details (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Primary Payment Card -->
            <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-xs space-y-6">

                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-4">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ORDER ID MIDTRANS</span>
                        <h2 class="text-xl font-extrabold text-slate-900 font-mono mt-0.5">{{ $payment->order_id }}</h2>
                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                            <span>Kode Sistem: <strong class="font-mono text-slate-700">{{ $payment->code }}</strong></span>
                            @if ($payment->gateway_transaction_id)
                                <span>&bull;</span>
                                <span>Trx ID: <strong class="font-mono text-slate-700">{{ $payment->gateway_transaction_id }}</strong></span>
                            @endif
                        </div>
                    </div>
                    <div class="text-left sm:text-right text-xs space-y-0.5">
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">Waktu Transaksi</span>
                        <span class="font-bold text-slate-800 block">{{ $payment->created_at->format('d M Y, H:i') }} WIB</span>
                        @if ($payment->paid_at)
                            <span class="text-[11px] text-emerald-600 font-medium block">Lunas: {{ $payment->paid_at->format('d M Y, H:i') }} WIB</span>
                        @endif
                    </div>
                </div>

                <!-- Financial Highlight Box -->
                <div class="rounded-xl bg-slate-900 text-white p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Nominal Tanda Jadi</span>
                        <p class="text-2xl sm:text-3xl font-black font-mono text-white mt-0.5">
                            {{ $payment->formatted_amount }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1">Pembayaran SPK Toyota AutoConnect</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-[10px] text-slate-400 block uppercase">Metode Pembayaran</span>
                        <p class="text-sm font-bold text-white mt-0.5 capitalize">
                            {{ str_replace('_', ' ', $payment->payment_method ?? 'Midtrans Snap') }}
                        </p>
                        <span class="text-[10px] text-slate-400">Gateway: Midtrans Production/Sandbox</span>
                    </div>
                </div>

                <!-- Vehicle & SPK Booking Box -->
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ $payment->vehicleBooking->vehicle->primary_image_url }}"
                             alt="{{ $payment->vehicleBooking->vehicle->name }}"
                             class="h-14 w-20 object-cover rounded-lg border border-slate-200 bg-slate-950 flex-shrink-0">
                        <div>
                            <span class="text-[10px] font-mono text-slate-400 block">SPK: {{ $payment->vehicleBooking->code }}</span>
                            <h3 class="text-sm font-extrabold text-slate-900 mt-0.5">{{ $payment->vehicleBooking->vehicle->name }}</h3>
                            <p class="text-[11px] text-slate-500">{{ $payment->vehicleBooking->branch->name }}</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <a href="{{ route('admin.sales.bookings.show', $payment->vehicleBooking) }}"
                           class="inline-flex items-center gap-1 text-xs font-bold text-red-600 hover:underline">
                            Buka Berkas SPK &rarr;
                        </a>
                    </div>
                </div>

                <!-- Refund Notice if exists -->
                @if ($payment->status === 'refunded')
                    <div class="rounded-xl bg-purple-50 border border-purple-200 p-4 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700">Transaksi Telah Dikembalikan (Refund)</span>
                            <span class="text-[11px] text-purple-600">{{ $payment->refunded_at ? $payment->refunded_at->format('d M Y, H:i') : '' }}</span>
                        </div>
                        <p class="text-xs text-purple-900 font-bold font-mono">Nominal: Rp {{ number_format($payment->refund_amount, 0, ',', '.') }}</p>
                        <p class="text-xs text-purple-800">Alasan: {{ $payment->refund_reason }}</p>
                    </div>
                @endif

                <!-- Gateway Raw Payload Inspection Accordion -->
                @if ($payment->gateway_response)
                    <div class="rounded-xl border border-slate-200 overflow-hidden" x-data="{ rawOpen: false }">
                        <button type="button"
                                @click="rawOpen = !rawOpen"
                                class="w-full flex items-center justify-between bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                            <span>Metadata Respons Gateway Midtrans (JSON)</span>
                            <span x-text="rawOpen ? 'Tutup &uarr;' : 'Buka &darr;'" class="text-[10px] text-slate-400"></span>
                        </button>
                        <div x-show="rawOpen" x-cloak class="p-4 bg-slate-950 text-emerald-400 text-[11px] font-mono overflow-x-auto max-h-60">
                            <pre>{{ json_encode($payment->gateway_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Webhook Notifications Audit Trail Card -->
            <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3">
                    Log Webhook Notifikasi Midtrans (Idempotency Audit)
                </h3>
                <div class="space-y-3">
                    @forelse ($payment->notifications as $notif)
                        <div class="rounded-lg bg-slate-50 p-3.5 border border-slate-200/80 text-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-slate-800">ID: {{ $notif->notification_id }}</span>
                                <span class="rounded bg-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-700 uppercase">
                                    {{ $notif->processing_status }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span>Diterima: {{ $notif->received_at ? $notif->received_at->format('d M Y, H:i:s') : $notif->created_at->format('d M Y, H:i:s') }}</span>
                                @if ($notif->processed_at)
                                    <span>Diproses: {{ $notif->processed_at->format('H:i:s') }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada log notifikasi webhook yang tercatat untuk transaksi ini.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Column: Customer & Sales Profile (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Customer Card -->
            <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-xs space-y-3 text-xs">
                <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] border-b border-slate-100 pb-2">
                    Profil Nasabah Pembayar
                </h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-[10px] text-slate-400 block">Nama Nasabah:</span>
                        <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $payment->user->name }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block">Email:</span>
                        <p class="text-slate-800">{{ $payment->user->email }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block">Nomor Telepon:</span>
                        <p class="font-mono text-slate-800">{{ $payment->user->phone_number ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Booking & Sales PIC Card -->
            <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-xs space-y-3 text-xs">
                <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] border-b border-slate-100 pb-2">
                    Konsultan Sales &amp; Cabang
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Cabang Penjualan:</span>
                        <span class="font-bold text-slate-800">{{ $payment->vehicleBooking->branch->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Sales PIC:</span>
                        <span class="font-semibold text-slate-800">
                            {{ $payment->vehicleBooking->salesOpportunity?->assignedSales->name ?? 'Sales Executive Toyota' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Status Alokasi Unit:</span>
                        <span class="font-bold text-slate-800 capitalize">{{ $payment->vehicleBooking->status_label }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Refund Modal -->
    <div x-show="refundModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="refundModalOpen = false"
             class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white p-6 shadow-xl border border-slate-200 space-y-4">
            
            <div class="flex items-center gap-2.5 text-purple-700 font-bold">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                <h3 class="text-sm font-extrabold text-slate-900">Konfirmasi Pengembalian Dana (Refund)</h3>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                Tindakan ini akan mencatat pengembalian dana pembayaran tanda jadi senilai <strong class="text-slate-800 font-mono">{{ $payment->formatted_amount }}</strong> dan memperbarui status SPK.
            </p>

            <form action="{{ route('admin.payments.refund', $payment) }}" method="POST" class="space-y-3.5 text-xs">
                @csrf

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                        Nominal Refund (Rp)
                    </label>
                    <input type="number"
                           name="refund_amount"
                           value="{{ $payment->amount }}"
                           max="{{ $payment->amount }}"
                           min="1000"
                           required
                           class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-mono font-bold text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                        Alasan Pengembalian Dana <span class="text-red-600">*</span>
                    </label>
                    <textarea name="refund_reason"
                              rows="3"
                              required
                              placeholder="Contoh: Pembatalan pesanan SPK disetujui manajemen atau unit tidak tersedia..."
                              class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button"
                            @click="refundModalOpen = false"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-purple-700 px-4 py-1.5 text-xs font-bold text-white hover:bg-purple-800 transition shadow-xs">
                        Proses Refund
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

