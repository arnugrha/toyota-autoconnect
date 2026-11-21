@extends('layouts.dashboard')

@section('title', 'Audit Trail — Toyota AutoConnect')
@section('header_title', 'Catatan Audit Trail & Log Sistem')
@section('header_subtitle', 'Rekam jejak aktivitas pengguna, otentikasi, perubahan data master, transaksi, dan pengaturan sistem')

@section('content')
<div class="space-y-6">

    <!-- Filter Card -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <!-- Search Keyword -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Aktivitas</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Nama pengguna, deskripsi, IP..."
                           class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <!-- Module Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Modul</label>
                    <select name="module" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">Semua Modul</option>
                        @foreach($modules as $key => $name)
                            <option value="{{ $key }}" {{ $filters['module'] === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis Aktivitas</label>
                    <select name="action" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">Semua Aksi</option>
                        @foreach($actions as $key => $name)
                            <option value="{{ $key }}" {{ $filters['action'] === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Role Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Role Pengguna</label>
                    <select name="role" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">Semua Role</option>
                        @foreach($roles as $roleName)
                            <option value="{{ $roleName }}" {{ $filters['role'] === $roleName ? 'selected' : '' }}>{{ $roleName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- User Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pengguna</label>
                    <select name="user_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">Semua Pengguna</option>
                        @foreach($users as $id => $name)
                            <option value="{{ $id }}" {{ (string)$filters['user_id'] === (string)$id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date Range & Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-semibold whitespace-nowrap">Rentang Tanggal:</span>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] }}"
                           class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <span class="text-xs text-slate-400">s/d</span>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] }}"
                           class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div class="flex items-center gap-2 self-end">
                    <a href="{{ route('admin.audit-logs.index') }}"
                       class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Reset Filter
                    </a>
                    <button type="submit"
                            class="rounded-xl bg-slate-900 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table Card -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Daftar Log Aktivitas Sistem</h3>
            <span class="text-xs text-slate-400 font-medium">Total: {{ $auditLogs->total() }} Catatan Aktivitas</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs min-w-[1100px]">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4 whitespace-nowrap min-w-[120px]">Waktu (WIB)</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[170px]">Pengguna &amp; Role</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[140px]">Modul</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[130px]">Aksi</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[240px]">Ringkasan Aktivitas</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[170px]">IP &amp; Info Sesi</th>
                        <th class="py-3 px-4 whitespace-nowrap text-center min-w-[100px]">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($auditLogs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Timestamp -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $log->created_at->format('d/m/Y') }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>

                            <!-- User & Role -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $log->user_name ?? 'Sistem / Anonim' }}</div>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                                    {{ $log->user_role ?? 'System' }}
                                </span>
                            </td>

                            <!-- Module Badge -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $modules[$log->module] ?? $log->module }}
                                </span>
                            </td>

                            <!-- Action Badge -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @php
                                    $actionStyles = match($log->action) {
                                        'login' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'login_failed' => 'bg-red-50 text-red-700 border-red-200',
                                        'logout' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        'created' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'updated' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'deleted' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'status_changed' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'settings_updated' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase border {{ $actionStyles }}">
                                    {{ $actions[$log->action] ?? $log->action }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="py-3.5 px-4">
                                <p class="text-xs text-slate-800 max-w-sm line-clamp-2 leading-relaxed">
                                    {{ $log->description ?? '—' }}
                                </p>
                            </td>

                            <!-- IP Address & User Agent -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-mono text-[11px] text-slate-600 block">{{ $log->ip_address }}</span>
                                <span class="text-[10px] text-slate-400 block truncate max-w-xs" title="{{ $log->user_agent }}">
                                    {{ Str::limit($log->user_agent, 28) }}
                                </span>
                            </td>

                            <!-- Detail Action -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('admin.audit-logs.show', $log) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition">
                                    <span>Detail</span>
                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-xs text-slate-400">
                                Tidak ada catatan audit trail yang cocok dengan kriteria pencarian atau filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($auditLogs->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/40">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

