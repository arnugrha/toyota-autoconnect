@extends('layouts.dashboard')

@section('title', 'Service Orders / SPK Servis — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Perintah Kerja Servis (Service Order / SPK)</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola progres 9 tahapan pengerjaan bengkel resmi, hasil inspeksi mekanik, rincian suku cadang, dan faktur servis resmi.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.service-bookings.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Daftar Booking Masuk
            </a>
            <a href="{{ route('admin.service-reminders.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                Pengingat Servis
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3.5">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total WO</span>
            <p class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl border border-sky-200 bg-sky-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-sky-700 block">Dalam Pengerjaan</span>
            <p class="text-xl font-black text-sky-900 mt-1">{{ number_format($stats['in_progress']) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block">Tahap Inspeksi</span>
            <p class="text-xl font-black text-amber-900 mt-1">{{ number_format($stats['inspection']) }}</p>
        </div>
        <div class="rounded-2xl border border-orange-200 bg-orange-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-orange-700 block">Tunggu Part</span>
            <p class="text-xl font-black text-orange-900 mt-1">{{ number_format($stats['waiting_parts']) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Selesai Dikerjakan</span>
            <p class="text-xl font-black text-emerald-900 mt-1">{{ number_format($stats['completed']) }}</p>
        </div>
        <div class="rounded-2xl border border-teal-200 bg-teal-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 block">Diserahkan (Delivered)</span>
            <p class="text-xl font-black text-teal-900 mt-1">{{ number_format($stats['delivered']) }}</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
        <form action="{{ route('admin.service-orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-4 relative">
                <input type="text"
                       name="q"
                       value="{{ $filters['q'] }}"
                       placeholder="Cari No. WO, plat nomor, nama pelanggan, teknisi..."
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            <div class="sm:col-span-3">
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="all">Semua Status Pengerjaan</option>
                    <option value="scheduled" {{ $filters['status'] === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                    <option value="confirmed" {{ $filters['status'] === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                    <option value="vehicle_received" {{ $filters['status'] === 'vehicle_received' ? 'selected' : '' }}>Kendaraan Diterima</option>
                    <option value="inspection" {{ $filters['status'] === 'inspection' ? 'selected' : '' }}>Pemeriksaan / Diagnosa</option>
                    <option value="in_progress" {{ $filters['status'] === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                    <option value="waiting_parts" {{ $filters['status'] === 'waiting_parts' ? 'selected' : '' }}>Menunggu Suku Cadang</option>
                    <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Pengerjaan Selesai</option>
                    <option value="delivered" {{ $filters['status'] === 'delivered' ? 'selected' : '' }}>Kendaraan Diserahkan</option>
                    <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            @if(auth()->user()->hasRole('Super Admin'))
                <div class="sm:col-span-3">
                    <select name="branch_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">Semua Cabang Bengkel</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $filters['branch_id'] == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-slate-900 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Filter
                </button>
                @if($filters['q'] || $filters['status'] !== 'all' || $filters['branch_id'])
                    <a href="{{ route('admin.service-orders.index') }}" class="p-2 text-slate-500 hover:text-red-600 text-xs font-bold">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card with Horizontal Scroll -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 min-w-[1250px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[140px]">No. Work Order</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[160px]">Pelanggan &amp; Kontak</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[180px]">Unit Mobil &amp; Plat</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[180px]">Layanan Servis</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[170px]">Mekanik / Teknisi</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[160px]">Cabang</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center min-w-[130px]">Status</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-right min-w-[120px]">Biaya Akhir</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-right min-w-[110px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $order->order_number }}
                                <span class="block font-sans text-[10px] text-slate-400 font-normal mt-0.5">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $order->user?->name }}</span>
                                <span class="text-[11px] text-slate-500 block">{{ $order->user?->phone ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $order->vehicle_name }}</span>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded font-mono text-[10px] font-bold border border-slate-200">
                                        {{ $order->license_plate }}
                                    </span>
                                    @if($order->current_mileage)
                                        <span class="text-[10px] text-slate-500 font-semibold">{{ number_format($order->current_mileage, 0, ',', '.') }} KM</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-semibold text-slate-900 block whitespace-nowrap">{{ $order->serviceType?->name ?? 'Servis Berkala' }}</span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-800 block">{{ $order->technician_name ?? '-' }}</span>
                                @if($order->serviceAdvisor)
                                    <span class="text-[10px] text-slate-400 block">SA: {{ $order->serviceAdvisor->name }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                                {{ $order->branch?->name }}
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $order->status_badge_class }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right font-mono font-bold text-slate-900 text-sm whitespace-nowrap">
                                {{ $order->formatted_final_cost }}
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.service-orders.show', $order) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-slate-50 hover:text-red-600 transition shadow-2xs">
                                    Buka WO
                                    <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center text-slate-500 text-xs">
                                Tidak ada data Work Order yang sesuai dengan filter.
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
@endsection

