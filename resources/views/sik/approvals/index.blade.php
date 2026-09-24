@extends('layouts.app')

@section('title', 'Persetujuan SIK - HOD & Atasan SBA')

@section('content')
<div class="min-h-full">
    <!-- Header -->
    <header class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Antrean Persetujuan SIK
                </h1>
                <p class="text-sm text-[#475569] mt-1">Daftar permohonan izin keluar yang memerlukan persetujuan Anda sebagai HOD / Atasan.</p>
            </div>
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200 shadow-sm flex items-center gap-3">
                <span class="material-symbols-outlined text-green-600 text-[22px]">check_circle</span>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg bg-red-50 p-4 border border-red-200 shadow-sm flex items-center gap-3">
                <span class="material-symbols-outlined text-red-600 text-[22px]">error</span>
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Filter Search Bar -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 mb-6 shadow-sm">
            <form action="{{ route('sik.approvals.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pemohon, nomor SIK, atau keperluan..."
                        class="block w-full rounded-lg border border-[#E5E7EB] pl-10 pr-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-[#94A3B8] text-[20px]">search</span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-900 transition-colors">
                        Cari
                    </button>
                    @if(request()->filled('search') || request()->filled('department'))
                        <a href="{{ route('sik.approvals.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Approval Table List -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB] text-left text-sm">
                    <thead class="bg-[#F8FAFC] text-xs font-semibold text-[#475569] uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">Nomor & Pemohon</th>
                            <th scope="col" class="px-6 py-4">Departemen & Jabatan</th>
                            <th scope="col" class="px-6 py-4">Tanggal & Waktu</th>
                            <th scope="col" class="px-6 py-4">Keperluan</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi Persetujuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] bg-white">
                        @forelse ($pendingPermits as $permit)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-bold text-[#0F172A] font-mono text-xs">{{ $permit->sik_number }}</span>
                                    <div class="font-semibold text-sm text-slate-900 mt-0.5">{{ $permit->user->name }}</div>
                                    <div class="text-[11px] text-slate-500">ID: {{ $permit->employee_id ?: ($permit->user->employee_id ?: '-') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-900">{{ $permit->department }}</div>
                                    <div class="text-xs text-slate-500">{{ $permit->position ?: '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-900">{{ $permit->permit_date->format('d M Y') }}</div>
                                    <div class="text-xs text-blue-700 font-semibold mt-0.5">
                                        {{ $permit->expected_departure_time }} - {{ $permit->expected_return_time }} WIB
                                    </div>
                                </td>
                                <td class="px-6 py-4 max-w-[260px]">
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $permit->purpose_type === 'dinas' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-purple-100 text-purple-800 border border-purple-200' }}">
                                            {{ $permit->purpose_type }}
                                        </span>
                                        <p class="font-medium text-slate-800 text-xs leading-snug line-clamp-2" title="{{ $permit->purpose }}">
                                            {{ $permit->purpose }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium space-x-2">
                                    <a href="{{ route('sik.show', $permit) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold">
                                        Review
                                    </a>

                                    <button type="button" onclick="openApprove('{{ $permit->id }}', '{{ $permit->sik_number }}', '{{ $permit->user->name }}')" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700 font-semibold shadow-sm">
                                        Setujui
                                    </button>

                                    <button type="button" onclick="openReject('{{ $permit->id }}', '{{ $permit->sik_number }}', '{{ $permit->user->name }}')" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700 font-semibold shadow-sm">
                                        Tolak
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                    <span class="material-symbols-outlined text-4xl text-green-500 block mb-2">task_alt</span>
                                    Tidak ada pengajuan SIK yang sedang menunggu persetujuan Anda saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pendingPermits->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB]">
                    {{ $pendingPermits->links() }}
                </div>
            @endif
        </div>
    </main>
</div>

<!-- Modal Approve -->
<div id="approveModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center gap-2 text-green-600 font-bold text-lg">
            <span class="material-symbols-outlined">check_circle</span>
            Setujui Pengajuan SIK
        </div>
        <p class="text-xs text-slate-600">Apakah Anda yakin menyetujui pengajuan SIK <strong id="approveSikNum"></strong> dari <strong id="approveUser"></strong>?</p>
        <form id="approveForm" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Persetujuan (Opsional)</label>
                <textarea name="approval_note" rows="2" placeholder="Tuliskan catatan jika ada..." class="w-full rounded-lg border border-slate-300 p-2 text-xs focus:ring-1 focus:ring-green-500"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeModal('approveModal')" class="px-4 py-2 rounded-lg border text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-xs font-semibold text-white hover:bg-green-700">Setujui SIK</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center gap-2 text-red-600 font-bold text-lg">
            <span class="material-symbols-outlined">cancel</span>
            Tolak Pengajuan SIK
        </div>
        <p class="text-xs text-slate-600">Tolak pengajuan SIK <strong id="rejectSikNum"></strong> dari <strong id="rejectUser"></strong>.</p>
        <form id="rejectForm" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="rejection_reason" required rows="3" placeholder="Tuliskan alasan penolakan secara jelas..." class="w-full rounded-lg border border-slate-300 p-2 text-xs focus:ring-1 focus:ring-red-500"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeModal('rejectModal')" class="px-4 py-2 rounded-lg border text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-xs font-semibold text-white hover:bg-red-700">Tolak SIK</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openApprove(id, sikNum, userName) {
        document.getElementById('approveSikNum').textContent = '#' + sikNum;
        document.getElementById('approveUser').textContent = userName;
        document.getElementById('approveForm').action = "/sik-management/approvals/" + id + "/approve";
        document.getElementById('approveModal').classList.remove('hidden');
    }

    function openReject(id, sikNum, userName) {
        document.getElementById('rejectSikNum').textContent = '#' + sikNum;
        document.getElementById('rejectUser').textContent = userName;
        document.getElementById('rejectForm').action = "/sik-management/approvals/" + id + "/reject";
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection
