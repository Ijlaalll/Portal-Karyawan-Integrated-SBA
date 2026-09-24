@extends('layouts.app')

@section('title', 'Tarif Ruangan - Admin SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Cost Center Management
                </h1>
                <p class="text-sm text-[#64748B] mt-1">
                    Konfigurasi tarif sewa internal per jam untuk pemakaian ruang meeting.
                </p>
            </div>
        </div>
    </header>

    @include('admin.settings.cost_centers.tabs')

    <main class="space-y-6">
        @if (session('success'))
            <div class="rounded-lg bg-green-50 p-4 border border-green-200">
                <div class="flex">
                    <span class="material-symbols-outlined text-green-700 text-[20px] mr-2">check_circle</span>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <span class="material-symbols-outlined text-red-700 text-[20px] mr-2">error</span>
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Information Note -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
            <span class="material-symbols-outlined text-blue-600 text-[22px] mt-0.5">info</span>
            <div class="text-xs text-blue-900 leading-relaxed">
                <p class="font-bold">Ketentuan Snapshot Tarif Ruang Meeting:</p>
                <p class="mt-0.5">
                    Tarif dihitung otomatis berdasarkan durasi pemesanan (Durasi Jam × Tarif Per Jam). 
                    Setiap perubahan tarif hanya akan berlaku untuk reservasi baru dan <strong>tidak akan mengubah nilai transaksi historis</strong> yang sudah tercatat sebelumnya.
                </p>
            </div>
        </div>

        <!-- Room Pricing Table -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB]">
                    <thead class="bg-[#F9FAFB]">
                        <tr>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#64748B] uppercase tracking-wider">Nama Ruangan</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#64748B] uppercase tracking-wider">Lokasi & Kapasitas</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-[#64748B] uppercase tracking-wider">Tarif Per Jam</th>
                            <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-[#64748B] uppercase tracking-wider">Status Tarif</th>
                            <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-[#64748B] uppercase tracking-wider">Status Ruangan</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-[#64748B] uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm font-medium">
                        @forelse ($rooms as $room)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-bold text-[#0F172A]">{{ $room->name }}</div>
                                    @if($room->description)
                                        <div class="text-xs text-[#64748B] truncate max-w-xs">{{ $room->description }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-[#475569]">
                                    <div>{{ $room->location }}</div>
                                    <div class="text-xs text-[#64748B]">{{ $room->capacity }} Orang</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right font-black text-[#2563EB]">
                                    Rp {{ number_format((float)$room->price_per_hour, 0, ',', '.') }} / jam
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @if ($room->is_pricing_active)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                            Dikenakan Tarif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-500/20">
                                            Nonaktif (Bebas Biaya)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @if ($room->status === 'active')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-green-50 text-green-700">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                    <button type="button" 
                                        onclick="openPricingModal({{ $room->id }}, '{{ addslashes($room->name) }}', {{ (float)$room->price_per_hour }}, {{ $room->is_pricing_active ? '1' : '0' }})" 
                                        class="inline-flex items-center gap-1 bg-[#EFF6FF] text-[#2563EB] hover:bg-[#DBEAFE] px-3 py-1.5 rounded-md font-bold transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                        Ubah Tarif
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                    Belum ada data ruangan meeting.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Edit Pricing Modal -->
<div id="pricingModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white border border-[#E5E7EB] rounded-2xl w-full max-w-md p-6 shadow-xl flex flex-col space-y-4 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-lg font-bold text-[#0F172A] flex items-center gap-2">
                <span class="material-symbols-outlined text-[#2563EB]">sell</span>
                Atur Tarif Ruangan
            </h3>
            <button type="button" onclick="closePricingModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="pricingForm" method="POST" class="space-y-4 pt-1">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Ruangan</label>
                <input type="text" id="modalRoomName" readonly class="block w-full rounded-md border border-[#E5E7EB] bg-slate-50 px-3 py-2 text-[#0F172A] text-sm font-bold shadow-xs">
            </div>

            <div>
                <label for="price_per_hour" class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Tarif Per Jam (Rp)</label>
                <div class="relative rounded-md shadow-xs">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-gray-500 sm:text-sm font-bold">Rp</span>
                    </div>
                    <input type="number" name="price_per_hour" id="modalPricePerHour" min="0" step="1000" required
                        class="block w-full rounded-md border border-[#E5E7EB] pl-10 pr-3 py-2 text-[#0F172A] text-sm font-bold focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                </div>
            </div>

            <div>
                <label for="modalIsPricingActive" class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Status Penagihan Biaya</label>
                <select name="is_pricing_active" id="modalIsPricingActive" required
                    class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] text-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="1">Aktif (Dikenakan Tarif)</option>
                    <option value="0">Nonaktif (Bebas Biaya / Free)</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closePricingModal()" class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB] cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="rounded-lg bg-[#2563EB] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-[#2563EB] cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPricingModal(roomId, roomName, currentPrice, isPricingActive) {
        const modal = document.getElementById('pricingModal');
        const form = document.getElementById('pricingForm');
        const roomNameInput = document.getElementById('modalRoomName');
        const priceInput = document.getElementById('modalPricePerHour');
        const statusSelect = document.getElementById('modalIsPricingActive');

        form.action = '/admin/settings/cost-centers/pricing/' + roomId;
        roomNameInput.value = roomName;
        priceInput.value = currentPrice;
        statusSelect.value = isPricingActive ? '1' : '0';

        modal.classList.remove('hidden');
    }

    function closePricingModal() {
        document.getElementById('pricingModal').classList.add('hidden');
    }
</script>
@endsection
