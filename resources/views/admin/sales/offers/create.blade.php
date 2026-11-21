@extends('layouts.dashboard')

@section('title', 'Buat Penawaran Resmi — ' . $opportunity->code . ' — Toyota AutoConnect')
@section('header_title', 'Buat Surat Penawaran Resmi (Quotation)')
@section('header_subtitle', 'Terbitkan surat penawaran harga resmi dengan perhitungan diskon dan biaya tambahan')

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

    <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-6 space-y-6">
        
        <!-- Target Opportunity Summary -->
        <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Target Opportunity &amp; Pelanggan</span>
                <h3 class="text-sm font-bold text-slate-900 mt-0.5">{{ $opportunity->code }} &bull; {{ $opportunity->customer->name }}</h3>
                <p class="text-xs text-slate-500">{{ $opportunity->vehicle->name }} &bull; {{ $opportunity->branch->name }}</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Harga Dasar OTR</span>
                <p class="text-sm font-extrabold text-slate-900 mt-0.5 font-mono">
                    Rp {{ number_format($opportunity->vehicle->price, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <form action="{{ route('admin.sales.offers.store') }}" method="POST" class="space-y-4"
              x-data="{
                  basePrice: {{ $opportunity->vehicle->price ?? 0 }},
                  discount: 0,
                  additional: 0,
                  get total() {
                      return Math.max(0, Number(this.basePrice) - Number(this.discount || 0) + Number(this.additional || 0));
                  }
              }">
            @csrf
            <input type="hidden" name="sales_opportunity_id" value="{{ $opportunity->id }}">

            <!-- Live Calculation Preview Card -->
            <div class="rounded-xl bg-slate-900 text-white p-4 space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-400 border-b border-slate-800 pb-2">
                    <span>Harga OTR Unit:</span>
                    <span class="font-mono text-white">Rp {{ number_format($opportunity->vehicle->price, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-xs text-emerald-400 border-b border-slate-800 pb-2">
                    <span>Potongan Diskon Dealer:</span>
                    <span class="font-mono">- Rp <span x-text="Number(discount || 0).toLocaleString('id-ID')"></span></span>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-300 border-b border-slate-800 pb-2">
                    <span>Biaya Tambahan / Aksesori:</span>
                    <span class="font-mono">+ Rp <span x-text="Number(additional || 0).toLocaleString('id-ID')"></span></span>
                </div>
                <div class="flex items-center justify-between text-sm font-bold pt-1">
                    <span>Total Bersih Penawaran:</span>
                    <span class="text-base font-extrabold text-red-400 font-mono">
                        Rp <span x-text="total.toLocaleString('id-ID')"></span>
                    </span>
                </div>
            </div>

            <!-- Discount & Additional Input -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nominal Diskon Dealer (Rp)
                    </label>
                    <input type="number" name="discount_amount" x-model.number="discount" min="0" value="{{ old('discount_amount', 0) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Maksimal 25% dari harga kendaraan</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Biaya Tambahan / Administrasi (Rp)
                    </label>
                    <input type="number" name="additional_cost" x-model.number="additional" min="0" value="{{ old('additional_cost', 0) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Biaya paket aksesori atau pengurusan resmi</span>
                </div>
            </div>

            <!-- Valid Until Date -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Masa Berlaku Penawaran <span class="text-red-600">*</span>
                </label>
                <input type="date" name="valid_until" required min="{{ date('Y-m-d') }}" value="{{ old('valid_until', date('Y-m-d', strtotime('+14 days'))) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan &amp; Spesifikasi Paket Penawaran</label>
                <textarea name="notes" rows="3" placeholder="Informasi bonus kaca film, karpet dasar, paket servis gratis, atau ketentuan khusus..."
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('notes') }}</textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <a href="{{ route('admin.sales.opportunities.show', $opportunity) }}"
                   class="rounded-lg bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-red-600 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    Simpan Draf Penawaran
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

