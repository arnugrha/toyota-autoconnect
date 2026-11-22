@extends('layouts.dashboard')

@section('title', 'Manajemen Pengguna — Toyota AutoConnect')
@section('header_title', 'Manajemen Akun Pengguna & Staf')
@section('header_subtitle', 'Kelola hak akses pengguna internal, staf dealer, dan pelanggan terdaftar')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Search Bar -->
    <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <!-- Search Keyword -->
            <div class="sm:col-span-5 relative">
                <input type="text" name="q" value="{{ $filters['q'] }}"
                       placeholder="Cari nama, email, atau nomor telepon..."
                       class="w-full rounded-lg border border-slate-300 pl-9 pr-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                <svg class="h-4 w-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>

            <!-- Role Filter -->
            <div class="sm:col-span-3">
                <select name="role"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                    <option value="">Semua Role</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->name }}" {{ $filters['role'] === $r->name ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-2">
                <select name="status"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                    <option value="">Semua Status</option>
                    <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $filters['status'] === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit"
                        class="flex-1 rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-slate-800 transition text-center">
                    Filter
                </button>
                @if ($filters['q'] || $filters['role'] || $filters['status'])
                    <a href="{{ route('admin.users.index') }}"
                       class="rounded-lg bg-slate-100 px-2.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition"
                       title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Header & Create Button -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xs sm:text-sm font-bold text-slate-900">
                Daftar Pengguna ({{ $users->total() }})
            </h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Seluruh akun yang terdaftar dalam sistem Toyota AutoConnect</p>
        </div>

        @can('users.create')
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Akun Staf
            </a>
        @endcan
    </div>

    <!-- Users Table Card -->
    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[950px]">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[220px]">Nama Lengkap &amp; Email</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[140px]">Nomor Kontak</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[160px]">Role / Hak Akses</th>
                        <th class="py-3 px-4 whitespace-nowrap text-center min-w-[110px]">Status</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[120px]">Terdaftar</th>
                        <th class="py-3 px-4 whitespace-nowrap text-right min-w-[90px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $u)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-[10px] font-bold text-white flex-shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="font-bold text-slate-900 truncate flex items-center gap-1.5">
                                            {{ $u->name }}
                                            @if ($u->id === auth()->id())
                                                <span class="rounded bg-slate-100 px-1 py-0.2 text-[9px] font-bold text-slate-600">Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 truncate">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-slate-600 font-mono text-[11px]">
                                {{ $u->phone ?? '—' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @foreach ($u->roles as $role)
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold {{ $role->name === 'Owner' ? 'bg-amber-50 text-amber-800 border border-amber-300 font-extrabold' : ($role->name === 'Super Admin' ? 'bg-red-50 text-red-700 border border-red-200' : ($role->name === 'Customer' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700 border border-slate-200')) }}">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-center">
                                @if ($u->is_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-slate-500 text-[11px]">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-right">
                                @can('users.update')
                                    <a href="{{ route('admin.users.edit', $u) }}"
                                       class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-200 transition">
                                        <svg class="h-3 w-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                        Edit
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs text-slate-400">
                                Tidak ada akun yang sesuai dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
