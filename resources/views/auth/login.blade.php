@extends('layouts.app')

@section('title', 'Sign In - SBA Portal')

@section('content')
<div class="min-h-screen flex">
    <!-- Left panel: branding and abstract visual (visible only on lg screens) -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#1E3A8A] relative items-center justify-center p-12 overflow-hidden">
        <!-- Abstract pattern background -->
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-blue-500 opacity-20 filter blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-indigo-500 opacity-20 filter blur-3xl"></div>

        <div class="relative z-10 text-white max-w-md">
            <h1 class="text-5xl font-extrabold tracking-tight">SBA</h1>
            <p class="text-lg font-medium text-blue-200 mt-2">PT. Solusi Bangun Andalas</p>
            <div class="mt-8 border-t border-blue-400/30 pt-6">
                <h3 class="text-xl font-bold">Portal Internal Terintegrasi</h3>
                <p class="text-sm text-blue-200/80 mt-2 leading-relaxed">
                    Akses terpadu untuk pemesanan ruang rapat dan sistem inspeksi kebersihan toilet operasional pabrik.
                </p>
            </div>
        </div>
    </div>

    <!-- Right panel: login form -->
    <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-20 bg-white">
        <div class="mx-auto w-full max-w-sm lg:w-96 animate-fade-in">
            <!-- Mobile-only header branding -->
            <div class="lg:hidden mb-8">
                <h1 class="text-4xl font-extrabold text-[#1E3A8A]">SBA</h1>
                <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider">PT. Solusi Bangun Andalas</p>
            </div>

            <div>
                <h2 class="text-2xl font-bold tracking-tight text-[#0F172A]">Welcome back</h2>
                <p class="mt-1 text-sm text-[#64748B]">Sign in to continue to your dashboard</p>
            </div>

            <div class="mt-8">
                @if ($errors->any())
                    <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                        <div class="flex">
                            <div class="ml-1">
                                <h3 class="text-sm font-semibold text-red-800">
                                    Login Failed
                                </h3>
                                <div class="mt-1 text-xs text-red-700 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form class="space-y-6" action="{{ url('/login') }}" method="POST" id="login-form" onsubmit="setLoadingState();">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#334155]">
                            Email Address
                        </label>
                        <div class="mt-1.5 relative rounded-md shadow-sm">
                            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                                class="block w-full rounded-md border border-[#D1D5DB] px-3.5 py-2.5 text-[#0F172A] placeholder-[#9CA3AF] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] transition-all sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#334155]">
                            Password
                        </label>
                        <div class="mt-1.5 relative rounded-md shadow-sm">
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                class="block w-full rounded-md border border-[#D1D5DB] pl-3.5 pr-10 py-2.5 text-[#0F172A] placeholder-[#9CA3AF] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] transition-all sm:text-sm">
                            <button type="button" onclick="togglePasswordVisibility();" class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#64748B] hover:text-[#0F172A] transition-colors focus:outline-none">
                                <span class="material-symbols-outlined text-[20px]" id="password-toggle-icon">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox"
                                class="h-4 w-4 rounded border-[#D1D5DB] text-[#2563EB] focus:ring-[#2563EB] transition-all">
                            <label for="remember" class="ml-2 block text-sm text-[#475569] font-medium">
                                Remember me
                            </label>
                        </div>
                    </div>

                    <div>
                        <button type="submit" id="submit-button"
                            class="flex w-full justify-center items-center gap-2 rounded-md bg-[#2563EB] py-2.5 px-4 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2 transition-all">
                            <span id="button-text">Sign In</span>
                            <span id="button-spinner" class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.4s ease-out forwards;
    }
</style>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('password-toggle-icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.textContent = 'visibility_off';
        } else {
            passwordInput.type = 'password';
            toggleIcon.textContent = 'visibility';
        }
    }

    function setLoadingState() {
        const button = document.getElementById('submit-button');
        const text = document.getElementById('button-text');
        const spinner = document.getElementById('button-spinner');
        
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');
        text.textContent = 'Signing In...';
        spinner.classList.remove('hidden');
    }
</script>
@endsection
