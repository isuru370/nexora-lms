{{--
    Image picker with instant preview and an optional "remove" switch.
    Sends <name> (file) and remove_<name> (1) when the current image is removed.
--}}
@props(['name', 'label', 'current' => null, 'shape' => 'circle', 'hint' => null, 'placeholder' => null])

@php
    $error = $errors->first($name);
    $frame = $shape === 'circle' ? 'rounded-full' : 'rounded-2xl';
@endphp

<div {{ $attributes->merge(['class' => 'space-y-2']) }}
     x-data="{ preview: @js($current), removed: false, picked: false }">
    <p class="text-sm font-medium text-slate-700">{{ $label }}</p>

    <div class="flex items-center gap-4">
        <div class="relative grid size-20 shrink-0 place-items-center overflow-hidden bg-slate-100 ring-1 ring-slate-200 {{ $frame }}">
            <template x-if="preview && ! removed">
                <img x-bind:src="preview" alt="" class="size-full {{ $shape === 'circle' ? 'object-cover' : 'object-contain p-1.5' }}">
            </template>
            <template x-if="! preview || removed">
                <span class="text-xs font-medium text-slate-400">{{ $placeholder ?? __('None') }}</span>
            </template>
        </div>

        <div class="space-y-2">
            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50">
                <x-ui.icon name="plus" class="size-4" />
                <span x-text="preview && ! removed ? @js(__('Change')) : @js(__('Upload'))"></span>
                <input type="file" name="{{ $name }}" accept="image/png,image/jpeg,image/webp" class="sr-only"
                       x-on:change="const f = $event.target.files[0]; if (f) { preview = URL.createObjectURL(f); removed = false; picked = true }">
            </label>

            @if ($current)
                <label class="flex items-center gap-2 text-sm text-slate-500" x-show="! picked">
                    <input type="checkbox" name="remove_{{ $name }}" value="1" x-model="removed" class="size-4 rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                    {{ __('Remove') }}
                </label>
            @endif
        </div>
    </div>

    @if ($error)
        <p class="flex items-center gap-1.5 text-sm text-red-600">
            <x-ui.icon name="alert" class="size-4 shrink-0" />
            {{ $error }}
        </p>
    @elseif ($hint)
        <p class="text-sm text-slate-500">{{ $hint }}</p>
    @endif
</div>
