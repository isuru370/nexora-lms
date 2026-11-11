@props(['initials', 'size' => 'md', 'color' => null])

@php
    $sizes = ['sm' => 'size-8 text-xs', 'md' => 'size-10 text-sm', 'lg' => 'size-14 text-lg', 'xl' => 'size-20 text-2xl'];
@endphp

<span
    {{ $attributes->merge(['class' => 'grid shrink-0 place-items-center rounded-full font-semibold text-white ring-2 ring-white '.($sizes[$size] ?? $sizes['md'])]) }}
    style="background-color: {{ $color ?? 'var(--color-ink-700)' }}"
>{{ $initials }}</span>
