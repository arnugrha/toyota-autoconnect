@extends('layouts.dashboard')

@section('title', 'Tambah Kendaraan Baru — Toyota AutoConnect')
@section('header_title', 'Tambah Master Kendaraan')
@section('header_subtitle', 'Daftarkan tipe unit baru, atur spesifikasi teknis, harga, dan unggah foto')

@section('content')
<div class="max-w-4xl space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.vehicles.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Kendaraan
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800">
            <div class="font-bold mb-1">Terdapat kesalahan pengisian formulir:</div>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.vehicles.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Card 1: Informasi Pokok Kendaraan -->
        <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Informasi Pokok &amp; Publikasi</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Model Kendaraan <span class="text-red-600">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}" placeholder="Contoh: Toyota All New Kijang Innova Zenix"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Varian / Tipe</label>
                    <input type="text" name="variant" value="{{ old('variant') }}" placeholder="Contoh: 2.0 Q HV Modellista TSS"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Kendaraan <span class="text-red-600">*</span></label>
                    <select name="category_id" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga OTR (Rp) <span class="text-red-600">*</span></label>
                    <input type="number" name="price" required min="0" value="{{ old('price') }}" placeholder="Contoh: 425000000"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Publikasi <span class="text-red-600">*</span></label>
                    <select name="publication_status" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="published" {{ old('publication_status', 'published') === 'published' ? 'selected' : '' }}>Published (Tampil di Katalog)</option>
                        <option value="draft" {{ old('publication_status') === 'draft' ? 'selected' : '' }}>Draft (Disimpan Sementara)</option>
                        <option value="archived" {{ old('publication_status') === 'archived' ? 'selected' : '' }}>Archived (Diarsipkan)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ketersediaan Model <span class="text-red-600">*</span></label>
                    <select name="status" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>Available (Siap Dipesan)</option>
                        <option value="incoming" {{ old('status') === 'incoming' ? 'selected' : '' }}>Incoming (Segera Hadir)</option>
                        <option value="reserved" {{ old('status') === 'reserved' ? 'selected' : '' }}>Reserved (Indent Khusus)</option>
                    </select>
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                               class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                        <span class="text-xs font-semibold text-slate-800">Tandai sebagai Unit Pilihan Unggulan (Featured)</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi &amp; Ringkasan Kendaraan</label>
                <textarea name="description" rows="3" placeholder="Tuliskan ulasan keunggulan desain, fitur keselamatan TSS, dan efisiensi mesin..."
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- Card 2: Spesifikasi Teknis -->
        <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Spesifikasi Mesin &amp; Dimensi</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Transmisi</label>
                    <input type="text" name="transmission" value="{{ old('transmission') }}" placeholder="Contoh: 10-Speed Direct Shift CVT"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bahan Bakar</label>
                    <input type="text" name="fuel_type" value="{{ old('fuel_type') }}" placeholder="Contoh: Bensin / Hybrid Electric"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kapasitas Mesin</label>
                    <input type="text" name="engine_capacity" value="{{ old('engine_capacity') }}" placeholder="Contoh: 1.987 cc M20A-FXS"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kapasitas Penumpang (Kursi)</label>
                    <input type="number" name="passenger_capacity" min="1" max="50" value="{{ old('passenger_capacity', 7) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Dimensi (P x L x T)</label>
                    <input type="text" name="dimensions" value="{{ old('dimensions') }}" placeholder="Contoh: 4.755 x 1.850 x 1.795 mm"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Card 3: Pengelolaan Foto Unit -->
        <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Foto &amp; Media Kendaraan</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Foto Utama Kendaraan (Primary)</label>
                    <input type="file" name="primary_image" accept="image/jpeg,image/png,image/webp"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    <p class="text-[10px] text-slate-400 mt-1">Format JPG, PNG, atau WebP. Maks 5 MB.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Galeri Foto Tambahan (Bisa multi-file)</label>
                    <input type="file" name="additional_images[]" multiple accept="image/jpeg,image/png,image/webp"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    <p class="text-[10px] text-slate-400 mt-1">Pilih beberapa foto sekaligus untuk interior/eksterior.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.vehicles.index') }}"
               class="rounded-lg bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                Batal
            </a>
            <button type="submit"
                    class="rounded-lg bg-red-600 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                Simpan Kendaraan
            </button>
        </div>
    </form>

</div>
@endsection
