@extends('layouts.dashboard')

@section('title', 'Analisis Pelanggan & Segmentasi — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Analisis Pelanggan &amp; Segmentasi Perilaku
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Distribusi basis pelanggan terdaftar, pertumbuhan akun, loyalitas repeat customer, dan riwayat aktivitas transaksi.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold shadow-xs">
                <span>Total {{ number_format($data['kpis']['total_customers']) }} Pelanggan</span>
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    @include('admin.analytics.partials.nav-tabs')

    <!-- Date Range Filter -->
    @include('admin.analytics.partials.period-filter')

    <!-- 1. Customer KPIs Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Pelanggan</span>
            <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ number_format($data['kpis']['total_customers']) }}</span>
            <p class="text-[10px] text-slate-400 mt-1">Basis data terdaftar</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Pelanggan Baru</span>
            <span class="text-2xl font-extrabold text-emerald-600 mt-1 block">+{{ number_format($data['kpis']['new_customers']) }}</span>
            <p class="text-[10px] text-slate-400 mt-1">Mendaftar periode ini</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Memiliki SPK Mobil</span>
            <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ number_format($data['kpis']['with_bookings']) }}</span>
            <p class="text-[10px] text-slate-400 mt-1">Pernah booking unit</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Pernah Test Drive</span>
            <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ number_format($data['kpis']['with_test_drives']) }}</span>
            <p class="text-[10px] text-slate-400 mt-1">Uji coba kendaraan</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Servis Bengkel</span>
            <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ number_format($data['kpis']['with_services']) }}</span>
            <p class="text-[10px] text-slate-400 mt-1">Pelanggan after-sales</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Repeat Buyer</span>
            <span class="text-2xl font-extrabold text-purple-600 mt-1 block">{{ number_format($data['kpis']['repeat_customers']) }}</span>
            <p class="text-[10px] text-slate-400 mt-1">&gt; 1 SPK atau Servis</p>
        </div>
    </div>

    <!-- 2. Customer Registration Trend -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Tren Pertumbuhan Registrasi Pelanggan Baru</h2>
                <p class="text-[11px] text-slate-500">Jumlah akun pelanggan baru per bulan selama 12 bulan terakhir</p>
            </div>
        </div>

        @php
            $maxC = !empty($data['chart']['data']) ? max(max($data['chart']['data']), 1) : 1;
        @endphp

        <div class="overflow-x-auto pb-2">
            <div class="min-w-[600px] h-40 flex items-end gap-2 pt-6 pb-2 border-b border-slate-200">
                @foreach($data['chart']['labels'] as $idx => $lbl)
                    @php
                        $val = $data['chart']['data'][$idx] ?? 0;
                        $hPct = min(100, max(8, round(($val / $maxC) * 100)));
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 group relative h-full justify-end">
                        <span class="text-[9px] font-bold text-slate-600 {{ $val > 0 ? '' : 'invisible' }}">{{ $val }}</span>
                        <div class="w-full max-w-[28px] rounded-t-md transition-all duration-300 {{ $val > 0 ? 'bg-slate-900 group-hover:bg-red-600' : 'bg-slate-100' }}"
                             style="height: {{ $val > 0 ? $hPct : 4 }}%;"></div>
                        <span class="text-[10px] font-semibold text-slate-400 truncate w-full text-center block mt-1">{{ $lbl }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 3. Paginated Customer Table with Segment Filter & Search -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Direktori &amp; Riwayat Aktivitas Pelanggan</h2>
                <p class="text-[11px] text-slate-500">Segmentasi berdasarkan keterlibatan booking, test drive, dan transaksi</p>
            </div>
        </div>

        <!-- Filters & Search -->
        <form method="GET" action="{{ route('admin.analytics.customers') }}" class="flex flex-wrap items-center gap-2 mb-4 text-xs">
            <input type="hidden" name="period" value="{{ request('period', 'this_month') }}">
            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama, email, nomor HP..."
                   class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-red-600">

            <select name="segment" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-800">
                <option value="">Semua Segmentasi Pelanggan</option>
                <option value="has_booking" {{ request('segment') === 'has_booking' ? 'selected' : '' }}>Pernah Booking SPK</option>
                <option value="has_service" {{ request('segment') === 'has_service' ? 'selected' : '' }}>Pernah Servis Bengkel</option>
                <option value="has_test_drive" {{ request('segment') === 'has_test_drive' ? 'selected' : '' }}>Pernah Test Drive</option>
                <option value="repeat" {{ request('segment') === 'repeat' ? 'selected' : '' }}>Repeat Order (&gt; 1 transaksi)</option>
            </select>

            <button type="submit" class="rounded-xl bg-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition">
                Filter
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[950px]">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Pelanggan</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[140px]">Kontak</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Terdaftar Sejak</th>
                        <th class="py-2.5 px-3 whitespace-nowrap text-center min-w-[100px]">SPK Mobil</th>
                        <th class="py-2.5 px-3 whitespace-nowrap text-center min-w-[100px]">Test Drive</th>
                        <th class="py-2.5 px-3 whitespace-nowrap text-center min-w-[120px]">Booking Servis</th>
                        <th class="py-2.5 px-3 whitespace-nowrap text-center min-w-[130px]">Transaksi Lunas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                    @forelse($data['customers'] as $c)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 px-3">
                                <p class="font-bold text-slate-900">{{ $c->name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $c->email }}</p>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-slate-700">{{ $c->phone ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">{{ $c->created_at->format('d/m/Y') }}</td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[11px] font-extrabold {{ $c->vehicle_bookings_count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $c->vehicle_bookings_count }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[11px] font-extrabold {{ $c->test_drive_bookings_count > 0 ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $c->test_drive_bookings_count }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[11px] font-extrabold {{ $c->service_bookings_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $c->service_bookings_count }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[11px] font-extrabold {{ $c->paid_payments_count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $c->paid_payments_count }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-xs text-slate-400 font-medium">
                                Tidak ada pelanggan yang sesuai dengan kriteria filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data['customers']->hasPages())
            <div class="mt-4 pt-3 border-t border-slate-100">
                {{ $data['customers']->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

