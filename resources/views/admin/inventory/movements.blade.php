@extends('layouts.dashboard')

@section('title', 'Riwayat Pergerakan Stok — Toyota AutoConnect')
@section('header_title', 'Riwayat Audit Trail Stok Unit')
@section('header_subtitle', 'Catatan kronologis perubahan, penambahan, mutasi, dan penyesuaian stok per cabang')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.inventory.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Ringkasan Inventory
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.inventory.movements') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-4">
                <select name="branch_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Lokasi Cabang</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $filters['branch_id'] == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-4">
                <select name="type"
                        class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Jenis Pergerakan</option>
                    <option value="adjustment_in" {{ $filters['type'] === 'adjustment_in' ? 'selected' : '' }}>Penambahan Stok (+)</option>
                    <option value="adjustment_out" {{ $filters['type'] === 'adjustment_out' ? 'selected' : '' }}>Pengurangan Stok (-)</option>
                    <option value="initial_stock" {{ $filters['type'] === 'initial_stock' ? 'selected' : '' }}>Stok Awal Sistem</option>
                    <option value="reservation" {{ $filters['type'] === 'reservation' ? 'selected' : '' }}>Alokasi Reservasi</option>
                </select>
            </div>

            <div class="sm:col-span-4 flex items-center gap-1.5">
                <button type="submit"
                        class="flex-1 rounded-lg bg-slate-900 py-1.5 px-3 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Filter Log
                </button>
                @if ($filters['branch_id'] || $filters['type'])
                    <a href="{{ route('admin.inventory.movements') }}"
                       class="rounded-lg bg-slate-100 py-1.5 px-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Movements Table Card -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1050px]">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[160px]">Waktu Kejadian</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[200px]">Model Kendaraan</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[160px]">Cabang</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Jenis Pergerakan</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap min-w-[110px]">Perubahan</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap min-w-[150px]">Sebelum &rarr; Sesudah</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[220px]">Alasan &amp; Aktor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                {{ $m->created_at->format('d M Y, H:i') }} WIB
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $m->inventory->vehicle->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $m->inventory->vehicle->variant ?? $m->inventory->vehicle->category }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ $m->inventory->branch->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $m->inventory->branch->code }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="rounded px-2 py-0.5 text-[10px] font-semibold {{ $m->quantity > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $m->movement_type_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-extrabold text-sm whitespace-nowrap {{ $m->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $m->quantity > 0 ? '+'.$m->quantity : $m->quantity }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                {{ $m->stock_before }} &rarr; <strong class="text-slate-900">{{ $m->stock_after }}</strong>
                            </td>
                            <td class="py-3 px-4 min-w-[220px]">
                                <div class="text-slate-700">{{ $m->reason ?? '—' }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Oleh: <strong class="text-slate-600">{{ $m->user?->name ?? 'Sistem Dealer' }}</strong></div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-xs text-slate-400">
                                Belum ada riwayat pergerakan stok tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="p-3 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
