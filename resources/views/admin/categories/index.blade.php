@extends('layouts.dashboard')

@section('title', 'Manajemen Kategori — Toyota AutoConnect')
@section('header_title', 'Master Kategori Kendaraan')
@section('header_subtitle', 'Kelola klasifikasi segmen kendaraan (MPV, SUV, Sedan, Hybrid EV, dll)')

@section('content')
<div class="space-y-6">

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-3.5 text-xs text-emerald-800">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-xl bg-red-50 border border-red-200 p-3.5 text-xs text-red-800">
            <svg class="h-4 w-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Add Category Form (4 Cols) -->
        <div class="lg:col-span-4">
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Tambah Kategori Baru</h3>

                <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori <span class="text-red-600">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Compact SUV"
                               class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Slug (Opsional)</label>
                        <input type="text" name="slug" placeholder="compact-suv"
                               class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Ringkas</label>
                        <textarea name="description" rows="3" placeholder="Penjelasan segmen target pasar..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                            <span class="text-xs text-slate-700">Status Langsung Aktif</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-red-600 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Tambah Kategori
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Categories Table (8 Cols) -->
        <div class="lg:col-span-8">
            <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Daftar Kategori Terdaftar</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Total kategori yang digunakan untuk filter katalog</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[720px]">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Nama &amp; Slug</th>
                                <th class="py-3 px-4 whitespace-nowrap min-w-[200px]">Deskripsi</th>
                                <th class="py-3 px-4 whitespace-nowrap text-center min-w-[110px]">Jumlah Model</th>
                                <th class="py-3 px-4 whitespace-nowrap text-center min-w-[100px]">Status</th>
                                <th class="py-3 px-4 whitespace-nowrap text-right min-w-[140px]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($categories as $cat)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900">{{ $cat->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $cat->slug }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 max-w-xs truncate">
                                        {{ $cat->description ?? '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="rounded bg-slate-100 px-2 py-0.5 font-bold text-slate-700 text-[10px]">
                                            {{ $cat->vehicles_count }} Unit
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if ($cat->is_active)
                                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 font-bold text-emerald-700 text-[10px] border border-emerald-200">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 font-bold text-slate-500 text-[10px]">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap space-x-2">
                                        <form method="POST" action="{{ route('admin.categories.toggle', $cat) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-xs font-semibold text-slate-600 hover:text-red-600 underline">
                                                {{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
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
                                    <td colspan="5" class="py-8 text-center text-xs text-slate-400">
                                        Belum ada kategori yang ditambahkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($categories->hasPages())
                    <div class="p-3 border-t border-slate-100">
                        {{ $categories->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
