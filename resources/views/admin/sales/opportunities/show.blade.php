@extends('layouts.dashboard')

@section('title', 'Detail Opportunity ' . $opportunity->code . ' — Toyota AutoConnect')
@section('header_title', 'Detail Peluang Penjualan')
@section('header_subtitle', 'Pantau tahapan transaksi, penawaran resmi, alokasi booking unit, dan catatan sales')

@section('content')
<div class="space-y-6 max-w-6xl">


    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800 space-y-1">
            <span class="font-bold block">Terdapat kesalahan pembaruan:</span>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('admin.sales.opportunities.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Opportunity
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.sales.offers.create', $opportunity) }}"
               class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-900 transition flex items-center gap-1">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                Buat Penawaran Resmi
            </a>

            <a href="{{ route('admin.sales.bookings.create', $opportunity) }}"
               class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition flex items-center gap-1">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Buat Pemesanan Unit (SPK)
            </a>
        </div>
    </div>

    <!-- Opportunity Header Card -->
    <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 sm:p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">SALES OPPORTUNITY CODE</span>
                <h2 class="text-xl font-extrabold text-slate-900 font-mono mt-0.5">{{ $opportunity->code }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Cabang: <strong class="text-slate-800">{{ $opportunity->branch->name }}</strong></p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[10px] text-slate-400 block font-bold uppercase tracking-wider">Estimasi Nilai Transaksi</span>
                <span class="text-lg font-extrabold text-red-600 font-mono">
                    Rp {{ number_format($opportunity->estimated_value, 0, ',', '.') }}
                </span>
                <div class="mt-1">
                    <span class="inline-flex items-center rounded-full px-3 py-0.5 text-[11px] font-bold border {{ $opportunity->stage_badge_class }}">
                        {{ $opportunity->stage_label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 8-Stage Pipeline Stepper Bar -->
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Alur Tahapan Penjualan (Pipeline Progress):</span>
            @php
                $allStages = [
                    'prospecting' => 'Prospek',
                    'qualification' => 'Kualifikasi',
                    'presentation' => 'Presentasi',
                    'test_drive' => 'Test Drive',
                    'negotiation' => 'Negosiasi',
                    'booking' => 'Booking SPK',
                    'closed_won' => 'Closed Won',
                    'closed_lost' => 'Closed Lost',
                ];
                $stageKeys = array_keys($allStages);
                $currentIndex = array_search($opportunity->stage, $stageKeys);
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-1.5 text-center text-xs">
                @foreach ($allStages as $k => $lbl)
                    @php
                        $index = array_search($k, $stageKeys);
                        $isCurrent = ($k === $opportunity->stage);
                        $isPassed = ($index < $currentIndex && $opportunity->stage !== 'closed_lost');
                    @endphp
                    <div class="p-2 rounded-lg border text-[11px] font-semibold {{ $isCurrent ? 'bg-red-600 text-white border-red-600 font-bold shadow-xs' : ($isPassed ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200') }}">
                        {{ $lbl }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Info & Actions -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Customer & Vehicle Card -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                    Profil Calon Pelanggan &amp; Kendaraan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100 space-y-1">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Data Pelanggan</span>
                        <p class="font-bold text-slate-900 text-sm">{{ $opportunity->customer->name }}</p>
                        <p class="text-slate-600">{{ $opportunity->customer->email }}</p>
                        <p class="text-slate-600 font-mono">{{ $opportunity->customer->phone ?? 'Belum ada nomor telepon' }}</p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100 space-y-1">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Unit Kendaraan Diminati</span>
                        <p class="font-bold text-slate-900 text-sm">{{ $opportunity->vehicle->name }}</p>
                        <p class="text-slate-500">{{ $opportunity->vehicle->variant ?? 'Standar' }} &bull; {{ $opportunity->vehicle->category }}</p>
                        <p class="text-red-600 font-mono font-bold mt-1">OTR: Rp {{ number_format($opportunity->vehicle->price, 0, ',', '.') }}</p>
                    </div>
                </div>

                @if ($opportunity->notes)
                    <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100 text-xs">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Catatan Kebutuhan:</span>
                        <p class="text-slate-700 mt-1">{{ $opportunity->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Quotations / Sales Offers Section -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
                        Surat Penawaran Resmi ({{ $opportunity->offers->count() }})
                    </h3>
                    <a href="{{ route('admin.sales.offers.create', $opportunity) }}" class="text-[11px] font-bold text-red-600 hover:underline">
                        + Buat Penawaran Baru
                    </a>
                </div>

                @forelse ($opportunity->offers as $offer)
                    <div class="rounded-xl border border-slate-200 p-3.5 flex items-center justify-between hover:bg-slate-50/70 transition">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-900 text-xs">{{ $offer->code }}</span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold border {{ $offer->status_badge_class }}">
                                    {{ $offer->status_label }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Total: <strong class="text-slate-900 font-mono">Rp {{ number_format($offer->total_amount, 0, ',', '.') }}</strong>
                                (Diskon: Rp {{ number_format($offer->discount_amount, 0, ',', '.') }}) &bull; Berlaku s/d {{ $offer->valid_until->format('d M Y') }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($offer->status === 'draft')
                                <form action="{{ route('admin.sales.offers.send', $offer) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="rounded bg-slate-800 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-slate-900">
                                        Kirim ke Pelanggan
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.sales.offers.show', $offer) }}" class="rounded bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-200">
                                Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">
                        Belum ada draf atau surat penawaran resmi yang dibuat untuk opportunity ini.
                    </div>
                @endforelse
            </div>

            <!-- Vehicle Bookings (SPK) Section -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
                        Pemesanan Unit Kendaraan SPK ({{ $opportunity->bookings->count() }})
                    </h3>
                    <a href="{{ route('admin.sales.bookings.create', $opportunity) }}" class="text-[11px] font-bold text-red-600 hover:underline">
                        + Buat Booking SPK
                    </a>
                </div>

                @forelse ($opportunity->bookings as $bk)
                    <div class="rounded-xl border border-slate-200 p-3.5 flex items-center justify-between hover:bg-slate-50/70 transition">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-900 text-xs">{{ $bk->code }}</span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold border {{ $bk->status_badge_class }}">
                                    {{ $bk->status_label }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Nilai SPK: <strong class="text-slate-900 font-mono">Rp {{ number_format($bk->net_price, 0, ',', '.') }}</strong>
                                &bull; Tanda Jadi: Rp {{ number_format($bk->booking_fee, 0, ',', '.') }}
                            </div>
                        </div>

                        <a href="{{ route('admin.sales.bookings.show', $bk) }}" class="rounded bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                            Kelola Alokasi Stok
                        </a>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">
                        Belum ada pemesanan unit SPK yang diajukan untuk opportunity ini.
                    </div>
                @endforelse
            </div>

            <!-- Test Drive History -->
            @if ($testDrives->count() > 0)
                <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                        Riwayat Jadwal Uji Kemudi Pelanggan ({{ $testDrives->count() }})
                    </h3>
                    <div class="space-y-2">
                        @foreach ($testDrives as $td)
                            <div class="rounded-xl border border-slate-100 p-3 flex items-center justify-between text-xs bg-slate-50/60">
                                <div>
                                    <span class="font-mono font-bold text-slate-800">{{ $td->code }}</span>
                                    <span class="text-slate-500 text-[11px] block">{{ $td->scheduled_at->format('d M Y, H:i') }} WIB &bull; {{ $td->branch->name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $td->status_badge_class }}">
                                        {{ $td->status_label }}
                                    </span>
                                    <a href="{{ route('admin.test-drives.show', $td) }}" class="text-slate-500 hover:text-slate-800">
                                        &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Column: Stage Controls & Audit Log -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Stage Update Form Card -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 space-y-4" x-data="{ currentStage: '{{ $opportunity->stage }}' }">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                    Perbarui Tahap Pipeline
                </h3>

                <form action="{{ route('admin.sales.opportunities.update', $opportunity) }}" method="POST" class="space-y-3.5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tahap Penjualan (Stage)</label>
                        <select name="stage" x-model="currentStage" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none font-semibold">
                            <option value="prospecting">Prospek Awal (Prospecting)</option>
                            <option value="qualification">Kualifikasi Kebutuhan (Qualification)</option>
                            <option value="presentation">Presentasi & Penawaran (Presentation)</option>
                            <option value="test_drive">Uji Kemudi (Test Drive)</option>
                            <option value="negotiation">Negosiasi Harga (Negotiation)</option>
                            <option value="booking">Pemesanan Unit (Booking SPK)</option>
                            <option value="closed_won">Penjualan Berhasil (Closed Won)</option>
                            <option value="closed_lost">Batal / Kalah (Closed Lost)</option>
                        </select>
                    </div>

                    <div x-show="currentStage === 'closed_lost'" x-cloak>
                        <label class="block text-[11px] font-bold text-rose-700 mb-1">
                            Alasan Pembatalan / Lost <span class="text-rose-600">*</span>
                        </label>
                        <textarea name="lost_reason" rows="2" placeholder="Contoh: Memilih merek kompetitor, kendala anggaran..."
                                  class="w-full rounded-lg border border-rose-300 bg-rose-50/30 px-3 py-1.5 text-xs text-slate-900 focus:border-rose-600 focus:ring-1 focus:ring-rose-600 focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Sales Executive Ditugaskan</label>
                        <select name="assigned_to"
                                class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <option value="">-- Belum Ditugaskan --</option>
                            @foreach ($salesConsultants as $sc)
                                <option value="{{ $sc->id }}" {{ $opportunity->assigned_to == $sc->id ? 'selected' : '' }}>
                                    {{ $sc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Perbarui Estimasi Nilai (Rp)</label>
                        <input type="number" name="estimated_value" value="{{ $opportunity->estimated_value }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-red-600 py-2 px-4 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Simpan Perubahan Tahapan
                    </button>
                </form>
            </div>

            <!-- Add Note Form & Sales Activities Log -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                    Catatan Aktivitas Penjualan
                </h3>

                <form action="{{ route('admin.sales.opportunities.notes.store', $opportunity) }}" method="POST" class="space-y-2">
                    @csrf
                    <textarea name="note" rows="2" required placeholder="Tambah catatan progres follow-up sales..."
                              class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                    <button type="submit" class="rounded-lg bg-slate-800 px-3 py-1 text-xs font-semibold text-white hover:bg-slate-900">
                        Kirim Catatan
                    </button>
                </form>

                <!-- Activity Timeline -->
                <div class="space-y-3 pt-3 border-t border-slate-100 max-h-96 overflow-y-auto">
                    @forelse ($opportunity->activities as $act)
                        <div class="rounded-xl bg-slate-50 p-3 border border-slate-100 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 text-[11px]">{{ $act->user->name ?? 'Sistem' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $act->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-slate-600 text-[11px]">{{ $act->description }}</p>
                        </div>
                    @empty
                        <div class="text-center py-4 text-xs text-slate-400">
                            Belum ada riwayat aktivitas yang tercatat.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

