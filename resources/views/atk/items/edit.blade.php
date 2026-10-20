@extends('layouts.app')

@section('title', 'Edit Barang ATK - SBA')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <header class="flex items-center gap-4">
        <a href="{{ route('atk.items.index') }}" class="text-slate-600 hover:text-slate-900 transition-colors">
            <span class="material-symbols-outlined text-[28px] align-middle">arrow_back</span>
        </a>
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Edit Barang ATK
            </h1>
            <p class="text-sm text-[#475569] mt-0.5">
                Perbarui detail barang {{ $item->name }}.
            </p>
        </div>
    </header>

    @if($errors->any())
        <div class="rounded-md bg-red-50 p-4 border border-red-200 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm">
        <form action="{{ route('atk.items.update', $item) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Category Select -->
            <div class="space-y-2">
                <label for="category_id" class="block text-sm font-semibold text-[#0F172A]">Kategori ATK <span class="text-red-500">*</span></label>
                <select name="category_id" id="category_id" required
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Name Input -->
            <div class="space-y-2">
                <label for="name" class="block text-sm font-semibold text-[#0F172A]">Nama Barang <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}" required placeholder="Contoh: Kertas A4 80gr"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
            </div>

            <!-- Description Input -->
            <div class="space-y-2">
                <label for="description" class="block text-sm font-semibold text-[#0F172A]">Deskripsi Barang</label>
                <textarea name="description" id="description" rows="4" placeholder="Tulis deskripsi detail tentang barang ini..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">{{ old('description', $item->description) }}</textarea>
            </div>

            <!-- Item Image Management (Non-Mandatory / Opsional) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-semibold text-[#0F172A]">
                        Foto Barang <span class="text-xs font-normal text-slate-500">(Opsional / Tidak Wajib)</span>
                    </label>
                    <span class="inline-flex items-center text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                        JPG, PNG, WEBP (Maks. 2MB)
                    </span>
                </div>

                @if($item->image_url)
                    <!-- Current Image Card -->
                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-16 h-16 rounded-lg bg-white border border-slate-200 overflow-hidden shrink-0 p-0.5">
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-contain">
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-[#0F172A] truncate">Foto Saat Ini</p>
                                <p class="text-[11px] text-slate-500 truncate">Foto ini aktif ditampilkan pada katalog user</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <input type="checkbox" name="remove_image" id="remove_image" value="1"
                                class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <label for="remove_image" class="text-xs font-semibold text-red-600 select-none cursor-pointer">
                                Hapus Foto Ini
                            </label>
                        </div>
                    </div>
                @endif

                <!-- Upload New Image Dropzone -->
                <div id="image_upload_container" class="relative border-2 border-dashed border-slate-300 hover:border-[#2563EB] rounded-xl p-5 bg-slate-50/60 hover:bg-blue-50/20 transition-all text-center cursor-pointer group">
                    <input type="file" name="image" id="image_input" accept="image/jpeg,image/png,image/jpg,image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewEditImage(this)">
                    
                    <!-- Placeholder State -->
                    <div id="image_placeholder_state" class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-[#2563EB] flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-[24px]">cloud_upload</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#0F172A]"><span class="text-[#2563EB]">{{ $item->image_url ? 'Unggah foto baru untuk mengganti' : 'Pilih file foto' }}</span> atau tarik ke sini</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Format JPG, PNG, WEBP maksimal 2MB</p>
                        </div>
                    </div>

                    <!-- Preview State (Initially Hidden) -->
                    <div id="image_preview_state" class="hidden flex flex-col items-center justify-center space-y-3">
                        <div class="relative w-36 h-36 rounded-xl overflow-hidden border border-slate-200 bg-white shadow-xs p-1">
                            <img id="image_preview_img" src="#" alt="Preview Foto Barang Baru" class="w-full h-full object-contain rounded-lg">
                        </div>
                        <div class="flex items-center gap-3">
                            <span id="image_filename" class="text-xs font-semibold text-slate-600 truncate max-w-xs"></span>
                            <button type="button" onclick="clearEditImagePreview(event)" class="z-20 text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded-lg transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">delete</span> Batal Foto Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status (is_active) Input -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#0F172A]">Status</label>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="1" {{ old('is_active', $item->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                            class="h-4 w-4 border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                        <span class="text-sm font-medium text-slate-700">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="0" {{ old('is_active', $item->is_active ? '1' : '0') == '0' ? 'checked' : '' }}
                            class="h-4 w-4 border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                        <span class="text-sm font-medium text-slate-700">Tidak Aktif</span>
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('atk.items.index') }}" class="px-4 py-2 border border-gray-300 rounded text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-[#2563EB] text-white rounded text-sm font-semibold hover:bg-[#1D4ED8] transition-colors shadow-sm">
                    Perbarui Barang
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewEditImage(input) {
        const file = input.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran gambar melebihi batas 2MB. Silakan pilih gambar yang lebih kecil.');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image_preview_img').src = e.target.result;
                document.getElementById('image_filename').textContent = file.name;
                document.getElementById('image_placeholder_state').classList.add('hidden');
                document.getElementById('image_preview_state').classList.remove('hidden');

                // If user selected to remove existing image, uncheck it since they uploaded a replacement
                const removeCheckbox = document.getElementById('remove_image');
                if (removeCheckbox) {
                    removeCheckbox.checked = false;
                }
            };
            reader.readAsDataURL(file);
        }
    }

    function clearEditImagePreview(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const input = document.getElementById('image_input');
        input.value = '';
        document.getElementById('image_preview_img').src = '#';
        document.getElementById('image_preview_state').classList.add('hidden');
        document.getElementById('image_placeholder_state').classList.remove('hidden');
    }
</script>
@endsection
