@extends('layouts.dashboard')

@section('title', 'Manajemen Cabang — Toyota AutoConnect')
@section('header_title', 'Master Cabang Dealer')
@section('header_subtitle', 'Kelola cabang resmi Toyota AutoConnect untuk operasional multi-cabang & inventory')

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

        <!-- Left Column: Add Branch Form (4 Cols) -->
        <div class="lg:col-span-4">
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Tambah Cabang Baru</h3>

                <form method="POST" action="{{ route('admin.branches.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Cabang <span class="text-red-600">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: AutoConnect Pluit"
                               class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Cabang Unik <span class="text-red-600">*</span></label>
                        <input type="text" name="code" required placeholder="AT-JKT-03"
                               class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none uppercase">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kota <span class="text-red-600">*</span></label>
                            <input type="text" name="city" required placeholder="Jakarta Utara"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon <span class="text-red-600">*</span></label>
                            <input type="text" name="phone" required placeholder="021-6601234"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap <span class="text-red-600">*</span></label>
                        <textarea name="address" rows="2" required placeholder="Jl. Raya Pluit Selatan No..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Cabang (Opsional)</label>
                        <input type="email" name="email" placeholder="pluit@toyota-autoconnect.id"
                               class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                            <span class="text-xs text-slate-700">Cabang Langsung Aktif</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-red-600 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Tambah Cabang
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Branches Table (8 Cols) -->
        <div class="lg:col-span-8">
            <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Daftar Cabang Dealer</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Lokasi showroom &amp; bengkel resmi terintegrasi</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[720px]">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4 whitespace-nowrap min-w-[160px]">Nama &amp; Kode</th>
                                <th class="py-3 px-4 whitespace-nowrap min-w-[200px]">Kota &amp; Alamat</th>
                                <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Kontak</th>
                                <th class="py-3 px-4 whitespace-nowrap text-center min-w-[100px]">Status</th>
                                <th class="py-3 px-4 whitespace-nowrap text-right min-w-[140px]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($branches as $branch)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900">{{ $branch->name }}</div>
                                        <div class="text-[10px] text-red-600 font-mono font-bold">{{ $branch->code }}</div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-semibold text-slate-800">{{ $branch->city }}</div>
                                        <div class="text-[10px] text-slate-500 truncate max-w-xs">{{ $branch->address }}</div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="text-slate-900 font-medium">{{ $branch->phone }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $branch->email ?? '—' }}</div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-center">
                                        @if ($branch->is_active)
                                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 font-bold text-emerald-700 text-[10px] border border-emerald-200">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 font-bold text-slate-500 text-[10px]">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-right space-x-2">
                                        <form method="POST" action="{{ route('admin.branches.toggle', $branch) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-xs font-semibold text-slate-600 hover:text-red-600 underline">
                                                {{ $branch->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang ini?')">
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
                                        Belum ada data cabang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($branches->hasPages())
                    <div class="p-3 border-t border-slate-100">
                        {{ $branches->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
