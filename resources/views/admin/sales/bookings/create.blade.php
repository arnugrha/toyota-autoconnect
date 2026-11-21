@extends('layouts.dashboard')

@section('title', 'Buat Pemesanan Unit (SPK) — ' . $opportunity->code . ' — Toyota AutoConnect')
@section('header_title', 'Buat Pemesanan Unit Kendaraan (SPK)')
@section('header_subtitle', 'Terbitkan nomor registrasi pemesanan mobil baru dan persiapkan alokasi unit showroom')

@section('content')
<div class="space-y-6 max-w-3xl">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.sales.opportunities.show', $opportunity) }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Detail Opportunity
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

    <!-- Stock Availability Monitor Card -->
    <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 space-y-3">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Status Inventaris Showroom Terkini</span>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                <span class="text-[10px] text-slate-400 uppercase font-semibold block">Stok Fisik Showroom</span>
                <p class="text-base font-extrabold text-slate-900 mt-0.5">{{ $inventory->stock_quantity ?? 0 }} Unit</p>
                <p class="text-[10px] text-slate-500">Cabang {{ $opportunity->branch->name }}</p>
            </div>
            <div class="rounded-xl bg-amber-50/50 p-3 border border-amber-200/50">
                <span class="text-[10px] text-amber-700 uppercase font-semibold block">Telah Dialokasikan (SPK)</span>
                <p class="text-base font-extrabold text-amber-800 mt-0.5">{{ $inventory->reserved_quantity ?? 0 }} Unit</p>
                <p class="text-[10px] text-amber-700/80">Booking aktif</p>
            </div>
            <div class="rounded-xl bg-emerald-50/50 p-3 border border-emerald-200/50">
                <span class="text-[10px] text-emerald-700 uppercase font-semibold block">Stok Bebas Tersedia</span>
                <p class="text-base font-extrabold text-emerald-800 mt-0.5">{{ $inventory ? $inventory->available_stock : 0 }} Unit</p>
                <p class="text-[10px] text-emerald-700/80">Siap dialokasikan</p>
            </div>
        </div>

        @if (!$inventory || $inventory->available_stock <= 0)
            <div class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-800 font-semibold flex items-center gap-2">
                <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z" />
                </svg>
                <span>Peringatan: Stok unit kendaraan untuk cabang ini sedang kosong / habis. Anda tetap dapat mencatat pending booking namun belum dapat mengalokasikan unit.</span>
            </div>
        @endif
    </div>

    <!-- Booking Form Card -->
    <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-6 space-y-5">
        <form action="{{ route('admin.sales.bookings.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="sales_opportunity_id" value="{{ $opportunity->id }}">

            <!-- Tautan ke Penawaran Resmi jika ada -->
            @if ($opportunity->offers->count() > 0)
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Pilih Surat Penawaran Acuan (Opsional)
                    </label>
                    <select name="sales_offer_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">-- Tanpa Tautan Penawaran (Gunakan Harga Master Kendaraan) --</option>
                        @foreach ($opportunity->offers as $of)
                            <option value="{{ $of->id }}" {{ old('sales_offer_id') == $of->id ? 'selected' : '' }}>
                                {{ $of->code }} — Total Rp {{ number_format($of->total_amount, 0, ',', '.') }} (Diskon: Rp {{ number_format($of->discount_amount, 0, ',', '.') }}) &bull; Status: {{ $of->status_label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Nilai Tanda Jadi & Masa Berlaku -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nominal Tanda Jadi / Booking Fee (Rp) <span class="text-red-600">*</span>
                    </label>
                    <input type="number" name="booking_fee" value="{{ old('booking_fee', 5000000) }}" required min="0"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Standar booking fee tanda jadi dealer resmi</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Batas Waktu Pelunasan / Kedaluwarsa SPK
                    </label>
                    <input type="date" name="expires_at" min="{{ date('Y-m-d') }}" value="{{ old('expires_at', date('Y-m-d', strtotime('+7 days'))) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan Pemesanan</label>
                <textarea name="notes" rows="3" placeholder="Informasi kesepakatan warna unit, estimasi jadwal serah terima, atau permintaan khusus pelanggan..."
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('notes') }}</textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <a href="{{ route('admin.sales.opportunities.show', $opportunity) }}"
                   class="rounded-lg bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-red-600 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    Terbitkan Pemesanan (Pending Alokasi)
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

