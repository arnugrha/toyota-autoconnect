@extends(auth()->user()->isCustomer() ? 'layouts.public' : 'layouts.dashboard')

@section('title', 'Pengaturan Profil — Toyota AutoConnect')
@section('header_title', 'Pengaturan Profil Pengguna')
@section('header_subtitle', 'Kelola informasi identitas pribadi dan keamanan kata sandi akun Anda')

@section('content')
<div class="{{ auth()->user()->isCustomer() ? 'bg-slate-50 min-h-[85vh] py-8 sm:py-12' : '' }}">
    <div class="{{ auth()->user()->isCustomer() ? 'mx-auto max-w-4xl px-4 sm:px-6 lg:px-8' : 'max-w-4xl' }} space-y-6">

        @if(auth()->user()->isCustomer())
            <!-- Customer Header & Breadcrumb -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-6">
                <div>
                    <nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-2 font-medium">
                        <a href="{{ route('home') }}" class="hover:text-red-600 transition">Beranda</a>
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                        <span class="text-slate-800 font-semibold">Profil Saya</span>
                    </nav>
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-600 text-white shadow-sm">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Profil Akun</h1>
                            <p class="text-xs text-slate-500 mt-0.5">Kelola identitas diri dan pengaturan keamanan kata sandi akun pelanggan Anda</p>
                        </div>
                    </div>
                </div>

                <a href="{{ route('customer.inquiries.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-red-600 transition self-start sm:self-auto">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Riwayat Test Drive
                </a>
            </div>
        @endif


    @if (session('password_status'))
        <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-3.5 text-xs text-emerald-800">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('password_status') }}</span>
        </div>
    @endif

    <!-- Profile Overview Card -->
    <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-sm font-extrabold text-white border-2 border-red-500 shadow-sm flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-slate-900">{{ $user->name }}</h3>
                    <span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold text-red-600 border border-red-200">
                        {{ $user->role_name }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">{{ $user->email }}</p>
            </div>
        </div>

        <div class="text-[11px] text-slate-500">
            Terdaftar pada: <span class="font-medium text-slate-700">{{ $user->created_at->format('d M Y, H:i') }} WIB</span>
        </div>
    </div>

    <!-- Section Navigation Anchors -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="#profile-info"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:border-slate-300 hover:text-slate-900 shadow-2xs transition">
            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Informasi Pribadi
        </a>
        <a href="#security"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 border border-red-200 text-red-700 hover:bg-red-100 transition shadow-2xs">
            <svg class="h-3.5 w-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Keamanan &amp; Ubah Kata Sandi
        </a>
    </div>

    <!-- Section 1: Update Profile Details -->
    <div id="profile-info" class="rounded-xl bg-white p-5 sm:p-6 border border-slate-200 shadow-sm scroll-mt-6">
        <div class="border-b border-slate-100 pb-3 mb-5">
            <h3 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Informasi Pribadi
            </h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Perbarui nama lengkap dan nomor kontak telepon yang terhubung dengan akun</p>
        </div>

        @if ($errors->has('name') || $errors->has('phone'))
            <div class="mb-4 rounded-xl bg-red-50 p-3.5 border border-red-200 text-xs text-red-700">
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->get('name') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                    @foreach ($errors->get('phone') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-red-600">*</span></label>
                    <input id="name" name="name" type="text" required
                           value="{{ old('name', $user->name) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                    <input id="phone" name="phone" type="tel"
                           value="{{ old('phone', $user->phone) }}"
                           placeholder="081234567890"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Alamat Email (Akun Utama)</label>
                    <input type="email" disabled value="{{ $user->email }}"
                           class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-500 cursor-not-allowed">
                    <p class="text-[10px] text-slate-400 mt-1">Perubahan email memerlukan verifikasi identitas resmi administrator.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Tingkat Hak Akses / Role</label>
                    <input type="text" disabled value="{{ $user->role_name }}"
                           class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-500 cursor-not-allowed">
                    <p class="text-[10px] text-slate-400 mt-1">Role dikelola secara terpusat oleh Super Admin dan tidak dapat diubah mandiri.</p>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit"
                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-red-600 focus:ring-offset-2 transition flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: Change Password -->
    <div id="security" class="rounded-xl bg-white p-5 sm:p-6 border border-slate-200 shadow-sm scroll-mt-6"
         x-data="{
             showCurrent: false,
             showNew: false,
             showConfirm: false,
             newPw: '',
             confirmPw: '',
             get hasMinLength() { return this.newPw.length >= 8; },
             get hasNumber() { return /[0-9]/.test(this.newPw); },
             get hasMixedCase() { return /[a-z]/.test(this.newPw) && /[A-Z]/.test(this.newPw); },
             get hasSpecial() { return /[^A-Za-z0-9]/.test(this.newPw); },
             get score() {
                 if (!this.newPw) return 0;
                 let s = 0;
                 if (this.hasMinLength) s++;
                 if (this.hasNumber) s++;
                 if (this.hasMixedCase) s++;
                 if (this.hasSpecial) s++;
                 return s;
             },
             get strength() {
                 if (!this.newPw) return { text: 'Belum diisi', color: 'bg-slate-200', textClass: 'text-slate-400', width: '0%' };
                 if (this.score <= 1) return { text: 'Kurang / Lemah', color: 'bg-red-500', textClass: 'text-red-600', width: '25%' };
                 if (this.score === 2) return { text: 'Sedang / Cukup', color: 'bg-amber-500', textClass: 'text-amber-600', width: '50%' };
                 if (this.score === 3) return { text: 'Kuat', color: 'bg-blue-600', textClass: 'text-blue-600', width: '75%' };
                 return { text: 'Sangat Kuat', color: 'bg-emerald-600', textClass: 'text-emerald-600', width: '100%' };
             },
             get isMatched() {
                 if (!this.confirmPw) return null;
                 return this.newPw === this.confirmPw;
             }
         }">
        <div class="border-b border-slate-100 pb-3 mb-5">
            <h3 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                Perbarui Kata Sandi Akun
            </h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Kelola kata sandi akun Anda secara berkala untuk menjaga keamanan akses dan perlindungan data</p>
        </div>

        @if ($errors->has('current_password') || $errors->has('password'))
            <div class="mb-4 rounded-xl bg-red-50 p-3.5 border border-red-200 text-xs text-red-700">
                <div class="font-bold mb-1">Gagal memperbarui kata sandi:</div>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->get('current_password') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                    @foreach ($errors->get('password') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Saat Ini <span class="text-red-600">*</span></label>
                <div class="relative">
                    <input id="current_password" name="current_password" :type="showCurrent ? 'text' : 'password'" required
                           placeholder="Masukkan kata sandi akun Anda yang aktif saat ini"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-9 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                    <button type="button" @click="showCurrent = !showCurrent"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600"
                            tabindex="-1">
                        <svg x-show="!showCurrent" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="showCurrent" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="new_password" class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Baru <span class="text-red-600">*</span></label>
                    <div class="relative">
                        <input id="new_password" name="password" :type="showNew ? 'text' : 'password'" required
                               x-model="newPw"
                               placeholder="Minimal 8 karakter kombinasi"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-9 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                        <button type="button" @click="showNew = !showNew"
                                class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600"
                                tabindex="-1">
                            <svg x-show="!showNew" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg x-show="showNew" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>

                    <!-- Password Strength Meter -->
                    <div class="mt-2" x-show="newPw.length > 0" x-cloak>
                        <div class="flex items-center justify-between text-[11px] mb-1">
                            <span class="text-slate-500">Tingkat Keamanan:</span>
                            <span class="font-bold" :class="strength.textClass" x-text="strength.text"></span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full transition-all duration-300 rounded-full"
                                 :class="strength.color"
                                 :style="`width: ${strength.width}`"></div>
                        </div>

                        <!-- Real-time Validation Checks -->
                        <div class="grid grid-cols-2 gap-1.5 mt-2.5 text-[10px]">
                            <div class="flex items-center gap-1" :class="hasMinLength ? 'text-emerald-700 font-semibold' : 'text-slate-400'">
                                <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span>Min. 8 karakter</span>
                            </div>
                            <div class="flex items-center gap-1" :class="hasNumber ? 'text-emerald-700 font-semibold' : 'text-slate-400'">
                                <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span>Memiliki angka</span>
                            </div>
                            <div class="flex items-center gap-1" :class="hasMixedCase ? 'text-emerald-700 font-semibold' : 'text-slate-400'">
                                <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span>Huruf besar &amp; kecil</span>
                            </div>
                            <div class="flex items-center gap-1" :class="hasSpecial ? 'text-emerald-700 font-semibold' : 'text-slate-400'">
                                <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span>Karakter simbol</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Kata Sandi Baru <span class="text-red-600">*</span></label>
                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation" :type="showConfirm ? 'text' : 'password'" required
                               x-model="confirmPw"
                               placeholder="Ulangi kata sandi baru"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-9 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none transition">
                        <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600"
                                tabindex="-1">
                            <svg x-show="!showConfirm" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg x-show="showConfirm" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>

                    <!-- Password Matching Feedback -->
                    <div class="mt-2 text-[11px]" x-show="confirmPw.length > 0" x-cloak>
                        <span x-show="isMatched === true" class="text-emerald-600 font-semibold flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Konfirmasi kata sandi cocok
                        </span>
                        <span x-show="isMatched === false" class="text-red-600 font-semibold flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            Konfirmasi belum sama dengan kata sandi baru
                        </span>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 transition flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Simpan Kata Sandi Baru
                </button>
            </div>
        </form>
    </div>

    </div>
</div>
@endsection
