@extends('layouts.dashboard')

@section('title', 'Manajemen Pengajuan Pembiayaan & Leasing — Toyota AutoConnect')
@section('header_title', 'Manajemen Pembiayaan & Leasing')
@section('header_subtitle', 'Pantau pengajuan kredit nasabah dan status verifikasi lembaga leasing rekanan')

@section('content')
<div class="space-y-6">

    <!-- Flash message -->
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

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
        <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Aplikasi</p>
            <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Semua pengajuan</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-blue-200 shadow-xs bg-blue-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Terkirim</p>
            <p class="text-xl font-extrabold text-blue-700 mt-1">{{ $stats['submitted'] }}</p>
            <p class="text-[10px] text-blue-600/80 mt-0.5">Menunggu analis</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-amber-200 shadow-xs bg-amber-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Sedang Diproses</p>
            <p class="text-xl font-extrabold text-amber-700 mt-1">{{ $stats['processing'] }}</p>
            <p class="text-[10px] text-amber-600/80 mt-0.5">Survey & Analisis</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-emerald-200 shadow-xs bg-emerald-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Disetujui</p>
            <p class="text-xl font-extrabold text-emerald-700 mt-1">{{ $stats['approved'] }}</p>
            <p class="text-[10px] text-emerald-600/80 mt-0.5">Siap akad & PO</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-purple-200 shadow-xs bg-purple-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-purple-700">Perlu Revisi</p>
            <p class="text-xl font-extrabold text-purple-700 mt-1">{{ $stats['need_revision'] }}</p>
            <p class="text-[10px] text-purple-600/80 mt-0.5">Dokumen kurang</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-rose-200 shadow-xs bg-rose-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Ditolak</p>
            <p class="text-xl font-extrabold text-rose-700 mt-1">{{ $stats['rejected'] }}</p>
            <p class="text-[10px] text-rose-600/80 mt-0.5">Tidak disetujui</p>
        </div>
    </div>

    <!-- Filters & Action Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs space-y-3">
        <form method="GET" action="{{ route('admin.leasing.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">

            <!-- Search -->
            <div class="sm:col-span-4">
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari kode pengajuan, ref provider, nama, email..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-3">
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-700 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="all">Semua Status Pengajuan</option>
                    <option value="draft" {{ $filters['status'] === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ $filters['status'] === 'submitted' ? 'selected' : '' }}>Terkirim</option>
                    <option value="processing" {{ $filters['status'] === 'processing' ? 'selected' : '' }}>Sedang Diproses Analis</option>
                    <option value="approved" {{ $filters['status'] === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                    <option value="need_revision" {{ $filters['status'] === 'need_revision' ? 'selected' : '' }}>Perlu Revisi Dokumen</option>
                    <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                    <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                </select>
            </div>

            <!-- Leasing Provider Filter -->
            <div class="sm:col-span-3">
                <select name="leasing_provider_id" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-700 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Mitra Leasing</option>
                    @foreach ($providers as $provider)
                        <option value="{{ $provider->id }}" {{ $filters['leasing_provider_id'] == $provider->id ? 'selected' : '' }}>
                            {{ $provider->name }} ({{ $provider->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="sm:col-span-2 flex items-center gap-1.5">
                <button type="submit"
                        class="flex-1 rounded-lg bg-red-600 py-1.5 px-3 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    Filter
                </button>
                <a href="{{ route('admin.leasing.index') }}"
                   class="rounded-lg border border-slate-300 bg-white py-1.5 px-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Applications Table Card -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-left text-xs">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap">Kode &amp; Ref Provider</th>
                        <th class="py-3 px-4 whitespace-nowrap">Pemohon &amp; Sales</th>
                        <th class="py-3 px-4 whitespace-nowrap">Unit Kendaraan</th>
                        <th class="py-3 px-4 whitespace-nowrap">Mitra Leasing</th>
                        <th class="py-3 px-4 whitespace-nowrap">DP &amp; Tenor</th>
                        <th class="py-3 px-4 whitespace-nowrap">Estimasi Angsuran</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap">Status</th>
                        <th class="py-3 px-4 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($applications as $app)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono whitespace-nowrap">
                                <a href="{{ route('admin.leasing.show', $app) }}" class="font-bold text-slate-900 hover:text-red-600 block">
                                    {{ $app->code }}
                                </a>
                                @if ($app->provider_reference)
                                    <div class="text-[10px] text-slate-400 font-mono">Ref: {{ $app->provider_reference }}</div>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $app->submitted_at ? $app->submitted_at->format('d M Y') : $app->created_at->format('d M Y') }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $app->user->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $app->user->email ?? '-' }}</div>
                                @if ($app->salesOpportunity?->assignedSales)
                                    <div class="text-[10px] text-blue-600 font-medium mt-0.5">
                                        Sales: {{ $app->salesOpportunity->assignedSales->name }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $app->vehicle->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $app->formatted_vehicle_price }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-800 block">{{ $app->leasingProvider->name ?? '-' }}</span>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $app->leasingProvider->code ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-900">{{ $app->formatted_down_payment }}</div>
                                <div class="text-[10px] text-slate-500">{{ $app->tenor_months }} Bulan</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $app->formatted_monthly_installment }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $app->status_badge_class }}">
                                    {{ $app->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.leasing.show', $app) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                                    Detail &amp; Tindakan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-slate-400 italic">
                                Belum ada berkas pengajuan pembiayaan leasing yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($applications->hasPages())
            <div class="border-t border-slate-200 p-4">
                {{ $applications->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

