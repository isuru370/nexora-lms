@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'prefix' => null,
    'suffix' => null,
])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $inputValue = $type === 'password' ? null : old($name, $value);
    $ring = $error
        ? 'ring-red-300 focus-within:ring-red-500'
        : 'ring-slate-200 focus-within:ring-ink-500';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">
            {{ $label }}
            @if ($attributes->has('required'))
                <span class="text-accent-600">*</span>
            @endif
        </label>
    @endif

    <div class="flex items-center rounded-xl bg-white shadow-sm ring-1 ring-inset {{ $ring }} transition focus-within:ring-2">
        @if ($prefix)
            <span class="pl-3.5 text-sm text-slate-400 select-none">{{ $prefix }}</span>
        @endif

        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if ($inputValue !== null) value="{{ $inputValue }}" @endif
            {{ $attributes->except(['class', 'id'])->merge(['class' => 'block h-11 w-full min-w-0 rounded-xl border-0 bg-transparent px-3.5 text-[15px] text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0']) }}
            @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        >

        @if ($suffix)
            <span class="pr-3.5 text-sm text-slate-400 select-none">{{ $suffix }}</span>
        @endif
    </div>

    @if ($error)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-sm text-red-600">
            <x-ui.icon name="alert" class="size-4 shrink-0" />
            {{ $error }}
        </p>
    @elseif ($hint)
        <p class="text-sm text-slate-500">{{ $hint }}</p>
    @endif
</div>
