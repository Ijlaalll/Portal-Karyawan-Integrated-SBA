@extends('layouts.app')

@section('title', 'Master ATK - SBA')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Master ATK
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Kelola master data barang, varian, dan kategori alat tulis kantor.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" onclick="openImportAtkModal()" class="border border-[#CBD5E1] text-[#334155] bg-white rounded px-4 py-2 font-semibold text-sm flex items-center gap-1.5 hover:bg-slate-50 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-[#64748B]">upload_file</span> Import ATK
            </button>
            <a href="{{ route('atk.categories.index') }}" class="border border-[#D1D5DB] text-slate-700 bg-white rounded px-4 py-2 font-semibold text-sm flex items-center gap-1.5 hover:bg-slate-50 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[18px]">category</span> Kelola Kategori
            </a>
            <a href="{{ route('atk.items.create') }}" class="bg-[#2563EB] text-white rounded px-4 py-2 font-semibold text-sm flex items-center gap-1.5 hover:bg-[#1D4ED8] transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span> Tambah Barang
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 p-4 border border-emerald-200 text-sm text-emerald-800 flex items-start gap-2.5 shadow-xs">
            <span class="material-symbols-outlined text-emerald-600 text-[20px] shrink-0 mt-0.5">check_circle</span>
            <div class="flex-1 font-medium">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl bg-red-50 p-4 border border-red-200 text-sm text-red-800 flex items-start gap-2.5 shadow-xs">
            <span class="material-symbols-outlined text-red-600 text-[20px] shrink-0 mt-0.5">error</span>
            <div class="flex-1 font-medium">{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl bg-red-50 p-4 border border-red-200 text-sm text-red-800 shadow-xs space-y-1.5">
            <div class="font-bold flex items-center gap-2 text-red-900">
                <span class="material-symbols-outlined text-red-600 text-[20px]">error</span>
                <span>Terjadi kendala saat memproses:</span>
            </div>
            <ul class="list-disc list-inside text-xs text-red-700 pl-6 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#475569]">
                <thead>
                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                        <th class="py-3.5 px-6">Nama Barang</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Kategori</th>
                        <th class="py-3.5 px-6">Deskripsi</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Jumlah Varian</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Status</th>
                        <th class="py-3.5 px-6 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6 font-semibold text-[#0F172A]">
                                <div class="flex items-center gap-3">
                                    @if($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-lg object-contain bg-slate-50 border border-slate-200 shrink-0 p-0.5">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-400 border border-slate-200 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('atk.items.show', $item) }}" class="hover:text-[#2563EB] hover:underline block truncate">{{ $item->name }}</a>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="inline-flex items-center w-fit rounded-lg bg-[#EFF6FF] px-2.5 py-1 text-xs font-semibold text-[#2563EB] ring-1 ring-inset ring-[#2563EB]/15 whitespace-nowrap">
                                    {{ $item->category->name }}
                                </span>
                            </td>
                            <td class="py-4 px-6 max-w-xs truncate" title="{{ $item->description }}">{{ $item->description ?: '-' }}</td>
                            <td class="py-4 px-6 font-semibold text-slate-700 whitespace-nowrap">{{ $item->variants->count() }} varian</td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($item->is_active)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right flex items-center justify-end gap-x-2">
                                <a href="{{ route('atk.items.show', $item) }}" class="text-xs font-bold text-green-700 hover:text-green-800 bg-green-50 px-2.5 py-1.5 rounded transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">visibility</span> Detail
                                </a>
                                <a href="{{ route('atk.items.edit', $item) }}" class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] bg-[#EFF6FF] px-2.5 py-1.5 rounded transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">edit</span> Edit
                                </a>
                                <form action="{{ route('atk.items.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini? Penghapusan akan diblokir jika terdapat varian di dalamnya.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 px-2.5 py-1.5 rounded transition-colors flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">delete</span> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center italic text-slate-400">Belum ada data barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB] bg-[#F9FAFB]">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Import CSV Master ATK -->
<div id="import-atk-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="import-atk-modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeImportAtkModal()"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 border border-slate-100">
            <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">upload_file</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A]" id="import-atk-modal-title">
                            Import Master ATK (Excel / CSV)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Unggah file Microsoft Excel (.xlsx) atau CSV (.csv) untuk mendaftarkan barang, kategori, dan stok awal secara massal.</p>
                    </div>
                </div>
                <button type="button" onclick="closeImportAtkModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Download Template Box -->
            <div class="mt-4 p-3.5 sm:p-4 bg-gradient-to-br from-blue-50/90 to-slate-50 border border-blue-100 rounded-xl space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3 min-w-0 flex-1">
                        <div class="h-9 w-9 rounded-lg bg-blue-100/90 text-[#2563EB] flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[20px]">table_view</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-xs font-bold text-[#0F172A] leading-tight">Template Master ATK (Excel)</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed mt-0.5">Format file sudah dioptimalkan agar langsung terbagi rapi per kolom di Microsoft Excel.</p>
                        </div>
                    </div>
                    <a href="{{ route('atk.items.template') }}" 
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-[0.98] text-white rounded-xl text-xs font-bold shadow-sm hover:shadow transition-all shrink-0 w-full sm:w-auto text-center"
                       title="Unduh file template yang siap diisi langsung di Microsoft Excel">
                        <span class="material-symbols-outlined text-[17px]">download</span>
                        <span>Unduh Template Excel</span>
                    </a>
                </div>

                <!-- Guidance tip for Excel and numbers -->
                <div class="p-2.5 sm:p-3 bg-amber-50/90 border border-amber-200/80 rounded-xl text-xs text-amber-900 leading-relaxed flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-amber-600 text-[18px] shrink-0 mt-0.5">lightbulb</span>
                    <div class="min-w-0 flex-1 text-[11px] text-amber-900">
                        <p class="font-bold text-amber-950">Tips Pengisian Data di Excel:</p>
                        <p class="text-[10.5px] text-amber-800/90 mt-0.5">
                            Tulis nominal angka pada <code class="bg-amber-100/80 text-amber-950 px-1 py-0.5 rounded font-mono">harga_beli</code>, <code class="bg-amber-100/80 text-amber-950 px-1 py-0.5 rounded font-mono">stok_awal</code>, dan <code class="bg-amber-100/80 text-amber-950 px-1 py-0.5 rounded font-mono">stok_minimum</code> hanya berupa angka murni tanpa simbol mata uang (contoh: <code class="bg-amber-100/80 text-amber-950 px-1 py-0.5 rounded font-mono font-bold">48000</code>, bukan <code class="bg-amber-100/80 text-amber-950 px-1 py-0.5 rounded font-mono line-through">Rp 48.000</code>). Anda dapat menyimpan dan mengunggah langsung file dalam format <span class="font-bold text-amber-950">.xlsx</span> (Excel biasa).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <form action="{{ route('atk.items.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Pilih File Excel (.xlsx) atau CSV (.csv) <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv,.txt" required
                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100 border border-slate-200 rounded-xl cursor-pointer bg-slate-50/50 p-1.5 focus:outline-none focus:border-[#2563EB]">
                    <p class="text-[10px] text-slate-400 mt-1">Mendukung format Microsoft Excel (.xlsx) atau CSV (.csv), maksimal 10MB.</p>
                    @error('file')
                        <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="update_existing" id="update_existing_atk" value="1" checked
                        class="rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB] h-4 w-4">
                    <label for="update_existing_atk" class="text-xs text-slate-600 select-none">
                        Perbarui data dan stok jika barang/varian sudah ada
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                    <button type="button" onclick="closeImportAtkModal()" class="w-full sm:w-auto px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors text-center">
                        Batal
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-[0.98] text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">cloud_upload</span>
                        Unggah & Impor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openImportAtkModal() {
        document.getElementById('import-atk-modal').classList.remove('hidden');
    }

    function closeImportAtkModal() {
        document.getElementById('import-atk-modal').classList.add('hidden');
    }

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openImportAtkModal();
        });
    @endif
</script>
@endsection
