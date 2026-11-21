@extends('layouts.dashboard')

@section('title', "Tiket {$ticket->ticket_number} — Toyota AutoConnect")

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.tickets.index') }}" class="hover:text-red-600 transition">Helpdesk CS</a>
                <span>/</span>
                <span class="text-slate-900 font-bold font-mono">{{ $ticket->ticket_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight font-mono">{{ $ticket->ticket_number }}</h1>
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold border {{ $ticket->status_badge_class }}">
                    {{ $ticket->status_label }}
                </span>
                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold border {{ $ticket->priority_badge_class }}">
                    Prioritas {{ $ticket->priority_label }}
                </span>
            </div>
        </div>

        <a href="{{ route('admin.tickets.index') }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
            &larr; Kembali ke Daftar Tiket
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-2xs flex items-center gap-2">
            <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Messages Thread & Reply Form -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Subject Banner -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span>Kategori: <strong class="text-slate-800">{{ $ticket->category_label }}</strong></span>
                    <span>Dibuat: {{ $ticket->created_at->format('d/m/Y H:i') }} WIB</span>
                </div>
                <h2 class="text-base font-extrabold text-slate-900">{{ $ticket->subject }}</h2>
            </div>

            <!-- Messages Stream -->
            <div class="space-y-4">
                @foreach($ticket->messages as $msg)
                    @php
                        $isCustomer = ($msg->user_id === $ticket->user_id);
                        $isInternal = $msg->is_internal;
                    @endphp
                    <div class="rounded-2xl p-5 shadow-2xs border {{ $isInternal ? 'bg-amber-50/80 border-amber-300 ring-2 ring-amber-400/20' : ($isCustomer ? 'bg-white border-slate-200' : 'bg-slate-900 text-white border-slate-900') }}">
                        <div class="flex items-center justify-between gap-4 pb-2 border-b {{ $isInternal ? 'border-amber-200' : ($isCustomer ? 'border-slate-100' : 'border-slate-800') }} mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold {{ $isInternal ? 'text-amber-950' : ($isCustomer ? 'text-slate-900' : 'text-white') }}">
                                    {{ $msg->user?->name ?? 'Pengguna' }}
                                </span>
                                @if($isInternal)
                                    <span class="text-[9px] px-2 py-0.5 rounded font-black uppercase tracking-wider bg-amber-200 text-amber-900 border border-amber-300">
                                        Catatan Internal (Hanya Staf)
                                    </span>
                                @elseif($isCustomer)
                                    <span class="text-[9px] px-2 py-0.5 rounded font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Pelanggan
                                    </span>
                                @else
                                    <span class="text-[9px] px-2 py-0.5 rounded font-bold bg-red-600 text-white">
                                        Customer Care (Staf)
                                    </span>
                                @endif
                            </div>
                            <span class="text-[10px] {{ $isInternal ? 'text-amber-700' : ($isCustomer ? 'text-slate-400' : 'text-slate-400') }}">
                                {{ $msg->created_at->format('d/m/Y H:i') }} WIB
                            </span>
                        </div>

                        <p class="text-xs leading-relaxed whitespace-pre-line {{ $isInternal ? 'text-amber-950' : ($isCustomer ? 'text-slate-800' : 'text-slate-200') }}">
                            {{ $msg->message }}
                        </p>

                        @if($msg->attachment_path)
                            <div class="mt-3 pt-3 border-t {{ $isInternal ? 'border-amber-200' : ($isCustomer ? 'border-slate-100' : 'border-slate-800') }}">
                                <a href="{{ asset('storage/' . $msg->attachment_path) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1.5 text-xs font-bold underline {{ $isInternal ? 'text-amber-900' : ($isCustomer ? 'text-red-600' : 'text-red-400') }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                    </svg>
                                    Lihat Lampiran Dokumen
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Reply Box -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Kirim Balasan / Catatan Internal</h3>

                <form action="{{ route('admin.tickets.reply', $ticket) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <textarea name="message"
                                  rows="4"
                                  required
                                  placeholder="Ketik balasan untuk pelanggan atau catatan koordinasi tim di sini..."
                                  class="w-full rounded-xl border border-slate-300 p-3.5 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                    </div>

                    <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-amber-950">
                            <input type="checkbox" name="is_internal" value="1" class="rounded text-amber-600 focus:ring-amber-500 h-4 w-4">
                            <span>Tandai sebagai Catatan Internal (Internal Note — Pelanggan TIDAK dapat melihat pesan ini)</span>
                        </label>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                        <div>
                            <input type="file"
                                   name="attachment"
                                   accept=".jpg,.jpeg,.png,.pdf,.docx"
                                   class="text-xs text-slate-600 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                        </div>

                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                            <span>Kirim Pesan</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right Sidebar: Management Controls -->
        <div class="space-y-6">

            <!-- Control 1: Status Updater -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                    Status Penanganan Tiket
                </h3>

                <form action="{{ route('admin.tickets.status.update', $ticket) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open (Baru)</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress (Ditangani)</option>
                            <option value="waiting_customer" {{ $ticket->status === 'waiting_customer' ? 'selected' : '' }}>Waiting Customer (Tunggu Balasan)</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved (Selesai)</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed (Ditutup)</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-xs font-bold text-white hover:bg-slate-800 transition">
                        Perbarui Status
                    </button>
                </form>
            </div>

            <!-- Control 2: Assign Agent -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                    Penugasan Petugas CS
                </h3>

                <form action="{{ route('admin.tickets.assign', $ticket) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <select name="assigned_to" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <option value="">-- Belum Ditugaskan --</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }} ({{ $agent->role_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-xs font-bold text-white hover:bg-slate-800 transition">
                        Tugaskan Petugas
                    </button>
                </form>
            </div>

            <!-- Info 3: Pelanggan & Cabang -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-3 text-xs">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                    Data Pelanggan
                </h3>

                <div>
                    <span class="text-[11px] text-slate-400 block font-medium">Nama:</span>
                    <span class="font-bold text-slate-900 text-sm block mt-0.5">{{ $ticket->user?->name }}</span>
                </div>

                <div>
                    <span class="text-[11px] text-slate-400 block font-medium">Email:</span>
                    <span class="text-slate-700 block">{{ $ticket->user?->email }}</span>
                </div>

                <div>
                    <span class="text-[11px] text-slate-400 block font-medium">Telepon / WhatsApp:</span>
                    <span class="text-slate-700 font-semibold block">{{ $ticket->user?->phone ?? '-' }}</span>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <span class="text-[11px] text-slate-400 block font-medium">Cabang Terkait:</span>
                    <span class="text-slate-800 font-bold block mt-0.5">{{ $ticket->branch?->name ?? 'Layanan Pusat' }}</span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

