@extends('layouts.public')

@section('title', 'Daftar Akun Pelanggan — Toyota AutoConnect')
@section('meta_description', 'Daftar akun pelanggan Toyota AutoConnect untuk memantau pemesanan, simulasi kredit, dan booking service berkala.')

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-12 overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm"
         x-data="{ showPassword: false }">
        
        <!-- Left Visual Panel -->
        <div class="lg:col-span-5 bg-slate-900 p-8 text-white flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-red-600/20 blur-2xl"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-600 text-white font-extrabold text-sm">
                        T
                    </div>
                    <span class="text-sm font-bold tracking-tight">TOYOTA <span class="font-normal text-slate-400">AutoConnect</span></span>
                </div>

                <div class="mt-8">
                    <span class="inline-block rounded-md bg-white/10 px-2 py-0.5 text-[10px] font-bold text-red-400">
                        REGISTRASI PELANGGAN
                    </span>
                    <h2 class="mt-2 text-xl font-bold leading-snug">Mulai Pengalaman Berkendara Terbaik Anda</h2>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Daftar akun pelanggan untuk menikmati kemudahan penjadwalan test drive, pelacakan pemesanan unit, pengajuan kredit, dan booking service berkala resmi Toyota.
                    </p>
                </div>

                <div class="mt-6 space-y-2.5 text-xs text-slate-300">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Akses katalog lengkap & penawaran promo</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Simulasi kredit & booking konsultasi sales</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Riwayat pemesanan & status booking service</span>
                    </div>
                </div>
            </div>

            <div class="relative z-10 pt-6 mt-6 border-t border-slate-800/80 text-[11px] text-slate-400">
                <p class="flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Privasi & data pelanggan terjamin aman
                </p>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
                    <div>
                        <h1 class="text-lg sm:text-xl font-extrabold text-slate-900">Buat Akun Pelanggan</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Lengkapi formulir berikut untuk mendaftar akun baru</p>
                    </div>
                    <a href="{{ route('home') }}" class="text-[11px] font-semibold text-slate-400 hover:text-red-600 transition flex items-center gap-1">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Beranda
                    </a>
                </div>

                @if ($errors->any())
                    <div class="mb-4 rounded-xl bg-red-50 p-3.5 border border-red-200 text-xs text-red-700">
                        <div class="font-bold flex items-center gap-1.5 mb-1 text-red-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z" />
                            </svg>
                            Registrasi belum berhasil:
                        </div>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-red-600">*</span></label>
                        <input id="name" name="name" type="text" autocomplete="name" required
                               value="{{ old('name') }}"
                               placeholder="Nama lengkap sesuai KTP"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Email <span class="text-red-600">*</span></label>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                   value="{{ old('email') }}"
                                   placeholder="nama@email.com"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                        </div>

                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                            <input id="phone" name="phone" type="tel" autocomplete="tel"
                                   value="{{ old('phone') }}"
                                   placeholder="081234567890"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi <span class="text-red-600">*</span></label>
                            <div class="relative">
                                <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                                       placeholder="Minimal 8 karakter"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-9 text-xs text-slate-900 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                                <button type="button" @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 focus:outline-none"
                                        tabindex="-1">
                                    <svg x-show="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Kata Sandi <span class="text-red-600">*</span></label>
                            <input id="password_confirmation" name="password_confirmation" :type="showPassword ? 'text' : 'password'" required
                                   placeholder="Ulangi kata sandi"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                        </div>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" name="terms" value="1" required {{ old('terms') ? 'checked' : '' }}
                                   class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-600">
                            <span class="text-[11px] text-slate-600 leading-snug">
                                Saya menyetujui <span class="font-bold text-slate-800">Syarat & Ketentuan</span> serta Kebijakan Privasi Toyota AutoConnect dalam pemrosesan data pelanggan.
                            </span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full mt-2 rounded-lg bg-red-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-red-600 focus:ring-offset-2 transition flex items-center justify-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.765Z" />
                        </svg>
                        Daftar Sebagai Pelanggan
                    </button>
                </form>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" class="font-bold text-red-600 hover:text-red-700 transition">
                        Masuk di Sini
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
