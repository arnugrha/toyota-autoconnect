@extends('layouts.dashboard')

@section('title', 'Detail Prospek ' . $lead->lead_number . ' — Toyota AutoConnect CRM')
@section('header_title', 'Kelola Prospek: ' . $lead->lead_number)
@section('header_subtitle', 'Detail interaksi, riwayat follow-up, dan progres penawaran pelanggan')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.crm.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Prospek
        </a>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $lead->status_badge_class }}">
                Tahap: {{ $lead->status_label }}
            </span>
        </div>
    </div>

    <!-- 2-Column Operational Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Lead & Customer Context (7 Cols) -->
        <div class="lg:col-span-7 space-y-5">
            
            <!-- Customer & Vehicle Identity Card -->
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-red-600 font-mono">{{ $lead->lead_number }}</span>
                        <h3 class="text-sm font-bold text-slate-900 mt-0.5">Informasi Calon Pembeli</h3>
                    </div>
                    <span class="text-[10px] text-slate-400">
                        Sumber: <strong class="text-slate-700 uppercase">{{ str_replace('_', ' ', $lead->source) }}</strong>
                    </span>
                </div>

                <!-- Customer Details -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs bg-slate-50 p-3.5 rounded-lg border border-slate-200/70">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Nama Customer:</span>
                        <span class="font-bold text-slate-900 mt-0.5 block">{{ $lead->customer->name }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Email:</span>
                        <span class="font-semibold text-slate-800 mt-0.5 block truncate">{{ $lead->customer->email }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Telepon / WhatsApp:</span>
                        <span class="font-semibold text-slate-800 mt-0.5 block font-mono">{{ $lead->customer->phone ?? '—' }}</span>
                    </div>
                </div>

                <!-- Vehicle Info (If linked) -->
                @if ($lead->vehicle)
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-500 mb-2">Model Kendaraan yang Diminati:</span>
                        <div class="flex items-center gap-3.5 rounded-xl border border-slate-200 p-3 bg-white">
                            <img src="{{ $lead->vehicle->primary_image_url }}" alt="{{ $lead->vehicle->name }}" class="h-12 w-18 object-cover rounded-lg bg-slate-200 flex-shrink-0">
                            <div class="flex-1 overflow-hidden">
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-bold uppercase text-red-600 bg-red-50 px-1.5 py-0.2 rounded">{{ $lead->vehicle->category }}</span>
                                    <h4 class="font-bold text-slate-900 text-xs truncate">{{ $lead->vehicle->name }}</h4>
                                </div>
                                <p class="text-xs font-semibold text-slate-700 mt-0.5">{{ $lead->vehicle->formatted_price }}</p>
                            </div>
                            <a href="{{ route('vehicles.show', $lead->vehicle->slug) }}" target="_blank"
                               class="text-[11px] font-semibold text-red-600 hover:underline flex-shrink-0">
                                Pratinjau Unit &rarr;
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Original Inquiry Message (If lead created via inquiry) -->
                @if ($lead->inquiry)
                    <div class="border-t border-slate-100 pt-3">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-bold uppercase text-slate-500">Pesan Inquiry Sumber (#INQ-{{ str_pad($lead->inquiry->id, 5, '0', STR_PAD_LEFT) }}):</span>
                            <span class="text-[10px] text-slate-400">{{ $lead->inquiry->submitted_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3 text-xs text-slate-800 border border-slate-200/60 leading-relaxed whitespace-pre-line">
                            <p class="font-bold text-slate-900 mb-1">Topik: {{ $lead->inquiry->subject }}</p>
                            {{ $lead->inquiry->message }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Activity Log Timeline (Append-Only) -->
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <div class="border-b border-slate-100 pb-3 mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-900">Riwayat Aktivitas &amp; Follow-up</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Catatan audit log interaksi prospek (bersifat append-only)</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-700">
                        {{ $lead->activities->count() }} Aktivitas
                    </span>
                </div>

                <div class="relative pl-6 space-y-5 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse ($lead->activities as $act)
                        <div class="relative">
                            <!-- Bullet Icon -->
                            <div class="absolute -left-6 top-1 h-2.5 w-2.5 rounded-full border-2 border-white {{ $act->activity_type === 'status_changed' ? 'bg-red-600' : ($act->activity_type === 'assigned' ? 'bg-purple-600' : 'bg-blue-600') }}"></div>

                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/80 text-xs">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-slate-800">{{ $act->activity_type_label }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $act->created_at->format('d M Y, H:i') }} WIB</span>
                                </div>
                                <p class="text-slate-700 leading-relaxed">{{ $act->description }}</p>
                                <div class="mt-2 pt-1 border-t border-slate-200/50 flex items-center justify-between text-[10px] text-slate-400">
                                    <span>Dilakukan oleh: <strong class="text-slate-600">{{ $act->user?->name ?? 'Sistem' }}</strong></span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada aktivitas tercatat.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Column: Operational Controls & Forms (5 Cols) -->
        <div class="lg:col-span-5 space-y-5">
            
            <!-- Operational Status & Assignment Box -->
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Status &amp; Penugasan Sales</h3>

                <!-- Assigned Sales Executive -->
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/70 text-xs">
                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Sales Penanggung Jawab:</span>
                    @if ($lead->assignedSales)
                        <div class="font-bold text-slate-900 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            {{ $lead->assignedSales->name }}
                        </div>
                        <span class="text-[10px] text-slate-500 font-mono block mt-0.5">{{ $lead->assignedSales->email }}</span>
                    @else
                        <div class="font-bold text-amber-700 flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z" />
                            </svg>
                            Belum Ditugaskan ke Sales
                        </div>
                    @endif
                </div>

                <!-- Reassign Form (Only if User has leads.assign permission) -->
                @if ($canAssign)
                    <form method="POST" action="{{ route('admin.leads.assign', $lead) }}" class="space-y-2 pt-1 border-t border-slate-100">
                        @csrf
                        @method('PUT')
                        <label class="block text-[11px] font-bold text-slate-700">Tugaskan / Ganti Sales Executive:</label>
                        <div class="flex gap-2">
                            <select name="assigned_to" required
                                    class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                <option value="">Pilih Sales Executive</option>
                                @foreach ($eligibleSales as $sales)
                                    <option value="{{ $sales->id }}" {{ $lead->assigned_to === $sales->id ? 'selected' : '' }}>
                                        {{ $sales->name }} ({{ $sales->role_name }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition">
                                Tugaskan
                            </button>
                        </div>
                    </form>
                @endif

                <!-- Next Follow-up Alert -->
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/70 text-xs">
                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Jadwal Kontak Berikutnya:</span>
                    @if ($lead->next_follow_up_at)
                        <div class="font-bold {{ $lead->is_overdue ? 'text-red-600' : 'text-slate-900' }}">
                            {{ $lead->next_follow_up_at->format('d F Y, H:i') }} WIB
                        </div>
                        @if ($lead->is_overdue)
                            <span class="inline-block text-[10px] font-bold uppercase text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-200 mt-1">
                                Perhatian: Tindak Lanjut Jatuh Tempo!
                            </span>
                        @endif
                    @else
                        <span class="text-slate-400 italic">Belum ada jadwal follow-up yang ditetapkan.</span>
                    @endif
                </div>

                <!-- Update Status Form (If User has leads.change-status permission) -->
                @if ($canChangeStatus)
                    <form method="POST" action="{{ route('admin.leads.status.update', $lead) }}" class="space-y-3 pt-3 border-t border-slate-100">
                        @csrf
                        @method('PUT')
                        <label class="block text-[11px] font-bold text-slate-700">Perbarui Tahap Pipeline:</label>
                        <select name="status" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <option value="new" {{ $lead->status === 'new' ? 'selected' : '' }}>1. Prospek Baru (New Lead)</option>
                            <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>2. Sudah Dihubungi (Contacted)</option>
                            <option value="interested" {{ $lead->status === 'interested' ? 'selected' : '' }}>3. Berminat / Hot Lead (Interested)</option>
                            <option value="test_drive" {{ $lead->status === 'test_drive' ? 'selected' : '' }}>4. Jadwal Test Drive</option>
                            <option value="negotiation" {{ $lead->status === 'negotiation' ? 'selected' : '' }}>5. Negosiasi Penawaran</option>
                            <option value="booking" {{ $lead->status === 'booking' ? 'selected' : '' }}>6. SPK / Booking Fee</option>
                            <option value="deal" {{ $lead->status === 'deal' ? 'selected' : '' }}>7. Deal (Penjualan)</option>
                            <option value="lost" {{ $lead->status === 'lost' ? 'selected' : '' }}>8. Batal / Lost</option>
                        </select>

                        <div>
                            <label class="block text-[10px] text-slate-500 mb-1">Catatan Penjelasan Perubahan (Wajib bila dari Lost &rarr; Deal):</label>
                            <input type="text" name="note" placeholder="Contoh: Customer sepakat penawaran OTR..."
                                   class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>

                        <button type="submit"
                                class="w-full rounded-lg bg-red-600 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition flex items-center justify-center gap-1.5">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Simpan Perubahan Tahap
                        </button>
                    </form>
                @endif
            </div>

            <!-- Add Follow-up Activity Form (If User has leads.add-activity permission) -->
            @if ($canAddActivity)
                <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm space-y-4">
                    <div class="border-b border-slate-100 pb-2">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-900">Catat Aktivitas Follow-up</h3>
                        <p class="text-[10px] text-slate-500 mt-0.5">Catatan internal staf dealer, aman dan tidak terlihat oleh customer</p>
                    </div>

                    <form method="POST" action="{{ route('admin.leads.activities.store', $lead) }}" class="space-y-3 text-xs">
                        @csrf

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis Interaksi: <span class="text-red-600">*</span></label>
                            <select name="activity_type" required
                                    class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                <option value="follow_up_note">Catatan Internal / Follow-up Biasa</option>
                                <option value="contacted">Customer Berhasil Dihubungi (Telepon / WA)</option>
                                <option value="inquiry_responded">Jawaban Resmi Terhadap Inquiry Dikirimkan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Deskripsi Hasil Interaksi: <span class="text-red-600">*</span></label>
                            <textarea name="description" rows="3" required
                                      placeholder="Contoh: Sudah dihubungi via telepon. Customer berminat varian warna hitam dan meminta estimasi kredit 3 tahun..."
                                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none"></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Jadwal Kontak Berikutnya (Opsional):</label>
                            <input type="datetime-local" name="next_follow_up_at"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>

                        <button type="submit"
                                class="w-full rounded-lg bg-slate-900 py-2 text-xs font-bold text-white hover:bg-slate-800 transition flex items-center justify-center gap-1.5">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambahkan Catatan Aktivitas
                        </button>
                    </form>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
