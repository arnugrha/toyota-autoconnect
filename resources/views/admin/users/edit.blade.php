@extends('layouts.dashboard')

@section('title', 'Edit Pengguna — Toyota AutoConnect')
@section('header_title', 'Perbarui Data & Hak Akses Pengguna')
@section('header_subtitle', 'Kelola status keaktifan dan role operasional akun ' . $user->name)

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

    @if ($user->id === auth()->id())
        <div class="rounded-xl bg-amber-50 p-4 border border-amber-200 text-xs text-amber-800 flex items-start gap-2.5">
            <svg class="h-4 w-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <div>
                <span class="font-bold">Perhatian Akun Administrator:</span>
                Anda sedang mengedit akun yang sedang aktif digunakan saat ini. Sistem keamanan otomatis mencegah penonaktifan atau penurunan role diri sendiri untuk menjaga akses sistem.
            </div>
        </div>
    @endif

    <div class="rounded-xl bg-white p-5 sm:p-6 border border-slate-200 shadow-sm">
        <div class="border-b border-slate-100 pb-3 mb-5">
            <h3 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Perbarui Informasi Pengguna
            </h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Edit nama, nomor kontak, penugasan role, dan status aktif akun pengguna</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-xl bg-red-50 p-3.5 border border-red-200 text-xs text-red-700">
                <div class="font-bold mb-1">Pembaruan gagal:</div>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-red-600">*</span></label>
                <input id="name" name="name" type="text" required
                       value="{{ old('name', $user->name) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Alamat Email (Permanen)</label>
                    <input type="email" disabled value="{{ $user->email }}"
                           class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-500 cursor-not-allowed">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon</label>
                    <input id="phone" name="phone" type="tel"
                           value="{{ old('phone', $user->phone) }}"
                           placeholder="081234567890"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                </div>
            </div>

            <div>
                <label for="role" class="block text-xs font-bold text-slate-700 mb-1">Penugasan Role / Izin Akses <span class="text-red-600">*</span></label>
                <select id="role" name="role" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                    @foreach ($roles as $r)
                        <option value="{{ $r->name }}" {{ old('role', $user->roles->first()?->name) === $r->name ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-500 mt-1">Mengubah role akan memperbarui seluruh hak akses permission secara instan.</p>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                           class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                    <span class="text-xs font-semibold text-slate-700">Status Akun Aktif</span>
                </label>
                <p class="text-[10px] text-slate-500 pl-6 mt-0.5">Jika dinonaktifkan, pengguna tidak akan dapat login ke dalam sistem aplikasi.</p>
            </div>

            <!-- Section: Reset Kata Sandi Akun (Opsional) -->
            <div class="pt-4 border-t border-slate-100" x-data="{ openReset: {{ $errors->has('password') ? 'true' : 'false' }}, showPw: false }">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            Reset Kata Sandi Akun
                        </h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Atur kata sandi baru untuk akun pengguna ini jika pengguna lupa kata sandi atau meminta pembaruan</p>
                    </div>
                    <button type="button" @click="openReset = !openReset"
                            class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                        <span x-text="openReset ? 'Tutup Reset' : '+ Buka Form Reset'"></span>
                    </button>
                </div>

                <div x-show="openReset" x-cloak class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <p class="text-[11px] text-slate-500">Kosongkan kolom di bawah jika Anda tidak bermaksud mereset kata sandi akun pengguna ini.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Baru</label>
                            <div class="relative">
                                <input id="password" name="password" :type="showPw ? 'text' : 'password'"
                                       placeholder="Minimal 8 karakter"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-9 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition bg-white">
                                <button type="button" @click="showPw = !showPw"
                                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600"
                                        tabindex="-1">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                            <input id="password_confirmation" name="password_confirmation" :type="showPw ? 'text' : 'password'"
                                   placeholder="Ulangi kata sandi baru"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition bg-white">
                        </div>
                    </div>
                </div>
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
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
