@extends('layouts.dashboard')

@section('title', 'CRM & Manajemen Lead — Toyota AutoConnect')
@section('header_title', 'CRM & Manajemen Prospek Calon Pembeli')
@section('header_subtitle', 'Pantau pipeline penjualan, status interaksi pelanggan, dan penugasan Sales Executive')

@section('content')
<div class="space-y-6">

    <!-- Pipeline Summary Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total Leads -->
        <div class="rounded-xl bg-white p-3.5 border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Total Prospek</span>
            <span class="text-xl font-extrabold text-slate-900 mt-1 block">{{ $stats['total'] }}</span>
            <span class="text-[9px] text-slate-400 mt-0.5 block">Dalam cakupan akses</span>
        </div>

        <!-- New Leads -->
        <div class="rounded-xl bg-white p-3.5 border border-blue-200 shadow-sm bg-blue-50/20">
            <span class="text-[10px] font-bold text-blue-700 uppercase tracking-wider block">Prospek Baru</span>
            <span class="text-xl font-extrabold text-blue-700 mt-1 block">{{ $stats['new'] }}</span>
            <span class="text-[9px] text-blue-600 mt-0.5 block">Perlu verifikasi awal</span>
        </div>

        <!-- In Progress -->
        <div class="rounded-xl bg-white p-3.5 border border-amber-200 shadow-sm bg-amber-50/20">
            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">Sedang Diproses</span>
            <span class="text-xl font-extrabold text-amber-700 mt-1 block">{{ $stats['in_progress'] }}</span>
            <span class="text-[9px] text-amber-600 mt-0.5 block">Follow-up &amp; negosiasi</span>
        </div>

        <!-- Deals Won -->
        <div class="rounded-xl bg-white p-3.5 border border-emerald-200 shadow-sm bg-emerald-50/20">
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Deal (Penjualan)</span>
            <span class="text-xl font-extrabold text-emerald-700 mt-1 block">{{ $stats['deal'] }}</span>
            <span class="text-[9px] text-emerald-600 mt-0.5 block">Siap proses SPK</span>
        </div>

        <!-- Lost -->
        <div class="rounded-xl bg-white p-3.5 border border-rose-200 shadow-sm bg-rose-50/20">
            <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Batal / Lost</span>
            <span class="text-xl font-extrabold text-rose-700 mt-1 block">{{ $stats['lost'] }}</span>
            <span class="text-[9px] text-rose-500 mt-0.5 block">Tidak tertarik/batal</span>
        </div>

        <!-- Overdue Follow-ups -->
        <div class="rounded-xl bg-white p-3.5 border border-purple-200 shadow-sm bg-purple-50/20">
            <span class="text-[10px] font-bold text-purple-700 uppercase tracking-wider block">Jatuh Tempo</span>
            <span class="text-xl font-extrabold text-purple-700 mt-1 block">{{ $stats['overdue'] }}</span>
            <span class="text-[9px] text-purple-600 mt-0.5 block">Perlu kontak segera</span>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.crm.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <!-- Search Keyword -->
            <div class="sm:col-span-4 relative">
                <input type="text" name="q" value="{{ $filters['q'] }}"
                       placeholder="Cari no. lead, customer, atau kendaraan..."
                       class="w-full rounded-lg border border-slate-300 pl-9 pr-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                <svg class="h-4 w-4 text-slate-400 absolute left-3 top-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-3">
                <select name="status"
                        class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                    <option value="">Semua Tahap Pipeline</option>
                    <option value="new" {{ $filters['status'] === 'new' ? 'selected' : '' }}>Prospek Baru</option>
                    <option value="contacted" {{ $filters['status'] === 'contacted' ? 'selected' : '' }}>Sudah Dihubungi</option>
                    <option value="interested" {{ $filters['status'] === 'interested' ? 'selected' : '' }}>Berminat / Hot Lead</option>
                    <option value="test_drive" {{ $filters['status'] === 'test_drive' ? 'selected' : '' }}>Jadwal Test Drive</option>
                    <option value="negotiation" {{ $filters['status'] === 'negotiation' ? 'selected' : '' }}>Negosiasi Penawaran</option>
                    <option value="booking" {{ $filters['status'] === 'booking' ? 'selected' : '' }}>SPK / Booking Fee</option>
                    <option value="deal" {{ $filters['status'] === 'deal' ? 'selected' : '' }}>Deal (Penjualan)</option>
                    <option value="lost" {{ $filters['status'] === 'lost' ? 'selected' : '' }}>Batal / Lost</option>
                </select>
            </div>

            <!-- Sales Filter (If User Has View Team Permission) -->
            @if ($canViewTeam)
                <div class="sm:col-span-3">
                    <select name="assigned_to"
                            class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                        <option value="">Semua Sales Penanggung Jawab</option>
                        <option value="unassigned" {{ $filters['assigned_to'] === 'unassigned' ? 'selected' : '' }}>[ Belum Ditugaskan ]</option>
                        @foreach ($salesTeam as $sales)
                            <option value="{{ $sales->id }}" {{ $filters['assigned_to'] == $sales->id ? 'selected' : '' }}>
                                {{ $sales->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Action Filter Buttons -->
            <div class="{{ $canViewTeam ? 'sm:col-span-2' : 'sm:col-span-5' }} flex items-center gap-2">
                <button type="submit"
                        class="flex-1 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition text-center">
                    Terapkan
                </button>

                @if ($filters['filter'] === 'overdue')
                    <a href="{{ route('admin.crm.index', array_merge(request()->except('filter'))) }}"
                       class="rounded-lg bg-red-100 text-red-700 px-2.5 py-1.5 text-xs font-bold border border-red-200"
                       title="Matikan filter jatuh tempo">
                        Jatuh Tempo Aktif
                    </a>
                @else
                    <a href="{{ route('admin.crm.index', array_merge(request()->all(), ['filter' => 'overdue'])) }}"
                       class="rounded-lg bg-slate-100 text-slate-700 px-2.5 py-1.5 text-xs font-semibold hover:bg-slate-200"
                       title="Tampilkan hanya yang perlu follow up segera">
                        Jatuh Tempo
                    </a>
                @endif

                @if ($filters['q'] || $filters['status'] || $filters['assigned_to'] || $filters['filter'])
                    <a href="{{ route('admin.crm.index') }}"
                       class="rounded-lg bg-slate-100 px-2 py-1.5 text-xs font-semibold text-slate-500 hover:bg-slate-200"
                       title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Leads Table Card -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-900">
                    Daftar Prospek Penjualan ({{ $leads->total() }})
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ $canViewTeam ? 'Cakupan seluruh tim sales dealer Toyota AutoConnect' : 'Daftar prospek yang ditugaskan khusus kepada Anda' }}
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1100px]">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">No. Prospek &amp; Tanggal</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[180px]">Calon Pembeli (Customer)</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[160px]">Model Kendaraan</th>
                        <th class="py-3 px-4 whitespace-nowrap text-center min-w-[130px]">Tahap Pipeline</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[160px]">Sales Ditugaskan</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[160px]">Follow-up Berikutnya</th>
                        <th class="py-3 px-4 whitespace-nowrap text-right min-w-[90px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($leads as $lead)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 font-mono">{{ $lead->lead_number }}</div>
                                <div class="text-[10px] text-slate-400">{{ $lead->created_at->format('d M Y, H:i') }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $lead->customer->name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $lead->customer->phone ?? $lead->customer->email }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($lead->vehicle)
                                    <div class="font-semibold text-slate-900">{{ $lead->vehicle->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $lead->vehicle->category }}</div>
                                @else
                                    <span class="text-slate-400 italic">Umum / Showroom</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold border {{ $lead->status_badge_class }}">
                                    {{ $lead->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($lead->assignedSales)
                                    <span class="font-medium text-slate-900 flex items-center gap-1.5">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $lead->assignedSales->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        Belum Ditugaskan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($lead->next_follow_up_at)
                                    <div class="text-[11px] font-medium {{ $lead->is_overdue ? 'text-red-600 font-bold' : 'text-slate-700' }}">
                                        {{ $lead->next_follow_up_at->format('d M Y, H:i') }}
                                    </div>
                                    @if ($lead->is_overdue)
                                        <span class="inline-block text-[9px] font-bold uppercase text-red-600 bg-red-50 px-1 rounded">
                                            Jatuh Tempo
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum dijadwalkan</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-right">
                                <a href="{{ route('admin.leads.show', $lead) }}"
                                   class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-slate-800 transition">
                                    Kelola
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-xs text-slate-400">
                                Tidak ada data prospek yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leads->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
