@extends('layouts.dashboard')

@section('title', 'Detail Test Drive ' . $booking->code . ' — Toyota AutoConnect')
@section('header_title', 'Kelola Jadwal Test Drive')
@section('header_subtitle', 'Verifikasi, konfirmasi jadwal, dan kelola penugasan staf showroom')

@section('content')
<div class="space-y-6 max-w-6xl">


    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800 space-y-1">
            <span class="font-bold block">Terdapat kendala pada pembaruan:</span>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.test-drives.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Test Drive
        </a>

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400 font-mono">ID #{{ $booking->id }}</span>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $booking->status_badge_class }}">
                {{ $booking->status_label }}
            </span>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Details -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Summary Card -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">KODE REGISTRASI JADWAL</span>
                        <h2 class="text-lg font-extrabold text-slate-900 font-mono mt-0.5">{{ $booking->code }}</h2>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 block font-bold uppercase tracking-wider">Diajukan Pada</span>
                        <span class="text-xs font-mono font-medium text-slate-600">{{ $booking->created_at->format('d/m/Y H:i') }} WIB</span>
                    </div>
                </div>

                <!-- Schedule Highlight -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Jadwal Sesi</span>
                        <p class="text-sm font-extrabold text-slate-900 mt-1 font-mono">
                            {{ $booking->scheduled_at->format('d M Y, H:i') }} WIB
                        </p>
                        <p class="text-[11px] text-red-600 font-medium mt-0.5">Durasi alokasi: {{ $booking->duration_minutes }} menit</p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Cabang Showroom</span>
                        <p class="text-sm font-extrabold text-slate-900 mt-1">{{ $booking->branch->name }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $booking->branch->city }} &bull; {{ $booking->branch->phone }}</p>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="rounded-xl border border-slate-200 p-4 space-y-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Informasi Pelanggan</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-slate-400 text-[10px] block">Nama Pelanggan</span>
                            <span class="font-bold text-slate-900">{{ $booking->customer_name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block">Nomor Telepon</span>
                            <span class="font-mono font-semibold text-slate-800">{{ $booking->customer_phone }}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-slate-400 text-[10px] block">Email Akun</span>
                            <span class="font-medium text-slate-700">{{ $booking->customer_email }}</span>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Details -->
                <div class="rounded-xl border border-slate-200 p-4 flex items-center gap-4">
                    <img src="{{ $booking->vehicle->primary_image_url }}"
                         alt="{{ $booking->vehicle->name }}"
                         class="h-16 w-24 object-cover rounded-lg border border-slate-200 bg-slate-950 flex-shrink-0">
                    <div>
                        <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-600">
                            {{ $booking->vehicle->category }}
                        </span>
                        <h4 class="text-sm font-extrabold text-slate-900 mt-1">{{ $booking->vehicle->name }}</h4>
                        <p class="text-xs text-slate-500">{{ $booking->vehicle->variant ?? 'Varian Standar' }}</p>
                        <p class="text-xs font-bold text-red-600 mt-0.5">
                            Rp {{ number_format($booking->vehicle->price, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                @if ($booking->customer_note)
                    <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Catatan Pelanggan:</span>
                        <p class="text-xs text-slate-700 mt-1 italic">"{{ $booking->customer_note }}"</p>
                    </div>
                @endif
            </div>

            <!-- CRM Lead History / Audit Trail -->
            @if ($booking->lead && $booking->lead->activities->count() > 0)
                <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
                            Riwayat Aktivitas CRM Terkait (Lead {{ $booking->lead->lead_number }})
                        </h3>
                        <a href="{{ route('admin.leads.show', $booking->lead) }}" class="text-[11px] font-semibold text-red-600 hover:underline">
                            Lihat di CRM &rarr;
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        @foreach ($booking->lead->activities->take(5) as $act)
                            <div class="rounded-lg bg-slate-50 p-3 border border-slate-100 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800 text-[11px]">{{ ucfirst(str_replace('_', ' ', $act->activity_type)) }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $act->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                <p class="text-slate-600 text-[11px] mt-1">{{ $act->description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Column: Staff Actions & Status Update Forms -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Current Staff Note & Audit Box -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                    Status &amp; Penugasan Staf
                </h3>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Sales Consultant Ditugaskan:</span>
                        <span class="font-bold text-slate-800">{{ $booking->assignedSales->name ?? 'Belum Ditugaskan' }}</span>
                    </div>

                    @if ($booking->confirmed_at)
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Waktu Konfirmasi:</span>
                            <span class="font-mono text-slate-800">{{ $booking->confirmed_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Dikonfirmasi Oleh:</span>
                            <span class="font-semibold text-slate-800">{{ $booking->confirmedByUser->name ?? 'Staf Showroom' }}</span>
                        </div>
                    @endif

                    @if ($booking->completed_at)
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Selesai Pada:</span>
                            <span class="font-mono text-slate-800">{{ $booking->completed_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif

                    @if ($booking->cancelled_at)
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Dibatalkan Pada:</span>
                            <span class="font-mono text-slate-800">{{ $booking->cancelled_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>

                @if ($booking->staff_note)
                    <div class="rounded-xl bg-blue-50/70 p-3 border border-blue-100 text-xs">
                        <span class="text-[10px] font-bold text-blue-900 uppercase tracking-wider block">Catatan Staf Showroom:</span>
                        <p class="text-blue-950 mt-0.5">{{ $booking->staff_note }}</p>
                    </div>
                @endif
            </div>

            <!-- Action Controls Form -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs p-5 space-y-4" x-data="{ actionType: '{{ $booking->status === 'pending' ? 'confirmed' : 'completed' }}' }">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                    Tindakan Manajemen Jadwal
                </h3>

                <form action="{{ route('admin.test-drives.status.update', $booking) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Select Action Target -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Pilih Tindakan Perubahan Status</label>
                        <select name="status" x-model="actionType" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none font-semibold">
                            <option value="confirmed">Konfirmasi Jadwal Sesi (Confirmed)</option>
                            <option value="completed">Tandai Test Drive Selesai (Completed)</option>
                            <option value="no_show">Tandai Pelanggan Tidak Hadir (No-Show)</option>
                            <option value="rejected">Tolak Pengajuan Jadwal (Rejected)</option>
                            <option value="cancelled">Batalkan Jadwal (Cancelled)</option>
                        </select>
                    </div>

                    <!-- Assign Sales Consultant -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tugaskan Sales Consultant Pendamping</label>
                        <select name="assigned_to"
                                class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <option value="">-- Biarkan / Belum Ditugaskan --</option>
                            @foreach ($salesConsultants as $sc)
                                <option value="{{ $sc->id }}" {{ $booking->assigned_to == $sc->id ? 'selected' : '' }}>
                                    {{ $sc->name }} ({{ $sc->role_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Mandatory Reason Field for Rejection / Cancellation -->
                    <div x-show="actionType === 'rejected' || actionType === 'cancelled'" x-cloak>
                        <label class="block text-[11px] font-bold text-rose-700 mb-1">
                            Alasan Penolakan / Pembatalan <span class="text-rose-600">*</span>
                        </label>
                        <textarea name="reason" rows="2" placeholder="Tuliskan alasan penolakan/pembatalan jadwal..."
                                  class="w-full rounded-lg border border-rose-300 bg-rose-50/30 px-3 py-1.5 text-xs text-slate-900 focus:border-rose-600 focus:ring-1 focus:ring-rose-600 focus:outline-none"></textarea>
                    </div>

                    <!-- Staff Note Field -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Staf / Internal Notes</label>
                        <textarea name="staff_note" rows="2" placeholder="Catatan konfirmasi, rute uji kemudi, atau pesan ke pelanggan..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ $booking->staff_note }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-red-600 py-2.5 px-4 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition flex items-center justify-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Simpan Perubahan Status
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection

