{{--
    The teacher's mark + name, used in headers and sidebars.
    Logo if uploaded, otherwise photo, otherwise initials.
--}}
@props(['teacher', 'dark' => false, 'size' => 'sm', 'subtitle' => null])

<span {{ $attributes->merge(['class' => 'flex min-w-0 items-center gap-3']) }}>
    @if ($teacher->logoUrl())
        <span @class(['grid shrink-0 place-items-center overflow-hidden rounded-xl bg-white p-1 ring-1', 'size-9' => $size === 'sm', 'size-11' => $size !== 'sm', 'ring-white/20' => $dark, 'ring-slate-200' => ! $dark])>
            <img src="{{ $teacher->logoUrl() }}" alt="" class="max-h-full max-w-full object-contain">
        </span>
    @else
        <x-ui.avatar :src="$teacher->photoUrl()" :initials="$teacher->initials()" :color="$teacher->brandColor()" :size="$size" :class="$dark ? 'ring-white/20' : ''" />
    @endif

    <span class="min-w-0">
        <span @class(['block truncate font-semibold', 'text-white' => $dark, 'text-slate-900' => ! $dark, 'text-sm' => $subtitle, 'text-base' => ! $subtitle])>{{ $teacher->display_name }}</span>
        @if ($subtitle)
            <span @class(['block truncate text-xs', 'text-ink-200' => $dark, 'text-slate-500' => ! $dark])>{{ $subtitle }}</span>
        @endif
    </span>
</span>
