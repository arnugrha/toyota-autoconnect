@extends('layouts.dashboard')

@section('title', "Work Order {$order->order_number} — Toyota AutoConnect")

@section('content')
<div class="space-y-6" x-data="{ showStatusModal: false, showInspectionModal: false, showAddItemModal: false }">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.service-orders.index') }}" class="hover:text-red-600 transition">Work Orders</a>
                <span>/</span>
                <span class="text-slate-900 font-bold font-mono">{{ $order->order_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight font-mono">{{ $order->order_number }}</h1>
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold border {{ $order->status_badge_class }}">
                    {{ $order->status_label }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.service-orders.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                &larr; Kembali
            </a>

            <button type="button"
                    @click="showStatusModal = true"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Perbarui Tahapan Status
            </button>

            <button type="button"
                    @click="showInspectionModal = true"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 px-3.5 py-2 text-xs font-bold text-amber-800 hover:bg-amber-100 transition shadow-2xs">
                <svg class="h-4 w-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
                Catat Diagnosa / Inspeksi
            </button>

            <button type="button"
                    @click="showAddItemModal = true"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Part / Jasa
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-2xs flex items-center gap-2">
            <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 9 Stages Workflow Bar -->
    @php
        $stages = [
            'scheduled' => 'Terjadwal',
            'confirmed' => 'Konfirmasi',
            'vehicle_received' => 'Mobil Tiba',
            'inspection' => 'Inspeksi',
            'in_progress' => 'Dikerjakan',
            'waiting_parts' => 'Tunggu Part',
            'completed' => 'Selesai',
            'delivered' => 'Diserahkan',
        ];
        $stageKeys = array_keys($stages);
        $currentIndex = array_search($order->status, $stageKeys);
    @endphp
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs overflow-x-auto">
        <div class="flex items-center min-w-[720px] justify-between text-xs font-bold">
            @foreach($stages as $key => $label)
                @php
                    $kIndex = array_search($key, $stageKeys);
                    $isPassed = ($currentIndex !== false && $kIndex <= $currentIndex);
                    $isCurrent = ($order->status === $key);
                @endphp
                <div class="flex items-center gap-2 {{ $loop->last ? '' : 'flex-1' }}">
                    <div class="flex flex-col items-center">
                        <div class="h-7 w-7 rounded-full flex items-center justify-center font-bold text-[10px] {{ $isCurrent ? 'bg-red-600 text-white ring-4 ring-red-100' : ($isPassed ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400') }}">
                            @if($isPassed && !$isCurrent)
                                &check;
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </div>
                        <span class="text-[10px] mt-1 whitespace-nowrap {{ $isCurrent ? 'text-red-600 font-extrabold' : ($isPassed ? 'text-slate-800' : 'text-slate-400') }}">
                            {{ $label }}
                        </span>
                    </div>
                    @if(!$loop->last)
                        <div class="h-0.5 flex-1 mx-2 {{ $isPassed && ($currentIndex > $kIndex) ? 'bg-emerald-600' : 'bg-slate-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <!-- Card 1: Data Kendaraan & Pemilik -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                    Informasi Kendaraan &amp; Pelanggan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Pemilik / Pelanggan:</span>
                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $order->user?->name }}</span>
                        <span class="text-slate-600 block">{{ $order->user?->email }}</span>
                        <span class="text-slate-600 block font-semibold">{{ $order->user?->phone ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Kendaraan:</span>
                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $order->vehicle_name }}</span>
                        <span class="inline-block mt-1 px-2.5 py-0.5 bg-slate-100 text-slate-800 font-mono text-xs font-bold rounded border border-slate-200">
                            {{ $order->license_plate }}
                        </span>
                        <p class="text-slate-600 mt-1">Kilometer Odometer: <strong>{{ $order->current_mileage ? number_format($order->current_mileage, 0, ',', '.') . ' KM' : '-' }}</strong></p>
                    </div>
                </div>

                @if($order->customer_complaints)
                    <div class="pt-3 border-t border-slate-100 text-xs">
                        <span class="text-[11px] text-slate-400 block font-semibold mb-1">Keluhan Pelanggan Saat Masuk:</span>
                        <div class="p-3 bg-slate-50 rounded-xl text-slate-700 italic border border-slate-100">
                            "{{ $order->customer_complaints }}"
                        </div>
                    </div>
                @endif
            </div>

            <!-- Card 2: Hasil Inspeksi Teknisi & Tindakan -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                        Hasil Pemeriksaan (Inspeksi) &amp; Solusi Perbaikan
                    </h3>
                    <button type="button" @click="showInspectionModal = true" class="text-xs text-red-600 hover:text-red-700 font-bold">
                        Edit Hasil Inspeksi &rarr;
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Teknisi Penanggung Jawab:</span>
                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $order->technician_name ?? 'Belum Ditugaskan' }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Service Advisor:</span>
                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $order->serviceAdvisor?->name ?? 'Staf Dealer' }}</span>
                    </div>
                </div>

                <div class="space-y-3 pt-2 text-xs">
                    <div>
                        <span class="text-[11px] text-slate-500 font-bold block mb-1">Temuan / Diagnosa Masalah:</span>
                        <div class="p-3.5 bg-slate-50 rounded-xl text-slate-800 border border-slate-100 whitespace-pre-line leading-relaxed">
                            {{ $order->inspection_result ?: 'Belum ada hasil inspeksi yang dicatat.' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-500 font-bold block mb-1">Rekomendasi Tindakan / Perbaikan:</span>
                        <div class="p-3.5 bg-slate-50 rounded-xl text-slate-800 border border-slate-100 whitespace-pre-line leading-relaxed">
                            {{ $order->repair_actions ?: 'Belum ada rekomendasi tindakan yang dicatat.' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Rincian Suku Cadang & Biaya Jasa -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                            Rincian Pekerjaan Jasa &amp; Suku Cadang Resmi
                        </h3>
                        <p class="text-[11px] text-slate-500">Item suku cadang dan jasa teknisi akan otomatis menjumlahkan nilai total faktur servis.</p>
                    </div>
                    <button type="button" @click="showAddItemModal = true" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-700 transition">
                        + Tambah Item
                    </button>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs min-w-[820px]">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                            <tr>
                                <th class="px-4 py-3 whitespace-nowrap min-w-[45px]">No</th>
                                <th class="px-4 py-3 whitespace-nowrap min-w-[95px]">Tipe</th>
                                <th class="px-4 py-3 whitespace-nowrap min-w-[230px]">Deskripsi Suku Cadang / Jasa</th>
                                <th class="px-4 py-3 text-center whitespace-nowrap min-w-[130px]">Konfirmasi User</th>
                                <th class="px-4 py-3 text-center whitespace-nowrap min-w-[65px]">Qty</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap min-w-[115px]">Harga Satuan</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap min-w-[115px]">Subtotal</th>
                                <th class="px-4 py-3 text-center whitespace-nowrap min-w-[65px]">Hapus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($order->items as $idx => $item)
                                <tr class="hover:bg-slate-50/50 {{ $item->isRejected() ? 'bg-rose-50/30 opacity-75' : '' }}">
                                    <td class="px-4 py-3 text-slate-400 font-mono whitespace-nowrap">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold {{ in_array($item->item_type, ['labor', 'service']) ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                            {{ $item->item_type_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-900 {{ $item->isRejected() ? 'line-through text-slate-500' : '' }}">{{ $item->name }}</div>
                                        @if($item->sparePart)
                                            <span class="font-mono text-[10px] font-bold text-slate-500">SKU: {{ $item->sparePart->part_number }}</span>
                                        @elseif($item->serviceType)
                                            <span class="font-mono text-[10px] font-bold text-slate-500">Kode: {{ $item->serviceType->code }}</span>
                                        @endif
                                        @if($item->customer_response_note)
                                            <p class="text-[10px] text-rose-600 italic mt-0.5">Catatan pelanggan: "{{ $item->customer_response_note }}"</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $item->approval_badge_class }}">
                                            {{ $item->approval_status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-800 whitespace-nowrap">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-700 whitespace-nowrap">{{ $item->formatted_unit_price }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold whitespace-nowrap {{ $item->isRejected() ? 'line-through text-slate-400' : ($item->isPendingApproval() ? 'text-amber-700' : 'text-slate-900') }}">
                                        {{ $item->formatted_subtotal }}
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <form action="{{ route('admin.service-orders.items.destroy', [$order, $item]) }}" method="POST" onsubmit="return confirm('Hapus item ini? Jika stok sudah terpotong, stok akan dikembalikan otomatis ke gudang.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 p-1">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-slate-400 text-xs">
                                        Belum ada suku cadang atau jasa yang ditambahkan pada Work Order ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold text-xs">
                            <tr class="border-b border-slate-200/70">
                                <td colspan="6" class="px-4 py-2 text-right text-slate-600 font-semibold">Subtotal Biaya Jasa:</td>
                                <td class="px-4 py-2 text-right font-mono text-slate-800">{{ $order->formatted_labor_cost }}</td>
                                <td></td>
                            </tr>
                            <tr class="border-b border-slate-200/70">
                                <td colspan="6" class="px-4 py-2 text-right text-slate-600 font-semibold">Subtotal Suku Cadang (Disetujui):</td>
                                <td class="px-4 py-2 text-right font-mono text-slate-800">{{ $order->formatted_parts_cost }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="px-4 py-3.5 text-right uppercase tracking-wider text-slate-900">
                                    Total Biaya Servis Disetujui:
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono text-base text-red-600">
                                    {{ $order->formatted_final_cost }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Card 4: Jejak Audit Log -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                    Jejak Aktivitas &amp; Riwayat Log (Audit Trail)
                </h3>

                <div class="space-y-3">
                    @forelse($order->logs as $log)
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                            <div class="h-2 w-2 rounded-full bg-red-600 mt-1.5 flex-shrink-0"></div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="text-slate-600 mt-0.5">{{ $log->notes }}</p>
                                @if($log->user)
                                    <span class="text-[10px] text-slate-400 block mt-1">Dicatat oleh: {{ $log->user->name }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada riwayat aktivitas.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Sidebar: Ringkasan & Milestones -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                    Ringkasan Work Order
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-[11px] text-slate-400 block font-medium">Cabang Bengkel:</span>
                        <span class="font-bold text-slate-900 mt-0.5 block">{{ $order->branch?->name }}</span>
                        <span class="text-[11px] text-slate-500 block">{{ $order->branch?->city }}</span>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[11px] text-slate-400 block font-medium">Kendaraan Tiba:</span>
                        <span class="font-semibold text-slate-800 mt-0.5 block">{{ $order->received_at ? $order->received_at->format('d/m/Y H:i') : '-' }}</span>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[11px] text-slate-400 block font-medium">Pengerjaan Selesai:</span>
                        <span class="font-semibold text-slate-800 mt-0.5 block">{{ $order->completed_at ? $order->completed_at->format('d/m/Y H:i') : '-' }}</span>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[11px] text-slate-400 block font-medium">Kendaraan Diserahkan:</span>
                        <span class="font-semibold text-slate-800 mt-0.5 block">{{ $order->delivered_at ? $order->delivered_at->format('d/m/Y H:i') : '-' }}</span>
                    </div>
                </div>

                @if($order->serviceBooking)
                    <div class="pt-3 border-t border-slate-100">
                        <a href="{{ route('admin.service-bookings.show', $order->serviceBooking) }}" class="text-xs font-bold text-red-600 hover:text-red-700 block text-center">
                            Lihat Dokumen Booking Induk &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <!-- Sidebar Card: Invoice & Pembayaran Servis -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Invoice &amp; Pembayaran</h3>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $order->payment_badge_class }}">
                        {{ $order->payment_status_label }}
                    </span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Nomor Invoice:</span>
                        <span class="font-mono font-bold text-slate-900">{{ $order->invoice_number ?? 'Terbit saat selesai' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Biaya Jasa Servis:</span>
                        <span class="font-mono font-semibold text-slate-800">{{ $order->formatted_labor_cost }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Suku Cadang Disetujui:</span>
                        <span class="font-mono font-semibold text-slate-800">{{ $order->formatted_parts_cost }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="font-bold text-slate-900">Grand Total Invoice:</span>
                        <span class="font-mono font-black text-sm text-red-600">{{ $order->formatted_final_cost }}</span>
                    </div>
                    @if($order->paid_at)
                        <p class="text-[10px] text-emerald-700 font-semibold pt-1">
                            Dibayar lunas pada {{ $order->paid_at->format('d/m/Y H:i') }} WIB
                        </p>
                    @endif
                </div>

                @if(! $order->isPaid() && in_array($order->status, ['completed', 'delivered']))
                    <form method="POST" action="{{ route('admin.service-orders.mark-paid', $order) }}"
                          onsubmit="return confirm('Tandai invoice servis ini sudah dibayar LUNAS oleh pelanggan?')">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Tandai Invoice Lunas
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>

    <!-- Modal Update Status -->
    <div x-show="showStatusModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div @click.away="showStatusModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Perbarui Status Pengerjaan Servis</h3>
                <button type="button" @click="showStatusModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.service-orders.status.update', $order) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Status Baru <span class="text-red-600">*</span></label>
                    <select name="status" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="scheduled" {{ $order->status === 'scheduled' ? 'selected' : '' }}>Terjadwal (Scheduled)</option>
                        <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi (Confirmed)</option>
                        <option value="vehicle_received" {{ $order->status === 'vehicle_received' ? 'selected' : '' }}>Kendaraan Diterima di Bengkel (Vehicle Received)</option>
                        <option value="inspection" {{ $order->status === 'inspection' ? 'selected' : '' }}>Pemeriksaan &amp; Diagnosa (Inspection)</option>
                        <option value="in_progress" {{ $order->status === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan Teknisi (In Progress)</option>
                        <option value="waiting_parts" {{ $order->status === 'waiting_parts' ? 'selected' : '' }}>Menunggu Suku Cadang (Waiting Parts)</option>
                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Pengerjaan Selesai (Completed)</option>
                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Kendaraan Diserahkan ke Pelanggan (Delivered)</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan alasan perubahan status..." class="w-full rounded-xl border border-slate-300 p-3 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showStatusModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition">
                        Perbarui Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Catat Diagnosa / Inspeksi -->
    <div x-show="showInspectionModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div @click.away="showInspectionModal = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Catat Hasil Inspeksi &amp; Diagnosa</h3>
                <button type="button" @click="showInspectionModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.service-orders.inspection.store', $order) }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Teknisi</label>
                        <input type="text"
                               name="technician_name"
                               value="{{ $order->technician_name }}"
                               placeholder="Contoh: Budi Santoso"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Odometer (KM)</label>
                        <input type="number"
                               name="current_mileage"
                               value="{{ $order->current_mileage }}"
                               placeholder="10000"
                               min="0"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Temuan / Hasil Diagnosa Mekanik <span class="text-red-600">*</span></label>
                    <textarea name="inspection_result"
                              required
                              rows="3"
                              placeholder="Kondisi minyak rem menipis, kanvas rem depan aus 60%..."
                              class="w-full rounded-xl border border-slate-300 p-3 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ $order->inspection_result }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tindakan Perbaikan / Servis yang Dilakukan</label>
                    <textarea name="repair_actions"
                              rows="3"
                              placeholder="Penggantian kampas rem depan, pembersihan caliper rem, penggantian oli mesin..."
                              class="w-full rounded-xl border border-slate-300 p-3 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ $order->repair_actions }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showInspectionModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition">
                        Simpan Hasil Diagnosa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Suku Cadang / Jasa (Strict Master Dropdown) -->
    <div x-show="showAddItemModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div @click.away="showAddItemModal = false"
             x-data="workOrderItemModal({
                 spareParts: {{ $spareParts->keyBy('id')->toJson() }},
                 serviceTypes: {{ $serviceTypes->keyBy('id')->toJson() }}
             })"
             class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Tambah Suku Cadang / Jasa Servis</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pilih langsung dari katalog master sparepart atau jasa resmi bengkel.</p>
                </div>
                <button type="button" @click="showAddItemModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.service-orders.items.store', $order) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Item <span class="text-red-600">*</span></label>
                    <select name="item_type"
                            x-model="itemType"
                            @change="onTypeChange()"
                            required
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="part">Suku Cadang (Toyota Genuine Parts)</option>
                        <option value="labor">Jasa / Biaya Pengerjaan Teknisi</option>
                    </select>
                </div>

                <!-- Dropdown Sparepart (ketika item_type === 'part') -->
                <div x-show="itemType === 'part'">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Suku Cadang (Master Sparepart) <span class="text-red-600">*</span></label>
                    <select name="spare_part_id"
                            x-model="sparePartId"
                            @change="onSelectSparePart()"
                            :required="itemType === 'part'"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">-- Pilih Sparepart dari Gudang --</option>
                        @foreach($spareParts as $sp)
                            <option value="{{ $sp->id }}" {{ $sp->stock_quantity <= 0 ? 'disabled' : '' }}>
                                [{{ $sp->part_number }}] {{ $sp->name }} — Stok: {{ $sp->stock_quantity }} {{ $sp->unit }} (Rp {{ number_format($sp->selling_price, 0, ',', '.') }}){{ $sp->stock_quantity <= 0 ? ' [HABIS]' : '' }}
                            </option>
                        @endforeach
                    </select>

                    <div class="mt-1.5 flex items-center justify-between text-[11px]" x-show="selectedPart" x-cloak>
                        <span class="text-slate-500">Sisa Stok Gudang:</span>
                        <span class="font-bold" :class="(selectedPart?.stock_quantity || 0) <= 5 ? 'text-amber-600' : 'text-emerald-700'"
                              x-text="(selectedPart?.stock_quantity || 0) + ' ' + (selectedPart?.unit || 'Pcs')"></span>
                    </div>
                </div>

                <!-- Dropdown Jasa Servis (ketika item_type === 'labor') -->
                <div x-show="itemType === 'labor'" x-cloak>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Pekerjaan Jasa (Master Layanan) <span class="text-red-600">*</span></label>
                    <select name="service_type_id"
                            x-model="serviceTypeId"
                            @change="onSelectServiceType()"
                            :required="itemType === 'labor'"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">-- Pilih Pekerjaan Jasa Bengkel --</option>
                        @foreach($serviceTypes as $st)
                            <option value="{{ $st->id }}">
                                [{{ $st->code }}] {{ $st->name }} — Rp {{ number_format($st->estimated_cost, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kuantitas (Qty) <span class="text-red-600">*</span></label>
                        <input type="number"
                               name="quantity"
                               x-model.number="quantity"
                               min="1"
                               :max="itemType === 'part' && selectedPart ? selectedPart.stock_quantity : 99"
                               required
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Satuan (Rp) <span class="text-red-600">*</span></label>
                        <input type="number"
                               name="unit_price"
                               x-model.number="unitPrice"
                               min="0"
                               :readonly="itemType === 'part'"
                               required
                               :class="itemType === 'part' ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800'"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-mono font-bold focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>
                </div>

                <!-- Live Subtotal Preview -->
                <div class="rounded-xl bg-slate-50 border border-slate-200 px-3.5 py-2.5 flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-600">Estimasi Subtotal:</span>
                    <span class="font-mono font-black text-red-600" x-text="'Rp ' + formatRupiah((Number(quantity) || 0) * (Number(unitPrice) || 0))"></span>
                </div>

                <!-- Approval Requirement Checkbox (for Spare Parts) -->
                <div x-show="itemType === 'part'" class="rounded-xl bg-amber-50 border border-amber-200 p-3">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="hidden" name="requires_approval" value="0">
                        <input type="checkbox" name="requires_approval" value="1" checked
                               class="mt-0.5 rounded border-amber-400 text-red-600 focus:ring-red-600">
                        <span class="text-[11px] text-amber-900 leading-relaxed">
                            <strong class="font-bold">Wajib Konfirmasi Pelanggan Terlebih Dahulu</strong><br>
                            Item akan muncul di halaman pelanggan untuk disetujui/ditolak. Stok baru berkurang setelah pelanggan menyetujui.
                        </span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showAddItemModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                        Tambahkan ke Rincian
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('workOrderItemModal', (config) => ({
            spareParts: config.spareParts || {},
            serviceTypes: config.serviceTypes || {},
            itemType: 'part',
            sparePartId: '',
            serviceTypeId: '',
            quantity: 1,
            unitPrice: 0,

            get selectedPart() {
                return this.sparePartId ? (this.spareParts[this.sparePartId] || null) : null;
            },

            get selectedService() {
                return this.serviceTypeId ? (this.serviceTypes[this.serviceTypeId] || null) : null;
            },

            onTypeChange() {
                this.sparePartId = '';
                this.serviceTypeId = '';
                this.quantity = 1;
                this.unitPrice = 0;
            },

            onSelectSparePart() {
                if (this.selectedPart) {
                    this.unitPrice = Number(this.selectedPart.selling_price || 0);
                    if (this.quantity > this.selectedPart.stock_quantity) {
                        this.quantity = Math.max(1, this.selectedPart.stock_quantity);
                    }
                } else {
                    this.unitPrice = 0;
                }
            },

            onSelectServiceType() {
                if (this.selectedService) {
                    this.unitPrice = Number(this.selectedService.estimated_cost || 0);
                } else {
                    this.unitPrice = 0;
                }
            },

            formatRupiah(num) {
                return new Intl.NumberFormat('id-ID').format(Math.round(num || 0));
            }
        }));
    });
</script>
@endsection

