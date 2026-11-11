@props(['initials' => '', 'size' => 'md', 'color' => null, 'src' => null])

@php
    $sizes = ['sm' => 'size-8 text-xs', 'md' => 'size-10 text-sm', 'lg' => 'size-14 text-lg', 'xl' => 'size-24 text-3xl'];
    $classes = 'grid shrink-0 place-items-center overflow-hidden rounded-full font-semibold text-white ring-2 ring-white '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($src)
    <img src="{{ $src }}" alt="" {{ $attributes->merge(['class' => $classes.' bg-slate-100 object-cover']) }}>
@else
    <span
        {{ $attributes->merge(['class' => $classes]) }}
        style="background-color: {{ $color ?? 'var(--color-ink-700)' }}"
    >{{ $initials }}</span>
@endif
