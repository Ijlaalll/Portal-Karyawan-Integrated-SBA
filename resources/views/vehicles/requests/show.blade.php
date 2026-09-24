@extends('layouts.app')

@section('title', 'Detail Permohonan Kendaraan - SBA System')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('vehicles.requests.index') }}" class="p-2 text-[#64748B] hover:text-[#0F172A] hover:bg-slate-100 rounded-xl transition-colors">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-[#0F172A] font-mono">
                        {{ $vehicleRequest->request_number }}
                    </h1>
                    @if($vehicleRequest->status === 'pending_approval')
                        <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Menunggu Persetujuan
                        </span>
                    @elseif($vehicleRequest->status === 'approved')
                        <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Disetujui
                        </span>
                    @elseif($vehicleRequest->status === 'rejected')
                        <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            Ditolak
                        </span>
                    @elseif($vehicleRequest->status === 'completed')
                        <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                            Selesai
                        </span>
                    @elseif($vehicleRequest->status === 'cancelled')
                        <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                            Dibatalkan
                        </span>
                    @endif
                </div>
                <p class="text-xs text-[#64748B] mt-1">Diajukan pada {{ $vehicleRequest->created_at->format('d M Y, H:i') }} WIB</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($vehicleRequest->status === 'pending_approval' && $vehicleRequest->user_id === auth()->id())
                <form action="{{ route('vehicles.requests.cancel', $vehicleRequest) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan ini?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 px-3.5 py-2 text-xs font-semibold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors">
                        <span class="material-symbols-outlined text-[16px]">cancel</span>
                        Batalkan Permohonan
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-200">
            <div class="flex items-center gap-2 text-sm font-medium text-emerald-800">
                <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 p-4 border border-rose-200">
            <div class="flex items-center gap-2 text-sm font-semibold text-rose-800 mb-1">
                <span class="material-symbols-outlined text-[20px]">error</span>
                Terjadi kesalahan:
            </div>
            <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
                <h2 class="text-base font-bold text-[#0F172A] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#2563EB] text-[20px]">description</span>
                    Detail Perjalanan Dinas
                </h2>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold text-[#64748B]">Keperluan</dt>
                        <dd class="mt-1 font-medium text-[#0F172A] bg-slate-50 p-3 rounded-xl border border-slate-200">{{ $vehicleRequest->purpose }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold text-[#64748B]">Tujuan Perjalanan</dt>
                        <dd class="mt-1 font-semibold text-[#0F172A] flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-rose-500 text-[18px]">location_on</span>
                            {{ $vehicleRequest->destination }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#64748B]">Jadwal Berangkat</dt>
                        <dd class="mt-1 font-semibold text-[#0F172A]">{{ $vehicleRequest->start_datetime->format('d M Y, H:i') }} WIB</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#64748B]">Perkiraan Kembali</dt>
                        <dd class="mt-1 font-semibold text-[#0F172A]">{{ $vehicleRequest->end_datetime->format('d M Y, H:i') }} WIB</dd>
                    </div>

                    @if($vehicleRequest->status === 'pending_approval' && $vehicleRequest->vehicle)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold text-[#64748B]">Preferensi Kendaraan Pemohon</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0F172A] flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600 text-[18px]">directions_car</span>
                                <span>{{ $vehicleRequest->vehicle->name }} ({{ $vehicleRequest->vehicle->plate_number }})</span>
                                <span class="text-[10px] font-medium px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200">Preferensi Pemohon</span>
                            </dd>
                        </div>
                    @endif

                    @if($vehicleRequest->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold text-[#64748B]">Catatan Tambahan</dt>
                            <dd class="mt-1 text-xs text-[#334155]">{{ $vehicleRequest->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Approval & Rejection Details -->
            @if($vehicleRequest->status === 'rejected')
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-rose-800 flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-[20px] text-rose-600">block</span>
                        Alasan Penolakan
                    </h3>
                    <p class="text-sm text-rose-700 bg-white p-3 rounded-xl border border-rose-200 font-medium">
                        {{ $vehicleRequest->rejection_reason }}
                    </p>
                    @if($vehicleRequest->approver)
                        <p class="text-xs text-rose-600 mt-2">Ditolak oleh: {{ $vehicleRequest->approver->name }} pada {{ $vehicleRequest->approved_at?->format('d M Y, H:i') }} WIB</p>
                    @endif
                </div>
            @endif

            <!-- GA Operational Approval & Allocation Form -->
            @if($isGa && $vehicleRequest->status === 'pending_approval')
                <div class="bg-white border-2 border-blue-200 rounded-2xl p-6 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 text-[22px]">fact_check</span>
                            <h2 class="text-base font-bold text-[#0F172A]">Persetujuan & Penugasan Armada (GA)</h2>
                        </div>
                        <span class="text-xs font-medium px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-mono">Role: GA Operasional</span>
                    </div>
                    <p class="text-xs text-[#64748B] mb-5">Tentukan mobil dan supir operasional yang tersedia untuk jadwal ini, kemudian setujui permohonan.</p>

                    <form action="{{ route('vehicles.requests.approve', $vehicleRequest) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="vehicle_id" class="block text-xs font-semibold text-[#334155]">Pilih Mobil Operasional <span class="text-rose-500">*</span></label>
                            <select name="vehicle_id" id="vehicle_id" required class="mt-1 w-full rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none">
                                <option value="">-- Pilih Mobil yang Tersedia --</option>
                                @foreach($availableVehicles as $av)
                                    <option value="{{ $av->id }}" {{ $vehicleRequest->vehicle_id == $av->id ? 'selected' : '' }}>
                                        {{ $av->name }} ({{ $av->plate_number }}) - {{ $av->fuel_type }}
                                        {{ $vehicleRequest->vehicle_id == $av->id ? ' [Preferensi Pemohon]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @if($availableVehicles->isEmpty())
                                <p class="text-xs text-rose-600 mt-1">Tidak ada unit mobil yang berstatus available pada rentang jadwal ini.</p>
                            @endif
                        </div>

                        <div>
                            <label for="driver_id" class="block text-xs font-semibold text-[#334155]">
                                Pilih Supir Operasional <span class="text-xs font-normal text-[#64748B]">(Opsional - Kosongkan jika Lepas Kunci)</span>
                            </label>
                            <select name="driver_id" id="driver_id" class="mt-1 w-full rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none">
                                <option value="">-- Tanpa Supir (Lepas Kunci / Karyawan Menyetir Sendiri) --</option>
                                @foreach($availableDrivers as $ad)
                                    <option value="{{ $ad->id }}">
                                        {{ $ad->name }} ({{ $ad->phone_number }})
                                    </option>
                                @endforeach
                            </select>
                            @if($availableDrivers->isEmpty())
                                <p class="text-xs text-amber-600 mt-1">Tidak ada supir operasional yang berstatus available pada rentang jadwal ini. Anda tetap dapat menyetujui dengan opsi Lepas Kunci.</p>
                            @endif
                        </div>

                        <div class="pt-3 flex flex-col sm:flex-row items-center gap-3">
                            <button type="submit" @if($availableVehicles->isEmpty()) disabled class="w-full sm:flex-1 bg-slate-200 text-slate-400 font-semibold text-sm py-2.5 px-4 rounded-xl cursor-not-allowed flex items-center justify-center gap-1.5 transition-colors" @else class="w-full sm:flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm py-2.5 px-4 rounded-xl shadow-xs transition-colors flex items-center justify-center gap-1.5" @endif>
                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                Setujui & Tugaskan
                            </button>
                            <button type="button" onclick="openRejectModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 shadow-xs hover:bg-red-100 hover:border-red-300 transition-colors shrink-0">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                                Tolak Permohonan
                            </button>
                        </div>
                    </form>
                </div>
            @elseif($vehicleRequest->status === 'pending_approval')
                <!-- Pending Status Information Card for Requester / Non-GA -->
                <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-5 flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-600 text-[22px] mt-0.5 shrink-0">hourglass_top</span>
                    <div>
                        <h3 class="text-sm font-bold text-amber-900">Menunggu Proses Persetujuan dan Alokasi Armada</h3>
                        <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                            Permohonan ini telah tercatat dalam sistem dan sedang menunggu proses verifikasi serta alokasi armada oleh GA.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Details -->
        <div class="space-y-6">
            <!-- Requester Card -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs">
                <h3 class="text-xs font-bold text-[#64748B] uppercase tracking-wider mb-3">Informasi Pemohon</h3>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center">
                        {{ strtoupper(substr($vehicleRequest->user->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="font-bold text-[#0F172A] text-sm">{{ $vehicleRequest->user->name }}</div>
                        <div class="text-xs text-[#64748B]">{{ $vehicleRequest->user->department ?? '-' }}</div>
                        <div class="text-xs text-[#64748B]">{{ $vehicleRequest->user->email }}</div>
                    </div>
                </div>
            </div>

            <!-- Assigned Fleet Card -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs">
                <h3 class="text-xs font-bold text-[#64748B] uppercase tracking-wider mb-3">Alokasi Armada & Supir</h3>
                
                <div class="space-y-4 text-sm">
                    <div>
                        <div class="text-xs font-semibold text-[#64748B]">Mobil Ditugaskan:</div>
                        @if($vehicleRequest->vehicle)
                            <div class="font-bold text-[#0F172A] mt-1">{{ $vehicleRequest->vehicle->name }}</div>
                            <div class="font-mono text-xs text-blue-700 font-semibold">{{ $vehicleRequest->vehicle->plate_number }}</div>
                            <div class="text-xs text-[#64748B]">Bahan Bakar: {{ $vehicleRequest->vehicle->fuel_type }}</div>
                        @else
                            <div class="text-xs text-slate-400 italic mt-1">Belum dialokasikan</div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <div class="text-xs font-semibold text-[#64748B]">Supir Ditugaskan:</div>
                        @if($vehicleRequest->driver)
                            <div class="font-bold text-[#0F172A] mt-1">{{ $vehicleRequest->driver->name }}</div>
                            <div class="text-xs text-emerald-700 font-mono mt-0.5">{{ $vehicleRequest->driver->phone_number }}</div>
                        @elseif(in_array($vehicleRequest->status, ['approved', 'completed']))
                            <div class="inline-flex items-center gap-1 px-2.5 py-1 mt-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                <span class="material-symbols-outlined text-[14px]">key</span>
                                Lepas Kunci (Tanpa Supir)
                            </div>
                        @else
                            <div class="text-xs text-slate-400 italic mt-1">Belum dialokasikan</div>
                        @endif
                    </div>

                    @if($vehicleRequest->approver && $vehicleRequest->status === 'approved')
                        <div class="pt-3 border-t border-slate-100 text-xs text-[#64748B]">
                            <div>Disetujui oleh: <strong class="text-[#0F172A]">{{ $vehicleRequest->approver->name }}</strong></div>
                            <div>Waktu: {{ $vehicleRequest->approved_at?->format('d M Y, H:i') }} WIB</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($isGa && $vehicleRequest->status === 'pending_approval')
    <!-- Modal Penolakan Permohonan Kendaraan -->
    <div id="rejectVehicleModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#E2E8F0] space-y-4 transform transition-all">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5 text-red-600">
                    <div class="p-2 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">cancel</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A]">Tolak Permohonan Kendaraan</h3>
                        <p class="text-xs text-[#64748B]">Permohonan #{{ $vehicleRequest->request_number }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeRejectModal()" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form action="{{ route('vehicles.requests.reject', $vehicleRequest) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="rejection_reason" class="block text-xs font-semibold text-[#334155] mb-1.5">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="3" required placeholder="Tuliskan alasan mengapa permohonan ini tidak dapat disetujui (misal: unit kendaraan sedang dalam perawatan berkala)..." class="w-full rounded-xl border border-[#CBD5E1] p-3 text-sm text-[#0F172A] placeholder-[#94A3B8] focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeRejectModal()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-[#64748B] hover:bg-slate-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-red-700 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">block</span>
                        Tolak Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal() {
            const modal = document.getElementById('rejectVehicleModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }
        function closeRejectModal() {
            const modal = document.getElementById('rejectVehicleModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeRejectModal();
        });
    </script>
@endif
@endsection
