@extends('layouts.public')

@section('title', 'Detail Pengajuan Leasing ' . $application->code . ' — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Navigation -->
        <div class="flex items-center justify-between">
            <a href="{{ route('customer.leasing.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Pengajuan
            </a>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $application->status_badge_class }}">
                    {{ $application->status_label }}
                </span>
            </div>
        </div>

        <!-- Flash messages -->
        @if (session('success'))
            <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-xs text-emerald-800">
                <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-2.5 rounded-xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800">
                <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Notice Alert for Need Revision -->
        @if ($application->status === 'need_revision')
            <div class="rounded-2xl bg-purple-50 border border-purple-200 p-5 space-y-2">
                <div class="flex items-center gap-2 text-purple-900 font-bold text-sm">
                    <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    <span>Catatan Revisi Dokumen dari Lembaga Pembiayaan</span>
                </div>
                <p class="text-xs text-purple-800 leading-relaxed">
                    {{ $application->revision_notes ?: 'Mohon hubungi konsultan sales cabang Anda untuk memperbarui kelengkapan berkas dokumen persyaratan kredit.' }}
                </p>
            </div>
        @endif

        <!-- Notice Alert for Rejection -->
        @if ($application->status === 'rejected')
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-5 space-y-2">
                <div class="flex items-center gap-2 text-rose-900 font-bold text-sm">
                    <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z" />
                    </svg>
                    <span>Pengajuan Kredit Belum Dapat Disetujui</span>
                </div>
                <p class="text-xs text-rose-800 leading-relaxed">
                    Catatan: {{ $application->notes ?: 'Kualifikasi kredit belum memenuhi kriteria analisis risiko internal mitra leasing.' }}
                </p>
            </div>
        @endif

        <!-- Notice Alert for Approved -->
        @if ($application->status === 'approved')
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5 space-y-2">
                <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>Pengajuan Pembiayaan Disetujui (Approved)</span>
                </div>
                <p class="text-xs text-emerald-800 leading-relaxed">
                    Selamat! Permohonan kredit Anda telah disetujui resmi oleh <strong class="font-bold">{{ $application->leasingProvider->name }}</strong>. Konsultan sales cabang kami akan segera menghubungi Anda untuk tahap penandatanganan perjanjian akad kredit dan penyerahan unit mobil.
                </p>
            </div>
        @endif

        <!-- Main Application Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-6 sm:p-8 space-y-6">

            <!-- Card Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NOMOR REGISTER PENGAJUAN KREDIT</span>
                    <h1 class="text-xl font-extrabold text-slate-900 font-mono mt-0.5">{{ $application->code }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-slate-500">
                        <span>Mitra Leasing: <strong class="text-slate-800 font-bold">{{ $application->leasingProvider->name }} ({{ $application->leasingProvider->code }})</strong></span>
                        @if ($application->provider_reference)
                            <span>&bull;</span>
                            <span>Ref Provider: <strong class="font-mono text-slate-700">{{ $application->provider_reference }}</strong></span>
                        @endif
                    </div>
                </div>

                <div class="text-left sm:text-right space-y-0.5">
                    <span class="text-[10px] text-slate-400 block uppercase tracking-wider font-bold">Waktu Pengajuan</span>
                    <span class="text-xs font-bold text-slate-800 block">
                        {{ $application->submitted_at ? $application->submitted_at->format('d M Y, H:i') : $application->created_at->format('d M Y, H:i') }} WIB
                    </span>
                    @if ($application->salesOpportunity?->assignedSales)
                        <span class="text-[11px] text-slate-500 block">Sales: {{ $application->salesOpportunity->assignedSales->name }}</span>
                    @endif
                </div>
            </div>

            <!-- Vehicle & Financing Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Vehicle Box -->
                <div class="rounded-xl bg-slate-50 p-5 border border-slate-100 space-y-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kendaraan Yang Diajukan</span>
                    <div class="flex items-center gap-4">
                        <img src="{{ $application->vehicle->primary_image_url }}"
                             alt="{{ $application->vehicle->name }}"
                             class="h-20 w-28 object-cover rounded-lg border border-slate-200 bg-slate-950 flex-shrink-0">
                        <div>
                            <span class="inline-block rounded bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700">
                                {{ $application->vehicle->category }}
                            </span>
                            <h3 class="text-base font-extrabold text-slate-900 mt-1">{{ $application->vehicle->name }}</h3>
                            <p class="text-xs text-slate-500">{{ $application->vehicle->variant ?? 'Varian Standar' }}</p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Harga OTR Resmi:</span>
                        <span class="font-mono font-bold text-slate-900">{{ $application->formatted_vehicle_price }}</span>
                    </div>
                </div>

                <!-- Financing Metrics -->
                <div class="rounded-xl bg-slate-900 text-white p-5 space-y-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Skema Pembiayaan</span>
                    
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Uang Muka (DP)</span>
                            <p class="font-mono font-bold text-sm text-white mt-0.5">
                                {{ $application->formatted_down_payment }}
                            </p>
                            @php
                                $dpPct = $application->vehicle_price > 0 ? round(($application->down_payment / $application->vehicle_price) * 100, 1) : 0;
                            @endphp
                            <span class="text-[10px] text-slate-400">({{ $dpPct }}%)</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Pokok Pembiayaan</span>
                            <p class="font-mono font-bold text-sm text-white mt-0.5">
                                Rp {{ number_format(max(0, $application->vehicle_price - $application->down_payment), 0, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Tenor Pembiayaan</span>
                            <p class="font-bold text-sm text-white mt-0.5">
                                {{ $application->tenor_months }} Bulan
                            </p>
                            <span class="text-[10px] text-slate-400">({{ round($application->tenor_months / 12, 1) }} Tahun)</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Mitra Leasing</span>
                            <p class="font-mono font-bold text-sm text-white mt-0.5">
                                {{ $application->leasingProvider->code }}
                            </p>
                            <span class="text-[10px] text-slate-400">Suku Bunga {{ $application->leasingProvider->base_interest_rate }}%</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase block">Cicilan Per Bulan</span>
                            <span class="text-xs text-slate-400">Estimasi angsuran</span>
                        </div>
                        <p class="text-lg font-black font-mono text-red-400">
                            {{ $application->formatted_monthly_installment }}
                        </p>
                    </div>
                </div>

            </div>

            <!-- Applicant Biodata Overview -->
            <div class="rounded-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-5 py-3 border-b border-slate-200 text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Data Profil Pemohon Kredit
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-[10px] text-slate-400 block">Nama Pemohon (Akun)</span>
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
                        <span class="text-[10px] text-slate-400 block">Perusahaan / Instansi</span>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $application->applicant_data['company_name'] ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block">Status Perkawinan</span>
                        <p class="font-semibold text-slate-800 capitalize mt-0.5">{{ str_replace('_', ' ', $application->applicant_data['marital_status'] ?? '-') }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-[10px] text-slate-400 block">Kontak Email / Telp</span>
                        <p class="font-medium text-slate-800 mt-0.5">{{ $application->user->email ?? '-' }} &bull; {{ $application->user->phone_number ?? '-' }}</p>
                    </div>
                    @if ($application->notes)
                        <div class="sm:col-span-2 md:col-span-3 pt-2 border-t border-slate-100">
                            <span class="text-[10px] text-slate-400 block">Catatan Tambahan:</span>
                            <p class="text-slate-600 mt-0.5">{{ $application->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Application Timeline & Logs -->
            <div class="rounded-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-5 py-3 border-b border-slate-200 text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Riwayat Jejak Audit & Aktivitas (Audit Trail)
                </div>
                <div class="p-5 space-y-4">
                    @forelse ($application->logs as $log)
                        <div class="flex items-start gap-3">
                            <div class="h-2 w-2 rounded-full bg-red-600 mt-1.5 flex-shrink-0"></div>
                            <div class="space-y-0.5 text-xs">
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
                                    Dicatat oleh: {{ $log->user->name ?? 'Sistem Otomatis' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada riwayat aktivitas tercatat.</p>
                    @endforelse
                </div>
            </div>

            <!-- Actions / Cancellation Option -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Ada pertanyaan seputar kredit? Hubungi konsultan sales kami di dealer resmi Toyota.
                </div>

                @if ($application->canBeCancelled())
                    <form action="{{ route('customer.leasing.cancel', $application) }}"
                          method="POST"
                          onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan kredit ini?')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-rose-300 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 hover:border-rose-400 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            Batalkan Pengajuan
                        </button>
                    </form>
                @endif
            </div>

        </div>

    </div>
</div>
@endsection
