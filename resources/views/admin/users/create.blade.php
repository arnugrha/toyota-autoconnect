@extends('layouts.dashboard')

@section('title', 'Tambah Akun Staf — Toyota AutoConnect')
@section('header_title', 'Tambah Akun Staf Baru')
@section('header_subtitle', 'Alokasikan kredensial akun dan role operasional untuk staf dealer')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Pengguna
        </a>
    </div>

    <div class="rounded-xl bg-white p-5 sm:p-6 border border-slate-200 shadow-sm">
        <div class="border-b border-slate-100 pb-3 mb-5">
            <h3 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.765Z" />
                </svg>
                Formulir Pendaftaran Staf Internal
            </h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Seluruh akun yang dibuat melalui formulir ini akan memiliki akses sesuai role yang dipilih</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-xl bg-red-50 p-3.5 border border-red-200 text-xs text-red-700">
                <div class="font-bold mb-1">Gagal menyimpan data:</div>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Staf <span class="text-red-600">*</span></label>
                <input id="name" name="name" type="text" required
                       value="{{ old('name') }}"
                       placeholder="Contoh: Budi Santoso"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Alamat Email Perusahaan <span class="text-red-600">*</span></label>
                    <input id="email" name="email" type="email" required
                           value="{{ old('email') }}"
                           placeholder="nama@toyota-autoconnect.id"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon Operasional</label>
                    <input id="phone" name="phone" type="tel"
                           value="{{ old('phone') }}"
                           placeholder="081234567890"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block text-xs font-bold text-slate-700 mb-1">Role / Posisi Staf <span class="text-red-600">*</span></label>
                    <select id="role" name="role" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                        <option value="">Pilih Role Operasional</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->name }}" {{ old('role') === $r->name ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Awal <span class="text-red-600">*</span></label>
                    <input id="password" name="password" type="password" required
                           placeholder="Minimal 8 karakter"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                           class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                    <span class="text-xs font-semibold text-slate-700">Akun Langsung Aktif</span>
                </label>
                <p class="text-[10px] text-slate-500 pl-6 mt-0.5">Staf dapat segera melakukan login setelah akun dibuat.</p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}"
                   class="rounded-lg bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-red-600 focus:ring-offset-2 transition flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Simpan Akun Staf
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
