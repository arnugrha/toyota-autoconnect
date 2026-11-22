<div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-xs mb-6">
    <form method="GET" action="{{ $formAction ?? url()->current() }}" class="flex flex-wrap items-end gap-3 text-xs">
        
        <!-- Period Preset Selector -->
        <div class="min-w-[160px] flex-1 sm:flex-initial">
            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Rentang Periode</label>
            <select name="period" 
                    id="period-select"
                    onchange="if(this.value === 'custom') { document.getElementById('custom-dates').classList.remove('hidden'); } else { document.getElementById('custom-dates').classList.add('hidden'); this.form.submit(); }"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                <option value="today" {{ ($dateFilter['period'] ?? '') === 'today' ? 'selected' : '' }}>Hari Ini</option>
                <option value="last_7_days" {{ ($dateFilter['period'] ?? '') === 'last_7_days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                <option value="last_30_days" {{ ($dateFilter['period'] ?? '') === 'last_30_days' ? 'selected' : '' }}>30 Hari Terakhir</option>
                <option value="this_month" {{ ($dateFilter['period'] ?? '') === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                <option value="last_month" {{ ($dateFilter['period'] ?? '') === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                <option value="this_year" {{ ($dateFilter['period'] ?? '') === 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
                <option value="custom" {{ ($dateFilter['period'] ?? '') === 'custom' ? 'selected' : '' }}>Kustom Rentang Tanggal...</option>
            </select>
        </div>

        <!-- Custom Date Range (Hidden by default unless custom is selected) -->
        <div id="custom-dates" class="{{ ($dateFilter['period'] ?? '') === 'custom' ? '' : 'hidden' }} flex items-end gap-2">
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Mulai</label>
                <input type="date" name="start_date" 
                       value="{{ request('start_date', $dateFilter['start_date']->format('Y-m-d')) }}"
                       class="rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Sampai</label>
                <input type="date" name="end_date" 
                       value="{{ request('end_date', $dateFilter['end_date']->format('Y-m-d')) }}"
                       class="rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
            </div>
        </div>

        <!-- Branch / Dealer Filter (For Super Admin & Owner or users without hardcoded branch) -->
        @if(auth()->user()->hasRole(['Super Admin', 'Owner']) && isset($branches))
            <div class="min-w-[180px] flex-1 sm:flex-initial">
                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Jaringan Cabang Dealer</label>
                <select name="branch_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                    <option value="">Semua Cabang Dealer</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ ($selectedBranchId ?? request('branch_id')) == $b->id ? 'selected' : '' }}>
                            {{ $b->name }} ({{ $b->city }})
                        </option>
                    @endforeach
                </select>
            </div>
        @elseif(!empty(auth()->user()->branch))
            <div class="min-w-[160px] flex-1 sm:flex-initial">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cabang Anda</label>
                <div class="px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-slate-800 font-bold truncate">
                    {{ auth()->user()->branch->name }}
                </div>
            </div>
        @endif

        <!-- Filter Submit Button -->
        <div class="flex items-center gap-2">
            <button type="submit" 
                    class="rounded-xl bg-slate-900 py-2 px-4 text-xs font-bold text-white hover:bg-slate-800 transition shadow-2xs">
                Terapkan
            </button>
            
            <a href="{{ $formAction ?? url()->current() }}" 
               class="rounded-xl border border-slate-300 py-2 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
               title="Reset Filter">
                Reset
            </a>
        </div>

        <!-- Active Period Pill -->
        <div class="ml-auto hidden lg:flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 text-red-700 border border-red-200 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-red-600"></span>
                <span>{{ $dateFilter['label'] ?? 'Periode Aktif' }}</span>
            </span>
        </div>

    </form>
</div>

