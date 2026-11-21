@extends('layouts.dashboard')

@section('title', 'Analisis Bengkel & After-Sales — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Analisis Bengkel &amp; Layanan Purna Jual (After-Sales)
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Laporan aktivitas booking servis berkala, perintah kerja (WO), pendapatan bengkel resmi, dan jenis perawatan terpopuler.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold shadow-xs">
                <span>Bengkel Resmi Toyota</span>
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    @include('admin.analytics.partials.nav-tabs')

    <!-- Date Range & Branch Filters -->
    @include('admin.analytics.partials.period-filter', ['branches' => $data['branches'] ?? null, 'selectedBranchId' => $selectedBranchId ?? null])

    <!-- 1. KPIs Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pendapatan Bengkel -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Pendapatan Bengkel</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-extrabold text-slate-900 truncate" title="Rp {{ number_format($data['kpis']['workshop_revenue'], 0, ',', '.') }}">
                    Rp {{ number_format($data['kpis']['workshop_revenue'], 0, ',', '.') }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Akumulasi total biaya servis selesai</p>
        </div>

        <!-- Total Booking Servis -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Booking Servis</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($data['kpis']['total_bookings']) }}</span>
                <span class="text-xs font-bold text-slate-400">Unit</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Pending: {{ $data['kpis']['pending_bookings'] }} | Konfirmasi: {{ $data['kpis']['confirmed_bookings'] }}</p>
        </div>

        <!-- Perintah Kerja (WO) Aktif -->
        <div class="rounded-2xl border border-amber-200 bg-amber-50/30 p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-amber-800 uppercase tracking-wider">Perintah Kerja Aktif</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-extrabold text-amber-600">{{ number_format($data['kpis']['active_orders']) }}</span>
                <span class="text-xs font-bold text-amber-500">Dalam Pengerjaan</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Inspeksi, servis, atau tunggu part</p>
        </div>

        <!-- Servis Selesai & Delivered -->
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/30 p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Servis Selesai &amp; Diambil</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-extrabold text-emerald-600">{{ number_format($data['kpis']['completed_orders']) }}</span>
                <span class="text-xs font-bold text-emerald-500">Completed</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Kendaraan selesai diserahkan ke pelanggan</p>
        </div>
    </div>

    <!-- 2. Layanan Terpopuler -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Jenis Layanan Servis Terpopuler</h2>
        @if($data['popular_types']->isEmpty())
            <div class="py-8 text-center text-xs text-slate-400">Belum ada catatan transaksi servis pada periode ini.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[700px]">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-2 px-3 whitespace-nowrap min-w-[180px]">Nama Layanan Servis</th>
                            <th class="py-2 px-3 whitespace-nowrap min-w-[160px]">Kategori Perawatan</th>
                            <th class="py-2 px-3 text-center whitespace-nowrap min-w-[140px]">Frekuensi Pengerjaan</th>
                            <th class="py-2 px-3 text-right whitespace-nowrap min-w-[160px]">Total Pendapatan Bengkel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                        @foreach($data['popular_types'] as $pt)
                            <tr>
                                <td class="py-2.5 px-3 font-bold text-slate-900">{{ $pt->service_name }}</td>
                                <td class="py-2.5 px-3 text-slate-600">{{ ucfirst($pt->category_name ?? 'Servis Umum') }}</td>
                                <td class="py-2.5 px-3 text-center font-extrabold text-slate-900">{{ $pt->count }} kali</td>
                                <td class="py-2.5 px-3 text-right font-extrabold text-slate-900">Rp {{ number_format($pt->revenue, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- 3. Paginated Service Orders List with Filters -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Daftar Perintah Kerja (WO) Bengkel</h2>
                <p class="text-[11px] text-slate-500">Monitor status pengerjaan servis 9 tahap teknisi dan advisor</p>
            </div>
        </div>

        <!-- Inline Filters & Search -->
        <form method="GET" action="{{ route('admin.analytics.after-sales') }}" class="flex flex-wrap items-center gap-2 mb-4 text-xs">
            <input type="hidden" name="period" value="{{ request('period', 'this_month') }}">
            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
            @if(request('branch_id')) <input type="hidden" name="branch_id" value="{{ request('branch_id') }}"> @endif

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari No WO, plat nomor, mobil..."
                   class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-red-600">

            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-800">
                <option value="">Semua Status WO</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Servis Selesai</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Sudah Diserahkan</option>
                <option value="waiting_parts" {{ request('status') === 'waiting_parts' ? 'selected' : '' }}>Menunggu Suku Cadang</option>
            </select>

            <button type="submit" class="rounded-xl bg-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition">
                Cari
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1050px]">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">No. WO</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Waktu</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Pelanggan</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Plat &amp; Unit</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Jenis Servis</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Service Advisor</th>
                        <th class="py-2.5 px-3 whitespace-nowrap text-right min-w-[130px]">Biaya Total</th>
                        <th class="py-2.5 px-3 whitespace-nowrap text-center min-w-[110px]">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                    @forelse($data['orders'] as $wo)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $wo->order_number }}</td>
                            <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">{{ $wo->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-900">{{ $wo->user?->name ?? 'N/A' }}</td>
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-slate-900 font-mono">{{ $wo->license_plate ?? '-' }}</span>
                                <span class="block text-[10px] text-slate-500">{{ $wo->vehicle_name ?? '-' }}</span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-700">{{ $wo->serviceType?->name ?? 'Servis Berkala' }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $wo->serviceAdvisor?->name ?? 'Staff Bengkel' }}</td>
                            <td class="py-2.5 px-3 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($wo->total_cost, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ in_array($wo->status, ['completed', 'delivered']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $wo->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-xs text-slate-400 font-medium">
                                Tidak ada data perintah kerja bengkel yang sesuai filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data['orders']->hasPages())
            <div class="mt-4 pt-3 border-t border-slate-100">
                {{ $data['orders']->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

