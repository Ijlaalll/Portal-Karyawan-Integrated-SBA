@extends('layouts.app')

@section('title', 'Tambah Pengisian BBM - SBA System')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Tambah Pengisian BBM</h1>
            <p class="text-sm text-[#64748B] mt-1">Catat realisasi pengisian bahan bakar armada kendaraan operasional.</p>
        </div>
        <a href="{{ route('vehicles.fuel.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-semibold text-[#334155] shadow-xs hover:bg-[#F8FAFC] transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 p-4 border border-rose-200">
            <div class="flex items-center gap-2 text-sm font-semibold text-rose-800 mb-1">
                <span class="material-symbols-outlined text-[20px]">error</span>
                Terdapat kesalahan pengisian formulir:
            </div>
            <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
        <form action="{{ route('vehicles.fuel.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Kendaraan -->
            <div>
                <label for="vehicle_id" class="block text-sm font-semibold text-[#334155]">Kendaraan <span class="text-rose-500">*</span></label>
                <select name="vehicle_id" id="vehicle_id" required onchange="updateVehicleInfo(this)" class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="">-- Pilih Kendaraan --</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" 
                            data-fuel="{{ $vehicle->fuel_type }}" 
                            data-plate="{{ $vehicle->plate_number }}"
                            {{ old('vehicle_id', $selectedVehicleId) == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->name }} — {{ $vehicle->plate_number }} ({{ $vehicle->fuel_type }})
                        </option>
                    @endforeach
                </select>
                <div id="vehicle_fuel_badge" class="mt-2 text-xs text-[#64748B] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-slate-400">local_gas_station</span>
                    <span>Jenis Bahan Bakar: <strong id="vehicle_fuel_text" class="text-slate-800 font-semibold">-</strong></span>
                </div>
            </div>

            <!-- Tanggal & Waktu Pengisian -->
            <div>
                <label for="recorded_at" class="block text-sm font-semibold text-[#334155]">Tanggal & Waktu Pengisian <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="recorded_at" id="recorded_at" value="{{ old('recorded_at', now()->format('Y-m-d\TH:i')) }}" required class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] px-4 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                <p class="mt-1 text-xs text-[#64748B]">Dapat disesuaikan jika mencatat nota/struk pengisian lampau.</p>
            </div>

            <!-- Odometer & Jumlah BBM (Liter) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="odometer" class="block text-sm font-semibold text-[#334155]">Odometer (km) <span class="text-rose-500">*</span></label>
                    <div class="relative mt-1.5">
                        <input type="number" name="odometer" id="odometer" value="{{ old('odometer') }}" required min="0" placeholder="Contoh: 20500" class="no-spinner w-full rounded-xl border border-[#CBD5E1] px-4 py-2.5 pr-14 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-xs font-semibold text-[#64748B] pointer-events-none">KM</span>
                    </div>
                    <p class="mt-1 text-xs text-[#64748B]">Angka total kilometer saat pengisian.</p>
                </div>

                <div>
                    <label for="fuel_liters" class="block text-sm font-semibold text-[#334155]">Jumlah BBM (Liter) <span class="text-rose-500">*</span></label>
                    <div class="relative mt-1.5">
                        <input type="number" step="0.01" min="0.01" name="fuel_liters" id="fuel_liters" value="{{ old('fuel_liters') }}" required placeholder="Contoh: 45.5" class="no-spinner w-full rounded-xl border border-[#CBD5E1] px-4 py-2.5 pr-14 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-xs font-semibold text-[#64748B] pointer-events-none">Liter</span>
                    </div>
                    <p class="mt-1 text-xs text-[#64748B]">Volume BBM yang diisikan (harus > 0).</p>
                </div>
            </div>

            <!-- Biaya BBM (Opsional) -->
            <div>
                <label for="fuel_cost" class="block text-sm font-semibold text-[#334155]">Biaya BBM <span class="text-xs font-normal text-[#64748B]">(Opsional)</span></label>
                <div class="relative mt-1.5">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-semibold text-[#64748B] pointer-events-none">Rp</span>
                    <input type="number" min="0" name="fuel_cost" id="fuel_cost" value="{{ old('fuel_cost') }}" placeholder="Contoh: 450000" class="no-spinner w-full rounded-xl border border-[#CBD5E1] pl-10 pr-4 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                </div>
                <p class="mt-1 text-xs text-[#64748B]">Total rupiah yang dibayarkan. Hanya untuk pencatatan (tidak mempengaruhi hitungan km/L).</p>
            </div>

            <!-- Catatan -->
            <div>
                <label for="notes" class="block text-sm font-semibold text-[#334155]">Catatan <span class="text-xs font-normal text-[#64748B]">(Opsional)</span></label>
                <textarea name="notes" id="notes" rows="2" placeholder="Catatan tambahan, rute perjalanan, atau keterangan pengisian..." class="mt-1.5 w-full rounded-xl border border-[#CBD5E1] px-4 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">{{ old('notes') }}</textarea>
            </div>

            <!-- Info Box -->
            <div class="rounded-xl bg-blue-50/70 border border-blue-200 p-4 text-xs text-blue-800 flex items-start gap-2.5">
                <span class="material-symbols-outlined text-[18px] text-blue-600 shrink-0 mt-0.5">info</span>
                <div class="leading-relaxed">
                    <strong>Perhitungan Otomatis:</strong> Nilai konsumsi bahan bakar (<strong>km/L</strong>) akan dihitung secara otomatis oleh sistem dengan rumus: <code>(Odometer Sekarang - Odometer Sebelumnya) / Liter BBM</code>. Untuk pengisian pertama pada suatu kendaraan, nilai konsumsi akan berstatus <em>Belum tersedia</em> (—).
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('vehicles.fuel.index') }}" class="rounded-xl border border-[#CBD5E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#334155] shadow-xs hover:bg-[#F8FAFC] transition-colors">
                    Batal
                </a>
                <button type="submit" class="rounded-xl bg-[#2563EB] px-5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors cursor-pointer">
                    Simpan Pengisian BBM
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateVehicleInfo(selectEl) {
        const selected = selectEl.options[selectEl.selectedIndex];
        const fuel = selected ? selected.getAttribute('data-fuel') : null;
        document.getElementById('vehicle_fuel_text').textContent = fuel || '-';
    }

    function initFuelCreate() {
        const select = document.getElementById('vehicle_id');
        if (select) {
            updateVehicleInfo(select);
        }
    }

    if (window.onPageLoad) {
        window.onPageLoad(initFuelCreate);
    } else {
        document.addEventListener('DOMContentLoaded', initFuelCreate);
    }
</script>
<style>
    /* Hilangkan panah stepper spinner bawaan browser agar tidak menimpa badge teks satuan (KM/Liter) */
    input.no-spinner::-webkit-inner-spin-button,
    input.no-spinner::-webkit-outer-spin-button {
        -webkit-appearance: none !important;
        margin: 0 !important;
    }
    input.no-spinner {
        -moz-appearance: textfield !important;
    }
</style>
@endsection
