@extends('layouts.dashboard')

@section('title', 'Monitoring Notifikasi & Audit Log — Toyota AutoConnect')
@section('header_title', 'Monitoring & Audit Log Notifikasi')
@section('header_subtitle', 'Pantau pengiriman notifikasi multi-channel (In-App, Email, WhatsApp) dan metrik keberhasilan integrasi')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- Total -->
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Notifikasi</p>
            <p class="mt-1 text-2xl font-black text-slate-900 tracking-tight">{{ number_format($metrics['total']) }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Semua kanal sistem</p>
        </div>

        <!-- Sent / Delivered -->
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-4 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Berhasil Terkirim</p>
            <p class="mt-1 text-2xl font-black text-emerald-900 tracking-tight">{{ number_format($metrics['sent']) }}</p>
            <p class="text-[10px] text-emerald-600 font-semibold mt-0.5">
                {{ $metrics['total'] > 0 ? round(($metrics['sent'] / $metrics['total']) * 100, 1) : 0 }}% sukses
            </p>
        </div>

        <!-- Failed -->
        <div class="rounded-2xl border border-red-200 bg-red-50/40 p-4 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-red-700">Gagal Dikirim</p>
            <p class="mt-1 text-2xl font-black text-red-900 tracking-tight">{{ number_format($metrics['failed']) }}</p>
            <p class="text-[10px] text-red-600 font-semibold mt-0.5">Perlu perhatian / retry</p>
        </div>

        <!-- WhatsApp -->
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">WhatsApp Resmi</p>
            <p class="mt-1 text-2xl font-black text-slate-900 tracking-tight">{{ number_format($metrics['whatsapp_count']) }}</p>
            <p class="text-[10px] text-emerald-600 font-bold mt-0.5">Meta Cloud API</p>
        </div>

        <!-- Email -->
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Email SMTP</p>
            <p class="mt-1 text-2xl font-black text-slate-900 tracking-tight">{{ number_format($metrics['email_count']) }}</p>
            <p class="text-[10px] text-blue-600 font-bold mt-0.5">Template HTML</p>
        </div>

        <!-- In-App -->
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">In-App Notifikasi</p>
            <p class="mt-1 text-2xl font-black text-slate-900 tracking-tight">{{ number_format($metrics['in_app_count']) }}</p>
            <p class="text-[10px] text-purple-600 font-bold mt-0.5">Database Real-time</p>
        </div>
    </div>


    <!-- Filters & Search Form -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.notifications.monitoring') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Cari Log Notifikasi</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Penerima, judul notifikasi, provider message ID..."
                           class="w-full rounded-xl border border-slate-300 pl-9 pr-3 py-2 text-xs focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
            </div>

            <!-- Filter Channel -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Saluran (Channel)</label>
                <select name="channel" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Saluran</option>
                    <option value="whatsapp" {{ request('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="mail" {{ request('channel') === 'mail' ? 'selected' : '' }}>Email SMTP</option>
                    <option value="in_app" {{ request('channel') === 'in_app' ? 'selected' : '' }}>In-App Database</option>
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Status Pengiriman</label>
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent (Terkirim)</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered (Diterima)</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed (Gagal)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-slate-900 py-2 px-3 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Filter
                </button>
                <a href="{{ route('admin.notifications.monitoring') }}" class="rounded-xl border border-slate-300 py-2 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table Card with Horizontal Scroll -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Log Riwayat Transmisi Notifikasi</h2>
            <span class="text-[11px] text-slate-400 font-medium">Menampilkan {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} log</span>
        </div>

        <!-- Horizontal scroll wrapper for wide tables -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[1150px]">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-600 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[130px]">Waktu &amp; ID</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[170px]">Penerima &amp; Akun</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[120px]">Saluran</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[130px]">Kategori</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[220px]">Judul &amp; Konten</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[110px]">Status</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[180px]">Provider Ref / Error</th>
                        <th class="py-3 px-4 whitespace-nowrap text-right min-w-[80px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Waktu & ID -->
                            <td class="py-3.5 px-4 font-mono text-[11px] whitespace-nowrap">
                                <span class="font-bold text-slate-900">#{{ $log->id }}</span>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </div>
                            </td>

                            <!-- Penerima & Akun -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $log->recipient }}</div>
                                @if($log->user)
                                    <div class="text-[10px] text-slate-400">User: {{ $log->user->name }}</div>
                                @endif
                                @if($log->branch)
                                    <div class="text-[10px] text-slate-400">Cabang: {{ $log->branch->name }}</div>
                                @endif
                            </td>

                            <!-- Saluran (Channel) -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($log->channel === 'whatsapp')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="h-3 w-3 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.288.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.353.101.173.45 0.742.965 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.679.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/>
                                        </svg>
                                        WhatsApp
                                    </span>
                                @elseif($log->channel === 'mail')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                        <svg class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                        </svg>
                                        Email
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-purple-50 text-purple-700 border border-purple-200">
                                        <svg class="h-3 w-3 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                        </svg>
                                        In-App
                                    </span>
                                @endif
                            </td>

                            <!-- Kategori -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-bold uppercase tracking-wider text-[10px] text-slate-700 bg-slate-100 px-2 py-0.5 rounded">
                                    {{ $log->category }}
                                </span>
                            </td>

                            <!-- Judul & Konten -->
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-bold text-slate-900 truncate" title="{{ $log->title }}">{{ $log->title }}</div>
                                <div class="text-[11px] text-slate-500 truncate mt-0.5" title="{{ $log->content }}">{{ $log->content }}</div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if(in_array($log->status, ['sent', 'delivered']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        {{ ucfirst($log->status) }}
                                    </span>
                                @elseif($log->status === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                        Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-700">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Provider Ref / Error -->
                            <td class="py-3.5 px-4 max-w-[200px]">
                                @if($log->provider_message_id)
                                    <div class="font-mono text-[10px] text-slate-600 truncate" title="{{ $log->provider_message_id }}">
                                        ID: {{ $log->provider_message_id }}
                                    </div>
                                @endif
                                @if($log->failure_reason)
                                    <div class="text-[10px] text-red-600 font-medium truncate mt-0.5" title="{{ $log->failure_reason }}">
                                        Err: {{ $log->failure_reason }}
                                    </div>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                @can('notifications.manage')
                                    <form action="{{ route('admin.notifications.resend', $log->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-red-600 transition"
                                                title="Kirim Ulang / Retry">
                                            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                            <span>Retry</span>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <svg class="h-10 w-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                                <p class="font-bold text-slate-700 text-xs">Belum Ada Catatan Log Notifikasi</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Seluruh transmisi pesan akan terekam secara otomatis di tabel audit log ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

