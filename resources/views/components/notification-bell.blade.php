@auth
<div class="relative" 
     x-data="{
        open: false,
        unreadCount: {{ auth()->user()->unreadNotifications()->count() }},
        notifications: [],
        loading: false,
        fetchNotifications() {
            this.loading = true;
            fetch('{{ route('notifications.dropdown') }}')
                .then(res => res.json())
                .then(data => {
                    this.unreadCount = data.unread_count;
                    this.notifications = data.notifications;
                    this.loading = false;
                })
                .catch(() => { this.loading = false; });
        },
        markAllRead() {
            fetch('{{ route('notifications.read-all') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(() => {
                this.unreadCount = 0;
                this.notifications.forEach(n => n.is_unread = false);
            });
        }
     }"
     @click.outside="open = false">

    <!-- Bell Trigger Button -->
    <button type="button" 
            @click="open = !open; if(open) fetchNotifications();"
            class="relative inline-flex items-center justify-center p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition focus:outline-none"
            title="Pusat Notifikasi">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        <!-- Dynamic Counter Badge -->
        <span x-show="unreadCount > 0" 
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              x-cloak
              class="absolute top-0.5 right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-black leading-none text-white ring-2 ring-white"
              style="top: 2px; right: 2px;">
        </span>
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white shadow-2xl border border-slate-200/90 z-50 overflow-hidden text-xs">

        <!-- Header -->
        <div class="px-4 py-3 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="font-bold text-sm tracking-tight">Notifikasi</span>
                <span x-show="unreadCount > 0" x-text="unreadCount + ' Baru'" class="rounded-full bg-red-600/90 px-2 py-0.5 text-[10px] font-extrabold text-white"></span>
            </div>
            <button type="button" 
                    @click="markAllRead()" 
                    x-show="unreadCount > 0"
                    class="text-[11px] font-semibold text-slate-300 hover:text-white underline transition">
                Tandai Semua Dibaca
            </button>
        </div>

        <!-- Notification List -->
        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 bg-slate-50/50">
            <template x-if="loading && notifications.length === 0">
                <div class="p-6 text-center text-slate-400">
                    <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-slate-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>Memuat notifikasi...</span>
                </div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="p-8 text-center">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </div>
                    <p class="font-semibold text-slate-700 text-xs">Belum Ada Notifikasi</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pemberitahuan aktivitas akan muncul di sini.</p>
                </div>
            </template>

            <template x-for="item in notifications" :key="item.id">
                <form :action="'/notifications/' + item.id + '/read'" method="POST" class="block">
                    @csrf
                    <button type="submit" 
                            class="w-full text-left p-3.5 hover:bg-white transition flex items-start gap-3 border-l-4"
                            :class="item.is_unread ? 'border-red-600 bg-white shadow-xs' : 'border-transparent bg-slate-50/70 opacity-75'">
                        
                        <div class="mt-0.5 flex-shrink-0">
                            <div class="w-2 h-2 rounded-full" :class="item.is_unread ? 'bg-red-600' : 'bg-slate-300'"></div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <span class="font-bold text-slate-900 truncate" x-text="item.title"></span>
                                <span class="text-[10px] text-slate-400 whitespace-nowrap flex-shrink-0" x-text="item.created_at_human"></span>
                            </div>
                            <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed" x-text="item.message"></p>
                        </div>
                    </button>
                </form>
            </template>
        </div>

        <!-- Footer -->
        <div class="p-2.5 bg-white border-t border-slate-100 text-center">
            <a href="{{ route('notifications.index') }}" 
               class="inline-flex items-center justify-center gap-1.5 w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition">
                <span>Buka Pusat Notifikasi Lengkap</span>
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>
    </div>
</div>
@endauth

