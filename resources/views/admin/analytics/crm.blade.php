@extends('layouts.dashboard')

@section('title', 'Analisis CRM & Lead Funnel — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Analisis CRM, Lead Funnel &amp; Konversi
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Pantau efektivitas konversi prospek pelanggan dari tahap awareness, test drive, negosiasi, hingga closing SPK.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold shadow-xs">
                <span>Rasio Konversi Rata-Rata: {{ $data['kpis']['conversion_rate'] }}%</span>
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    @include('admin.analytics.partials.nav-tabs')

    <!-- Date Range & Branch Filters -->
    @include('admin.analytics.partials.period-filter', ['branches' => $data['branches'] ?? null, 'selectedBranchId' => $selectedBranchId ?? null])

    <!-- 1. KPIs Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Leads Prospek</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-extrabold text-slate-900">{{ number_format($data['kpis']['total_leads']) }}</span>
                <span class="text-[11px] font-bold text-slate-400">Semua Kanal</span>
            </div>
            <p class="text-[10px] text-slate-400 mt-1">Prospek masuk pada periode ini</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kesepakatan Deal (SPK)</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-extrabold text-emerald-600">{{ number_format($data['kpis']['deals']) }}</span>
                <span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">
                    {{ $data['kpis']['conversion_rate'] }}%
                </span>
            </div>
            <p class="text-[10px] text-slate-400 mt-1">Prospek sukses closing unit</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Prospek Lost / Batal</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-extrabold text-rose-600">{{ number_format($data['kpis']['lost']) }}</span>
                <span class="text-[11px] font-bold text-rose-500">
                    {{ $data['kpis']['total_leads'] > 0 ? round(($data['kpis']['lost'] / $data['kpis']['total_leads']) * 100, 1) : 0 }}%
                </span>
            </div>
            <p class="text-[10px] text-slate-400 mt-1">Status batal / tidak berminat</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Rata-Rata Rasio Konversi</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-extrabold text-slate-900">{{ $data['kpis']['conversion_rate'] }}%</span>
                <span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded {{ $data['kpis']['conversion_rate'] >= 15 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $data['kpis']['conversion_rate'] >= 15 ? 'Optimal' : 'Follow-up' }}
                </span>
            </div>
            <p class="text-[10px] text-slate-400 mt-1">Target benchmark: 15% - 25%</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Rata-Rata Waktu Konversi</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-extrabold text-slate-900">
                    {{ $data['kpis']['avg_days_to_deal'] !== null ? $data['kpis']['avg_days_to_deal'] : '-' }}
                </span>
                <span class="text-xs font-bold text-slate-400">Hari</span>
            </div>
            <p class="text-[10px] text-slate-400 mt-1">Dari prospek baru ke closing SPK</p>
        </div>
    </div>

    <!-- 2. Visual Funnel 7 Stages -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-1">Visualisasi Funnel Penjualan (7 Tahap)</h2>
        <p class="text-[11px] text-slate-500 mb-5">Perjalanan prospek dari awal inquiries hingga closing kesepakatan</p>

        <div class="space-y-4">
            @php
                $stageColors = [
                    'new' => 'bg-slate-400',
                    'contacted' => 'bg-blue-500',
                    'interested' => 'bg-cyan-500',
                    'test_drive' => 'bg-purple-500',
                    'negotiation' => 'bg-amber-500',
                    'deal' => 'bg-emerald-500',
                    'lost' => 'bg-rose-500',
                ];
            @endphp

            @foreach($data['funnel'] as $stageKey => $stage)
                <div class="rounded-xl border border-slate-100 p-3 bg-slate-50/50">
                    <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $stageColors[$stageKey] ?? 'bg-slate-500' }}"></span>
                            <span class="text-slate-800">{{ $stage['label'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-900 font-extrabold">{{ $stage['count'] }} Lead</span>
                            <span class="text-slate-400 font-normal">({{ $stage['percentage'] }}%)</span>
                        </div>
                    </div>
                    <div class="w-full h-3 rounded-full bg-slate-200/70 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $stageColors[$stageKey] ?? 'bg-slate-500' }}"
                             style="width: {{ max(1, $stage['percentage']) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. Conversion by Dealer, Sales Executive, and Lead Sources -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Konversi per Cabang Dealer -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Rasio Konversi per Dealer</h2>
            @if($data['by_dealer']->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400">Belum ada data prospek per cabang.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[380px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2 whitespace-nowrap min-w-[140px]">Cabang</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[70px]">Leads</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[70px]">Deal</th>
                                <th class="py-2 text-right whitespace-nowrap min-w-[80px]">Konversi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                            @foreach($data['by_dealer'] as $d)
                                <tr>
                                    <td class="py-2 whitespace-nowrap">
                                        <p class="font-bold text-slate-900">{{ $d['name'] }}</p>
                                    </td>
                                    <td class="py-2 text-center text-slate-600 whitespace-nowrap">{{ $d['total_leads'] }}</td>
                                    <td class="py-2 text-center text-emerald-600 font-bold whitespace-nowrap">{{ $d['deals'] }}</td>
                                    <td class="py-2 text-right font-extrabold text-slate-900 whitespace-nowrap">{{ $d['conversion_rate'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Konversi per Sales Executive -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Efektivitas Sales Executive</h2>
            @if($data['by_executive']->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400">Belum ada penugasan sales pada periode ini.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[380px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2 whitespace-nowrap min-w-[140px]">Sales PIC</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[70px]">Leads</th>
                                <th class="py-2 text-center whitespace-nowrap min-w-[70px]">Closing</th>
                                <th class="py-2 text-right whitespace-nowrap min-w-[80px]">Rasio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                            @foreach($data['by_executive'] as $e)
                                <tr>
                                    <td class="py-2 font-bold text-slate-900 whitespace-nowrap">{{ $e['name'] }}</td>
                                    <td class="py-2 text-center text-slate-600 whitespace-nowrap">{{ $e['total_leads'] }}</td>
                                    <td class="py-2 text-center text-emerald-600 font-bold whitespace-nowrap">{{ $e['deals'] }}</td>
                                    <td class="py-2 text-right font-extrabold text-slate-900 whitespace-nowrap">{{ $e['conversion_rate'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Sumber Prospek (Lead Sources) -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Sumber Prospek (Lead Sources)</h2>
            @if($data['sources']->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400">Belum ada data sumber prospek.</div>
            @else
                <div class="space-y-3 pt-1">
                    @foreach($data['sources'] as $src)
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                <span class="text-slate-800 font-bold">{{ $src['name'] }}</span>
                                <span class="text-slate-900">{{ $src['count'] }} <span class="text-slate-400 font-normal">({{ $src['percentage'] }}%)</span></span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full bg-red-600" style="width: {{ $src['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    <!-- 4. Leads Detail Table with Filters & Pagination -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Daftar Prospek Leads Aktif</h2>
                <p class="text-[11px] text-slate-500">Data leads beserta riwayat tahap dan sales PIC</p>
            </div>
        </div>

        <!-- Inline Filters -->
        <form method="GET" action="{{ route('admin.analytics.crm') }}" class="flex flex-wrap items-center gap-2 mb-4 text-xs">
            <input type="hidden" name="period" value="{{ request('period', 'this_month') }}">
            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
            @if(request('branch_id')) <input type="hidden" name="branch_id" value="{{ request('branch_id') }}"> @endif

            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-800">
                <option value="">Semua Tahap Status</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Prospek Baru</option>
                <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Sudah Dihubungi</option>
                <option value="interested" {{ request('status') === 'interested' ? 'selected' : '' }}>Tertarik</option>
                <option value="test_drive" {{ request('status') === 'test_drive' ? 'selected' : '' }}>Test Drive</option>
                <option value="negotiation" {{ request('status') === 'negotiation' ? 'selected' : '' }}>Negosiasi</option>
                <option value="deal" {{ request('status') === 'deal' ? 'selected' : '' }}>Deal</option>
                <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
            </select>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1050px]">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Tanggal</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Nama Prospek</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[140px]">Kontak</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Unit Minat</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Sumber</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Cabang Dealer</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Sales PIC</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap min-w-[110px]">Tahap Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                    @forelse($data['leads'] as $ld)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">{{ $ld->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-900">{{ $ld->customer?->name ?? 'N/A' }}</td>
                            <td class="py-2.5 px-3 text-slate-600 font-mono text-[11px]">{{ $ld->customer_phone ?? $ld->customer_email ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-slate-800">{{ $ld->vehicle?->name ?? $ld->vehicle_interest ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ ucfirst(str_replace('_', ' ', $ld->source ?? 'Website')) }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $ld->assignedSales?->branch?->name ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $ld->assignedSales?->name ?? 'Belum Ditugaskan' }}</td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $ld->status === 'deal' ? 'bg-emerald-100 text-emerald-800' : ($ld->status === 'lost' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $ld->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-xs text-slate-400 font-medium">
                                Tidak ada data prospek lead yang sesuai dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data['leads']->hasPages())
            <div class="mt-4 pt-3 border-t border-slate-100">
                {{ $data['leads']->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

