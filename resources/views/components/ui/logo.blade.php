@props(['dark' => false])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-600 text-navy-950 shadow-sm">
        <x-ui.icon name="graduation" class="size-5" />
    </span>
    <span @class(['text-lg font-semibold tracking-tight', 'text-white' => $dark, 'text-navy-950' => ! $dark])>
        Nexora<span class="text-gold-500">LMS</span>
    </span>
</span>
