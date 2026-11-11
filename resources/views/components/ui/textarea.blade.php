@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'max' => null, 'rows' => 3])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $text = (string) old($name, $value);
    $ring = $error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-200 focus:ring-ink-500';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }} x-data="{ count: {{ mb_strlen($text) }} }">
    @if ($label)
        <div class="flex items-baseline justify-between">
            <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
            @if ($max)
                <span class="text-xs tabular-nums text-slate-400"><span x-text="count">{{ mb_strlen($text) }}</span> / {{ $max }}</span>
            @endif
        </div>
    @endif

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($max) maxlength="{{ $max }}" @endif
        x-on:input="count = $event.target.value.length"
        {{ $attributes->except(['class', 'id'])->merge(['class' => 'block w-full rounded-xl border-0 bg-white px-3.5 py-3 text-[15px] text-slate-900 shadow-sm ring-1 ring-inset '.$ring.' placeholder:text-slate-400 focus:ring-2 focus:outline-none']) }}
    >{{ $text }}</textarea>

    @if ($error)
        <p class="flex items-center gap-1.5 text-sm text-red-600">
            <x-ui.icon name="alert" class="size-4 shrink-0" />
            {{ $error }}
        </p>
    @elseif ($hint)
        <p class="text-sm text-slate-500">{{ $hint }}</p>
    @endif
</div>
