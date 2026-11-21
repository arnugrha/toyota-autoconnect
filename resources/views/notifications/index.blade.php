@extends(auth()->user()->isCustomer() ? 'layouts.public' : 'layouts.dashboard')

@section('title', 'Pusat Notifikasi — Toyota AutoConnect')
@section('header_title', 'Pusat Notifikasi Terpadu')
@section('header_subtitle', 'Pantau seluruh aktivitas pemesanan, pembayaran, jadwal servis, dan pembaruan sistem')

@section('content')
<div class="{{ auth()->user()->isCustomer() ? 'bg-slate-50 min-h-[85vh] py-8 sm:py-12' : '' }}">
    <div class="{{ auth()->user()->isCustomer() ? 'mx-auto max-w-5xl px-4 sm:px-6 lg:px-8' : 'max-w-6xl' }} space-y-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-6">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-600 text-white shadow-md">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Pusat Notifikasi</h1>
                            @if($unreadCount > 0)
                                <span class="rounded-full bg-red-600 px-2.5 py-0.5 text-xs font-black text-white shadow-xs">
                                    {{ $unreadCount }} Belum Dibaca
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola dan pantau seluruh notifikasi aktivitas akun Anda</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2.5">
                @if($unreadCount > 0)
                    <form action="{{ route('notifications.read-all') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-red-600 transition">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Tandai Semua Dibaca
                        </button>
                    </form>
                @endif

                @if(auth()->user()->isCustomer())
                    <a href="{{ route('customer.notifications.preferences') }}" 
                       class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        Preferensi Notifikasi
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-3.5 text-xs font-semibold text-emerald-800 flex items-center gap-2">
                <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none text-xs font-bold">
            <a href="{{ route('notifications.index') }}"
               class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ !request()->has('category') && !request()->has('status') ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-100' }}">
                Semua ({{ $counts['all'] }})
            </a>

            <a href="{{ route('notifications.index', ['status' => 'unread']) }}"
               class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->input('status') === 'unread' ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-100' }}">
                Belum Dibaca ({{ $counts['unread'] }})
            </a>

            <a href="{{ route('notifications.index', ['category' => 'vehicle_booking']) }}"
               class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->input('category') === 'vehicle_booking' ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-100' }}">
                Pemesanan SPK ({{ $counts['vehicle_booking'] }})
            </a>

            <a href="{{ route('notifications.index', ['category' => 'payment']) }}"
               class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->input('category') === 'payment' ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-100' }}">
                Pembayaran ({{ $counts['payment'] }})
            </a>

            <a href="{{ route('notifications.index', ['category' => 'service']) }}"
               class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->input('category') === 'service' ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-100' }}">
                Servis &amp; Perawatan ({{ $counts['service'] }})
            </a>

            <a href="{{ route('notifications.index', ['category' => 'test_drive']) }}"
               class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->input('category') === 'test_drive' ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-100' }}">
                Test Drive ({{ $counts['test_drive'] }})
            </a>

            <a href="{{ route('notifications.index', ['category' => 'sales_offer']) }}"
               class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->input('category') === 'sales_offer' ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-100' }}">
                Penawaran ({{ $counts['sales_offer'] }})
            </a>

            <a href="{{ route('notifications.index', ['category' => 'customer_service']) }}"
               class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->input('category') === 'customer_service' ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-100' }}">
                Bantuan &amp; Tiket ({{ $counts['customer_service'] }})
            </a>
        </div>

        <!-- Notifications List -->
        <div class="space-y-3">
            @forelse($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $category = $notification->data['category'] ?? 'announcement';
                    $title = $notification->data['title'] ?? 'Pemberitahuan Sistem';
                    $message = $notification->data['message'] ?? '';
                    $actionUrl = $notification->data['action_url'] ?? null;
                    $actionText = $notification->data['action_text'] ?? 'Lihat Rincian';

                    $categoryBadgeClass = match($category) {
                        'vehicle_booking' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'payment' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'test_drive' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'sales_offer' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'service' => 'bg-orange-50 text-orange-700 border-orange-200',
                        'customer_service' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                    };

                    $categoryLabel = match($category) {
                        'vehicle_booking' => 'Pemesanan Kendaraan (SPK)',
                        'payment' => 'Pembayaran & Invoice',
                        'test_drive' => 'Test Drive',
                        'sales_offer' => 'Penawaran Kendaraan',
                        'service' => 'Servis & Perawatan',
                        'customer_service' => 'Customer Care',
                        'account' => 'Informasi Akun',
                        default => 'Pengumuman Resmi',
                    };
                @endphp

                <div class="rounded-2xl border p-4 sm:p-5 transition {{ $isUnread ? 'bg-white border-red-200 shadow-sm ring-1 ring-red-100' : 'bg-white/80 border-slate-200/90 opacity-90' }}">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        
                        <!-- Left Content Area -->
                        <div class="flex items-start gap-3.5 flex-1">
                            <!-- Indicator / Icon -->
                            <div class="mt-1 flex-shrink-0">
                                @if($isUnread)
                                    <span class="flex h-3 w-3 relative">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-600"></span>
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full h-2.5 w-2.5 bg-slate-300"></span>
                                @endif
                            </div>

                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide border {{ $categoryBadgeClass }}">
                                        {{ $categoryLabel }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 font-medium">
                                        {{ $notification->created_at->diffForHumans() }} ({{ $notification->created_at->format('d M Y, H:i') }} WIB)
                                    </span>
                                </div>

                                <h3 class="text-sm font-bold text-slate-900 tracking-tight {{ $isUnread ? 'font-black' : '' }}">
                                    {{ $title }}
                                </h3>

                                <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">
                                    {{ $message }}
                                </p>
                            </div>
                        </div>

                        <!-- Right Actions -->
                        <div class="flex items-center gap-2 sm:self-center flex-shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                            @if(!empty($actionUrl))
                                <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-red-700 transition shadow-xs">
                                        <span>{{ $actionText }}</span>
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
                                </form>
                            @elseif($isUnread)
                                <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 hover:text-red-600 transition"
                                            title="Tandai Sudah Dibaca">
                                        <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        <span>Tandai Dibaca</span>
                                    </button>
                                </form>
                            @endif

                            <!-- Delete Notification -->
                            <form action="{{ route('notifications.destroy', $notification->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus notifikasi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-2 rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 transition" 
                                        title="Hapus Notifikasi">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-3 shadow-inner">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Tidak Ada Notifikasi Ditemukan</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                        Belum ada pesan notifikasi baru pada kategori ini. Notifikasi mengenai booking, pembayaran, atau servis akan ditampilkan di sini.
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="pt-4">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</div>
@endsection

