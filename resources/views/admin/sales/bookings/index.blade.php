@extends('layouts.dashboard')

@section('title', 'Pemesanan Kendaraan (SPK) — Toyota AutoConnect')
@section('header_title', 'Pemesanan Mobil & Alokasi Stok Unit (SPK)')
@section('header_subtitle', 'Kelola alokasi unit inventaris showroom berdasarkan surat pesanan kendaraan')

@section('content')
<div class="space-y-6">


    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Pemesanan SPK</p>
            <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Semua cabang</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-amber-200 shadow-xs bg-amber-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Pending Alokasi</p>
            <p class="text-xl font-extrabold text-amber-700 mt-1">{{ $stats['pending'] }}</p>
            <p class="text-[10px] text-amber-600/80 mt-0.5">Menunggu alokasi unit</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-emerald-200 shadow-xs bg-emerald-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Terkonfirmasi (Reserved)</p>
            <p class="text-xl font-extrabold text-emerald-700 mt-1">{{ $stats['confirmed'] }}</p>
            <p class="text-[10px] text-emerald-600/80 mt-0.5">Stok teralokasikan</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-blue-200 shadow-xs bg-blue-50/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Selesai (Delivered)</p>
            <p class="text-xl font-extrabold text-blue-700 mt-1">{{ $stats['completed'] }}</p>
            <p class="text-[10px] text-blue-600/80 mt-0.5">Unit diserahterimakan</p>
        </div>
        <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Batal / Kedaluwarsa</p>
            <p class="text-xl font-extrabold text-slate-600 mt-1">{{ $stats['cancelled'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Alokasi dilepaskan</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.sales.bookings.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-5">
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari kode SPK, pelanggan, model mobil..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            <div class="sm:col-span-3">
                <select name="status" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="all">Semua Status SPK</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending Alokasi</option>
                    <option value="confirmed" {{ $filters['status'] === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi (Stok Dialokasikan)</option>
                    <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Selesai Serah Terima</option>
                    <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="expired" {{ $filters['status'] === 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="branch_id" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Cabang Dealer</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $filters['branch_id'] == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-1 text-right">
                <button type="submit" class="w-full rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-900 transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1150px]">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[140px]">No. SPK &amp; Tanggal</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[180px]">Calon Pembeli</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[170px]">Unit Kendaraan</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Cabang Alokasi</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[140px]">Harga SPK Bersih</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[130px]">Tanda Jadi</th>
                        <th class="py-3 px-4 whitespace-nowrap text-center min-w-[130px]">Status Alokasi</th>
                        <th class="py-3 px-4 whitespace-nowrap text-right min-w-[110px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-900 block">{{ $booking->code }}</span>
                                <span class="font-mono text-[10px] text-slate-400 block">{{ $booking->booking_date->format('d/m/Y') }}</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $booking->customer->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $booking->customer->phone ?? $booking->customer->email }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $booking->vehicle->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $booking->vehicle->category }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ $booking->branch->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $booking->branch->city }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap font-mono font-bold text-slate-900">
                                Rp {{ number_format($booking->net_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-mono text-slate-800 block">Rp {{ number_format($booking->booking_fee, 0, ',', '.') }}</span>
                                <span class="text-[9px] font-semibold text-slate-500 block">({{ $booking->payment_status_label }})</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $booking->status_badge_class }}">
                                    {{ $booking->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                <a href="{{ route('admin.sales.bookings.show', $booking) }}"
                                   class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                                    Kelola Alokasi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-xs text-slate-400">
                                Belum ada data pemesanan unit kendaraan yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($bookings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

