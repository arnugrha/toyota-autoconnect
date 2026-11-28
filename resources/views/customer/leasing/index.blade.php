@extends('layouts.public')

@section('title', 'Status Pengajuan Pembiayaan Leasing — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">


        <!-- Page Header & Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Pengajuan Pembiayaan Leasing</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantau status aplikasi kredit mobil Toyota Anda dengan lembaga pembiayaan rekanan resmi</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('customer.leasing.create') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Buat Pengajuan Baru
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="flex flex-wrap items-center gap-1.5 bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs text-xs">
            <a href="{{ route('customer.leasing.index') }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ empty($currentStatus) ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Status
            </a>
            <a href="{{ route('customer.leasing.index', ['status' => 'submitted']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'submitted' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Terkirim
            </a>
            <a href="{{ route('customer.leasing.index', ['status' => 'processing']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'processing' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Diproses Analis
            </a>
            <a href="{{ route('customer.leasing.index', ['status' => 'approved']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'approved' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Disetujui
            </a>
            <a href="{{ route('customer.leasing.index', ['status' => 'need_revision']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'need_revision' ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Perlu Revisi
            </a>
            <a href="{{ route('customer.leasing.index', ['status' => 'rejected']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $currentStatus === 'rejected' ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Ditolak
            </a>
        </div>

        <!-- Table Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-5 whitespace-nowrap">Kode Pengajuan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Kendaraan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Mitra Leasing</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Uang Muka &amp; Tenor</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Estimasi Angsuran</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Status</th>
                            <th class="py-3.5 px-5 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($applications as $app)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-5 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ $app->code }}
                                    @if ($app->provider_reference)
                                        <div class="text-[10px] text-slate-400 font-mono">Ref: {{ $app->provider_reference }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $app->vehicle->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $app->vehicle->category }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800">{{ $app->leasingProvider->name }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-mono text-slate-900">{{ $app->formatted_down_payment }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $app->tenor_months }} Bulan ({{ round($app->tenor_months / 12, 1) }} Thn)</div>
                                </td>
                                <td class="py-4 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ $app->formatted_monthly_installment }}
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $app->status_badge_class }}">
                                        {{ $app->status_label }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-right whitespace-nowrap">
                                    <a href="{{ route('customer.leasing.show', $app) }}"
                                       class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-3 py-1 text-xs font-semibold text-white hover:bg-slate-800 transition">
                                        Pantau Status
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-16 text-center text-xs text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A2.25 2.25 0 0 0 18.25 8.25H5.75A2.25 2.25 0 0 0 3.5 10.333V21" />
                                            </svg>
                                        </div>
                                        <p class="font-medium text-slate-600">Belum ada pengajuan pembiayaan leasing yang aktif.</p>
                                        <a href="{{ route('customer.leasing.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700 transition shadow-xs">
                                            Buat Pengajuan Pembiayaan Sekarang &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($applications->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

