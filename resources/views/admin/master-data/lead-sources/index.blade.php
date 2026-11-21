@extends('layouts.dashboard')

@section('title', 'Master Sumber Lead CRM — Toyota AutoConnect')
@section('header_title', 'Master Sumber Prospek Lead (CRM)')
@section('header_subtitle', 'Kelola klasifikasi kanal akuisisi prospek (Website, Showroom, Referral, Campaign, Event, Hotline)')

@section('content')
<div class="space-y-6">

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-xs font-semibold text-emerald-800 shadow-xs">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-xl bg-red-50 border border-red-200 p-4 text-xs font-semibold text-red-800 shadow-xs">
            <svg class="h-4 w-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Add Lead Source Form (4 Cols) -->
        <div class="lg:col-span-4">
            <div class="rounded-2xl bg-white p-5 border border-slate-200 shadow-xs space-y-4">
                <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-2">Tambah Sumber Lead Baru</h3>

                <form method="POST" action="{{ route('admin.lead-sources.store') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sumber Prospek <span class="text-red-600">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Pameran GIIAS 2026"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Unik Kanal <span class="text-red-600">*</span></label>
                        <input type="text" name="code" required placeholder="giias_2026"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 lowercase focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Kanal</label>
                        <textarea name="description" rows="3" placeholder="Penjelasan asal kanal atau tujuan kampanye..."
                                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                            <span class="text-xs text-slate-700 font-semibold">Aktifkan kanal sumber lead ini</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-red-600 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Tambah Sumber Lead
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Lead Sources Table (8 Cols) -->
        <div class="lg:col-span-8 space-y-4">
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
                <!-- Filter Bar -->
                <div class="p-4 border-b border-slate-100 bg-slate-50/60">
                    <form method="GET" action="{{ route('admin.lead-sources.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div class="sm:col-span-2">
                            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama atau kode sumber lead..."
                                   class="w-full rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>

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
                    <table class="w-full text-left border-collapse text-xs min-w-[720px]">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4 whitespace-nowrap min-w-[180px]">Nama Sumber Lead</th>
                                <th class="py-3 px-4 whitespace-nowrap min-w-[130px]">Kode Kanal</th>
                                <th class="py-3 px-4 whitespace-nowrap min-w-[140px]">Total Terhubung</th>
                                <th class="py-3 px-4 whitespace-nowrap text-center min-w-[100px]">Status</th>
                                <th class="py-3 px-4 whitespace-nowrap text-right min-w-[140px]">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($leadSources as $source)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-extrabold text-slate-900">{{ $source->name }}</div>
                                        @if($source->description)
                                            <div class="text-[11px] text-slate-500 truncate max-w-xs mt-0.5">{{ $source->description }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-800">
                                            {{ $source->code }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap font-bold text-slate-900">
                                        {{ $source->leads_count ?? 0 }} Prospek
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        @if ($source->is_active)
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
                                        <form method="POST" action="{{ route('admin.lead-sources.toggle', $source) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-xs font-semibold text-slate-600 hover:text-red-600 underline">
                                                {{ $source->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.lead-sources.destroy', $source) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sumber lead ini?')">
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
                                        Belum ada sumber prospek lead yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($leadSources->hasPages())
                    <div class="p-3 border-t border-slate-100 bg-slate-50/30">
                        {{ $leadSources->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection

