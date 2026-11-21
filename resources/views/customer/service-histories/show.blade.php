@extends('layouts.public')

@section('title', "Faktur Servis {$order->order_number} — Toyota AutoConnect")

@section('content')
@include('customer.partials.subnav')

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
                Cetak Faktur Resmi
            </button>
        </div>

        <!-- Official Toyota Invoice Card -->
        <div class="rounded-2xl border border-slate-200 bg-white p-8 sm:p-10 shadow-2xs space-y-8 print:border-none print:shadow-none print:p-0">

            <!-- Header Bengkel Resmi -->
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
                    <span class="inline-block px-3 py-1 rounded bg-slate-100 text-slate-900 text-xs font-black uppercase tracking-wider">
                        FAKTUR SERVIS RESMI
                    </span>
                    <p class="font-mono text-base font-extrabold text-slate-900 mt-2">{{ $order->order_number }}</p>
                    <p class="text-xs text-slate-500 mt-1">Tanggal: {{ ($order->completed_at ?? $order->delivered_at)?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}</p>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border mt-1 {{ $order->status_badge_class }}">
                        {{ $order->status_label }}
                    </span>
                </div>
            </div>

            <!-- Customer & Vehicle Info Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                <div class="space-y-1.5 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-2">Informasi Pemilik:</span>
                    <p class="font-bold text-slate-900 text-sm">{{ $order->user?->name }}</p>
                    <p class="text-slate-600">{{ $order->user?->email }}</p>
                    <p class="text-slate-600">{{ $order->user?->phone ?? '-' }}</p>
                </div>

                <div class="space-y-1.5 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-2">Informasi Kendaraan:</span>
                    <p class="font-bold text-slate-900 text-sm">{{ $order->vehicle_name }}</p>
                    <p class="text-slate-600 font-mono font-bold">Plat Nomor: {{ $order->license_plate }}</p>
                    <p class="text-slate-600">Odometer: <strong>{{ number_format($order->current_mileage, 0, ',', '.') }} KM</strong></p>
                    @if($order->technician_name)
                        <p class="text-slate-600">Teknisi: <strong>{{ $order->technician_name }}</strong></p>
                    @endif
                </div>
            </div>

            <!-- Inspection & Notes -->
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

            <!-- Items Table -->
            <div class="space-y-3">
                <h4 class="font-bold text-slate-900 uppercase tracking-wider text-xs">Rincian Jasa &amp; Suku Cadang Resmi (Toyota Genuine Parts)</h4>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs min-w-[700px]">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                            <tr>
                                <th class="px-4 py-3 whitespace-nowrap min-w-[50px]">No</th>
                                <th class="px-4 py-3 whitespace-nowrap min-w-[100px]">Jenis</th>
                                <th class="px-4 py-3 whitespace-nowrap min-w-[240px]">Deskripsi Pekerjaan / Suku Cadang</th>
                                <th class="px-4 py-3 text-center whitespace-nowrap min-w-[70px]">Qty</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap min-w-[120px]">Harga Satuan</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap min-w-[120px]">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($order->items as $idx => $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 text-slate-400 font-mono whitespace-nowrap">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold {{ $item->item_type === 'labor' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                            {{ $item->item_type_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">{{ $item->name }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-800 whitespace-nowrap">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-700 whitespace-nowrap">{{ $item->formatted_unit_price }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">{{ $item->formatted_subtotal }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-slate-400 text-xs">
                                        Paket servis standar tanpa tambahan suku cadang khusus.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold">
                            <tr>
                                <td colspan="5" class="px-4 py-3.5 text-right text-xs uppercase tracking-wider text-slate-800">
                                    Total Biaya Servis:
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono text-base text-red-600">
                                    {{ $order->formatted_final_cost }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Footer / Garansi Servis -->
            <div class="pt-6 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-slate-500">
                <div>
                    <h5 class="font-bold text-slate-800 uppercase tracking-wider text-[11px] mb-1">Ketentuan Garansi Servis Toyota:</h5>
                    <p class="leading-relaxed text-[11px]">Seluruh pekerjaan bengkel dilindungi garansi servis resmi selama 1.000 KM atau 15 hari kalender sejak faktur diterbitkan.</p>
                </div>
                <div class="text-left sm:text-right text-[11px] space-y-1">
                    <p>Terima kasih telah mempercayakan kendaraan Anda pada Bengkel Resmi Toyota AutoConnect.</p>
                    <p class="font-mono text-slate-400">Dicetak digital pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

