@extends('layouts.dashboard')

@section('title', 'Buat Peluang Penjualan Baru — Toyota AutoConnect')
@section('header_title', 'Buka Sales Opportunity Baru')
@section('header_subtitle', 'Catat calon transaksi penjualan kendaraan untuk pelanggan dealer')

@section('content')
<div class="space-y-6 max-w-3xl">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.sales.opportunities.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Opportunity
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800 space-y-1">
            <span class="font-bold block">Terdapat kesalahan pengisian:</span>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-6 space-y-5">
        <form action="{{ route('admin.sales.opportunities.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Pelanggan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Calon Pelanggan <span class="text-red-600">*</span>
                </label>
                <select name="user_id" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">-- Pilih Akun Pelanggan --</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" {{ old('user_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->email }} &bull; {{ $c->phone ?? 'Tanpa Telp' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Model Kendaraan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Model Kendaraan Diminati <span class="text-red-600">*</span>
                </label>
                <select name="vehicle_id" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">-- Pilih Model Kendaraan --</option>
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>
                            {{ $v->name }} ({{ $v->category }} &bull; OTR Rp {{ number_format($v->price, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Cabang Dealer & Sales Consultant -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Cabang Dealer <span class="text-red-600">*</span>
                    </label>
                    <select name="branch_id" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->city }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Sales Executive Penanggung Jawab
                    </label>
                    <select name="assigned_to"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">-- Tugaskan ke Staf --</option>
                        @foreach ($salesConsultants as $sc)
                            <option value="{{ $sc->id }}" {{ old('assigned_to', auth()->id()) == $sc->id ? 'selected' : '' }}>
                                {{ $sc->name }} ({{ $sc->role_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Tahapan Awal & Estimasi Nilai -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahap Pipeline Awal</label>
                    <select name="stage"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="prospecting">Prospek Awal (Prospecting)</option>
                        <option value="qualification">Kualifikasi Kebutuhan (Qualification)</option>
                        <option value="presentation">Presentasi & Penawaran (Presentation)</option>
                        <option value="test_drive">Uji Kemudi (Test Drive)</option>
                        <option value="negotiation">Negosiasi Harga (Negotiation)</option>
                        <option value="booking">Pemesanan Unit (Booking SPK)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Estimasi Nilai Transaksi (Rp)
                    </label>
                    <input type="number" name="estimated_value" value="{{ old('estimated_value') }}" placeholder="Otomatis mengikuti harga mobil jika kosong"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>
            </div>

            <!-- Catatan Kebutuhan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Internal &amp; Kebutuhan Unit</label>
                <textarea name="notes" rows="3" placeholder="Informasi preferensi warna, metode pembelian tunai/kredit, atau negosiasi awal..."
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('notes') }}</textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <a href="{{ route('admin.sales.opportunities.index') }}"
                   class="rounded-lg bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-red-600 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    Simpan Peluang Penjualan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

