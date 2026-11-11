<x-layouts.student :title="__('Home')">
    <section class="bg-hero relative overflow-hidden rounded-3xl px-6 py-8 sm:px-8 sm:py-10">
        <div class="bg-grid pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_top_right,black,transparent_70%)]"></div>
        <div class="relative">
            <p class="text-sm font-medium text-gold-300">{{ __('Hello,') }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-white sm:text-3xl">{{ $user->name }}</h1>
            <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-navy-200">
                <x-ui.badge color="gold" class="bg-white/10 text-gold-200 ring-white/15">{{ $student?->student_code }}</x-ui.badge>
                @if ($student?->grade)
                    <span>· {{ $student->grade }}</span>
                @endif
                @if ($student?->school)
                    <span>· {{ $student->school }}</span>
                @endif
            </div>
        </div>
    </section>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <x-ui.card :title="__('Next live class')">
            <x-ui.empty-state class="!py-6" icon="video" :title="__('No live classes yet')" :description="__('When your teacher schedules a class, it shows here with a Join button.')" />
        </x-ui.card>

        <x-ui.card :title="__('Continue watching')">
            <x-ui.empty-state class="!py-6" icon="play" :title="__('No lessons yet')" :description="__('Your lessons will appear here after you join a class.')" />
        </x-ui.card>
    </div>

    <x-ui.card class="mt-4" :title="__('My classes')">
        <x-ui.empty-state icon="book" :title="__('You have not joined a class yet')" :description="__('Classes open soon. You will be able to join and pay right here.')" />
    </x-ui.card>
</x-layouts.student>
