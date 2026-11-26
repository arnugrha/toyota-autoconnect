@php
    $pendingSpareParts = $order->items->where('approval_status', 'pending_approval');
@endphp

@if($pendingSpareParts->isNotEmpty())
    <div class="rounded-2xl border-2 border-amber-300 bg-amber-50/90 p-5 sm:p-6 shadow-sm space-y-4 print:hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-amber-200">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs flex-shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm font-black text-amber-950">Konfirmasi Tambahan Suku Cadang Diperlukan</h3>
                        <span class="inline-flex items-center rounded-full bg-amber-600 px-2 py-0.5 text-[10px] font-bold text-white">
                            {{ $pendingSpareParts->count() }} Item Menunggu
                        </span>
                    </div>
                    <p class="text-xs text-amber-900/90 mt-0.5 leading-relaxed">
                        Teknisi bengkel merekomendasikan penggantian suku cadang tambahan berikut untuk kendaraan Anda (WO <strong class="font-mono">{{ $order->order_number }}</strong>). Silakan konfirmasi <strong>Setujui</strong> atau <strong>Tolak</strong> sebelum dipasang dan ditagihkan.
                    </p>
                </div>
            </div>

            @if($pendingSpareParts->count() > 1)
                <form method="POST" action="{{ route('customer.service-orders.items.approve-all', $order) }}" class="flex-shrink-0"
                      onsubmit="return confirm('Setujui seluruh {{ $pendingSpareParts->count() }} item suku cadang tambahan ini?')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Setujui Semua ({{ $pendingSpareParts->count() }})
                    </button>
                </form>
            @endif
        </div>

        @if($order->inspection_result)
            <div class="rounded-xl bg-white/80 border border-amber-200/80 p-3.5 text-xs text-slate-700">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block mb-0.5">Catatan Inspeksi Teknisi:</span>
                <p class="whitespace-pre-line leading-relaxed">{{ $order->inspection_result }}</p>
            </div>
        @endif

        <div class="space-y-3">
            @foreach($pendingSpareParts as $pItem)
                <div class="rounded-xl bg-white border border-amber-200 p-4 shadow-2xs space-y-3" x-data="{ showRejectForm: false }">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-sm text-slate-900">{{ $pItem->name }}</span>
                                @if($pItem->sparePart)
                                    <span class="font-mono text-[10px] font-bold bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded border border-slate-200">
                                        SKU: {{ $pItem->sparePart->part_number }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500">
                                Jumlah: <strong class="text-slate-800">{{ $pItem->quantity }} {{ $pItem->sparePart?->unit ?? 'Pcs' }}</strong>
                                &times; <span class="font-mono">{{ $pItem->formatted_unit_price }}</span>
                            </p>
                        </div>

                        <div class="flex flex-col sm:items-end gap-2">
                            <div class="text-left sm:text-right">
                                <span class="text-[10px] font-bold uppercase text-slate-400 block">Estimasi Tambahan Biaya</span>
                                <span class="font-mono text-base font-black text-red-600">{{ $pItem->formatted_subtotal }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('customer.service-orders.items.approve', [$order, $pItem]) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:bg-emerald-700 transition">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        Setujui &amp; Pasang
                                    </button>
                                </form>

                                <button type="button"
                                        @click="showRejectForm = !showRejectForm"
                                        class="inline-flex items-center gap-1 rounded-lg border border-rose-300 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Inline Rejection Reason Form -->
                    <div x-show="showRejectForm" x-cloak class="pt-3 border-t border-slate-100">
                        <form method="POST" action="{{ route('customer.service-orders.items.reject', [$order, $pItem]) }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            @csrf
                            <input type="text"
                                   name="customer_response_note"
                                   placeholder="Alasan penolakan (opsional), misal: ditunda ke servis berikutnya..."
                                   class="flex-1 rounded-lg border border-rose-300 px-3 py-2 text-xs text-slate-800 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none">
                            <button type="submit"
                                    class="rounded-lg bg-rose-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-rose-700 transition">
                                Konfirmasi Tolak
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
