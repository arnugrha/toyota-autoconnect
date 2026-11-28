@extends('layouts.dashboard')

@section('title', 'Master Layanan Servis — Toyota AutoConnect')
@section('header_title', 'Master Jenis Layanan Servis')
@section('header_subtitle', 'Kelola paket servis berkala, perbaikan umum, estimasi durasi dan estimasi biaya pengerjaan bengkel')

@section('content')
<div class="space-y-6">


    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Add Service Type Form (4 Cols) -->
        <div class="lg:col-span-4">
            <div class="rounded-2xl bg-white p-5 border border-slate-200 shadow-xs space-y-4">
                <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-2">Tambah Layanan Servis Baru</h3>

                <form method="POST" action="{{ route('admin.service-types.store') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Layanan Servis <span class="text-red-600">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Servis Berkala 40.000 KM"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Unik Servis <span class="text-red-600">*</span></label>
                        <input type="text" name="code" required placeholder="SRV-40K"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 uppercase focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Servis <span class="text-red-600">*</span></label>
                        <select name="category" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            @foreach($categories as $catKey => $catLabel)
                                <option value="{{ $catKey }}">{{ $catLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Durasi (Menit) <span class="text-red-600">*</span></label>
                            <input type="number" name="estimated_duration_minutes" value="60" required min="15" max="480" step="15"
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi Biaya (Rp) <span class="text-red-600">*</span></label>
                            <input type="number" name="estimated_cost" value="0" required min="0" step="10000"
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Pengerjaan</label>
                        <textarea name="description" rows="3" placeholder="Rincian part yang diganti, inspeksi titik keselamatan..."
                                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                            <span class="text-xs text-slate-700 font-semibold">Aktifkan paket servis ini</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-red-600 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Tambah Layanan Servis
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Service Types Table (8 Cols) -->
        <div class="lg:col-span-8 space-y-4">
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
                <!-- Filter Bar -->
                <div class="p-4 border-b border-slate-100 bg-slate-50/60">
                    <form method="GET" action="{{ route('admin.service-types.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama atau kode servis..."
                               class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">

                        <select name="category" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $catKey => $catLabel)
                                <option value="{{ $catKey }}" {{ $filters['category'] === $catKey ? 'selected' : '' }}>{{ $catLabel }}</option>
                            @endforeach
                        </select>

                        <div class="flex items-center gap-2">
                            <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                <option value="">Semua Status</option>
                                <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ $filters['status'] === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            <button type="submit" class="rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs min-w-[780px]">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4 whitespace-nowrap min-w-[200px]">Kode & Layanan Servis</th>
                                <th class="py-3 px-4 whitespace-nowrap min-w-[140px]">Kategori</th>
                                <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Durasi & Biaya</th>
                                <th class="py-3 px-4 whitespace-nowrap text-center min-w-[100px]">Status</th>
                                <th class="py-3 px-4 whitespace-nowrap text-right min-w-[140px]">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($serviceTypes as $st)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-extrabold text-slate-900">{{ $st->name }}</div>
                                        <div class="font-mono text-[10px] text-slate-400 font-bold uppercase mt-0.5">{{ $st->code }}</div>
                                        @if($st->description)
                                            <div class="text-[11px] text-slate-500 truncate max-w-xs mt-0.5">{{ $st->description }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $categories[$st->category] ?? $st->category }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900">
                                            {{ $st->estimated_cost > 0 ? 'Rp ' . number_format($st->estimated_cost, 0, ',', '.') : 'Gratis (Rp 0)' }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $st->estimated_duration_minutes }} Menit Pengerjaan</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        @if ($st->is_active)
                                            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 font-bold text-emerald-700 text-[10px] border border-emerald-200">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 font-bold text-slate-500 text-[10px]">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                                        <form method="POST" action="{{ route('admin.service-types.toggle', $st) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-xs font-semibold text-slate-600 hover:text-red-600 underline">
                                                {{ $st->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.service-types.destroy', $st) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus layanan servis ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 underline">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-xs text-slate-400">
                                        Belum ada jenis layanan servis yang cocok dengan kriteria pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($serviceTypes->hasPages())
                    <div class="p-3 border-t border-slate-100 bg-slate-50/30">
                        {{ $serviceTypes->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection

