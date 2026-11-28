@extends('layouts.dashboard')

@section('title', 'Pengingat Servis Berkala — Toyota AutoConnect')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Pengingat Servis Berkala (After-Sales Reminders)</h1>
            <p class="text-xs text-slate-500 mt-1">Sistem otomatisasi jadwal perawatan kendaraan pelanggan berdasarkan estimasi jarak tempuh (odometer) dan interval 6 bulan.</p>
        </div>
        <div>
            <form action="{{ route('admin.service-reminders.trigger') }}" method="POST" onsubmit="return confirm('Jalankan proses pengiriman notifikasi pengingat servis sekarang?')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    Jalankan Pemeriksaan Pengingat
                </button>
            </form>
        </div>
    </div>


    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Pengingat</span>
            <p class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block">Menunggu Jadwal</span>
            <p class="text-xl font-black text-amber-900 mt-1">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 block">Pengingat Terkirim</span>
            <p class="text-xl font-black text-blue-900 mt-1">{{ number_format($stats['sent']) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Telah Dibooking</span>
            <p class="text-xl font-black text-emerald-900 mt-1">{{ number_format($stats['booked']) }}</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
        <form action="{{ route('admin.service-reminders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-6 relative">
                <input type="text"
                       name="q"
                       value="{{ $filters['q'] }}"
                       placeholder="Cari plat nomor, nama pelanggan, tipe mobil..."
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>

            <div class="sm:col-span-4">
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending (Menunggu Jadwal)</option>
                    <option value="sent" {{ $filters['status'] === 'sent' ? 'selected' : '' }}>Sent (Terkirim)</option>
                    <option value="booked" {{ $filters['status'] === 'booked' ? 'selected' : '' }}>Booked (Telah Dibooking)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-slate-900 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Filter
                </button>
                @if($filters['q'] || $filters['status'] !== 'all')
                    <a href="{{ route('admin.service-reminders.index') }}" class="p-2 text-slate-500 hover:text-red-600 text-xs font-bold">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card with Horizontal Scroll -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 min-w-[1100px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[180px]">Pelanggan &amp; Kontak</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[180px]">Unit Mobil &amp; Plat</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[200px]">Rekomendasi Servis</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[160px]">Estimasi Tanggal</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[150px]">Target Odometer</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center min-w-[130px]">Status</th>
                        <th class="px-5 py-3.5 whitespace-nowrap min-w-[150px]">Terkirim Pada</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reminders as $rem)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $rem->user?->name }}</span>
                                <span class="text-[11px] text-slate-500 block">{{ $rem->user?->phone ?? $rem->user?->email }}</span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $rem->vehicle_name }}</span>
                                <span class="inline-block mt-0.5 px-2 py-0.5 bg-slate-100 text-slate-700 rounded font-mono text-[10px] font-bold border border-slate-200">
                                    {{ $rem->license_plate }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-900">
                                {{ $rem->recommended_service }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">
                                    {{ $rem->next_service_date ? \Carbon\Carbon::parse($rem->next_service_date)->translatedFormat('d M Y') : '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-slate-800 whitespace-nowrap">
                                {{ $rem->next_service_mileage ? number_format($rem->next_service_mileage, 0, ',', '.') . ' KM' : '-' }}
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $rem->status_badge_class }}">
                                    {{ $rem->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500 whitespace-nowrap">
                                {{ $rem->reminder_sent_at ? $rem->reminder_sent_at->format('d/m/Y H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500 text-xs">
                                Tidak ada data pengingat servis yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reminders->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $reminders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

