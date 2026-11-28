<!-- Customer Portal Header & Subnav -->
<div class="bg-white border-b border-slate-200 shadow-2xs">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-6 pb-0">
        <!-- Top Row: Customer Info & Quick Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5">
            <div class="flex items-center gap-3.5">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-600 bg-gradient-to-br from-red-600 to-red-700 text-white font-extrabold text-base shadow-sm ring-4 ring-red-50 flex-shrink-0" style="background-color: #dc2626;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">{{ auth()->user()->name }}</h1>
                        <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold text-red-700 border border-red-200">
                            Portal Pelanggan
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-2">
                        <span>{{ auth()->user()->email }}</span>
                        @if(auth()->user()->phone)
                            <span>&bull;</span>
                            <span>{{ auth()->user()->phone }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-red-600 transition">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    Profil Saya
                </a>
                <a href="{{ route('profile.edit') }}#security"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-red-600 transition">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    Ubah Kata Sandi
                </a>
                <a href="{{ route('vehicles.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Katalog Mobil
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-1 overflow-x-auto pb-px scrollbar-none border-b border-slate-200 text-xs font-semibold -mb-px">
            <a href="{{ route('customer.dashboard') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.dashboard') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span>Ringkasan Akun</span>
            </a>

            <a href="{{ route('customer.test-drives.index') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.test-drives.*') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Jadwal Test Drive</span>
            </a>

            <a href="{{ route('customer.bookings.index') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.bookings.*') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Pemesanan Mobil (SPK)</span>
            </a>

            <a href="{{ route('customer.offers.index') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.offers.*') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span>Penawaran Unit</span>
            </a>

            <a href="{{ route('customer.inquiries.index') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.inquiries.*') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.84-.84c.15-.658.334-1.304.544-1.933A7.472 7.472 0 0 1 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                <span>Inquiry &amp; Konsultasi</span>
            </a>

            <a href="{{ route('customer.simulations.index') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.simulations.*') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 3h.008v.008H8.25v-.008Zm0 3h.008v.008H8.25v-.008Zm3-6h.008v.008H11.25v-.008Zm0 3h.008v.008H11.25v-.008Zm0 3h.008v.008H11.25v-.008Zm3-6h.008v.008H14.25v-.008Zm0 3h.008v.008H14.25v-.008Zm3-3h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Zm-12-6.75h13.5a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Z" />
                </svg>
                <span>Simulasi Kredit</span>
            </a>

            <a href="{{ route('customer.leasing.index') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.leasing.*') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A2.25 2.25 0 0 0 18.25 8.25H5.75A2.25 2.25 0 0 0 3.5 10.333V21" />
                </svg>
                <span>Pengajuan Leasing</span>
            </a>

            <a href="{{ route('customer.payments.index') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.payments.*') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
                <span>Pembayaran SPK</span>
            </a>

            <a href="{{ route('customer.service-bookings.index') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.service-bookings.*') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27-2.828-2.828 3.27-3.27a4.5 4.5 0 0 0-6.32 4.486c.118.58.094 1.193-.07 1.743" />
                </svg>
                <span>Booking Servis</span>
            </a>

            <a href="{{ route('customer.service-histories.index') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.service-histories.*') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
                <span>Riwayat Servis</span>
            </a>

            <a href="{{ route('customer.service-reminders.index') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.service-reminders.*') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                <span>Pengingat Servis</span>
            </a>

            <a href="{{ route('customer.tickets.index') }}"
               class="flex items-center gap-2 px-4 py-3 border-b-2 transition whitespace-nowrap {{ request()->routeIs('customer.tickets.*') ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.502 49.188 49.188 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                </svg>
                <span>Pusat Bantuan (Tiket)</span>
            </a>
        </div>
    </div>
</div>

