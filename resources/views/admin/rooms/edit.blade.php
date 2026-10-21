@extends('layouts.app')

@section('title', 'Edit Ruangan - Admin SBA')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Edit Ruangan: {{ $room->name }}
        </h1>
        <a href="{{ route('admin.rooms.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            Kembali ke Ruangan
        </a>
    </header>
    
    <main>
        @if ($errors->any())
            <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Kesalahan dalam pengisian formulir:</h3>
                        <ul class="list-disc pl-5 mt-2 text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg p-6 sm:p-8 shadow-sm">
            <form action="{{ route('admin.rooms.update', $room) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#0F172A]">Nama Ruangan</label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $room->name) }}"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-[#0F172A]">Lokasi</label>
                        <input type="text" name="location" id="location" required value="{{ old('location', $room->location) }}"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <div>
                        <label for="capacity" class="block text-sm font-medium text-[#0F172A]">Kapasitas (orang)</label>
                        <input type="number" name="capacity" id="capacity" required min="1" value="{{ old('capacity', $room->capacity) }}"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-[#0F172A]">Deskripsi</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">{{ old('description', $room->description) }}</textarea>
                    </div>

                    <!-- Foto Ruangan -->
                    <div>
                        <label class="block text-sm font-medium text-[#0F172A] mb-1">
                            Foto Ruangan <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                        </label>

                        @if($room->image_url)
                            <div class="mb-3 p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-4">
                                <img src="{{ $room->image_url }}" alt="{{ $room->name }}" class="h-20 w-28 object-cover rounded-lg border border-slate-200 shrink-0">
                                <div class="space-y-1">
                                    <p class="text-xs font-bold text-[#0F172A]">Foto Ruangan Saat Ini</p>
                                    <p class="text-[11px] text-slate-500">Anda dapat mengunggah file baru di bawah untuk mengganti foto ini.</p>
                                    <div class="flex items-center gap-1.5 pt-1">
                                        <input type="checkbox" name="remove_image" id="remove_image" value="1"
                                            class="rounded border-slate-300 text-red-600 focus:ring-red-500 h-4 w-4">
                                        <label for="remove_image" class="text-xs font-bold text-red-600 cursor-pointer select-none">
                                            Hapus foto ruangan saat ini
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-xl hover:border-[#2563EB] transition-colors bg-slate-50/50">
                            <div class="space-y-2 text-center" id="upload-placeholder">
                                <div class="mx-auto h-12 w-12 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[26px]">add_photo_alternate</span>
                                </div>
                                <div class="flex text-xs text-slate-600 justify-center">
                                    <label for="image" class="relative cursor-pointer rounded-md font-bold text-[#2563EB] hover:text-[#1D4ED8] focus-within:outline-none">
                                        <span>{{ $room->image_url ? 'Pilih foto baru untuk mengganti' : 'Unggah foto ruangan' }}</span>
                                        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/jpg,image/webp" class="sr-only" onchange="previewImage(this)">
                                    </label>
                                    <p class="pl-1 text-slate-400">atau tarik ke sini</p>
                                </div>
                                <p class="text-[11px] text-slate-400">PNG, JPG, JPEG, atau WEBP hingga 2MB</p>
                            </div>
                            <div id="image-preview-container" class="hidden text-center">
                                <img id="image-preview" src="#" alt="Preview Foto Baru" class="max-h-48 rounded-lg shadow-sm mx-auto mb-2 object-cover border border-slate-200">
                                <button type="button" onclick="removeSelectedImage()" class="text-xs font-bold text-red-600 hover:text-red-700 inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">delete</span> Batalkan Pilihan Foto Baru
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-[#0F172A]">Status</label>
                        <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                            <option value="active" {{ old('status', $room->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status', $room->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-[#0F172A] mb-3">Fasilitas Ruangan</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @php
                                $selectedFacilities = is_array(old('facilities')) 
                                    ? old('facilities') 
                                    : $room->facilities->pluck('id')->toArray();
                            @endphp
                            @forelse ($facilities as $fac)
                                <div class="relative flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input id="facility_{{ $fac->id }}" name="facilities[]" type="checkbox" value="{{ $fac->id }}"
                                            {{ in_array($fac->id, $selectedFacilities) ? 'checked' : '' }}
                                            class="h-4 w-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="facility_{{ $fac->id }}" class="font-medium text-[#0F172A]">{{ $fac->name }}</label>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-[#475569] italic col-span-full">Tidak ada fasilitas yang tersedia di sistem. Buat fasilitas terlebih dahulu.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-[#E5E7EB] flex justify-end gap-x-3">
                    <a href="{{ route('admin.rooms.index') }}" class="rounded bg-white px-3 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">Batal</a>
                    <button type="submit" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
                        Perbarui Ruangan
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('image-preview-container').classList.remove('hidden');
                document.getElementById('upload-placeholder').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeSelectedImage() {
        const input = document.getElementById('image');
        input.value = '';
        document.getElementById('image-preview').src = '#';
        document.getElementById('image-preview-container').classList.add('hidden');
        document.getElementById('upload-placeholder').classList.remove('hidden');
    }
</script>
@endsection
