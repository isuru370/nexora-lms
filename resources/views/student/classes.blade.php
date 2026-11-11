<x-layouts.student :title="__('My classes')">
    <div class="flex items-center justify-between gap-3">
        <h1 class="text-xl font-semibold tracking-tight text-slate-900">{{ __('My classes') }}</h1>
        <x-ui.button :href="route('tenant.home')" variant="secondary" size="sm">{{ __('Find classes') }}</x-ui.button>
    </div>

    @if ($enrolments->isEmpty())
        <x-ui.card class="mt-6">
            <x-ui.empty-state icon="book" :title="__('You have not joined a class yet')" :description="__('Open a class on the teacher\'s page and tap Join class.')">
                <x-ui.button :href="route('tenant.home')" variant="brand">{{ __('See classes') }}</x-ui.button>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($enrolments as $enrolment)
                <x-offering.card :offering="$enrolment->offering">
                    <p class="mt-2 text-xs text-slate-500">{{ trans_choice('{0} No lessons yet|{1} 1 lesson|[2,*] :count lessons', $enrolment->offering->lessons_count, ['count' => $enrolment->offering->lessons_count]) }}</p>
                    @if ($enrolment->status === \App\Enums\EnrolmentStatus::Pending)
                        <x-ui.badge color="amber" class="mt-2 self-start">{{ __('Waiting for payment') }}</x-ui.badge>
                    @endif
                </x-offering.card>
            @endforeach
        </div>
    @endif
</x-layouts.student>
