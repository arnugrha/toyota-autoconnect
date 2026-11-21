@extends('layouts.dashboard')

@section('title', 'Kelola SPK ' . $booking->code . ' — Toyota AutoConnect')
@section('header_title', 'Kelola Pemesanan Mobil & Alokasi Stok')
@section('header_subtitle', 'Verifikasi SPK, pengalokasian unit inventaris, dan pencatatan tanda jadi')

@section('content')
<div class="space-y-6 max-w-5xl" x-data="{ cancelModalOpen: false, paymentModalOpen: false }">

    <!-- Flash message -->
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-xs text-emerald-800">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800">
            <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.sales.bookings.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Pemesanan SPK
        </a>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $booking->status_badge_class }}">
                {{ $booking->status_label }}
            </span>
        </div>
    </div>

    <!-- Booking Document Card -->
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NOMOR REGISTER PEMESANAN KENDARAAN (SPK)</span>
                <h2 class="text-xl font-extrabold text-slate-900 font-mono mt-0.5">{{ $booking->code }}</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Calon Pembeli: <strong class="text-slate-800">{{ $booking->customer->name }}</strong> &bull; Cabang: {{ $booking->branch->name }}
                </p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[10px] text-slate-400 block uppercase tracking-wider font-bold">Tanggal Pemesanan</span>
                <span class="text-xs font-extrabold text-slate-800 block">{{ $booking->booking_date->format('d F Y') }}</span>
                @if ($booking->expires_at)
                    <span class="text-[10px] text-amber-600 font-medium block">Batas Berlaku: {{ $booking->expires_at->format('d M Y') }}</span>
                @endif
                <span class="text-[10px] text-slate-400 block mt-0.5">Diterbitkan oleh: {{ $booking->createdBy->name ?? 'Sales Staff' }}</span>
            </div>
        </div>

        <!-- Vehicle Box -->
        <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 flex items-center justify-between">
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
            <div class="text-right">
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
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">Harga Kesepakatan OTR</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($booking->vehicle_price, 0, ',', '.') }}
                            </td>
                        </tr>
                        @if ($booking->discount_amount > 0)
                            <tr class="bg-emerald-50/50">
                                <td class="py-3 px-4 text-emerald-800 font-semibold whitespace-nowrap">Potongan Diskon Disepakati</td>
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
                            <td class="py-3 px-4 text-blue-900 whitespace-nowrap">Tanda Jadi (Booking Fee) Terdaftar</td>
                            <td class="py-3 px-4 text-right font-mono text-blue-900 whitespace-nowrap">
                                Rp {{ number_format($booking->booking_fee, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Real-Time Branch Inventory Status Box -->
        <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                    Status Alokasi Inventaris Cabang ({{ $booking->branch->name }})
                </span>
                @if ($booking->status === 'confirmed')
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Unit Telah Dialokasikan
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="rounded-lg bg-white p-3 border border-slate-200">
                    <span class="text-slate-400 text-[10px] block">Stok Fisik Showroom</span>
                    <span class="text-sm font-extrabold text-slate-900">{{ $booking->inventory->stock_quantity ?? 0 }} Unit</span>
                </div>
                <div class="rounded-lg bg-white p-3 border border-slate-200">
                    <span class="text-amber-600 text-[10px] font-semibold block">Total Reserved (SPK Aktif)</span>
                    <span class="text-sm font-extrabold text-amber-700">{{ $booking->inventory->reserved_quantity ?? 0 }} Unit</span>
                </div>
                <div class="rounded-lg bg-white p-3 border border-slate-200">
                    <span class="text-emerald-600 text-[10px] font-semibold block">Stok Bebas Tersedia</span>
                    <span class="text-sm font-extrabold text-emerald-700">{{ $booking->inventory ? $booking->inventory->available_stock : 0 }} Unit</span>
                </div>
            </div>
        </div>

        <!-- Payment Status Record -->
        <div class="rounded-xl border border-slate-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pencatatan Status Tanda Jadi (Internal)</span>
                <p class="text-xs font-bold text-slate-800 mt-0.5">{{ $booking->payment_status_label }}</p>
                <p class="text-[11px] text-slate-500">Pencatatan status tanda jadi internal dealer.</p>
            </div>
            <button type="button" @click="paymentModalOpen = true"
                    class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                Perbarui Status Tanda Jadi
            </button>
        </div>

        @if ($booking->payments && $booking->payments->isNotEmpty())
            <!-- Midtrans Payment Gateway Transactions -->
            <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-blue-50 text-blue-700 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border border-blue-200">
                            Midtrans Snap
                        </span>
                        <h4 class="text-xs font-bold text-slate-900">Riwayat Pembayaran Tanda Jadi Online (Midtrans)</h4>
                    </div>
                    <span class="text-[11px] text-slate-400 font-mono">{{ $booking->payments->count() }} transaksi tercatat</span>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @foreach ($booking->payments as $pmt)
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-slate-800">{{ $pmt->order_id }}</span>
                                    <span class="font-mono text-[10px] text-slate-400">({{ $pmt->code }})</span>
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    Metode: <strong class="capitalize">{{ $pmt->payment_method ?? 'Belum dipilih' }}</strong> &bull;
                                    Waktu: {{ $pmt->created_at->format('d M Y H:i') }} WIB
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <span class="font-mono font-bold text-slate-900">{{ $pmt->formatted_amount }}</span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $pmt->status_badge_class }}">
                                    {{ $pmt->status_label }}
                                </span>
                                @can('view', $pmt)
                                    <a href="{{ route('admin.payments.show', $pmt) }}"
                                       class="text-xs font-semibold text-red-600 hover:underline">
                                        Detail &rarr;
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($booking->cancellation_reason)
            <div class="rounded-xl bg-rose-50 p-4 border border-rose-100 text-xs text-rose-800">
                <span class="font-bold block uppercase tracking-wider text-[10px] text-rose-600">Alasan Pembatalan:</span>
                <p class="mt-0.5">{{ $booking->cancellation_reason }}</p>
            </div>
        @endif

        <!-- Action Controls -->
        <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <span class="text-xs text-slate-500">Status saat ini: <strong class="text-slate-800">{{ $booking->status_label }}</strong></span>

            <div class="flex items-center gap-2">
                @if ($booking->status === 'pending')
                    <form action="{{ route('admin.sales.bookings.confirm', $booking) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Konfirmasi &amp; Alokasikan Unit Stok
                        </button>
                    </form>
                @endif

                @if ($booking->status === 'confirmed')
                    <form action="{{ route('admin.sales.bookings.complete', $booking) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-blue-700 transition">
                            Serah Terima Unit (Complete SPK)
                        </button>
                    </form>

                    <button type="button" @click="cancelModalOpen = true"
                            class="rounded-lg bg-rose-50 border border-rose-300 px-3.5 py-2 text-xs font-bold text-rose-600 hover:bg-rose-100 transition">
                        Batalkan SPK &amp; Lepaskan Stok
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Payment Status Modal -->
    <div x-show="paymentModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div @click.outside="paymentModalOpen = false" 
             class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-xl border border-slate-200">
            <h3 class="text-sm font-bold text-slate-900">Perbarui Status Tanda Jadi (Internal)</h3>
            <p class="text-xs text-slate-500 mt-1">Pencatatan verifikasi penerimaan pembayaran tanda jadi untuk SPK {{ $booking->code }}.</p>

            <form action="{{ route('admin.sales.bookings.payment-status.update', $booking) }}" method="POST" class="mt-4 space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Pembayaran</label>
                    <select name="payment_status" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none font-semibold">
                        <option value="unpaid" {{ $booking->payment_status === 'unpaid' ? 'selected' : '' }}>Belum Lunas / Belum Terverifikasi</option>
                        <option value="verified" {{ $booking->payment_status === 'verified' ? 'selected' : '' }}>Tanda Jadi Terverifikasi (Diterima Kasir/Bank)</option>
                        <option value="refunded" {{ $booking->payment_status === 'refunded' ? 'selected' : '' }}>Tanda Jadi Dikembalikan (Refund)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Bukti / Kuitansi</label>
                    <textarea name="notes" rows="2" placeholder="Nomor kuitansi manual, bukti transfer, atau referensi kasir..."
                              class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ $booking->notes }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="paymentModalOpen = false"
                            class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-200">
                        Batal
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-slate-800">
                        Simpan Catatan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancellation Modal with Stock Release -->
    <div x-show="cancelModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div @click.outside="cancelModalOpen = false" 
             class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-xl border border-slate-200">
            <h3 class="text-sm font-bold text-slate-900">Batalkan Pemesanan SPK &amp; Lepaskan Stok</h3>
            <p class="text-xs text-slate-500 mt-1">
                Tindakan ini akan membatalkan SPK <strong class="text-slate-800">{{ $booking->code }}</strong> dan secara otomatis <strong>mengembalikan 1 unit alokasi stok</strong> ke inventaris bebas cabang {{ $booking->branch->name }}.
            </p>

            <form action="{{ route('admin.sales.bookings.cancel', $booking) }}" method="POST" class="mt-4 space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[11px] font-bold text-rose-700 mb-1">Alasan Pembatalan SPK <span class="text-rose-600">*</span></label>
                    <textarea name="cancellation_reason" rows="2" required placeholder="Tuliskan alasan pembatalan pemesanan unit..."
                              class="w-full rounded-lg border border-rose-300 bg-rose-50/30 px-3 py-1.5 text-xs text-slate-900 focus:border-rose-600 focus:ring-1 focus:ring-rose-600 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="cancelModalOpen = false"
                            class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-200">
                        Kembali
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-rose-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-rose-700">
                        Ya, Batalkan &amp; Lepaskan Stok
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

