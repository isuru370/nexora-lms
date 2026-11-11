@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'submit',
    'icon' => null,
    'block' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none select-none';

    $variants = [
        'primary' => 'bg-gradient-to-r from-accent-700 to-accent-500 text-white shadow-sm shadow-accent-600/20 hover:from-accent-600 hover:to-accent-400 focus-visible:ring-accent-500',
        'brand' => 'bg-brand text-white shadow-sm hover:brightness-110 focus-visible:ring-brand',
        'light' => 'bg-white text-accent-700 shadow-sm hover:bg-accent-50 focus-visible:ring-white',
        'secondary' => 'bg-white text-slate-700 ring-1 ring-inset ring-slate-200 shadow-sm hover:bg-slate-50 focus-visible:ring-ink-400',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:ring-ink-400',
        'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-500 focus-visible:ring-red-500',
    ];

    $sizes = [
        'sm' => 'h-9 px-3 text-sm',
        'md' => 'h-11 px-4 text-sm',
        'lg' => 'h-12 px-6 text-base',
    ];

    $classes = implode(' ', [$base, $variants[$variant] ?? $variants['primary'], $sizes[$size] ?? $sizes['md'], $block ? 'w-full' : '']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-ui.icon :name="$icon" class="size-4" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-ui.icon :name="$icon" class="size-4" />
        @endif
        {{ $slot }}
    </button>
@endif
