@if (session('status'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 6000)"
        x-transition.opacity.duration.200ms
        role="status"
        {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200']) }}
    >
        <x-ui.icon name="check-circle" class="mt-0.5 size-5 shrink-0 text-emerald-600" />
        <p class="flex-1">{{ session('status') }}</p>
        <button type="button" class="text-emerald-700/70 hover:text-emerald-900" x-on:click="show = false" aria-label="{{ __('Close') }}">
            <x-ui.icon name="x" class="size-4" />
        </button>
    </div>
@endif
