@extends('layouts.public')

@section('title', "Invoice Servis " . ($order->invoice_number ?? $order->order_number) . " — Toyota AutoConnect")

@section('content')
@include('customer.partials.subnav')

@php
    $laborItems = $order->items->filter(fn ($i) => in_array($i->item_type, ['labor', 'service']) && $i->approval_status === 'approved');
    $approvedPartItems = $order->items->filter(fn ($i) => $i->item_type === 'part' && $i->approval_status === 'approved');
    $rejectedPartItems = $order->items->filter(fn ($i) => $i->item_type === 'part' && $i->approval_status === 'rejected');
    $laborSubtotal = (float) $laborItems->sum('subtotal');
    $partsSubtotal = (float) $approvedPartItems->sum('subtotal');
    $grandTotal = $laborSubtotal + $partsSubtotal;
@endphp

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Actions Bar -->
        <div class="flex items-center justify-between gap-4 print:hidden">
            <a href="{{ route('customer.service-histories.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                &larr; Kembali ke Riwayat Servis
            </a>

            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.641-2.073-1.168-2.983m13.896 0c-.527.91-1.008 1.907-1.168 2.983M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
                </svg>
                Cetak / Simpan PDF Invoice
            </button>
        </div>

        <!-- Pending Spare Part Confirmation Card (if any) -->
        @include('customer.partials.spare-part-approval-card', ['order' => $order])

        <!-- Official Toyota Service Invoice Card -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-10 shadow-2xs space-y-7 print:border-none print:shadow-none print:p-0">

            <!-- Header Bengkel Resmi & Nomor Invoice -->
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b-2 border-slate-900">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-red-600 flex items-center justify-center text-white font-black text-sm">
                            T
                        </div>
                        <span class="text-lg font-black tracking-tight text-slate-900">TOYOTA AUTOCONNECT</span>
                    </div>
                    <p class="text-xs font-bold text-slate-700 mt-2">{{ $order->branch?->name }}</p>
                    <p class="text-xs text-slate-500 max-w-sm mt-0.5 leading-relaxed">{{ $order->branch?->address }}, {{ $order->branch?->city }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Telepon: {{ $order->branch?->phone ?? '(021) 500-311' }} &bull; Bengkel Resmi Toyota</p>
                </div>

                <div class="text-left sm:text-right">
                    <span class="inline-block px-3 py-1 rounded bg-slate-900 text-white text-xs font-black uppercase tracking-wider">
                        INVOICE SERVIS RESMI
                    </span>
                    <p class="font-mono text-base font-black text-slate-900 mt-2">
                        {{ $order->invoice_number ?? $order->order_number }}
                    </p>
                    <p class="text-[11px] font-mono text-slate-500">Ref. Work Order: {{ $order->order_number }}</p>
                    <p class="text-xs text-slate-500 mt-1">
                        Tanggal: {{ ($order->invoice_issued_at ?? $order->completed_at ?? $order->delivered_at ?? $order->updated_at)->translatedFormat('d F Y') }}
                    </p>
                    <div class="mt-2 flex items-center sm:justify-end gap-1.5 flex-wrap">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $order->status_badge_class }}">
                            {{ $order->status_label }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-extrabold border {{ $order->payment_badge_class }}">
                            {{ $order->isPaid() ? 'LUNAS' : 'MENUNGGU PEMBAYARAN' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer & Vehicle Info Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <div class="space-y-1.5 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-1.5">Ditagihkan Kepada:</span>
                    <p class="font-bold text-slate-900 text-sm">{{ $order->user?->name }}</p>
                    <p class="text-slate-600">{{ $order->user?->email }}</p>
                    <p class="text-slate-600">{{ $order->user?->phone ?? '-' }}</p>
                </div>

                <div class="space-y-1.5 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-1.5">Informasi Kendaraan &amp; Layanan:</span>
                    <p class="font-bold text-slate-900 text-sm">{{ $order->vehicle_name }}</p>
                    <p class="text-slate-600 font-mono font-bold">Plat Nomor: {{ $order->license_plate }}</p>
                    <p class="text-slate-600">Paket Utama: <strong>{{ $order->serviceType?->name ?? 'Servis Berkala' }}</strong></p>
                    <p class="text-slate-600">Odometer: <strong>{{ $order->current_mileage ? number_format($order->current_mileage, 0, ',', '.') . ' KM' : '-' }}</strong>
                        @if($order->technician_name)
                            &bull; Teknisi: <strong>{{ $order->technician_name }}</strong>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Inspection & Repair Actions -->
            @if($order->inspection_result || $order->repair_actions)
                <div class="rounded-xl border border-slate-200 p-5 space-y-3 text-xs bg-slate-50/50">
                    <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[11px]">Hasil Pemeriksaan &amp; Diagnosa Bengkel:</h4>
                    @if($order->inspection_result)
                        <div class="text-slate-700 leading-relaxed">
                            <strong class="font-semibold text-slate-900">Temuan Teknisi:</strong>
                            <p class="mt-0.5 whitespace-pre-line">{{ $order->inspection_result }}</p>
                        </div>
                    @endif
                    @if($order->repair_actions)
                        <div class="text-slate-700 leading-relaxed pt-2 border-t border-slate-200">
                            <strong class="font-semibold text-slate-900">Tindakan Perbaikan / Servis:</strong>
                            <p class="mt-0.5 whitespace-pre-line">{{ $order->repair_actions }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- SECTION A: Rincian Biaya Jasa Servis -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="font-extrabold text-slate-900 uppercase tracking-wider text-xs flex items-center gap-2">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-sky-100 text-sky-700 text-[10px] font-black">A</span>
                        Rincian Biaya Jasa Servis &amp; Pengerjaan Teknisi
                    </h4>
                    <span class="font-mono text-xs font-bold text-sky-700">Rp {{ number_format($laborSubtotal, 0, ',', '.') }}</span>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs min-w-[600px]">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-2.5 w-12">No</th>
                                <th class="px-4 py-2.5">Deskripsi Pekerjaan Jasa</th>
                                <th class="px-4 py-2.5 text-center w-20">Qty</th>
                                <th class="px-4 py-2.5 text-right w-32">Tarif Jasa</th>
                                <th class="px-4 py-2.5 text-right w-36">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($laborItems->values() as $idx => $item)
                                <tr>
                                    <td class="px-4 py-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">
                                        {{ $item->name }}
                                        @if($item->serviceType)
                                            <span class="block font-mono text-[10px] text-slate-400">Kode Layanan: {{ $item->serviceType->code }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-700">{{ $item->formatted_unit_price }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">{{ $item->formatted_subtotal }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-slate-400">
                                        Biaya jasa servis standar (Rp 0 / Free Service Package).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION B: Rincian Suku Cadang Resmi (Toyota Genuine Parts) -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="font-extrabold text-slate-900 uppercase tracking-wider text-xs flex items-center gap-2">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-emerald-100 text-emerald-700 text-[10px] font-black">B</span>
                        Rincian Suku Cadang Resmi (Disetujui Pelanggan)
                    </h4>
                    <span class="font-mono text-xs font-bold text-emerald-700">Rp {{ number_format($partsSubtotal, 0, ',', '.') }}</span>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs min-w-[600px]">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-2.5 w-12">No</th>
                                <th class="px-4 py-2.5">Nama Suku Cadang &amp; Kode Part (SKU)</th>
                                <th class="px-4 py-2.5 text-center w-20">Qty</th>
                                <th class="px-4 py-2.5 text-right w-32">Harga Satuan</th>
                                <th class="px-4 py-2.5 text-right w-36">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($approvedPartItems->values() as $idx => $item)
                                <tr>
                                    <td class="px-4 py-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-slate-900 block">{{ $item->name }}</span>
                                        @if($item->sparePart)
                                            <span class="font-mono text-[10px] font-bold text-slate-500">SKU: {{ $item->sparePart->part_number }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $item->quantity }} {{ $item->sparePart?->unit ?? '' }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-700">{{ $item->formatted_unit_price }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">{{ $item->formatted_subtotal }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-slate-400">
                                        Tidak ada penggantian suku cadang tambahan pada servis ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($rejectedPartItems->isNotEmpty())
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 text-xs space-y-1.5 print:hidden">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Item Suku Cadang Ditolak Pelanggan (Tidak Ditagihkan — Rp 0):</span>
                    <ul class="list-disc pl-4 space-y-1 text-slate-500">
                        @foreach($rejectedPartItems as $rItem)
                            <li>
                                <span class="line-through">{{ $rItem->name }} (Qty: {{ $rItem->quantity }})</span>
                                @if($rItem->customer_response_note)
                                    — <span class="italic">"{{ $rItem->customer_response_note }}"</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Grand Total Summary Box -->
            <div class="rounded-2xl bg-slate-900 text-white p-5 sm:p-6 space-y-2.5 text-xs">
                <div class="flex items-center justify-between text-slate-300">
                    <span>Subtotal Biaya Jasa Servis (A)</span>
                    <span class="font-mono font-semibold text-white">Rp {{ number_format($laborSubtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-300">
                    <span>Subtotal Suku Cadang Resmi (B)</span>
                    <span class="font-mono font-semibold text-white">Rp {{ number_format($partsSubtotal, 0, ',', '.') }}</span>
                </div>
                <div class="pt-3 mt-2 border-t border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400 block">Grand Total Tagihan Invoice</span>
                        @if($order->isPaid())
                            <span class="text-[11px] text-emerald-400 font-semibold">
                                Lunas dibayar pada {{ $order->paid_at?->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                        @else
                            <span class="text-[11px] text-amber-300">
                                Pembayaran dapat diselesaikan di kasir bengkel {{ $order->branch?->name }} saat pengambilan kendaraan.
                            </span>
                        @endif
                    </div>
                    <span class="font-mono text-2xl font-black text-white">
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Footer / Garansi Servis -->
            <div class="pt-6 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-slate-500">
                <div>
                    <h5 class="font-bold text-slate-800 uppercase tracking-wider text-[11px] mb-1">Ketentuan Garansi Servis Toyota:</h5>
                    <p class="leading-relaxed text-[11px]">Seluruh pekerjaan bengkel &amp; suku cadang resmi dilindungi garansi servis Toyota selama 1.000 KM atau 15 hari kalender sejak invoice diterbitkan.</p>
                </div>
                <div class="text-left sm:text-right text-[11px] space-y-1">
                    <p>Service Advisor: <strong class="text-slate-700">{{ $order->serviceAdvisor?->name ?? 'Tim Bengkel Resmi Toyota' }}</strong></p>
                    <p class="font-mono text-slate-400">Dicetak digital pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
