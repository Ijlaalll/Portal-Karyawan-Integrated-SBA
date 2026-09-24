@extends('layouts.app')

@section('title', 'Pengaturan SMTP - Admin SBA')

@section('content')
<div class="min-h-full py-2">
    <header class="mb-6">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Pengaturan SMTP
        </h1>
        <p class="text-sm text-[#64748B] mt-1">
            Kelola konfigurasi server SMTP dinamis, riwayat audit, log pengiriman email, serta template dan event notifikasi sistem.
        </p>
    </header>

    <!-- Top Navigation Tabs -->
    <div class="border-b border-[#E5E7EB] mb-8 overflow-x-auto">
        <nav class="-mb-px flex space-x-8 min-w-max" aria-label="Tabs">
            <a href="{{ route('admin.settings.smtp.show', ['tab' => 'config']) }}" class="border-b-2 py-4 px-1 text-sm font-semibold whitespace-nowrap shrink-0 {{ $tab === 'config' ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-[#64748B] hover:border-[#E5E7EB] hover:text-[#0F172A]' }} transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">settings</span>
                Konfigurasi
            </a>
            <a href="{{ route('admin.settings.smtp.show', ['tab' => 'history']) }}" class="border-b-2 py-4 px-1 text-sm font-semibold whitespace-nowrap shrink-0 {{ $tab === 'history' ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-[#64748B] hover:border-[#E5E7EB] hover:text-[#0F172A]' }} transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">history</span>
                Riwayat SMTP
            </a>
            <a href="{{ route('admin.settings.smtp.show', ['tab' => 'logs']) }}" class="border-b-2 py-4 px-1 text-sm font-semibold whitespace-nowrap shrink-0 {{ $tab === 'logs' ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-[#64748B] hover:border-[#E5E7EB] hover:text-[#0F172A]' }} transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">mail</span>
                Email Logs
            </a>
            <a href="{{ route('admin.settings.smtp.show', ['tab' => 'templates']) }}" class="border-b-2 py-4 px-1 text-sm font-semibold whitespace-nowrap shrink-0 {{ $tab === 'templates' ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-[#64748B] hover:border-[#E5E7EB] hover:text-[#0F172A]' }} transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">subject</span>
                Email Templates
            </a>
            <a href="{{ route('admin.settings.smtp.show', ['tab' => 'notifications']) }}" class="border-b-2 py-4 px-1 text-sm font-semibold whitespace-nowrap shrink-0 {{ $tab === 'notifications' ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-[#64748B] hover:border-[#E5E7EB] hover:text-[#0F172A]' }} transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">notifications</span>
                Notifikasi
            </a>
        </nav>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <div class="ml-3 flex-1 flex justify-between">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
            <div class="flex">
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 1: KONFIGURASI -->
    @if($tab === 'config')
        <!-- Dropdown Selection / Select Config -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-5 shadow-sm mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div class="flex-1 max-w-md space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="smtp_config_select" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        SMTP Configuration
                    </label>
                    @php
                        $currentSelected = $allSettings->firstWhere('id', $selectedId);
                    @endphp
                    @if($currentSelected && $currentSelected->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium"
                            style="background-color: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                            Active
                        </span>
                    @endif
                </div>

                @if($allSettings->isEmpty())
                    <p class="text-sm text-slate-500">No SMTP configuration has been configured yet.</p>
                @else
                    <div class="relative">
                        <select id="smtp_config_select" onchange="window.location.href='{{ route('admin.settings.smtp.show', ['tab' => 'config']) }}&id=' + this.value"
                            class="block w-full rounded-lg bg-white pl-3 pr-8 py-2 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 transition-colors appearance-none cursor-pointer"
                            style="border: 1px solid #CBD5E1;">
                            @foreach($allSettings as $item)
                                <option value="{{ $item->id }}" {{ $selectedId == $item->id ? 'selected' : '' }}>
                                    {{ $item->username }} ({{ $item->host }}) {{ $item->is_active ? '— Active' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-500">
                            <span class="material-symbols-outlined text-[18px]">unfold_more</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center shrink-0">
                <a href="{{ route('admin.settings.smtp.show', ['tab' => 'config', 'action' => 'create']) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm cursor-pointer"
                    style="background-color: #2563EB;">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Create New SMTP
                </a>
            </div>
        </div>

        @if($action === 'create' || $allSettings->isEmpty())
            <!-- CREATE FORM -->
            <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 sm:p-8 shadow-sm">
                <h3 class="text-base font-bold text-[#0F172A] mb-6">Create New SMTP Configuration</h3>
                <form action="{{ route('admin.settings.smtp.update') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- SMTP Host -->
                        <div class="md:col-span-2">
                            <label for="host" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">SMTP Host</label>
                            <input type="text" name="host" id="host" value="{{ old('host') }}" placeholder="Contoh: smtp.mailtrap.io atau smtp.gmail.com" required
                                class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                            @error('host')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SMTP Port -->
                        <div>
                            <label for="port" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">SMTP Port</label>
                            <input type="number" name="port" id="port" value="{{ old('port', 587) }}" placeholder="Contoh: 587 atau 465 atau 25" required
                                class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                            @error('port')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Encryption -->
                        <div>
                            <label for="encryption" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Enkripsi (Encryption)</label>
                            <select name="encryption" id="encryption"
                                class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                                <option value="none">None (Tanpa Enkripsi)</option>
                                <option value="tls" selected>TLS</option>
                                <option value="ssl">SSL</option>
                            </select>
                            @error('encryption')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SMTP Username -->
                        <div>
                            <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">SMTP Username</label>
                            <input type="text" name="username" id="username" value="{{ old('username') }}" placeholder="Masukkan username SMTP" required
                                class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                            @error('username')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SMTP Password -->
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">SMTP Password</label>
                            <input type="password" name="password" id="password" placeholder="Masukkan password SMTP" required
                                class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                            @error('password')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- From Address -->
                        <div>
                            <label for="from_address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Sender Email (From Address)</label>
                            <input type="email" name="from_address" id="from_address" value="{{ old('from_address') }}" placeholder="Contoh: noreply@sba.co.id" required
                                class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                            @error('from_address')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- From Name -->
                        <div>
                            <label for="from_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Sender Name (From Name)</label>
                            <input type="text" name="from_name" id="from_name" value="{{ old('from_name') }}" placeholder="Contoh: Admin SBA" required
                                class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                            @error('from_name')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                        @if($allSettings->isNotEmpty())
                            <a href="{{ route('admin.settings.smtp.show', ['tab' => 'config']) }}" class="rounded bg-white px-5 py-2.5 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB] transition-colors">Batal</a>
                        @endif
                        <button type="submit" class="rounded bg-[#2563EB] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">Create SMTP</button>
                    </div>
                </form>
            </div>
        @else
            <!-- EDIT FORM & WIDGET -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 sm:p-8 shadow-sm">
                        <form action="{{ route('admin.settings.smtp.update') }}" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" name="id" value="{{ $settings->id }}">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- SMTP Host -->
                                <div class="md:col-span-2">
                                    <label for="host" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">SMTP Host</label>
                                    <input type="text" name="host" id="host" value="{{ old('host', $settings->host ?? '') }}" placeholder="Contoh: smtp.mailtrap.io atau smtp.gmail.com" required
                                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                                    @error('host')
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- SMTP Port -->
                                <div>
                                    <label for="port" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">SMTP Port</label>
                                    <input type="number" name="port" id="port" value="{{ old('port', $settings->port ?? 2525) }}" placeholder="Contoh: 587 atau 465 atau 25" required
                                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                                    @error('port')
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Encryption -->
                                <div>
                                    <label for="encryption" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Enkripsi (Encryption)</label>
                                    <select name="encryption" id="encryption"
                                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                                        @php
                                            $currentEncryption = old('encryption', $settings->encryption ?? 'none');
                                            if (empty($currentEncryption)) {
                                                $currentEncryption = 'none';
                                            }
                                        @endphp
                                        <option value="none" {{ $currentEncryption === 'none' ? 'selected' : '' }}>None (Tanpa Enkripsi)</option>
                                        <option value="tls" {{ $currentEncryption === 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ $currentEncryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    </select>
                                    @error('encryption')
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- SMTP Username -->
                                <div>
                                    <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">SMTP Username</label>
                                    <input type="text" name="username" id="username" value="{{ old('username', $settings->username ?? '') }}" placeholder="Masukkan username SMTP" required
                                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                                    @error('username')
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- SMTP Password -->
                                <div>
                                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">SMTP Password</label>
                                    <input type="password" name="password" id="password" placeholder="••••••••"
                                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                                    <p class="mt-1 text-[10px] text-slate-400">Leave blank to keep the current SMTP password.</p>
                                    @error('password')
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- From Address -->
                                <div>
                                    <label for="from_address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Sender Email (From Address)</label>
                                    <input type="email" name="from_address" id="from_address" value="{{ old('from_address', $settings->from_address ?? '') }}" placeholder="Contoh: noreply@sba.co.id" required
                                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                                    @error('from_address')
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- From Name -->
                                <div>
                                    <label for="from_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Sender Name (From Name)</label>
                                    <input type="text" name="from_name" id="from_name" value="{{ old('from_name', $settings->from_name ?? '') }}" placeholder="Contoh: Admin SBA" required
                                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                                    @error('from_name')
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-6">
                                <div class="flex gap-2">
                                    <button type="button" onclick="triggerTestConnection({{ $settings->id }})" class="rounded bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
                                        Test Connection
                                    </button>
                                    <button type="button" onclick="triggerTestEmail({{ $settings->id }}, '{{ $settings->username }}')" class="rounded bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors">
                                        Test Email
                                    </button>
                                </div>
                                <div class="flex gap-2">
                                    @if(!$settings->is_active)
                                        <button type="button" onclick="triggerActivate({{ $settings->id }})" class="rounded bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 hover:bg-amber-100 transition-colors">
                                            Set as Active
                                        </button>
                                    @endif
                                    <button type="button" onclick="triggerDelete({{ $settings->id }}, {{ $settings->is_active ? 'true' : 'false' }})" class="rounded bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100 transition-colors cursor-pointer">
                                        Delete
                                    </button>
                                    <button type="submit" class="rounded bg-[#2563EB] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">Save Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- SMTP Connection Status widget -->
                <div class="lg:col-span-1">
                    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm flex flex-col space-y-6">
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Koneksi</h3>
                            <div class="mt-2 flex items-center gap-2">
                                @if(!$settings || $settings->status === 'not_tested')
                                    <span class="inline-flex items-center rounded-md bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600 ring-1 ring-inset ring-slate-500/10">
                                        Not Tested
                                    </span>
                                @elseif($settings->status === 'connected')
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-3 py-1.5 text-xs font-bold text-green-700 ring-1 ring-inset ring-green-600/20">
                                        Connected
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-600/20">
                                        Connection Failed
                                    </span>
                                @endif
                                
                                @if($settings && $settings->is_active)
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                        Active SMTP
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="border-t border-[#E5E7EB] pt-4 space-y-3 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Uji Sukses Terakhir:</span>
                                <span class="font-semibold text-slate-800">{{ $settings && $settings->last_success_at ? \Carbon\Carbon::parse($settings->last_success_at)->format('d M Y, H:i') : '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Uji Gagal Terakhir:</span>
                                <span class="font-semibold text-slate-800">{{ $settings && $settings->last_failure_at ? \Carbon\Carbon::parse($settings->last_failure_at)->format('d M Y, H:i') : '-' }}</span>
                            </div>
                        </div>

                        @if($settings && $settings->status === 'failed' && $settings->last_error_message)
                            <div class="border-t border-red-100 bg-red-50/50 rounded-xl p-4 text-xs text-red-800 border">
                                <p class="font-bold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">error</span> Pesan Kesalahan SMTP:
                                </p>
                                <p class="mt-1 break-words font-mono text-[11px] leading-relaxed">{{ $settings->last_error_message }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- SMTP CONFIGURATIONS LIST TABLE -->
        @if($allSettings->isNotEmpty())
            <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden mt-8">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-[#F9FAFB] flex items-center justify-between">
                    <h3 class="text-sm font-bold text-[#0F172A] uppercase tracking-wider">Saved SMTP Configurations</h3>
                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-700/10">
                        {{ $allSettings->count() }} Configured
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E5E7EB]">
                        <thead class="bg-[#F9FAFB]">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Host / Port</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Enkripsi</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengirim (From)</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal & Waktu</th>
                                <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB] bg-white text-slate-700 text-sm">
                            @foreach($allSettings as $item)
                                <tr class="hover:bg-[#F8FAFC] transition-colors">
                                    <td class="px-6 py-4.5 font-semibold text-[#0F172A]">
                                        {{ $item->host }}:{{ $item->port }}
                                    </td>
                                    <td class="px-6 py-4.5 uppercase font-bold text-xs text-slate-500">
                                        {{ $item->encryption ?: 'None' }}
                                    </td>
                                    <td class="px-6 py-4.5 font-mono text-xs">
                                        {{ $item->username }}
                                    </td>
                                    <td class="px-6 py-4.5 text-xs">
                                        <strong>{{ $item->from_name }}</strong><br>
                                        <span class="text-slate-500">&lt;{{ $item->from_address }}&gt;</span>
                                    </td>
                                    <td class="px-6 py-4.5">
                                        @if($item->is_active)
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-bold text-green-700 ring-1 ring-inset ring-green-600/20">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4.5 text-xs text-slate-500">
                                        {{ $item->updated_at ? $item->updated_at->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4.5 text-right whitespace-nowrap text-xs font-bold space-x-2">
                                        <a href="{{ route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $item->id, 'action' => 'view']) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">Edit</a>
                                        
                                        <button type="button" onclick="triggerTestConnection({{ $item->id }})" class="text-indigo-600 hover:text-indigo-900 ml-2 cursor-pointer">Test Conn</button>
                                        <button type="button" onclick="triggerTestEmail({{ $item->id }}, '{{ $item->username }}')" class="text-emerald-600 hover:text-emerald-950 ml-2 cursor-pointer">Test Email</button>
                                        
                                        @if(!$item->is_active)
                                            <button type="button" onclick="triggerActivate({{ $item->id }})" class="text-amber-600 hover:text-amber-800 ml-2 cursor-pointer">Set Active</button>
                                        @endif
                                        <button type="button" onclick="triggerDelete({{ $item->id }}, {{ $item->is_active ? 'true' : 'false' }})" class="text-red-600 hover:text-red-900 ml-2 cursor-pointer">Delete</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Hidden Forms -->
        <form id="activateForm" action="" method="POST" class="hidden">
            @csrf
        </form>
        <form id="deleteForm" action="" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        <form id="testConnectionForm" action="{{ route('admin.settings.smtp.test-connection') }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="smtp_setting_id" id="test_conn_setting_id">
        </form>

        <!-- Test Email Modal -->
        <div id="testEmailModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white border border-[#E5E7EB] rounded-2xl w-full max-w-md p-6 shadow-xl flex flex-col space-y-4 animate-in fade-in zoom-in duration-200">
                <div class="flex justify-between items-start border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Send Test Email</h3>
                        <p class="text-xs text-slate-400 font-semibold mt-0.5" id="testEmailConfigLabel"></p>
                    </div>
                    <button type="button" onclick="closeTestEmailModal()" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form action="{{ route('admin.settings.smtp.test') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="smtp_setting_id" id="test_email_setting_id">

                    <div>
                        <label for="test_recipient" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Recipient Email</label>
                        <input type="email" name="recipient" id="test_recipient" placeholder="recipient@example.com" required
                            class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                        <button type="button" onclick="closeTestEmailModal()" class="rounded bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">Batal</button>
                        <button type="submit" class="rounded bg-[#2563EB] px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">Send Test Email</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- JS Actions -->
        <script>
            function triggerActivate(id) {
                if (confirm('Are you sure you want to set this SMTP configuration as active/default?')) {
                    const form = document.getElementById('activateForm');
                    form.action = '/admin/settings/smtp/' + id + '/activate';
                    form.submit();
                }
            }

            function triggerDelete(id, isActive = false) {
                let confirmMessage = 'Apakah Anda yakin ingin menghapus konfigurasi SMTP ini? Kredensial/password yang tersimpan juga akan dihapus.';
                if (isActive) {
                    confirmMessage = '⚠️ PERINGATAN: Ini adalah konfigurasi SMTP AKTIF (Utama)!\n\nJika dihapus, pengiriman email notifikasi otomatis sistem akan berhenti bekerja atau beralih ke konfigurasi default sampai ada konfigurasi baru yang diaktifkan.\n\nApakah Anda benar-benar yakin ingin menghapus konfigurasi ini?';
                }
                if (confirm(confirmMessage)) {
                    const form = document.getElementById('deleteForm');
                    form.action = '/admin/settings/smtp/' + id;
                    form.submit();
                }
            }

            function triggerTestConnection(id) {
                const form = document.getElementById('testConnectionForm');
                document.getElementById('test_conn_setting_id').value = id;
                form.submit();
            }

            function triggerTestEmail(id, username) {
                document.getElementById('test_email_setting_id').value = id;
                document.getElementById('testEmailConfigLabel').textContent = 'Menggunakan konfigurasi: ' + username;
                document.getElementById('testEmailModal').classList.remove('hidden');
            }

            function closeTestEmailModal() {
                document.getElementById('testEmailModal').classList.add('hidden');
            }
        </script>
    @endif

    <!-- TAB 2: RIWAYAT SMTP -->
    @if($tab === 'history')
        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
            <table class="min-w-full divide-y divide-[#E5E7EB]">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tanggal & Waktu</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Diubah Oleh</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Host / Port</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Enkripsi</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Pengirim (From)</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                    @forelse ($histories as $hist)
                        <tr class="hover:bg-[#F8FAFC] transition-colors">
                            <td class="px-6 py-4.5 whitespace-nowrap text-[#475569] font-mono text-xs">
                                {{ $hist->created_at ? $hist->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap font-semibold">
                                {{ $hist->changer ? $hist->changer->name : 'System' }}
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap font-semibold text-[#2563EB]">
                                {{ $hist->host }}:{{ $hist->port }}
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap uppercase text-xs font-bold text-slate-500">
                                {{ $hist->encryption ?: 'None' }}
                            </td>
                            <td class="px-6 py-4.5 text-xs text-[#475569]">
                                <strong>{{ $hist->from_name }}</strong> <br> &lt;{{ $hist->from_address }}&gt;
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-xs">
                                <span class="inline-flex items-center rounded-md bg-[#EFF6FF] px-2.5 py-1 text-xs font-bold text-[#2563EB] ring-1 ring-inset ring-[#2563EB]/20 uppercase">
                                    {{ $hist->action }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                Belum ada riwayat perubahan SMTP.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $histories->links() }}
        </div>
    @endif

    <!-- TAB 3: EMAIL LOGS -->
    @if($tab === 'logs')
        <!-- Filters Form -->
        <form action="{{ route('admin.settings.smtp.show') }}" method="GET" class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm mb-6 flex flex-col gap-4">
            <input type="hidden" name="tab" value="logs">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="relative flex-1">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Penerima / Subjek</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..."
                        class="block w-full rounded-md border border-[#E5E7EB] pl-10 pr-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 pt-6 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-[#94A3B8] text-[20px]">search</span>
                    </div>
                </div>

                <!-- Email Type Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tipe Email</label>
                    <select name="email_type" class="w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                        <option value="all" {{ request('email_type') === 'all' ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="test_email" {{ request('email_type') === 'test_email' ? 'selected' : '' }}>Test Email</option>
                        <option value="reservasi_baru" {{ request('email_type') === 'reservasi_baru' ? 'selected' : '' }}>Reservasi Baru</option>
                        <option value="reservasi_disetujui" {{ request('email_type') === 'reservasi_disetujui' ? 'selected' : '' }}>Reservasi Disetujui</option>
                        <option value="reservasi_ditolak" {{ request('email_type') === 'reservasi_ditolak' ? 'selected' : '' }}>Reservasi Ditolak</option>
                        <option value="reservasi_dibatalkan" {{ request('email_type') === 'reservasi_dibatalkan' ? 'selected' : '' }}>Reservasi Dibatalkan</option>
                        <option value="pengingat_meeting" {{ request('email_type') === 'pengingat_meeting' ? 'selected' : '' }}>Pengingat Meeting</option>
                        <option value="akun_dibuat" {{ request('email_type') === 'akun_dibuat' ? 'selected' : '' }}>Akun Dibuat</option>
                        <option value="reset_password" {{ request('email_type') === 'reset_password' ? 'selected' : '' }}>Reset Password</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" class="w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Mulai Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-1.5 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                <a href="{{ route('admin.settings.smtp.show', ['tab' => 'logs']) }}" class="rounded bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">Reset</a>
                <button type="submit" class="rounded bg-[#2563EB] px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">Terapkan Filter</button>
            </div>
        </form>

        <!-- Logs Table -->
        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
            <table class="min-w-full divide-y divide-[#E5E7EB]">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tanggal & Waktu</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Penerima (Recipient)</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Subjek</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tipe Email</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Detail</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-[#F8FAFC] transition-colors">
                            <td class="px-6 py-4.5 whitespace-nowrap text-[#475569] font-mono text-xs">
                                {{ $log->created_at ? $log->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap font-semibold">
                                {{ $log->recipient }}
                            </td>
                            <td class="px-6 py-4.5 text-[#475569] font-medium">
                                {{ $log->subject }}
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-xs font-mono uppercase text-slate-500">
                                {{ str_replace('_', ' ', $log->email_type) }}
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                @if($log->status === 'sent')
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">
                                        Sent
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">
                                        Failed
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-xs">
                                @if($log->status === 'failed' && $log->error_detail)
                                    <button type="button" onclick="openErrorModal('{{ addslashes($log->error_detail) }}')" class="text-[#2563EB] hover:text-[#1D4ED8] font-bold cursor-pointer">
                                        Lihat Error
                                    </button>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                Tidak ada log pengiriman email yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $logs->links() }}
        </div>

        <!-- Error Detail Modal -->
        <div id="errorModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white border border-[#E5E7EB] rounded-2xl w-full max-w-lg p-6 shadow-xl flex flex-col space-y-4 animate-in fade-in zoom-in duration-200">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-bold text-[#0F172A] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-red-500">error</span> SMTP Error Detail
                    </h3>
                    <button type="button" onclick="closeErrorModal()" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 max-h-60 overflow-y-auto">
                    <p id="errorText" class="font-mono text-xs text-red-800 break-words leading-relaxed"></p>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="button" onclick="closeErrorModal()" class="rounded bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 cursor-pointer">Tutup</button>
                </div>
            </div>
        </div>

        <script>
            function openErrorModal(error) {
                document.getElementById('errorText').textContent = error;
                document.getElementById('errorModal').classList.remove('hidden');
            }
            function closeErrorModal() {
                document.getElementById('errorModal').classList.add('hidden');
            }
        </script>
    @endif

    <!-- TAB 4: EMAIL TEMPLATES -->
    @if($tab === 'templates')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Templates List -->
            <div class="lg:col-span-2">
                <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
                    <table class="min-w-full divide-y divide-[#E5E7EB]">
                        <thead class="bg-[#F9FAFB]">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Nama Template</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Subjek Default</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                            @foreach ($templates as $temp)
                                <tr class="hover:bg-[#F8FAFC] transition-colors">
                                    <td class="px-6 py-4.5 whitespace-nowrap font-bold">
                                        {{ $temp->name }}
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $temp->template_key }}</p>
                                    </td>
                                    <td class="px-6 py-4.5 text-slate-500 font-medium text-xs">
                                        {{ $temp->subject }}
                                    </td>
                                    <td class="px-6 py-4.5 whitespace-nowrap">
                                        @if($temp->is_active)
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Aktif</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4.5 whitespace-nowrap text-xs font-bold">
                                        <button type="button" onclick="openTemplateModal({{ $temp->id }}, '{{ $temp->template_key }}', '{{ addslashes($temp->name) }}', '{{ addslashes($temp->subject) }}', '{{ addslashes(str_replace("\n", '\n', $temp->body)) }}', {{ $temp->is_active ? 1 : 0 }})" class="text-[#2563EB] hover:text-[#1D4ED8] cursor-pointer">
                                            Edit Template
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- List Placeholder info card -->
            <div class="lg:col-span-1">
                <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm flex flex-col space-y-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Placeholder Variabel</h3>
                    <p class="text-xs text-slate-500">Anda dapat menggunakan variabel placeholder di bawah ini untuk merender data dinamis dari backend secara aman.</p>
                    
                    <div class="divide-y divide-slate-100 text-xs">
                        <div class="py-2 flex justify-between">
                            <span class="font-mono text-[#2563EB]">{user_name}</span>
                            <span class="text-slate-500">Nama Pemesan</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="font-mono text-[#2563EB]">{user_email}</span>
                            <span class="text-slate-500">Email Pemesan</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="font-mono text-[#2563EB]">{room_name}</span>
                            <span class="text-slate-500">Nama Ruangan</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="font-mono text-[#2563EB]">{date}</span>
                            <span class="text-slate-500">Tanggal Booking</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="font-mono text-[#2563EB]">{start_time}</span>
                            <span class="text-slate-500">Waktu Mulai</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="font-mono text-[#2563EB]">{end_time}</span>
                            <span class="text-slate-500">Waktu Selesai</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="font-mono text-[#2563EB]">{reservation_id}</span>
                            <span class="text-slate-500">ID Reservasi</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="font-mono text-[#2563EB]">{status}</span>
                            <span class="text-slate-500">Status Booking</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="font-mono text-[#2563EB]">{cancellation_note}</span>
                            <span class="text-slate-500">Alasan Tolak / Batal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template Editor Modal -->
        <div id="templateModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white border border-[#E5E7EB] rounded-2xl w-full max-w-2xl p-6 shadow-xl flex flex-col space-y-4 animate-in fade-in zoom-in duration-200">
                <div class="flex justify-between items-start border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Edit Email Template</h3>
                        <p id="templateNameLabel" class="text-xs text-slate-400 font-semibold mt-0.5"></p>
                    </div>
                    <button type="button" onclick="closeTemplateModal()" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form id="templateEditForm" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Subject -->
                    <div>
                        <label for="template_subject" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Subject Email</label>
                        <input type="text" name="subject" id="template_subject" required
                            class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <!-- Body -->
                    <div>
                        <label for="template_body" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Isi Email (Body)</label>
                        <textarea name="body" id="template_body" rows="8" required
                            class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm font-mono text-[12px] leading-relaxed resize-none"></textarea>
                    </div>

                    <!-- Status Toggle -->
                    <div>
                        <label for="template_is_active" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Status Template</label>
                        <select name="is_active" id="template_is_active" class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                            <option value="1">Aktif (Kirim Email)</option>
                            <option value="0">Tidak Aktif (Gunakan Fallback default)</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                        <button type="button" onclick="closeTemplateModal()" class="rounded bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">Batal</button>
                        <button type="submit" class="rounded bg-[#2563EB] px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">Simpan Template</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openTemplateModal(id, key, name, subject, body, isActive) {
                const modal = document.getElementById('templateModal');
                const form = document.getElementById('templateEditForm');
                
                document.getElementById('templateNameLabel').textContent = name + ' (' + key + ')';
                document.getElementById('template_subject').value = subject;
                document.getElementById('template_body').value = body.replace(/\\n/g, '\n');
                document.getElementById('template_is_active').value = isActive;
                
                form.action = '/admin/settings/smtp/templates/' + id;
                modal.classList.remove('hidden');
            }

            function closeTemplateModal() {
                document.getElementById('templateModal').classList.add('hidden');
            }
        </script>
    @endif

    <!-- TAB 5: NOTIFIKASI -->
    @if($tab === 'notifications')
        <div class="max-w-3xl">
            <form action="{{ route('admin.settings.smtp.notifications.update') }}" method="POST" class="bg-white border border-[#E5E7EB] rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">
                @csrf
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider border-b border-slate-100 pb-3">Konfigurasi Pengiriman Notifikasi Email</h3>

                <div class="space-y-4">
                    @foreach ($notificationSettings as $notif)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between py-4 border-b border-slate-100 last:border-b-0 gap-4">
                            <div>
                                <h4 class="text-sm font-bold text-[#0F172A]">{{ $notif->event_name }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Kirim notifikasi email ketika event ini terpicu.</p>
                            </div>

                            <div class="flex items-center gap-6">
                                <!-- Reminder Time Selector (Only for pengingat_meeting) -->
                                @if($notif->event_key === 'pengingat_meeting')
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-500 font-semibold">Waktu Pengingat:</span>
                                        <select name="notifications[{{ $notif->event_key }}][reminder_time]" class="rounded-md border border-[#E5E7EB] bg-white px-2.5 py-1.5 text-xs text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                                            <option value="15" {{ $notif->reminder_time === 15 ? 'selected' : '' }}>15 Menit</option>
                                            <option value="30" {{ $notif->reminder_time === 30 ? 'selected' : '' }}>30 Menit</option>
                                            <option value="60" {{ $notif->reminder_time === 60 ? 'selected' : '' }}>1 Jam</option>
                                        </select>
                                    </div>
                                @endif

                                <!-- ON/OFF Switch toggle -->
                                <div class="flex items-center">
                                    <input type="hidden" name="notifications[{{ $notif->event_key }}][is_active]" value="0">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="notifications[{{ $notif->event_key }}][is_active]" value="1" {{ $notif->is_active ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2563EB]"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                    <button type="submit" class="rounded bg-[#2563EB] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2 transition-colors">Simpan Konfigurasi</button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
