@extends('layouts.public')

@section('title', 'Form Pengajuan Pembiayaan Leasing — Toyota AutoConnect')

@section('content')
@include('customer.partials.subnav')

<div class="bg-slate-50 min-h-[75vh] py-8 sm:py-10"
     x-data="{
        vehicles: {{ Js::from($vehicles) }},
        providers: {{ Js::from($providers) }},
        selectedVehicleId: '{{ old('vehicle_id', $selectedVehicle->id ?? ($vehicles->first()->id ?? '')) }}',
        selectedProviderId: '{{ old('leasing_provider_id', $selectedSimulation->leasing_provider_id ?? ($providers->first()->id ?? '')) }}',
        downPayment: {{ old('down_payment', $selectedSimulation->down_payment ?? 70000000) }},
        tenorMonths: {{ old('tenor_months', $selectedSimulation->tenor_months ?? 36) }},
        
        get currentVehicle() {
            return this.vehicles.find(v => v.id == this.selectedVehicleId) || this.vehicles[0];
        },
        get currentProvider() {
            return this.providers.find(p => p.id == this.selectedProviderId) || this.providers[0];
        },
        get vehiclePrice() {
            return this.currentVehicle ? this.currentVehicle.price : 0;
        },
        get interestRate() {
            return this.currentProvider ? parseFloat(this.currentProvider.base_interest_rate) : 6.0;
        },
        get financingAmount() {
            return Math.max(0, this.vehiclePrice - this.downPayment);
        },
        get totalInterest() {
            return Math.round(this.financingAmount * (this.interestRate / 100) * (this.tenorMonths / 12));
        },
        get totalCreditPayment() {
            return this.financingAmount + this.totalInterest;
        },
        get monthlyInstallment() {
            return this.tenorMonths > 0 ? Math.round(this.totalCreditPayment / this.tenorMonths) : 0;
        },
        formatRupiah(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        },
        init() {
            if (!this.downPayment && this.vehiclePrice) {
                this.downPayment = Math.round(this.vehiclePrice * 0.2);
            }
        }
     }">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('customer.leasing.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Pengajuan
            </a>
            <span class="text-xs text-slate-400 font-medium">Langkah 1 dari 1: Formulir Pengajuan</span>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Formulir Pengajuan Pembiayaan Leasing</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kirimkan data awal permohonan kredit kendaraan Toyota Anda langsung ke lembaga pembiayaan rekanan</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl bg-rose-50 p-4 border border-rose-200 text-xs text-rose-800 space-y-1">
                <span class="font-bold block">Mohon perbaiki kesalahan berikut:</span>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('customer.leasing.store') }}" method="POST" class="space-y-6">
            @csrf

            @if ($selectedSimulation)
                <input type="hidden" name="credit_simulation_id" value="{{ $selectedSimulation->id }}">
            @endif

            <!-- Section 1: Unit & Pembiayaan -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-6 sm:p-7 space-y-5">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] text-white font-bold">1</span>
                    Pilihan Unit &amp; Skema Pembiayaan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Vehicle Select -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Model Kendaraan Toyota *</label>
                        <select name="vehicle_id" x-model="selectedVehicleId"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                            @foreach ($vehicles as $v)
                                <option value="{{ $v->id }}">
                                    {{ $v->name }} ({{ $v->category }}) — Rp {{ number_format($v->price, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Leasing Provider Select -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Lembaga Pembiayaan / Leasing Rekanan *</label>
                        <select name="leasing_provider_id" x-model="selectedProviderId"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                            @foreach ($providers as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->name }} (Bunga Mulai {{ $p->base_interest_rate }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Down Payment Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nominal Uang Muka (DP) *</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="down_payment" x-model.number="downPayment" min="1000000" step="1000000"
                                   class="w-full rounded-xl border border-slate-300 pl-10 pr-3.5 py-2.5 text-xs font-mono font-bold text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                        </div>
                        <div class="flex items-center gap-1.5 mt-2">
                            <button type="button" @click="downPayment = Math.round(vehiclePrice * 0.20)" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">DP 20%</button>
                            <button type="button" @click="downPayment = Math.round(vehiclePrice * 0.25)" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">DP 25%</button>
                            <button type="button" @click="downPayment = Math.round(vehiclePrice * 0.30)" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">DP 30%</button>
                            <button type="button" @click="downPayment = Math.round(vehiclePrice * 0.40)" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">DP 40%</button>
                        </div>
                    </div>

                    <!-- Tenor Select -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jangka Waktu Tenor *</label>
                        <select name="tenor_months" x-model.number="tenorMonths"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                            <option value="12">12 Bulan (1 Tahun)</option>
                            <option value="24">24 Bulan (2 Tahun)</option>
                            <option value="36">36 Bulan (3 Tahun)</option>
                            <option value="48">48 Bulan (4 Tahun)</option>
                            <option value="60">60 Bulan (5 Tahun)</option>
                            <option value="72">72 Bulan (6 Tahun)</option>
                        </select>
                    </div>
                </div>

                <!-- Live Summary Calculation Box -->
                <div class="rounded-xl bg-slate-900 p-4 text-white space-y-2">
                    <div class="flex items-center justify-between text-xs border-b border-slate-800 pb-2">
                        <span class="text-slate-400">Harga Unit OTR:</span>
                        <span class="font-mono font-bold" x-text="formatRupiah(vehiclePrice)"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs border-b border-slate-800 pb-2">
                        <span class="text-slate-400">Uang Muka (DP):</span>
                        <span class="font-mono font-bold text-emerald-400" x-text="formatRupiah(downPayment)"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs border-b border-slate-800 pb-2">
                        <span class="text-slate-400">Pokok Pembiayaan:</span>
                        <span class="font-mono font-bold" x-text="formatRupiah(financingAmount)"></span>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <div>
                            <span class="text-[11px] text-slate-400 block">Estimasi Angsuran per Bulan:</span>
                            <div class="text-lg font-extrabold text-red-500 font-mono mt-0.5" x-text="formatRupiah(monthlyInstallment)"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 text-right">
                            *Suku Bunga Acuan: <span x-text="interestRate"></span>% / thn
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Data Pemohon -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-6 sm:p-7 space-y-5">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] text-white font-bold">2</span>
                    Informasi Data Pemohon (Verifikasi Analis)
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap (Sesuai KTP)</label>
                        <input type="text" value="{{ auth()->user()->name }}" disabled
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-600 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Kontak</label>
                        <input type="email" value="{{ auth()->user()->email }}" disabled
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-600 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Pekerjaan / Profesi *</label>
                        <select name="employment_type" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                            <option value="Karyawan Swasta" {{ old('employment_type') === 'Karyawan Swasta' ? 'selected' : '' }}>Karyawan Swasta</option>
                            <option value="Pegawai Negeri Sipil / BUMN" {{ old('employment_type') === 'Pegawai Negeri Sipil / BUMN' ? 'selected' : '' }}>Pegawai Negeri Sipil / BUMN</option>
                            <option value="Wiraswasta / Pengusaha" {{ old('employment_type') === 'Wiraswasta / Pengusaha' ? 'selected' : '' }}>Wiraswasta / Pengusaha</option>
                            <option value="Profesional (Dokter/Pengacara/Konsultan)" {{ old('employment_type') === 'Profesional (Dokter/Pengacara/Konsultan)' ? 'selected' : '' }}>Profesional (Dokter/Pengacara/Konsultan)</option>
                            <option value="Lainnya" {{ old('employment_type') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Estimasi Penghasilan Bulanan (Rp) *</label>
                        <input type="number" name="monthly_income" value="{{ old('monthly_income', 20000000) }}" min="1000000" step="500000" required
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Perusahaan / Tempat Usaha</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Contoh: PT Astra Graphia Tbk"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor KTP / NIK (Opsional)</label>
                        <input type="text" name="id_card_number" value="{{ old('id_card_number') }}" placeholder="16 digit NIK KTP"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Pernikahan</label>
                        <select name="marital_status" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                            <option value="Menikah">Menikah</option>
                            <option value="Belum Menikah">Belum Menikah</option>
                            <option value="Cerai">Cerai</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Pokok Wajib Pajak (NPWP)</label>
                        <input type="text" name="npwp_number" value="{{ old('npwp_number') }}" placeholder="Nomor NPWP"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Tambahan untuk Analis Leasing</label>
                    <textarea name="notes" rows="2" placeholder="Catatan preferensi waktu kontak, dokumen tambahan, atau informasi terkait..."
                              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-red-600 focus:ring-1 focus:ring-red-600">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Agreement & Terms -->
            <div class="rounded-2xl bg-white border border-slate-200 shadow-2xs p-6 space-y-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="agree_terms" value="1" {{ old('agree_terms') ? 'checked' : '' }} required
                           class="rounded border-slate-300 text-red-600 focus:ring-red-500 mt-0.5 h-4 w-4">
                    <span class="text-xs text-slate-600 leading-relaxed">
                        Saya menyatakan data yang diisi adalah benar dan menyetujui data permohonan pembiayaan ini diteruskan kepada pihak lembaga pembiayaan/leasing rekanan resmi Toyota AutoConnect untuk keperluan analisa kelayakan kredit.
                    </span>
                </label>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('customer.leasing.index') }}"
                       class="rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
                        Kirimkan Pengajuan Pembiayaan
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

