<x-layouts.manage :title="$offering->title">
    <x-slot:actions>
        <x-ui.button :href="route('manage.classes.edit', $offering)" variant="secondary" size="sm" icon="settings" class="hidden sm:inline-flex">{{ __('Edit') }}</x-ui.button>
        <x-ui.button :href="route('manage.lessons.create', $offering)" size="sm" icon="plus">{{ __('Add lessons') }}</x-ui.button>
    </x-slot:actions>

    @error('offering')
        <div class="mb-6 flex items-center gap-2 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200">
            <x-ui.icon name="alert" class="size-5 shrink-0" /> {{ $message }}
        </div>
    @enderror

    {{-- Summary --}}
    <div class="bg-hero relative overflow-hidden rounded-3xl p-6 text-white sm:p-8">
        <div class="bg-grid pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_top_right,black,transparent_70%)]"></div>
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge class="bg-white/15 text-white ring-white/20">{{ $offering->type->label() }}</x-ui.badge>
                    @if ($offering->is_published)
                        <x-ui.badge class="bg-emerald-400/20 text-emerald-100 ring-emerald-300/30">{{ __('Published') }}</x-ui.badge>
                    @else
                        <x-ui.badge class="bg-white/10 text-white/80 ring-white/20">{{ __('Draft') }}</x-ui.badge>
                    @endif
                </div>
                <h2 class="mt-3 text-2xl font-semibold tracking-tight">{{ $offering->title }}</h2>
                <p class="mt-1 text-sm text-ink-100">
                    {{ $offering->subject }}
                    @if ($offering->grade) · {{ $offering->grade }} @endif
                    @if ($offering->mediumLabel()) · {{ $offering->mediumLabel() }} @endif
                </p>
            </div>
            <div class="grid grid-cols-3 gap-6 text-sm">
                <div><p class="text-ink-100">{{ __('Fee') }}</p><p class="mt-0.5 text-lg font-semibold">{{ $offering->feeLabel() }}</p></div>
                <div><p class="text-ink-100">{{ __('Lessons') }}</p><p class="mt-0.5 text-lg font-semibold">{{ $lessonsCount }}</p></div>
                <div><p class="text-ink-100">{{ __('Students') }}</p><p class="mt-0.5 text-lg font-semibold">{{ $studentsCount }}</p></div>
            </div>
        </div>
    </div>

    @if ($offering->isClass())
        <div class="mt-4 flex flex-wrap gap-2 text-xs">
            <x-ui.badge color="ink">{{ __('Paid months:') }} {{ $offering->old_lessons_access->label() }}</x-ui.badge>
            <x-ui.badge color="ink">{{ $offering->past_lessons_access ? __('Backlog open for new students') : __('No backlog for new students') }}</x-ui.badge>
            <x-ui.badge color="ink">{{ $offering->grace_days ? trans_choice('{1} 1 grace day|[2,*] :count grace days', $offering->grace_days, ['count' => $offering->grace_days]) : __('No grace days') }}</x-ui.badge>
            <a href="{{ route('manage.classes.edit', $offering) }}" class="font-medium text-accent-700 hover:underline">{{ __('Change') }}</a>
        </div>
    @endif

    {{-- Lessons --}}
    <div class="mt-8 space-y-6">
        @if ($lessonsCount === 0)
            <x-ui.card>
                <x-ui.empty-state icon="play" :title="__('No lessons yet')" :description="__('Upload your video to YouTube as Unlisted, then paste the link here. You can paste many links at once.')">
                    <x-ui.button :href="route('manage.lessons.create', $offering)" icon="plus">{{ __('Add lessons') }}</x-ui.button>
                </x-ui.empty-state>
            </x-ui.card>
        @else
            @foreach ($lessonGroups as $month => $lessons)
                <x-ui.card :padding="false" :title="$month ? \App\Support\Month::label($month) : __('Lessons')" :description="trans_choice('{1} 1 lesson|[2,*] :count lessons', $lessons->count(), ['count' => $lessons->count()])">
                    <ul class="divide-y divide-slate-100">
                        @foreach ($lessons as $lesson)
                            <li class="flex items-center gap-4 px-5 py-3 sm:px-6">
                                <img src="{{ $lesson->thumbnailUrl() }}" alt="" loading="lazy" class="aspect-video w-28 shrink-0 rounded-lg bg-slate-100 object-cover ring-1 ring-slate-200">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-900">{{ $lesson->title }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        <x-ui.badge :color="$lesson->visibility->color()">{{ $lesson->visibility->label() }}</x-ui.badge>
                                        @unless ($lesson->is_published)
                                            <x-ui.badge color="amber">{{ __('Hidden') }}</x-ui.badge>
                                        @endunless
                                        @if ($lesson->available_until)
                                            <x-ui.badge>{{ __('Until :date', ['date' => $lesson->available_until->format('d M')]) }}</x-ui.badge>
                                        @endif
                                    </div>
                                </div>
                                <x-ui.button :href="route('manage.lessons.edit', [$offering, $lesson])" variant="ghost" size="sm">{{ __('Edit') }}</x-ui.button>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endforeach
        @endif
    </div>
</x-layouts.manage>
