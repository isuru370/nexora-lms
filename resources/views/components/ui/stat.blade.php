@props(['label', 'value', 'hint' => null, 'icon' => null, 'tone' => 'navy'])

@php
    $tones = [
        'navy' => 'bg-navy-50 text-navy-700',
        'gold' => 'bg-gold-50 text-gold-700',
        'green' => 'bg-emerald-50 text-emerald-700',
        'red' => 'bg-red-50 text-red-700',
        'blue' => 'bg-blue-50 text-blue-700',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl bg-white p-5 shadow-card ring-1 ring-slate-200/70']) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        @if ($icon)
            <span class="grid size-9 place-items-center rounded-xl {{ $tones[$tone] ?? $tones['navy'] }}">
                <x-ui.icon :name="$icon" class="size-[18px]" />
            </span>
        @endif
    </div>
    <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-sm text-slate-500">{{ $hint }}</p>
    @endif
</div>
