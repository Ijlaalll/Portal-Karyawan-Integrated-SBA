@props([
    'action' => url()->current(),
    'period' => 'monthly',
    'dateInput' => null,
    'startDate' => null,
    'endDate' => null,
    'periodKey' => 'period',
    'dateKey' => 'date',
    'formId' => 'dashboardPeriodFilterForm',
    'extraHiddenInputs' => [],
    'jsPrefix' => 'dash',
])

@php
    $dateInput = $dateInput ?? ($period === 'yearly' ? date('Y') : ($period === 'monthly' ? date('Y-m') : date('Y-m-d')));
    $startDate = $startDate ?? \Carbon\Carbon::now('Asia/Jakarta');
    $endDate = $endDate ?? \Carbon\Carbon::now('Asia/Jakarta');
@endphp

<form action="{{ $action }}" method="GET" id="{{ $formId }}" class="flex flex-wrap items-center gap-3" onsubmit="sessionStorage.setItem('{{ $formId }}_scroll', document.querySelector('main')?.scrollTop || window.scrollY || 0);">
    @if(!empty($extraHiddenInputs))
        @foreach($extraHiddenInputs as $k => $v)
            @if(is_array($v))
                <input type="hidden" name="{{ $v['name'] }}" value="{{ $v['value'] }}">
            @else
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endif
        @endforeach
    @endif

    <!-- Date Range Label Badge -->
    <span class="text-xs font-bold text-[#475569] bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-lg whitespace-nowrap">
        @if($period === 'yearly')
            {{ $startDate->format('Y') }}
        @elseif($period === 'monthly')
            {{ $startDate->translatedFormat('F Y') }}
        @elseif($period === 'weekly')
            {{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}
        @else
            {{ $startDate->format('d M Y') }}
        @endif
    </span>

    <!-- Segmented Control Buttons -->
    <div class="inline-flex max-w-full overflow-x-auto rounded-lg border border-gray-200 bg-white p-0.5 shadow-sm text-xs font-semibold">
        <button type="button" onclick="{{ $jsPrefix }}SetPeriod('daily')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'daily' ? 'bg-[#2563EB] text-white shadow-xs' : 'text-[#475569] hover:text-[#0F172A]' }}">Harian</button>
        <button type="button" onclick="{{ $jsPrefix }}SetPeriod('weekly')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'weekly' ? 'bg-[#2563EB] text-white shadow-xs' : 'text-[#475569] hover:text-[#0F172A]' }}">Mingguan</button>
        <button type="button" onclick="{{ $jsPrefix }}SetPeriod('monthly')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'monthly' ? 'bg-[#2563EB] text-white shadow-xs' : 'text-[#475569] hover:text-[#0F172A]' }}">Bulanan</button>
        <button type="button" onclick="{{ $jsPrefix }}SetPeriod('yearly')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'yearly' ? 'bg-[#2563EB] text-white shadow-xs' : 'text-[#475569] hover:text-[#0F172A]' }}">Tahunan</button>
    </div>
    <input type="hidden" name="{{ $periodKey }}" id="{{ $formId }}_{{ $periodKey }}_input" value="{{ $period }}">

    <!-- Dynamic Period Selector Input -->
    <div>
        @if($period === 'yearly')
            <select name="{{ $dateKey }}" onchange="if(window.submitFormWithTurbo){submitFormWithTurbo(this)}else{this.form.submit()}" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                @for($y = 2024; $y <= 2030; $y++)
                    <option value="{{ $y }}" {{ $dateInput == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        @elseif($period === 'monthly')
            <input type="month" name="{{ $dateKey }}" value="{{ $dateInput }}" onchange="if(window.submitFormWithTurbo){submitFormWithTurbo(this)}else{this.form.submit()}" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
        @else
            <input type="date" name="{{ $dateKey }}" value="{{ $dateInput }}" onchange="if(window.submitFormWithTurbo){submitFormWithTurbo(this)}else{this.form.submit()}" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
        @endif
    </div>
</form>

<script>
    if (typeof window.{{ $jsPrefix }}SetPeriod !== 'function') {
        window.{{ $jsPrefix }}SetPeriod = function(val) {
            sessionStorage.setItem('{{ $formId }}_scroll', document.querySelector('main')?.scrollTop || window.scrollY || 0);
            const form = document.getElementById('{{ $formId }}');
            if (!form) return;
            
            const input = document.getElementById('{{ $formId }}_{{ $periodKey }}_input');
            if (input) input.value = val;
            
            const now = new Date();
            let dateVal = '';
            if (val === 'yearly') {
                dateVal = String(now.getFullYear());
            } else if (val === 'monthly') {
                dateVal = now.toISOString().slice(0, 7);
            } else {
                dateVal = now.toISOString().slice(0, 10);
            }
            
            const url = new URL(form.action, window.location.origin);
            // Copy existing query params from window or form
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.forEach((v, k) => url.searchParams.set(k, v));
            
            // Set updated period and date
            url.searchParams.set('{{ $periodKey }}', val);
            url.searchParams.set('{{ $dateKey }}', dateVal);
            
            const targetUrl = url.toString();
            if (window.navigateTo) { 
                window.navigateTo(targetUrl); 
            } else { 
                window.location.href = targetUrl; 
            }
        };
    }
</script>
