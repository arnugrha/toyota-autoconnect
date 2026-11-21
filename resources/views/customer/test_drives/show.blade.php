@extends('layouts.public')

@section('title', 'Detail Jadwal Test Drive ' . $booking->code . ' — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10" x-data="{ cancelModalOpen: false }">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-xs text-emerald-800">
                <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-2.5 rounded-xl bg-red-50 border border-red-200 p-4 text-xs text-red-800">
                <svg class="h-4 w-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Breadcrumb & Top Bar -->
        <div class="flex items-center justify-between">
            <a href="{{ route('customer.test-drives.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Jadwal
            </a>

            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $booking->status_badge_class }}">
                {{ $booking->status_label }}
            </span>
        </div>

        <!-- Booking Hero Card -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-6 sm:p-7">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 border-b border-slate-100 gap-3">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">KODE REGISTRASI JADWAL TEST DRIVE</span>
                    <h2 class="text-xl font-extrabold text-slate-900 font-mono mt-0.5">{{ $booking->code }}</h2>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-[10px] text-slate-400 block">Diajukan pada:</span>
                    <span class="text-xs font-medium text-slate-600">{{ $booking->created_at->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>

            <!-- Schedule & Location Highlights -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Jadwal Uji Kemudi</span>
                    <p class="text-base font-extrabold text-slate-900 mt-1">{{ $booking->scheduled_at->format('l, d M Y') }}</p>
                    <p class="text-xs font-semibold text-red-600 mt-0.5">Pukul {{ $booking->scheduled_at->format('H:i') }} WIB (Durasi ~{{ $booking->duration_minutes }} menit)</p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Cabang Showroom</span>
                    <p class="text-sm font-extrabold text-slate-900 mt-1">{{ $booking->branch->name }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $booking->branch->address }}, {{ $booking->branch->city }}</p>
                    <p class="text-[11px] text-slate-500 font-mono mt-0.5">Telp: {{ $booking->branch->phone }}</p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Status Konfirmasi</span>
                    <p class="text-sm font-bold text-slate-900 mt-1">{{ $booking->status_label }}</p>
                    @if ($booking->confirmed_at)
                        <p class="text-[10px] text-emerald-600 font-medium mt-0.5">Dikonfirmasi pada {{ $booking->confirmed_at->format('d M Y, H:i') }} WIB</p>
                    @elseif ($booking->status === 'pending')
                        <p class="text-[10px] text-amber-600 font-medium mt-0.5">Menunggu konfirmasi ketersediaan unit staf showroom</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Vehicle Details & Notes Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Vehicle Box -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-5 space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                    Unit Kendaraan Uji Kemudi
                </h3>

                <div class="flex items-center gap-4">
                    <img src="{{ $booking->vehicle->primary_image_url }}" 
                         alt="{{ $booking->vehicle->name }}"
                         class="h-20 w-32 object-cover rounded-xl border border-slate-200 bg-slate-950 flex-shrink-0">
                    <div>
                        <span class="inline-block rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-600">
                            {{ $booking->vehicle->category }}
                        </span>
                        <h4 class="text-sm font-extrabold text-slate-900 mt-1">{{ $booking->vehicle->name }}</h4>
                        <p class="text-xs text-slate-500">{{ $booking->vehicle->variant ?? 'Varian Standar' }}</p>
                        <p class="text-xs font-bold text-red-600 mt-1">
                            {{ $booking->vehicle->formatted_price }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-100">
                    <div>
                        <span class="text-slate-400 text-[10px] block">Transmisi</span>
                        <span class="font-semibold text-slate-700">{{ $booking->vehicle->transmission ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] block">Bahan Bakar</span>
                        <span class="font-semibold text-slate-700">{{ $booking->vehicle->fuel_type ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes & Guidelines Box -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-5 space-y-3.5">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                    Informasi &amp; Catatan
                </h3>

                @if ($booking->customer_note)
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Catatan Anda:</span>
                        <p class="text-xs text-slate-700 mt-0.5 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                            "{{ $booking->customer_note }}"
                        </p>
                    </div>
                @endif

                @if ($booking->staff_note)
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Catatan dari Staf Showroom:</span>
                        <p class="text-xs text-slate-800 font-medium mt-0.5 bg-blue-50/70 p-2.5 rounded-lg border border-blue-100 text-blue-900">
                            {{ $booking->staff_note }}
                        </p>
                    </div>
                @endif

                @if ($booking->rejection_reason)
                    <div>
                        <span class="text-[10px] font-bold text-rose-500 uppercase tracking-wider block">Alasan Penolakan:</span>
                        <p class="text-xs text-rose-800 bg-rose-50 p-2.5 rounded-lg border border-rose-100">
                            {{ $booking->rejection_reason }}
                        </p>
                    </div>
                @endif

                @if ($booking->cancellation_reason)
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Alasan Pembatalan:</span>
                        <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                            {{ $booking->cancellation_reason }}
                        </p>
                    </div>
                @endif

                <div class="rounded-xl bg-amber-50/70 p-3 border border-amber-200/60 text-[10px] text-amber-900 space-y-1">
                    <span class="font-bold block">Penting untuk Diketahui Saat Hari H:</span>
                    <ul class="list-disc list-inside space-y-0.5 text-slate-600">
                        <li>Wajib membawa SIM A asli yang masih berlaku.</li>
                        <li>Harap tiba di showroom 10-15 menit sebelum jam sesi dimulai.</li>
                        <li>Satu sesi uji kemudi didampingi oleh Sales Consultant profesional kami.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Cancellation Button Option for Customer -->
        @if ($booking->can_be_cancelled_by_customer)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between rounded-2xl bg-white border border-slate-200 p-5 shadow-2xs gap-3">
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Perlu mengubah rencana atau membatalkan sesi?</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Anda dapat membatalkan jadwal ini secara mandiri sebelum waktu sesi test drive tiba.</p>
                </div>
                <button type="button" @click="cancelModalOpen = true"
                        class="rounded-lg bg-white border border-rose-300 px-3.5 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 shadow-2xs transition flex-shrink-0">
                    Batalkan Jadwal
                </button>
            </div>

            <!-- Cancellation Modal -->
            <div x-show="cancelModalOpen" 
                 x-cloak 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div @click.outside="cancelModalOpen = false" 
                     class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-xl border border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900">Konfirmasi Pembatalan Test Drive</h3>
                    <p class="text-xs text-slate-500 mt-1">Apakah Anda yakin ingin membatalkan jadwal test drive <strong class="text-slate-800">{{ $booking->code }}</strong>?</p>

                    <form action="{{ route('customer.test-drives.cancel', $booking) }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Alasan Pembatalan (Opsional)</label>
                            <textarea name="cancellation_reason" rows="2" placeholder="Contoh: Ada keperluan mendadak, ingin reschedule..."
                                      class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="cancelModalOpen = false"
                                    class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-200">
                                Kembali
                            </button>
                            <button type="submit"
                                    class="rounded-lg bg-rose-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-rose-700">
                                Ya, Batalkan Jadwal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
