{{-- On/off switch that posts name=1 when on and name=0 when off. --}}
@props(['name', 'label', 'checked' => false, 'hint' => null])

@php
    $on = (bool) old($name, $checked);
@endphp

<label {{ $attributes->merge(['class' => 'flex cursor-pointer items-start justify-between gap-4']) }} x-data="{ on: @js($on) }">
    <span>
        <span class="block text-sm font-medium text-slate-800">{{ $label }}</span>
        @if ($hint)
            <span class="mt-0.5 block text-sm text-slate-500">{{ $hint }}</span>
        @endif
    </span>
    <input type="hidden" name="{{ $name }}" x-bind:value="on ? 1 : 0" value="{{ $on ? 1 : 0 }}">
    <button type="button" role="switch" x-bind:aria-checked="on" x-on:click="on = ! on"
        class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 rounded-full transition"
        x-bind:class="on ? 'bg-accent-600' : 'bg-slate-200'">
        <span class="absolute top-0.5 left-0.5 size-5 rounded-full bg-white shadow transition" x-bind:class="on && 'translate-x-5'"></span>
    </button>
</label>
