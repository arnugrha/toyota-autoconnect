@extends('layouts.dashboard')

@section('title', 'Penyesuaian Stok Unit — Toyota AutoConnect')
@section('header_title', 'Penyesuaian Stok Unit (Stock Adjustment)')
@section('header_subtitle', 'Catat penambahan stok unit baru atau pengurangan fisik dengan audit log lengkap')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.inventory.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Inventory
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800">
            <div class="font-bold mb-1">Terjadi kesalahan pada penyesuaian stok:</div>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-xl bg-white p-6 border border-slate-200 shadow-sm space-y-5">
        <div class="border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Formulir Koreksi &amp; Penyesuaian Stok Fisik</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Setiap perubahan stok akan langsung tercatat dalam audit trail dengan identitas akun Anda.</p>
        </div>

        <form method="POST" action="{{ route('admin.inventory.adjust.store') }}" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-slate-700 mb-1">Model Kendaraan <span class="text-red-600">*</span></label>
                <select name="vehicle_id" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Pilih Kendaraan</option>
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}" {{ (old('vehicle_id', $selectedVehicleId) == $v->id) ? 'selected' : '' }}>
                            {{ $v->name }} ({{ $v->variant ?? $v->category }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Lokasi Cabang Dealer <span class="text-red-600">*</span></label>
                <select name="branch_id" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Pilih Cabang</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" {{ (old('branch_id', $selectedBranchId) == $b->id) ? 'selected' : '' }}>
                            {{ $b->name }} ({{ $b->city }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jenis Penyesuaian <span class="text-red-600">*</span></label>
                    <select name="type" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="add" {{ old('type') === 'add' ? 'selected' : '' }}>Penambahan Stok (+) Unit Masuk Baru / Mutasi</option>
                        <option value="subtract" {{ old('type') === 'subtract' ? 'selected' : '' }}>Pengurangan Stok (-) Koreksi Fisik / Mutasi Keluar</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jumlah Unit (Kuantitas) <span class="text-red-600">*</span></label>
                    <input type="number" name="quantity" min="1" max="1000" required value="{{ old('quantity', 1) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Alasan Penyesuaian (Audit Log) <span class="text-red-600">*</span></label>
                <textarea name="reason" rows="3" required placeholder="Contoh: Penerimaan delivery unit dari pabrik Toyota Sunter nomor DO-1234..."
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('reason') }}</textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.inventory.index') }}"
                   class="rounded-lg bg-slate-100 px-4 py-2 font-semibold text-slate-600 hover:bg-slate-200 transition">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-red-600 px-5 py-2 font-bold text-white shadow-xs hover:bg-red-700 transition flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Proses Penyesuaian Stok
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
