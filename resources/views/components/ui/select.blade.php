@props(['name', 'label' => null, 'options' => [], 'value' => null, 'hint' => null, 'placeholder' => null])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $selected = (string) old($name, $value);
    $ring = $error ? 'ring-red-300 focus:ring-red-500' : 'ring-slate-200 focus:ring-ink-500';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    <select id="{{ $id }}" name="{{ $name }}"
        {{ $attributes->except(['class', 'id'])->merge(['class' => 'block h-11 w-full rounded-xl border-0 bg-white px-3.5 text-[15px] text-slate-900 shadow-sm ring-1 ring-inset '.$ring.' focus:ring-2 focus:outline-none']) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $optionValue === $selected)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if ($error)
        <p class="flex items-center gap-1.5 text-sm text-red-600"><x-ui.icon name="alert" class="size-4 shrink-0" /> {{ $error }}</p>
    @elseif ($hint)
        <p class="text-sm text-slate-500">{{ $hint }}</p>
    @endif
</div>
