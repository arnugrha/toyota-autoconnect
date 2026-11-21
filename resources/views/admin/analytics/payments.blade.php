@extends('layouts.dashboard')

@section('title', 'Analisis Transaksi Pembayaran — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Analisis Transaksi Pembayaran Gateway
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Laporan rekonsiliasi pembayaran booking fee dan pelunasan SPK melalui Midtrans Snap Gateway.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold shadow-xs">
                <span>Midtrans Snap Checkout</span>
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    @include('admin.analytics.partials.nav-tabs')

    <!-- Date Range & Branch Filters -->
    @include('admin.analytics.partials.period-filter', ['branches' => $data['branches'] ?? null, 'selectedBranchId' => $selectedBranchId ?? null])

    <!-- 1. KPIs Cards: Paid, Pending, Failed -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Berhasil Diterima -->
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/30 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="block text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Pembayaran Lunas</span>
                <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">Paid</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-extrabold text-slate-900 truncate block" title="Rp {{ number_format($data['kpis']['total_received'], 0, ',', '.') }}">
                    Rp {{ number_format($data['kpis']['total_received'], 0, ',', '.') }}
                </span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500 mt-2 pt-2 border-t border-emerald-100">
                <span>Volume Transaksi:</span>
                <span class="font-bold text-emerald-800">{{ number_format($data['kpis']['paid_count']) }} Transaksi</span>
            </div>
        </div>

        <!-- Rata-rata Transaksi -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Rata-Rata per Transaksi</span>
            <div class="mt-2">
                <span class="text-2xl font-extrabold text-slate-900 truncate block" title="Rp {{ number_format($data['kpis']['avg_transaction'], 0, ',', '.') }}">
                    Rp {{ number_format($data['kpis']['avg_transaction'], 0, ',', '.') }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 pt-2 border-t border-slate-100">Rerata nominal transaksi berhasil</p>
        </div>

        <!-- Tertunda / Pending -->
        <div class="rounded-2xl border border-amber-200 bg-amber-50/30 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="block text-[11px] font-bold text-amber-800 uppercase tracking-wider">Menunggu Pembayaran</span>
                <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">Pending</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-extrabold text-slate-900 truncate block" title="Rp {{ number_format($data['kpis']['total_pending'], 0, ',', '.') }}">
                    Rp {{ number_format($data['kpis']['total_pending'], 0, ',', '.') }}
                </span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500 mt-2 pt-2 border-t border-amber-100">
                <span>Volume Menunggu:</span>
                <span class="font-bold text-amber-800">{{ number_format($data['kpis']['pending_count']) }} Transaksi</span>
            </div>
        </div>

        <!-- Gagal / Expired -->
        <div class="rounded-2xl border border-rose-200 bg-rose-50/30 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="block text-[11px] font-bold text-rose-800 uppercase tracking-wider">Gagal / Kedaluwarsa</span>
                <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-rose-100 text-rose-800">Failed</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-extrabold text-slate-900 truncate block" title="Rp {{ number_format($data['kpis']['total_failed'], 0, ',', '.') }}">
                    Rp {{ number_format($data['kpis']['total_failed'], 0, ',', '.') }}
                </span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500 mt-2 pt-2 border-t border-rose-100">
                <span>Volume Gagal:</span>
                <span class="font-bold text-rose-800">{{ number_format($data['kpis']['failed_count']) }} Transaksi</span>
            </div>
        </div>
    </div>

    <!-- 2. Breakdown per Metode Pembayaran -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Distribusi Metode Pembayaran</h2>
        @if($data['methods']->isEmpty())
            <div class="py-6 text-center text-xs text-slate-400">Belum ada transaksi pembayaran lunas pada rentang ini.</div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($data['methods'] as $met)
                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3.5">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                            <span class="truncate">{{ ucfirst(str_replace('_', ' ', $met->method_name)) }}</span>
                            <span class="text-slate-900 font-extrabold">{{ $met->count }} tx</span>
                        </div>
                        <div class="text-base font-extrabold text-slate-900 mt-1">
                            Rp {{ number_format($met->amount, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- 3. Paginated Transactions List with Filters -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Daftar Transaksi Pembayaran</h2>
                <p class="text-[11px] text-slate-500">Log verifikasi seluruh transaksi dan status gateway</p>
            </div>
        </div>

        <!-- Inline Filters & Search -->
        <form method="GET" action="{{ route('admin.analytics.payments') }}" class="flex flex-wrap items-center gap-2 mb-4 text-xs">
            <input type="hidden" name="period" value="{{ request('period', 'this_month') }}">
            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
            @if(request('branch_id')) <input type="hidden" name="branch_id" value="{{ request('branch_id') }}"> @endif

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari order ID, pelanggan, SPK..."
                   class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-red-600">

            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-800">
                <option value="">Semua Status Transaksi</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed (Gagal)</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired (Kedaluwarsa)</option>
            </select>

            <button type="submit" class="rounded-xl bg-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition">
                Cari
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1050px]">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Order ID / Gateway Ref</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">Waktu Transaksi</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Pelanggan</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[140px]">No. Booking SPK</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Unit Mobil</th>
                        <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Metode</th>
                        <th class="py-2.5 px-3 whitespace-nowrap text-right min-w-[120px]">Nominal</th>
                        <th class="py-2.5 px-3 whitespace-nowrap text-center min-w-[100px]">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                    @forelse($data['transactions'] as $tx)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900">
                                {{ $tx->order_id }}
                                @if($tx->gateway_transaction_id)
                                    <span class="block text-[10px] text-slate-400 font-normal">{{ $tx->gateway_transaction_id }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-900">{{ $tx->user?->name ?? 'N/A' }}</td>
                            <td class="py-2.5 px-3 font-mono text-slate-700">{{ $tx->vehicleBooking?->code ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-slate-700">{{ $tx->vehicleBooking?->vehicle?->name ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ ucfirst(str_replace('_', ' ', $tx->payment_method ?? 'Snap')) }}</td>
                            <td class="py-2.5 px-3 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $tx->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($tx->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $tx->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-xs text-slate-400 font-medium">
                                Tidak ada transaksi pembayaran yang sesuai dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data['transactions']->hasPages())
            <div class="mt-4 pt-3 border-t border-slate-100">
                {{ $data['transactions']->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

