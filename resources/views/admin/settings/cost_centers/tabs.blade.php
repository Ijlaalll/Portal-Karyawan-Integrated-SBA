<div class="mb-6 border-b border-[#E5E7EB] bg-white rounded-t-xl px-4 pt-2 shadow-xs">
    <nav class="-mb-px flex space-x-2 sm:space-x-8 overflow-x-auto" aria-label="Tabs">
        <a href="{{ route('admin.settings.cost-centers.overview') }}" 
           class="{{ request()->routeIs('admin.settings.cost-centers.overview') ? 'border-[#2563EB] text-[#2563EB] font-bold' : 'border-transparent text-[#64748B] hover:border-slate-300 hover:text-[#0F172A]' }} whitespace-nowrap border-b-2 py-3.5 px-2 text-sm font-semibold transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">dashboard</span>
            Overview
        </a>
        <a href="{{ route('admin.settings.cost-centers.index') }}" 
           class="{{ request()->routeIs('admin.settings.cost-centers.index') || request()->routeIs('admin.settings.cost-centers.create') || request()->routeIs('admin.settings.cost-centers.edit') ? 'border-[#2563EB] text-[#2563EB] font-bold' : 'border-transparent text-[#64748B] hover:border-slate-300 hover:text-[#0F172A]' }} whitespace-nowrap border-b-2 py-3.5 px-2 text-sm font-semibold transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">payments</span>
            Cost Centers
        </a>
        <a href="{{ route('admin.settings.cost-centers.pricing') }}" 
           class="{{ request()->routeIs('admin.settings.cost-centers.pricing') ? 'border-[#2563EB] text-[#2563EB] font-bold' : 'border-transparent text-[#64748B] hover:border-slate-300 hover:text-[#0F172A]' }} whitespace-nowrap border-b-2 py-3.5 px-2 text-sm font-semibold transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">sell</span>
            Tarif Ruangan
        </a>
        <a href="{{ route('admin.settings.cost-centers.transactions') }}" 
           class="{{ request()->routeIs('admin.settings.cost-centers.transactions') ? 'border-[#2563EB] text-[#2563EB] font-bold' : 'border-transparent text-[#64748B] hover:border-slate-300 hover:text-[#0F172A]' }} whitespace-nowrap border-b-2 py-3.5 px-2 text-sm font-semibold transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">receipt_long</span>
            Aktivitas & Transaksi
        </a>
        <a href="{{ route('admin.settings.cost-centers.reports') }}" 
           class="{{ request()->routeIs('admin.settings.cost-centers.reports') ? 'border-[#2563EB] text-[#2563EB] font-bold' : 'border-transparent text-[#64748B] hover:border-slate-300 hover:text-[#0F172A]' }} whitespace-nowrap border-b-2 py-3.5 px-2 text-sm font-semibold transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">assessment</span>
            Laporan
        </a>
    </nav>
</div>
