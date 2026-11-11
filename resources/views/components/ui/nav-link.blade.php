@props(['href' => '#', 'icon', 'active' => false, 'soon' => false, 'dark' => true])

@php
    if ($dark) {
        $state = $active
            ? 'bg-white/10 text-white'
            : 'text-ink-200 hover:bg-white/5 hover:text-white';
    } else {
        $state = $active
            ? 'bg-ink-50 text-ink-900'
            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900';
    }
@endphp

@if ($soon)
    <span {{ $attributes->merge(['class' => 'group flex cursor-default items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium opacity-60 '.($dark ? 'text-ink-300' : 'text-slate-400')]) }}>
        <x-ui.icon :name="$icon" class="size-[18px]" />
        <span class="flex-1">{{ $slot }}</span>
        <span @class(['rounded-md px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide', 'bg-white/10 text-ink-200' => $dark, 'bg-slate-100 text-slate-500' => ! $dark])>{{ __('Soon') }}</span>
    </span>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition '.$state]) }} @if ($active) aria-current="page" @endif>
        @if ($active)
            <span class="absolute inset-y-2 left-0 w-0.5 rounded-full bg-accent-400"></span>
        @endif
        <x-ui.icon :name="$icon" class="size-[18px]" />
        <span class="flex-1">{{ $slot }}</span>
    </a>
@endif
