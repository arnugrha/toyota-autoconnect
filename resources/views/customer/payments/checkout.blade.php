@extends('layouts.public')

@section('title', 'Pembayaran Tanda Jadi SPK ' . $booking->code . ' — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Navigation -->
        <div class="flex items-center justify-between">
            <a href="{{ route('customer.bookings.show', $booking) }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Detail Pemesanan
            </a>

            <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 text-xs font-bold">
                Midtrans Snap Sandbox
            </span>
        </div>

        <!-- Checkout Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-6 sm:p-8 space-y-6"
             x-data="midtransCheckout('{{ $payment->snap_token }}', '{{ $payment->order_id }}', '{{ $payment->snap_redirect_url }}')">

            <div class="text-center space-y-2 pb-6 border-b border-slate-100">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                </div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Konfirmasi & Pembayaran Tanda Jadi</h1>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Kunci alokasi unit kendaraan resmi Toyota Anda dengan melakukan pembayaran tanda jadi (booking fee) secara instan dan aman.
                </p>
            </div>

            <!-- Booking Item Card -->
            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <img src="{{ $booking->vehicle->primary_image_url }}"
                         alt="{{ $booking->vehicle->name }}"
                         class="h-14 w-20 object-cover rounded-lg border border-slate-200 bg-slate-950 flex-shrink-0">
                    <div>
                        <span class="text-[10px] font-mono text-slate-400 block">{{ $booking->code }}</span>
                        <h3 class="text-sm font-extrabold text-slate-900 mt-0.5">{{ $booking->vehicle->name }}</h3>
                        <p class="text-[11px] text-slate-500">{{ $booking->branch->name }}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Tagihan</span>
                    <p class="text-base font-black font-mono text-slate-900 mt-0.5">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Payment Breakdown Details -->
            <div class="rounded-xl border border-slate-200 overflow-hidden text-xs">
                <div class="bg-slate-50 px-4 py-2.5 font-bold text-slate-700 border-b border-slate-200 flex justify-between">
                    <span>Rincian Pembayaran</span>
                    <span class="font-mono text-[10px] text-slate-400">Order ID: {{ $payment->order_id }}</span>
                </div>
                <div class="divide-y divide-slate-100 p-4 space-y-2">
                    <div class="flex justify-between text-slate-600 pt-1">
                        <span>Pemesanan Unit Kendaraan</span>
                        <span class="font-medium text-slate-800">{{ $booking->vehicle->name }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600 pt-2">
                        <span>Batas Waktu Pembayaran</span>
                        <span class="font-medium text-slate-800">
                            {{ $payment->expired_at ? $payment->expired_at->format('d M Y, H:i') : '24 Jam' }} WIB
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-slate-900 font-bold pt-3 border-t border-slate-200 text-sm">
                        <span>Nominal Tanda Jadi (Booking Fee)</span>
                        <span class="font-mono text-base text-red-600">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Supported Channels Badge -->
            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 text-center space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Metode Pembayaran Tersedia</span>
                <p class="text-xs text-slate-600 font-medium">
                    BCA Virtual Account, Mandiri Bill, BNI, BRI, Permata, GoPay, QRIS, dan Kartu Kredit / Debit
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3 pt-2">
                <button type="button"
                        @click="payWithSnap()"
                        :disabled="loading"
                        class="w-full flex items-center justify-center gap-2 rounded-xl bg-red-600 py-3.5 px-6 text-sm font-bold text-white shadow-md hover:bg-red-700 transition disabled:opacity-50">
                    <span x-show="!loading" class="flex items-center gap-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        Bayar Sekarang via Midtrans Snap
                    </span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Mempersiapkan Gateway...
                    </span>
                </button>

                @if ($payment->snap_redirect_url)
                    <div class="text-center pt-2">
                        <span class="text-xs text-slate-400 block mb-1">Jika pop-up tidak muncul di peramban Anda:</span>
                        <a href="{{ $payment->snap_redirect_url }}"
                           target="_blank"
                           class="text-xs font-semibold text-red-600 hover:underline inline-flex items-center gap-1">
                            Buka Halaman Pembayaran Langsung &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <div class="pt-4 border-t border-slate-100 text-center">
                <span class="text-[11px] text-slate-400">
                    Pembayaran diproses secara instan melalui sistem enkripsi PCI-DSS Midtrans Payment Gateway.
                </span>
            </div>

        </div>

    </div>
</div>

<!-- Midtrans Snap Script -->
<script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('midtransCheckout', (snapToken, orderId, redirectUrl) => ({
            snapToken: snapToken,
            orderId: orderId,
            redirectUrl: redirectUrl,
            loading: false,

            payWithSnap() {
                if (!this.snapToken) {
                    if (this.redirectUrl) {
                        window.location.href = this.redirectUrl;
                    } else {
                        alert('Token pembayaran tidak ditemukan. Silakan muat ulang halaman.');
                    }
                    return;
                }

                if (typeof window.snap === 'undefined') {
                    if (this.redirectUrl) {
                        window.location.href = this.redirectUrl;
                    } else {
                        alert('Modul Midtrans Snap belum selesai dimuat. Silakan tunggu beberapa saat atau buka halaman langsung.');
                    }
                    return;
                }

                const self = this;
                this.loading = true;

                window.snap.pay(this.snapToken, {
                    onSuccess: function(result) {
                        self.loading = false;
                        window.location.href = '{{ route('customer.payments.finish') }}?order_id=' + encodeURIComponent(self.orderId) + '&status_code=' + encodeURIComponent(result.status_code || 200) + '&transaction_status=settlement';
                    },
                    onPending: function(result) {
                        self.loading = false;
                        window.location.href = '{{ route('customer.payments.finish') }}?order_id=' + encodeURIComponent(self.orderId) + '&status_code=' + encodeURIComponent(result.status_code || 201) + '&transaction_status=pending';
                    },
                    onError: function(result) {
                        self.loading = false;
                        alert('Terjadi kendala saat memproses transaksi pembayaran. Silakan coba kembali.');
                    },
                    onClose: function() {
                        self.loading = false;
                        // Customer closed the popup without finishing the payment
                    }
                });
            }
        }));
    });
</script>
@endsection

