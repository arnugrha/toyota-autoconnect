@extends('layouts.public')

@section('title', 'Buat Tiket Bantuan Baru — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Breadcrumb & Header -->
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('customer.tickets.index') }}" class="hover:text-red-600 transition">Pusat Bantuan</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Buat Tiket Baru</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Formulir Tiket Layanan Pelanggan</h1>
            <p class="text-xs text-slate-500 mt-1">Sampaikan kebutuhan atau kendala Anda secara rinci agar tim Customer Care dapat membantu secara akurat.</p>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-2xs">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>Terdapat kesalahan pada input form:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-[11px] font-normal text-rose-700">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-2xs">
            <form action="{{ route('customer.tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategori Bantuan <span class="text-red-600">*</span></label>
                        <select name="category" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <option value="service_booking" {{ old('category', $preselectedCategory) === 'service_booking' ? 'selected' : '' }}>Booking Servis &amp; Perawatan</option>
                            <option value="service_complaint" {{ old('category', $preselectedCategory) === 'service_complaint' ? 'selected' : '' }}>Keluhan Layanan Bengkel</option>
                            <option value="vehicle_info" {{ old('category', $preselectedCategory) === 'vehicle_info' ? 'selected' : '' }}>Informasi Unit Mobil &amp; Spesifikasi</option>
                            <option value="payment" {{ old('category', $preselectedCategory) === 'payment' ? 'selected' : '' }}>Pembayaran &amp; Simulasi Kredit</option>
                            <option value="warranty" {{ old('category', $preselectedCategory) === 'warranty' ? 'selected' : '' }}>Klaim Garansi Resmi</option>
                            <option value="general_inquiry" {{ old('category', $preselectedCategory) === 'general_inquiry' ? 'selected' : '' }}>Pertanyaan Umum Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tingkat Prioritas <span class="text-red-600">*</span></label>
                        <select name="priority" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Rendah (Pertanyaan umum)</option>
                            <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Sedang (Standar)</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Tinggi (Butuh penanganan cepat)</option>
                            <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Mendesak / Urgent (Kendala darurat kendaraan)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Cabang Dealer / Bengkel Terkait (Opsional)</label>
                    <select name="branch_id" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">-- Layanan Pusat / Seluruh Cabang --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->city }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Subjek / Judul Tiket <span class="text-red-600">*</span></label>
                    <input type="text"
                           name="subject"
                           value="{{ old('subject', $preselectedSubject) }}"
                           required
                           placeholder="Contoh: Jadwal servis berkala Avanza nomor polisi B 1234 TOY"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi Lengkap Masalah / Pertanyaan <span class="text-red-600">*</span></label>
                    <textarea name="description"
                              rows="5"
                              required
                              placeholder="Jelaskan secara rinci detail pertanyaan, kendala, atau kronologi yang ingin Anda sampaikan..."
                              class="w-full rounded-xl border border-slate-300 p-3.5 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Lampiran Dokumen / Foto (Opsional)</label>
                    <input type="file"
                           name="attachment"
                           accept=".jpg,.jpeg,.png,.pdf"
                           class="w-full rounded-xl border border-slate-300 p-2 text-xs font-semibold text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200">
                    <p class="text-[11px] text-slate-400 mt-1">Format yang didukung: JPG, PNG, PDF (Maksimal 5MB).</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('customer.tickets.index') }}"
                       class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-900 rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition">
                        Kirim Tiket Bantuan
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection

