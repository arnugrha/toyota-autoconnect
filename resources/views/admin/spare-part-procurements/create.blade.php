@extends('layouts.dashboard')

@section('title', 'Buat Pengadaan Sparepart — Toyota AutoConnect')
@section('header_title', 'Buat Pengadaan Sparepart Baru')
@section('header_subtitle', 'Stok sparepart pada master gudang akan bertambah secara otomatis begitu dokumen pengadaan ini disimpan')

@section('content')
<div class="max-w-5xl space-y-6"
     x-data="procurementForm({
         parts: {{ $spareParts->keyBy('id')->toJson() }},
         preselectedPartId: {{ $preselectedPartId ? (int) $preselectedPartId : 'null' }}
     })">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.spare-part-procurements.index') }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
            &larr; Kembali ke Daftar Pengadaan
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800 shadow-2xs">
            <div class="font-bold mb-1">Gagal menyimpan pengadaan:</div>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.spare-part-procurements.store') }}" class="space-y-6">
        @csrf

        <!-- Card 1: Informasi Dokumen Pengadaan -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-extrabold text-slate-900">1. Informasi Pengadaan &amp; Supplier</h2>
                <p class="text-[11px] text-slate-500 mt-0.5">Nomor dokumen pengadaan (PRC-YYYYMM-XXXX) akan dibuat otomatis oleh sistem.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="procurement_date" class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengadaan <span class="text-red-600">*</span></label>
                    <input id="procurement_date"
                           type="date"
                           name="procurement_date"
                           value="{{ old('procurement_date', now()->toDateString()) }}"
                           required
                           class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div>
                    <label for="supplier_name" class="block text-xs font-bold text-slate-700 mb-1">Nama Supplier / Distributor <span class="text-red-600">*</span></label>
                    <input id="supplier_name"
                           type="text"
                           name="supplier_name"
                           value="{{ old('supplier_name', 'PT Toyota Astra Motor Parts Center') }}"
                           required
                           placeholder="Contoh: PT Toyota Astra Motor Parts Center"
                           class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div>
                    <label for="branch_id" class="block text-xs font-bold text-slate-700 mb-1">Cabang Gudang Penerima</label>
                    <select id="branch_id"
                            name="branch_id"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">Gudang Pusat (Semua Cabang)</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }} ({{ $branch->city }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="notes" class="block text-xs font-bold text-slate-700 mb-1">Catatan Pengadaan / No. Surat Jalan</label>
                <textarea id="notes"
                          name="notes"
                          rows="2"
                          placeholder="Contoh: Restock rutin bulanan untuk oli mesin, filter, dan kampas rem."
                          class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Card 2: Daftar Item Sparepart Masuk -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">2. Rincian Sparepart yang Diadakan</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pilih sparepart dari dropdown master. Harga beli akan terisi otomatis dan stok akan langsung bertambah saat disimpan.</p>
                </div>

                <button type="button"
                        @click="addRow()"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white hover:bg-slate-800 transition self-start sm:self-auto">
                    + Tambah Baris Sparepart
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(row, index) in rows" :key="row.key">
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 space-y-3">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-start">
                            <!-- Sparepart Dropdown -->
                            <div class="lg:col-span-5">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Pilih Sparepart <span class="text-red-600">*</span></label>
                                <select :name="`items[${index}][spare_part_id]`"
                                        x-model="row.spare_part_id"
                                        @change="onSelectPart(index)"
                                        required
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                    <option value="">-- Pilih Sparepart dari Master --</option>
                                    @foreach($spareParts as $sp)
                                        <option value="{{ $sp->id }}">
                                            [{{ $sp->part_number }}] {{ $sp->name }} (Stok: {{ $sp->stock_quantity }} {{ $sp->unit }})
                                        </option>
                                    @endforeach
                                </select>

                                <!-- Live Stock Preview -->
                                <div class="mt-1.5 text-[11px]" x-show="getPart(row.spare_part_id)" x-cloak>
                                    <span class="text-slate-500">Proyeksi Stok:</span>
                                    <span class="font-mono font-bold text-slate-700" x-text="getPart(row.spare_part_id)?.stock_quantity ?? 0"></span>
                                    <span class="text-slate-400">&rarr;</span>
                                    <span class="font-mono font-extrabold text-emerald-700"
                                          x-text="(Number(getPart(row.spare_part_id)?.stock_quantity || 0) + Number(row.quantity || 0)) + ' ' + (getPart(row.spare_part_id)?.unit || '')">
                                    </span>
                                </div>
                            </div>

                            <!-- Qty -->
                            <div class="lg:col-span-2">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Qty Masuk <span class="text-red-600">*</span></label>
                                <input type="number"
                                       :name="`items[${index}][quantity]`"
                                       x-model.number="row.quantity"
                                       min="1"
                                       required
                                       class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            </div>

                            <!-- Unit Cost -->
                            <div class="lg:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Harga Beli Satuan (Rp) <span class="text-red-600">*</span></label>
                                <input type="number"
                                       :name="`items[${index}][unit_cost]`"
                                       x-model.number="row.unit_cost"
                                       min="0"
                                       step="1000"
                                       required
                                       class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            </div>

                            <!-- Subtotal & Remove -->
                            <div class="lg:col-span-2 flex items-end justify-between lg:flex-col lg:items-end gap-2">
                                <div class="text-left lg:text-right">
                                    <span class="block text-[10px] font-bold uppercase text-slate-400">Subtotal</span>
                                    <span class="font-mono text-xs font-extrabold text-slate-900"
                                          x-text="'Rp ' + formatRupiah((Number(row.quantity) || 0) * (Number(row.unit_cost) || 0))"></span>
                                </div>
                                <button type="button"
                                        x-show="rows.length > 1"
                                        @click="removeRow(index)"
                                        class="text-[11px] font-bold text-rose-600 hover:text-rose-700">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Grand Total Summary Bar -->
            <div class="rounded-xl bg-slate-900 p-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="text-xs">
                    <span class="text-slate-400">Ringkasan Pengadaan:</span>
                    <strong class="text-white ml-1" x-text="rows.length + ' baris item'"></strong>
                    <span class="text-slate-500 mx-1.5">&bull;</span>
                    <strong class="text-emerald-400" x-text="'+' + totalQuantity + ' unit akan otomatis masuk ke stok'"></strong>
                </div>
                <div class="text-left sm:text-right">
                    <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-bold">Total Nilai Pengadaan</span>
                    <span class="font-mono text-lg font-black text-white" x-text="'Rp ' + formatRupiah(grandTotal)"></span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.spare-part-procurements.index') }}"
                   class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Simpan Pengadaan &amp; Tambah Stok Otomatis
                </button>
            </div>
        </div>
    </form>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('procurementForm', (config) => ({
            parts: config.parts || {},
            nextKey: 2,
            rows: [
                {
                    key: 1,
                    spare_part_id: config.preselectedPartId ? String(config.preselectedPartId) : '',
                    quantity: 10,
                    unit_cost: config.preselectedPartId && config.parts[config.preselectedPartId]
                        ? Number(config.parts[config.preselectedPartId].purchase_price)
                        : 0,
                }
            ],

            getPart(id) {
                return id ? (this.parts[id] || null) : null;
            },

            onSelectPart(index) {
                const row = this.rows[index];
                const part = this.getPart(row.spare_part_id);
                if (part) {
                    row.unit_cost = Number(part.purchase_price || 0);
                }
            },

            addRow() {
                this.rows.push({
                    key: this.nextKey++,
                    spare_part_id: '',
                    quantity: 10,
                    unit_cost: 0,
                });
            },

            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },

            get totalQuantity() {
                return this.rows.reduce((sum, r) => sum + (Number(r.quantity) || 0), 0);
            },

            get grandTotal() {
                return this.rows.reduce((sum, r) => sum + ((Number(r.quantity) || 0) * (Number(r.unit_cost) || 0)), 0);
            },

            formatRupiah(num) {
                return new Intl.NumberFormat('id-ID').format(Math.round(num || 0));
            }
        }));
    });
</script>
@endsection
