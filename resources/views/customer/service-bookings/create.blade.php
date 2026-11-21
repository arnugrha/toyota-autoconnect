@extends('layouts.public')

@section('title', 'Formulir Booking Servis Resmi Toyota — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10"
     x-data="{
        branchId: '{{ old('branch_id', $preselectedBranchId ?? ($branches->first()->id ?? '')) }}',
        bookingDate: '{{ old('booking_date', now()->addDay()->toDateString()) }}',
        selectedSlot: '{{ old('booking_time', '') }}',
        serviceTypeId: '{{ old('service_type_id', $preselectedTypeId ?? ($serviceTypes->first()->id ?? '')) }}',
        vehicleTypeOption: '{{ old('vehicle_option', $purchasedVehicles->isNotEmpty() ? 'owned' : 'custom') }}',
        vehicleName: '{{ old('vehicle_name', '') }}',
        licensePlate: '{{ old('license_plate', '') }}',
        vehicleId: '{{ old('vehicle_id', '') }}',
        currentMileage: '{{ old('current_mileage', '') }}',
        slots: [],
        loadingSlots: false,
        slotError: '',

        async fetchSlots() {
            if (!this.branchId || !this.bookingDate) return;
            this.loadingSlots = true;
            this.slotError = '';
            try {
                const res = await fetch(`{{ route('customer.service-bookings.available-slots') }}?branch_id=${this.branchId}&date=${this.bookingDate}`);
                const data = await res.json();
                if (data.success) {
                    this.slots = data.slots;
                    if (!this.slots.some(s => s.time === this.selectedSlot && (s.is_available ?? s.available))) {
                        this.selectedSlot = '';
                    }
                } else {
                    this.slotError = 'Gagal memuat slot waktu bengkel.';
                }
            } catch (e) {
                this.slotError = 'Terjadi kesalahan saat memeriksa ketersediaan slot.';
            } finally {
                this.loadingSlots = false;
            }
        },

        selectOwnedVehicle(id, name, plate) {
            this.vehicleId = id;
            this.vehicleName = name;
            this.licensePlate = plate;
        },

        init() {
            this.fetchSlots();
        }
     }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Breadcrumb & Title -->
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('customer.service-bookings.index') }}" class="hover:text-red-600 transition">Booking Servis</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Buat Permintaan Baru</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Formulir Booking Servis Toyota</h1>
            <p class="text-xs text-slate-500 mt-1">Pilih bengkel resmi, paket servis, jadwal kedatangan, dan keluhan kendaraan Anda.</p>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-2xs">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>Terdapat kesalahan pada isian form:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-[11px] font-normal text-rose-700">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('customer.service-bookings.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf

            <div class="lg:col-span-2 space-y-6">

                <!-- Step 1: Pilih Cabang Bengkel -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-[11px] font-extrabold text-white">1</span>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Pilih Bengkel Resmi Toyota</h2>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Dealer Cabang Bengkel <span class="text-red-600">*</span></label>
                        <select name="branch_id"
                                x-model="branchId"
                                @change="fetchSlots()"
                                required
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">
                                    {{ $branch->name }} — {{ $branch->city }} ({{ $branch->address }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Pilih bengkel resmi terdekat untuk kenyamanan pengerjaan kendaraan Anda.</p>
                    </div>
                </div>

                <!-- Step 2: Informasi Kendaraan -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-[11px] font-extrabold text-white">2</span>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Informasi Kendaraan Toyota Anda</h2>
                    </div>

                    @if($purchasedVehicles->isNotEmpty())
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-700 mb-2">Mobil Terdaftar Akun Anda</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($purchasedVehicles as $pv)
                                    <button type="button"
                                            @click="selectOwnedVehicle('{{ $pv->vehicle_id }}', '{{ $pv->vehicle?->name ?? 'Toyota' }}', '{{ $pv->registration_plate ?? '' }}'); vehicleTypeOption = 'owned'"
                                            :class="vehicleId == '{{ $pv->vehicle_id }}' && vehicleTypeOption == 'owned' ? 'border-red-600 bg-red-50/50 ring-2 ring-red-600/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                                            class="text-left p-3.5 rounded-xl border transition flex items-center justify-between">
                                        <div>
                                            <span class="text-xs font-bold text-slate-900 block">{{ $pv->vehicle?->name }}</span>
                                            <span class="text-[10px] text-slate-500 font-mono mt-0.5 block">No. SPK: {{ $pv->booking_number }}</span>
                                        </div>
                                        <svg x-show="vehicleId == '{{ $pv->vehicle_id }}' && vehicleTypeOption == 'owned'" class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Model / Nama Kendaraan <span class="text-red-600">*</span></label>
                            <input type="text"
                                   name="vehicle_name"
                                   x-model="vehicleName"
                                   required
                                   placeholder="Contoh: All New Yaris Cross HEV"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Polisi (Plat Mobil) <span class="text-red-600">*</span></label>
                            <input type="text"
                                   name="license_plate"
                                   x-model="licensePlate"
                                   required
                                   placeholder="Contoh: B 1234 TOY"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-semibold text-slate-800 uppercase focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kilometer Saat Ini (KM Odometer)</label>
                            <input type="number"
                                   name="current_mileage"
                                   x-model="currentMileage"
                                   min="0"
                                   placeholder="Contoh: 10000"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Master Katalog (Opsional)</label>
                            <select name="vehicle_id"
                                    x-model="vehicleId"
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                                <option value="">-- Pilih Model Dari Katalog (Opsional) --</option>
                                @foreach($catalogVehicles as $catV)
                                    <option value="{{ $catV->id }}">{{ $catV->name }} ({{ $catV->category?->name ?? 'Model' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Pilih Jenis Layanan Servis -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-[11px] font-extrabold text-white">3</span>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Pilih Jenis Layanan Servis</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($serviceTypes as $st)
                            <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition"
                                   :class="serviceTypeId == '{{ $st->id }}' ? 'border-red-600 bg-red-50/40 ring-2 ring-red-600/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                <input type="radio"
                                       name="service_type_id"
                                       value="{{ $st->id }}"
                                       x-model="serviceTypeId"
                                       class="sr-only">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold text-slate-900">{{ $st->name }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                        {{ $st->estimated_duration_minutes }} mnt
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">{{ $st->description }}</p>
                                <span class="text-xs font-bold text-red-600 mt-3">{{ $st->formatted_price }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Step 4: Jadwal Kedatangan & Slot Waktu -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-[11px] font-extrabold text-white">4</span>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Jadwal Tanggal &amp; Sesi Kedatangan</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Kedatangan <span class="text-red-600">*</span></label>
                            <input type="date"
                                   name="booking_date"
                                   x-model="bookingDate"
                                   @change="fetchSlots()"
                                   min="{{ now()->toDateString() }}"
                                   required
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">
                            <p class="text-[11px] text-slate-400 mt-1">Pilih tanggal operasional bengkel (Senin - Sabtu).</p>
                        </div>
                    </div>

                    <!-- Available Slots Grid -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Pilih Jam Kedatangan (Slot Waktu Tersedia) <span class="text-red-600">*</span></label>
                        <input type="hidden" name="booking_time" x-model="selectedSlot" required>

                        <!-- Loading State -->
                        <div x-show="loadingSlots" class="py-6 text-center text-xs text-slate-500 flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Memeriksa ketersediaan kapasitas bengkel...</span>
                        </div>

                        <!-- Error State -->
                        <div x-show="slotError" x-text="slotError" class="text-xs text-rose-600 font-semibold mb-2"></div>

                        <!-- Slots List -->
                        <div x-show="!loadingSlots && slots.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <template x-for="slot in slots" :key="slot.time">
                                <button type="button"
                                        :disabled="!(slot.is_available ?? slot.available)"
                                        @click="if(slot.is_available ?? slot.available) selectedSlot = slot.time"
                                        :class="{
                                            'bg-red-600 text-white border-red-600 font-extrabold shadow-xs': selectedSlot == slot.time && (slot.is_available ?? slot.available),
                                            'bg-white text-slate-800 border-slate-200 hover:border-red-400 hover:bg-red-50/30': selectedSlot != slot.time && (slot.is_available ?? slot.available),
                                            'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed opacity-60': !(slot.is_available ?? slot.available)
                                        }"
                                        class="p-2.5 rounded-xl border text-center text-xs font-semibold transition flex flex-col items-center justify-center">
                                    <span class="font-mono text-sm" x-text="slot.time + ' WIB'"></span>
                                    <span class="text-[9px] mt-0.5"
                                          :class="(slot.is_available ?? slot.available) ? 'text-slate-500' : 'text-rose-500 font-bold'"
                                          x-text="(slot.is_available ?? slot.available) ? `Sisa: ${slot.remaining_capacity} slot` : 'Penuh'">
                                    </span>
                                </button>
                            </template>
                        </div>

                        <div x-show="!loadingSlots && slots.length === 0" class="py-6 text-center text-xs text-amber-600 font-semibold bg-amber-50 rounded-xl border border-amber-200 p-4">
                            Bengkel tutup atau tidak ada slot operasional yang tersedia pada tanggal ini. Silakan pilih tanggal atau cabang lainnya.
                        </div>
                    </div>
                </div>

                <!-- Step 5: Keluhan & Catatan -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-[11px] font-extrabold text-white">5</span>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Keluhan &amp; Catatan Kendaraan</h2>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Keluhan atau Permintaan Khusus</label>
                        <textarea name="customer_complaints"
                                  rows="3"
                                  placeholder="Contoh: Bunyi berdecit saat rem ditekan pada kecepatan rendah, minta tolong periksa tekanan angin ban dan air wiper."
                                  class="w-full rounded-xl border border-slate-300 p-3 text-xs font-semibold text-slate-800 focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:outline-none">{{ old('customer_complaints') }}</textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Jelaskan gejala atau keluhan pada mobil agar Service Advisor kami dapat menyiapkan teknisi spesialis yang tepat.</p>
                    </div>
                </div>

            </div>

            <!-- Sidebar: Summary Card & Submit -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-5 sticky top-6">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                        Ringkasan Booking Servis
                    </h3>

                    <div class="space-y-3.5 text-xs">
                        <div>
                            <span class="text-[11px] text-slate-400 block font-medium">Kendaraan:</span>
                            <span class="font-bold text-slate-900 text-sm" x-text="vehicleName || 'Belum diisi'"></span>
                            <span class="inline-block mt-0.5 px-2 py-0.5 bg-slate-100 text-slate-700 font-mono text-[10px] font-bold rounded border border-slate-200" x-text="licensePlate || '-'"></span>
                        </div>

                        <div>
                            <span class="text-[11px] text-slate-400 block font-medium">Jadwal Kedatangan:</span>
                            <span class="font-bold text-slate-900" x-text="bookingDate || '-'"></span>
                            <span class="text-red-600 font-bold block" x-text="selectedSlot ? ('Pukul ' + selectedSlot + ' WIB') : 'Pilih slot waktu'"></span>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                            <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Garansi Servis Resmi Toyota:</span>
                            <ul class="text-[11px] text-slate-600 space-y-1 list-disc list-inside">
                                <li>100% Suku Cadang Toyota Asli (Genuine Parts)</li>
                                <li>Teknisi bersertifikasi resmi Toyota Motor</li>
                                <li>Riwayat servis tercatat digital &amp; terpusat</li>
                            </ul>
                        </div>
                    </div>

                    <button type="submit"
                            :disabled="!selectedSlot || !vehicleName || !licensePlate"
                            :class="(!selectedSlot || !vehicleName || !licensePlate) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-red-600 hover:bg-red-700 shadow-sm'"
                            class="w-full rounded-xl py-3 text-xs font-bold text-white transition flex items-center justify-center gap-2">
                        <span>Kirim Permintaan Booking</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                    <p class="text-[10px] text-slate-400 text-center">Permintaan booking akan langsung ditinjau oleh Service Advisor cabang terkait.</p>
                </div>
            </div>

        </form>

    </div>
</div>
@endsection

