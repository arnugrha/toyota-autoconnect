<div class="flex items-center gap-1.5 overflow-x-auto pb-2 mb-6 scrollbar-none text-xs font-bold border-b border-slate-200">
    @if(auth()->user()->hasRole(['Super Admin', 'Dealer Manager', 'Owner']))
        <a href="{{ route('admin.owner.revenue') }}"
           class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.owner.revenue') ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            💰 Monitoring Pendapatan
        </a>
    @endif

    <a href="{{ route('admin.analytics.dashboard') }}"
       class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.analytics.dashboard') ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
        Ringkasan Eksekutif
    </a>

    <a href="{{ route('admin.analytics.sales') }}"
       class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.analytics.sales') ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
        Penjualan Kendaraan
    </a>

    <a href="{{ route('admin.analytics.crm') }}"
       class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.analytics.crm') ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
        CRM &amp; Leads Funnel
    </a>

    @if(auth()->user()->hasRole(['Super Admin', 'Dealer Manager', 'Owner']))
        <a href="{{ route('admin.analytics.dealers') }}"
           class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.analytics.dealers') ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            Komparasi Dealer
        </a>
    @endif

    <a href="{{ route('admin.analytics.inventory') }}"
       class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.analytics.inventory') ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
        Inventaris &amp; Stok
    </a>

    <a href="{{ route('admin.analytics.payments') }}"
       class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.analytics.payments') ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
        Transaksi Pembayaran
    </a>

    <a href="{{ route('admin.analytics.after-sales') }}"
       class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.analytics.after-sales') ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
        Servis Bengkel
    </a>

    @if(!auth()->user()->hasRole(['Sales Executive', 'Inventory Officer', 'Service Advisor']))
        <a href="{{ route('admin.analytics.customers') }}"
           class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.analytics.customers') ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            Pelanggan
        </a>
    @endif

    @can('reports.view')
        <a href="{{ route('admin.reports.index') }}"
           class="px-3.5 py-2 rounded-xl transition whitespace-nowrap ml-auto {{ request()->routeIs('admin.reports.*') ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-800 hover:bg-slate-200' }}">
            &darr; Unduh Laporan (CSV)
        </a>
    @endcan
</div>

