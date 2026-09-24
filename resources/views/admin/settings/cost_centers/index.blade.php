@extends('layouts.app')

@section('title', 'Manajemen Cost Center - SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Cost Center Management
                </h1>
                <p class="text-sm text-[#64748B] mt-1">
                    Daftar unit Cost Center dan alokasi biaya departemen.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.settings.cost-centers.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">
                    <span class="material-symbols-outlined text-[18px]">add</span> Tambah Cost Center
                </a>
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

        <!-- Summary Metric Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-xs flex items-center gap-4">
                <div class="p-3 rounded-lg bg-[#EFF6FF] text-[#2563EB]">
                    <span class="material-symbols-outlined text-[24px]">payments</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Total Cost Center</p>
                    <p class="text-xl font-bold text-[#0F172A] mt-0.5">{{ $totalCostCenters }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-xs flex items-center gap-4">
                <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600">
                    <span class="material-symbols-outlined text-[24px]">verified</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status Aktif</p>
                    <p class="text-xl font-bold text-[#0F172A] mt-0.5">{{ $activeCount }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-xs flex items-center gap-4">
                <div class="p-3 rounded-lg bg-slate-100 text-slate-600">
                    <span class="material-symbols-outlined text-[24px]">cancel</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Nonaktif</p>
                    <p class="text-xl font-bold text-[#0F172A] mt-0.5">{{ $inactiveCount }}</p>
                </div>
            </div>
        </div>

        <!-- Search bar -->
        <form action="{{ route('admin.settings.cost-centers.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1 max-w-md">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode, nama, atau penanggung jawab..."
                    class="block w-full rounded-md border border-[#E5E7EB] bg-white pl-10 pr-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-[#94A3B8] text-[20px]">search</span>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                @if(!empty($search))
                    <a href="{{ route('admin.settings.cost-centers.index') }}" class="text-sm text-[#64748B] hover:text-[#0F172A] font-medium underline transition-all">
                        Reset Filter
                    </a>
                @endif
                <button type="submit" class="hidden">Cari</button>
            </div>
        </form>

        <!-- Cost Centers Table -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB]">
                    <thead class="bg-[#F9FAFB]">
                        <tr>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#64748B] uppercase tracking-wider">Kode</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#64748B] uppercase tracking-wider">Nama / Deskripsi</th>
                            <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-[#64748B] uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-[#64748B] uppercase tracking-wider">Pengguna</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-[#64748B] uppercase tracking-wider">Total Pengeluaran</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#64748B] uppercase tracking-wider">Penanggung Jawab</th>
                            <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-[#64748B] uppercase tracking-wider">Company</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-[#64748B] uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm font-medium">
                        @forelse ($costCenters as $cc)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap font-bold text-[#2563EB]">
                                    {{ $cc->cost_center_code }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-[#0F172A]">{{ $cc->name ?: $cc->description }}</div>
                                    @if($cc->name && $cc->name !== $cc->description)
                                        <div class="text-xs text-[#64748B] mt-0.5">{{ $cc->description }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @if ($cc->status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-500/20">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center text-[#64748B]">
                                    <span class="inline-flex items-center gap-1 font-semibold">
                                        <span class="material-symbols-outlined text-[16px] text-slate-400">group</span>
                                        {{ $cc->users_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right font-bold text-[#0F172A]">
                                    Rp {{ number_format((float)$cc->transactions_sum_total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-[#475569]">
                                    {{ $cc->person_responsible }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center rounded-md bg-[#EFF6FF] px-2 py-0.5 text-xs font-bold text-[#2563EB] ring-1 ring-inset ring-[#2563EB]/20">
                                        {{ $cc->company_code }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.settings.cost-centers.edit', $cc) }}" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold text-[#2563EB] bg-blue-50/80 hover:bg-blue-100/80 ring-1 ring-inset ring-blue-500/20 transition-all duration-150 shadow-xs hover:shadow-sm"
                                           title="Edit Cost Center">
                                            <span class="material-symbols-outlined text-[15px]">edit</span>
                                            <span>Edit</span>
                                        </a>
                                        <button type="button" 
                                                onclick="openDeleteModal({{ $cc->id }}, '{{ addslashes($cc->cost_center_code) }}')" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold text-rose-600 bg-rose-50/80 hover:bg-rose-100/80 ring-1 ring-inset ring-rose-500/20 transition-all duration-150 shadow-xs hover:shadow-sm cursor-pointer"
                                                title="Hapus Cost Center">
                                            <span class="material-symbols-outlined text-[15px]">delete</span>
                                            <span>Hapus</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400 italic">
                                    Belum ada data Cost Center.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $costCenters->links() }}
        </div>
    </main>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white border border-[#E5E7EB] rounded-2xl w-full max-w-md p-6 shadow-xl flex flex-col space-y-4 animate-in fade-in zoom-in duration-200">
        <div class="text-center">
            <span class="material-symbols-outlined text-[40px] text-red-500 mb-2">warning</span>
            <h3 class="text-lg font-bold text-[#0F172A]">Hapus Cost Center</h3>
            <p class="text-xs text-slate-500 mt-1">Apakah Anda yakin ingin menghapus Cost Center <span id="deleteCcName" class="font-bold"></span>? Tindakan ini tidak dapat dibatalkan jika tidak ada riwayat transaksi.</p>
        </div>

        <form id="deleteForm" method="POST" class="flex flex-col gap-2 pt-2">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full bg-red-600 text-white text-xs font-bold py-2.5 rounded-lg hover:bg-red-700 transition-colors shadow-sm text-center cursor-pointer">
                Hapus Permanen
            </button>
            <button type="button" onclick="closeDeleteModal()" class="w-full border border-gray-200 text-xs font-bold py-2.5 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors shadow-sm text-center cursor-pointer">
                Batal
            </button>
        </form>
    </div>
</div>

<script>
    function openDeleteModal(id, code) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const ccName = document.getElementById('deleteCcName');

        ccName.textContent = code;
        form.action = '/admin/settings/cost-centers/' + id;
        modal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>
@endsection
