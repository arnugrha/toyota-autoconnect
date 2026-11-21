@extends('layouts.public')

@section('title', 'Riwayat Servis Kendaraan — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Riwayat Servis &amp; Perawatan Kendaraan</h1>
                <p class="text-xs text-slate-500 mt-1">Catatan riwayat digital seluruh pekerjaan servis, penggantian oli, suku cadang, dan faktur resmi bengkel Toyota.</p>
            </div>
            <div>
                <a href="{{ route('customer.service-bookings.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Booking Servis Baru
                </a>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
            <form action="{{ route('customer.service-histories.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input type="text"
                           name="license_plate"
                           value="{{ $currentPlate }}"
                           placeholder="Cari berdasarkan nomor plat polisi (misal: B 1234)..."
                           class="w-full rounded-xl border border-slate-300 pl-10 pr-3.5 py-2 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>
                <button type="submit"
                        class="w-full sm:w-auto px-5 py-2 rounded-xl bg-slate-900 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Cari Riwayat
                </button>
                @if($currentPlate)
                    <a href="{{ route('customer.service-histories.index') }}"
                       class="w-full sm:w-auto px-4 py-2 text-center text-xs font-semibold text-slate-600 hover:text-red-600">
                        Reset Filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Table Card with Horizontal Scroll -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 min-w-[1000px]">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[140px]">No. Work Order</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[150px]">Tanggal Selesai</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[180px]">Kendaraan &amp; Plat</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[180px]">Layanan Servis</th>
                            <th class="px-5 py-3.5 whitespace-nowrap min-w-[160px]">Cabang Bengkel</th>
                            <th class="px-5 py-3.5 whitespace-nowrap text-right min-w-[130px]">Biaya Akhir</th>
                            <th class="px-5 py-3.5 whitespace-nowrap text-right min-w-[110px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ $order->order_number }}
                                    <span class="block font-sans text-[10px] font-bold text-emerald-600 mt-0.5">
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-800 block">
                                        {{ ($order->completed_at ?? $order->delivered_at)?->translatedFormat('d M Y') ?? $order->updated_at->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        {{ ($order->completed_at ?? $order->delivered_at)?->format('H:i') ?? $order->updated_at->format('H:i') }} WIB
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-900 block">{{ $order->vehicle_name }}</span>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded font-mono text-[10px] font-bold border border-slate-200">
                                            {{ $order->license_plate }}
                                        </span>
                                        @if($order->current_mileage)
                                            <span class="text-[10px] text-slate-500 font-semibold">{{ number_format($order->current_mileage, 0, ',', '.') }} KM</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-900 block">{{ $order->serviceType?->name ?? 'Servis Berkala' }}</span>
                                    @if($order->technician_name)
                                        <span class="text-[10px] text-slate-400">Teknisi: {{ $order->technician_name }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800 block">{{ $order->branch?->name }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $order->branch?->city }}</span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <span class="font-bold text-slate-900 font-mono text-sm block">
                                        {{ $order->formatted_final_cost }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('customer.service-histories.show', $order) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-slate-50 hover:text-red-600 transition shadow-2xs">
                                        Faktur Servis
                                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-xs font-bold text-slate-800">Belum ada riwayat servis kendaraan yang selesai</h3>
                                    <p class="text-[11px] text-slate-500 mt-1 max-w-sm mx-auto">Riwayat servis berkala dan perbaikan bengkel resmi Anda akan tercatat rapi di sini setelah kendaraan selesai diservis.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

