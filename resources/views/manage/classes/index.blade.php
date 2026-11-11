<x-layouts.manage :title="__('Classes')">
    <x-slot:actions>
        <x-ui.button :href="route('manage.classes.create')" icon="plus" size="sm">{{ __('New class') }}</x-ui.button>
    </x-slot:actions>

    @if ($offerings->isEmpty())
        <x-ui.card>
            <x-ui.empty-state icon="book" :title="__('Create your first class')" :description="__('A monthly class (students pay every month) or a course (students pay once). You can add many subjects.')">
                <x-ui.button :href="route('manage.classes.create')" icon="plus">{{ __('New class') }}</x-ui.button>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="space-y-8">
            @foreach ($offerings as $subject => $group)
                <section>
                    <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        {{ $subject }} <span class="rounded-full bg-slate-200/70 px-2 py-0.5 text-xs text-slate-600">{{ $group->count() }}</span>
                    </h2>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($group as $offering)
                            <a href="{{ route('manage.classes.show', $offering) }}" class="group flex flex-col rounded-2xl bg-white p-5 shadow-card ring-1 ring-slate-200/70 transition hover:-translate-y-0.5 hover:shadow-float">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="grid size-11 place-items-center rounded-xl bg-gradient-to-br from-accent-700 to-accent-500 text-white shadow-sm">
                                        <x-ui.icon :name="$offering->isClass() ? 'calendar' : 'book'" class="size-5" />
                                    </span>
                                    @if ($offering->is_published)
                                        <x-ui.badge color="green">{{ __('Published') }}</x-ui.badge>
                                    @else
                                        <x-ui.badge>{{ __('Draft') }}</x-ui.badge>
                                    @endif
                                </div>

                                <h3 class="mt-4 text-base font-semibold text-slate-900 group-hover:text-accent-700">{{ $offering->title }}</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $offering->type->label() }}
                                    @if ($offering->grade) · {{ $offering->grade }} @endif
                                    @if ($offering->mediumLabel()) · {{ $offering->mediumLabel() }} @endif
                                </p>

                                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-sm">
                                    <span class="font-semibold text-slate-900">{{ $offering->feeLabel() }}</span>
                                    <span class="flex items-center gap-3 text-slate-500">
                                        <span class="flex items-center gap-1"><x-ui.icon name="play" class="size-4" /> {{ $offering->lessons_count }}</span>
                                        <span class="flex items-center gap-1"><x-ui.icon name="users" class="size-4" /> {{ $offering->students_count }}</span>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</x-layouts.manage>
