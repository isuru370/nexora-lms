{{-- Published classes the student has not joined yet. Expects $available. --}}
<section class="mt-10">
    <div class="flex items-end justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Classes you can join') }}</h2>
            <p class="mt-0.5 text-sm text-slate-500">{{ __('Open a class and tap Join class.') }}</p>
        </div>
    </div>

    @if ($available->isEmpty())
        <x-ui.card class="mt-4">
            <x-ui.empty-state icon="book" :title="__('No other classes right now')" :description="__('New classes from your teacher will show up here.')" />
        </x-ui.card>
    @else
        <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($available as $offering)
                <x-offering.card :offering="$offering" />
            @endforeach
        </div>
    @endif
</section>
