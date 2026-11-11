<x-layouts.student :title="__('Home')">
    <section class="bg-hero relative overflow-hidden rounded-3xl px-6 py-8 sm:px-8 sm:py-10">
        <div class="bg-grid pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_top_right,black,transparent_70%)]"></div>
        <div class="relative">
            <p class="text-sm font-medium text-accent-100">{{ __('Hello,') }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-white sm:text-3xl">{{ $user->name }}</h1>
            <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-ink-200">
                <x-ui.badge color="accent" class="bg-white/10 text-white ring-white/20">{{ $student?->student_code }}</x-ui.badge>
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

        <x-ui.card :title="__('Continue watching')" :padding="$recent->isEmpty()">
            @forelse ($recent as $view)
                <a href="{{ route('tenant.lessons.show', $view->lesson) }}" class="flex items-center gap-3 border-b border-slate-100 px-5 py-3 last:border-0 hover:bg-slate-50 sm:px-6">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand text-white"><x-ui.icon name="play" class="size-4" /></span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-slate-900">{{ $view->lesson->title }}</span>
                        <span class="block truncate text-xs text-slate-500">{{ $view->lesson->offering?->title }}</span>
                    </span>
                </a>
            @empty
                <x-ui.empty-state class="!py-6" icon="play" :title="__('No lessons watched yet')" :description="__('Lessons you open will show up here.')" />
            @endforelse
        </x-ui.card>
    </div>

    <div class="mt-8 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('My classes') }}</h2>
        @if ($enrolments->isNotEmpty())
            <a href="{{ route('student.classes') }}" class="text-sm font-medium text-brand hover:underline">{{ __('See all') }}</a>
        @endif
    </div>

    @if ($enrolments->isEmpty())
        <x-ui.card class="mt-4">
            <x-ui.empty-state icon="book" :title="__('You have not joined a class yet')" :description="__('Pick one of the classes below and tap Join class.')" />
        </x-ui.card>
    @else
        <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($enrolments as $enrolment)
                <x-offering.card :offering="$enrolment->offering" />
            @endforeach
        </div>
    @endif

    @include('student.partials.available')
</x-layouts.student>
