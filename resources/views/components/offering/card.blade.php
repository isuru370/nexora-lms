@props(['offering', 'href' => null])

<a href="{{ $href ?? route('tenant.offerings.show', $offering) }}" {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-slate-200/70 transition hover:-translate-y-0.5 hover:shadow-float']) }}>
    <div class="bg-hero relative flex h-28 items-end p-4">
        <div class="bg-grid pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_top_right,black,transparent_75%)]"></div>
        <x-ui.badge class="relative bg-white/15 text-white ring-white/25">
            <x-ui.icon :name="$offering->isClass() ? 'calendar' : 'book'" class="size-3.5" /> {{ $offering->type->label() }}
        </x-ui.badge>
    </div>
    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-base font-semibold text-slate-900 group-hover:text-brand">{{ $offering->title }}</h3>
        <p class="mt-1 text-sm text-slate-500">
            {{ $offering->subject }}
            @if ($offering->grade) · {{ $offering->grade }} @endif
            @if ($offering->mediumLabel()) · {{ $offering->mediumLabel() }} @endif
        </p>
        {{ $slot }}
        <div class="mt-auto flex items-center justify-between pt-4">
            <span class="text-sm font-semibold text-slate-900">{{ $offering->feeLabel() }}</span>
            <span class="flex items-center gap-1 text-sm font-medium text-brand">{{ __('View') }} <x-ui.icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" /></span>
        </div>
    </div>
</a>
