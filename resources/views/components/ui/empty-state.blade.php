@props(['title', 'description' => null, 'icon' => 'sparkles'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center px-6 py-12 text-center']) }}>
    <span class="grid size-12 place-items-center rounded-2xl bg-gold-50 text-gold-600 ring-1 ring-gold-100">
        <x-ui.icon :name="$icon" class="size-6" />
    </span>
    <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $description }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
