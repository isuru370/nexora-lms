@props(['dark' => false])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <img src="{{ asset('images/nexora-icon.png') }}" alt="" class="size-9 rounded-xl shadow-sm ring-1 ring-black/5">
    <span @class(['text-lg font-semibold tracking-tight', 'text-white' => $dark, 'text-ink-950' => ! $dark])>
        Nexora<span @class(['text-accent-300' => $dark, 'text-accent-600' => ! $dark])>LMS</span>
    </span>
</span>
