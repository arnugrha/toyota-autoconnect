@extends('layouts.dashboard')

@section('title', 'Detail Pengajuan Leasing ' . $application->code . ' — Toyota AutoConnect')
@section('header_title', 'Detail Aplikasi Leasing ' . $application->code)
@section('header_subtitle', 'Verifikasi berkas kredit, sinkronisasi status provider, dan perbarui tahapan aplikasi')

@section('content')
<div class="space-y-6">

    <!-- Back button & Status -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('admin.leasing.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Aplikasi Leasing
        </a>

        <div class="flex items-center gap-2">
            <!-- Sync with Provider Button -->
            <form action="{{ route('admin.leasing.sync', $application) }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Sinkron Status Leasing
                </button>
            </form>

            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $application->status_badge_class }}">
                {{ $application->status_label }}
            </span>
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Details (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Primary Application Information Card -->
            <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-xs space-y-6">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-4">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">KODE BERKAS KREDIT</span>
                        <h2 class="text-xl font-extrabold text-slate-900 font-mono mt-0.5">{{ $application->code }}</h2>
                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                            <span>Mitra: <strong class="text-slate-800">{{ $application->leasingProvider->name }} ({{ $application->leasingProvider->code }})</strong></span>
                            @if ($application->provider_reference)
                                <span>&bull;</span>
                                <span>Ref: <strong class="font-mono text-slate-700">{{ $application->provider_reference }}</strong></span>
                            @endif
                        </div>
                    </div>
                    <div class="text-left sm:text-right text-xs text-slate-500 space-y-0.5">
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">Waktu Masuk</span>
                        <span class="font-bold text-slate-800 block">{{ $application->created_at->format('d M Y, H:i') }} WIB</span>
                        @if ($application->response_at)
                            <span class="text-[11px] text-slate-400 block">Respons: {{ $application->response_at->format('d M Y, H:i') }} WIB</span>
                        @endif
                    </div>
                </div>

                <!-- Vehicle Box -->
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img src="{{ $application->vehicle->primary_image_url }}"
                             alt="{{ $application->vehicle->name }}"
                             class="h-16 w-24 object-cover rounded-lg border border-slate-200 bg-slate-950 flex-shrink-0">
                        <div>
                            <span class="inline-block rounded bg-red-100 px-2 py-0.5 text-[9px] font-bold text-red-700">
                                {{ $application->vehicle->category }}
                            </span>
                            <h3 class="text-sm font-extrabold text-slate-900 mt-1">{{ $application->vehicle->name }}</h3>
                            <p class="text-xs text-slate-500">{{ $application->vehicle->variant ?? 'Varian Standar' }}</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Harga OTR</span>
                        <p class="text-sm font-extrabold font-mono text-slate-900 mt-0.5">{{ $application->formatted_vehicle_price }}</p>
                    </div>
                </div>

                <!-- Financial Scheme Breakdown -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2">
                    <div class="rounded-lg bg-slate-50 p-3 border border-slate-100">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Uang Muka (DP)</span>
                        <p class="text-xs font-mono font-bold text-slate-900 mt-1">{{ $application->formatted_down_payment }}</p>
                        @php
                            $dpPct = $application->vehicle_price > 0 ? round(($application->down_payment / $application->vehicle_price) * 100, 1) : 0;
                        @endphp
                        <span class="text-[10px] text-slate-500 font-medium">({{ $dpPct }}%)</span>
                    </div>

                    <div class="rounded-lg bg-slate-50 p-3 border border-slate-100">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Pokok Pinjaman</span>
                        <p class="text-xs font-mono font-bold text-slate-900 mt-1">
                            Rp {{ number_format(max(0, $application->vehicle_price - $application->down_payment), 0, ',', '.') }}
                        </p>
                        <span class="text-[10px] text-slate-500 font-medium">Nilai Pokok</span>
                    </div>

                    <div class="rounded-lg bg-slate-50 p-3 border border-slate-100">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Tenor Pembiayaan</span>
                        <p class="text-xs font-bold text-slate-900 mt-1">{{ $application->tenor_months }} Bulan</p>
                        <span class="text-[10px] text-slate-500 font-medium">({{ round($application->tenor_months / 12, 1) }} Thn)</span>
                    </div>

                    <div class="rounded-lg bg-slate-900 text-white p-3 border border-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Estimasi Angsuran</span>
                        <p class="text-xs font-mono font-bold text-red-400 mt-1">{{ $application->formatted_monthly_installment }}</p>
                        <span class="text-[10px] text-slate-400">/ bulan</span>
                    </div>
                </div>

                <!-- Applicant Profile Grid -->
                <div class="rounded-xl border border-slate-200 overflow-hidden text-xs">
                    <div class="bg-slate-50 px-4 py-2.5 font-bold text-slate-700 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        Data Calon Debitur / Pemohon
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Nama Nasabah (Akun)</span>
                            <p class="font-bold text-slate-800 mt-0.5">{{ $application->user->name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Nomor NIK / KTP</span>
                            <p class="font-mono font-bold text-slate-800 mt-0.5">{{ $application->applicant_data['id_card_number'] ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Nomor NPWP</span>
                            <p class="font-mono font-bold text-slate-800 mt-0.5">{{ $application->applicant_data['npwp_number'] ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Status Pekerjaan</span>
                            <p class="font-bold text-slate-800 capitalize mt-0.5">{{ str_replace('_', ' ', $application->applicant_data['employment_type'] ?? '-') }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Penghasilan Bulanan</span>
                            <p class="font-mono font-bold text-slate-800 mt-0.5">
                                @if (!empty($application->applicant_data['monthly_income']))
                                    Rp {{ number_format((int) $application->applicant_data['monthly_income'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Instansi / Perusahaan</span>
                            <p class="font-semibold text-slate-800 mt-0.5">{{ $application->applicant_data['company_name'] ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Status Perkawinan</span>
                            <p class="font-semibold text-slate-800 capitalize mt-0.5">{{ str_replace('_', ' ', $application->applicant_data['marital_status'] ?? '-') }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Email Nasabah</span>
                            <p class="text-slate-800 mt-0.5">{{ $application->user->email ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Nomor Kontak / WhatsApp</span>
                            <p class="font-mono text-slate-800 mt-0.5">{{ $application->user->phone_number ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                @if ($application->revision_notes)
                    <div class="rounded-xl bg-purple-50 border border-purple-200 p-4 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 block">Catatan Revisi Terakhir</span>
                        <p class="text-xs text-purple-900 leading-relaxed">{{ $application->revision_notes }}</p>
                    </div>
                @endif

                @if ($application->notes)
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Catatan Tambahan:</span>
                        <p class="text-xs text-slate-700 mt-1">{{ $application->notes }}</p>
                    </div>
                @endif

            </div>

            <!-- Audit Trail & Logs Card -->
            <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3">
                    Jejak Audit &amp; Riwayat Aktivitas Berkas
                </h3>
                <div class="space-y-4">
                    @forelse ($application->logs as $log)
                        <div class="flex items-start gap-3 text-xs">
                            <div class="h-2 w-2 rounded-full bg-red-600 mt-1.5 flex-shrink-0"></div>
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-800 capitalize">
                                        {{ str_replace('_', ' ', $log->event_type) }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">&bull; {{ $log->created_at->format('d M Y, H:i') }} WIB</span>
                                </div>
                                @if ($log->description)
                                    <p class="text-slate-600 text-[11px]">{{ $log->description }}</p>
                                @endif
                                <div class="text-[10px] text-slate-400">
                                    Operator: {{ $log->user->name ?? 'Sistem Otomatis' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada riwayat aktivitas tercatat.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Action Sidebar (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Status Transition Form Card -->
            <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-xs space-y-4"
                 x-data="{ nextStatus: '{{ $application->status }}' }">
                
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2.5">
                    Perbarui Status Berkas
                </h3>

                <form action="{{ route('admin.leasing.status.update', $application) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                            Pilih Status Baru <span class="text-red-600">*</span>
                        </label>
                        <select name="status"
                                x-model="nextStatus"
                                required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <option value="draft">Draft</option>
                            <option value="submitted">Terkirim ke Leasing</option>
                            <option value="processing">Sedang Diproses Analis</option>
                            <option value="approved">Disetujui (Approved)</option>
                            <option value="need_revision">Perlu Revisi Dokumen</option>
                            <option value="rejected">Ditolak (Rejected)</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                            Nomor Referensi Provider Leasing
                        </label>
                        <input type="text"
                               name="provider_reference"
                               value="{{ old('provider_reference', $application->provider_reference) }}"
                               placeholder="Contoh: TAF-APP-88912"
                               class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-mono text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <!-- Show Revision Notes when status is need_revision -->
                    <div x-show="nextStatus === 'need_revision'" x-cloak>
                        <label class="block text-[11px] font-bold text-purple-700 mb-1">
                            Catatan Revisi Dokumen untuk Nasabah <span class="text-red-600">*</span>
                        </label>
                        <textarea name="revision_notes"
                                  rows="3"
                                  placeholder="Sebutkan dokumen yang kurang atau perlu diperjelas (contoh: Foto slip gaji 3 bulan terakhir buram)..."
                                  class="w-full rounded-lg border border-purple-300 bg-purple-50/30 px-3 py-1.5 text-xs text-slate-900 focus:border-purple-600 focus:ring-1 focus:purple-600 focus:outline-none">{{ old('revision_notes', $application->revision_notes) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                            Catatan Audit / Alasan Keputusan
                        </label>
                        <textarea name="notes"
                                  rows="2"
                                  placeholder="Catatan verifikasi staf internal dealer..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-red-600 py-2.5 px-4 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Simpan Perubahan Status
                    </button>
                </form>
            </div>

            <!-- Linked Sales Opportunity & CRM Card -->
            <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-xs space-y-3 text-xs">
                <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] border-b border-slate-100 pb-2">
                    Koneksi Pipeline Sales (CRM)
                </h3>

                @if ($application->salesOpportunity)
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Pipeline Code:</span>
                            <a href="{{ route('admin.sales.opportunities.show', $application->salesOpportunity) }}"
                               class="font-mono font-bold text-red-600 hover:underline">
                                {{ $application->salesOpportunity->code }}
                            </a>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tahapan CRM:</span>
                            <span class="font-bold text-slate-800 capitalize">{{ str_replace('_', ' ', $application->salesOpportunity->stage) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Sales PIC:</span>
                            <span class="font-semibold text-slate-800">{{ $application->salesOpportunity->assignedSales->name ?? 'Belum Ditugaskan' }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-slate-400 italic text-[11px]">Tidak ada sales opportunity langsung yang terhubung.</p>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection

