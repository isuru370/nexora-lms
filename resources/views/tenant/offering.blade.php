<x-layouts.student :title="$offering->title">
    @error('lesson')
        <div class="mb-6 flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-amber-200">
            <x-ui.icon name="alert" class="size-5 shrink-0" /> {{ $message }}
        </div>
    @enderror

    {{-- Header --}}
    <section class="bg-hero relative overflow-hidden rounded-3xl p-6 text-white sm:p-8">
        <div class="bg-grid pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_top_right,black,transparent_70%)]"></div>
        <div class="relative grid gap-6 sm:grid-cols-[1fr_auto] sm:items-end">
            <div>
                <x-ui.badge class="bg-white/15 text-white ring-white/25">
                    <x-ui.icon :name="$offering->isClass() ? 'calendar' : 'book'" class="size-3.5" /> {{ $offering->type->label() }}
                </x-ui.badge>
                <h1 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $offering->title }}</h1>
                <p class="mt-1 text-sm text-ink-100">
                    {{ $offering->subject }}
                    @if ($offering->grade) · {{ $offering->grade }} @endif
                    @if ($offering->mediumLabel()) · {{ $offering->mediumLabel() }} @endif
                    · {{ trans_choice('{0} No lessons yet|{1} 1 lesson|[2,*] :count lessons', $lessonsCount, ['count' => $lessonsCount]) }}
                </p>
            </div>

            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur sm:min-w-64">
                <p class="text-sm text-ink-100">{{ $offering->isClass() ? __('Monthly fee') : __('Course fee') }}</p>
                <p class="mt-0.5 text-2xl font-semibold">{{ $offering->feeLabel() }}</p>

                <div class="mt-4">
                    @if (! auth()->check())
                        <x-ui.button :href="route('student.register')" variant="light" block>{{ __('Register to join') }}</x-ui.button>
                        <p class="mt-2 text-center text-xs text-ink-100">{{ __('Already registered?') }} <a href="{{ route('student.login') }}" class="font-semibold text-white underline">{{ __('Log in') }}</a></p>
                    @elseif (! $student)
                        <p class="text-sm text-ink-100">{{ __('Log in with a student account to join.') }}</p>
                    @elseif (! $enrolment)
                        <form method="POST" action="{{ route('student.enrol', $offering) }}">
                            @csrf
                            <x-ui.button variant="light" block>{{ __('Join class') }}</x-ui.button>
                        </form>
                    @elseif ($enrolment->status === \App\Enums\EnrolmentStatus::Pending)
                        <x-ui.button type="button" variant="light" block disabled>{{ __('Payment opens soon') }}</x-ui.button>
                    @else
                        <p class="flex items-center gap-2 text-sm font-medium"><x-ui.icon name="check-circle" class="size-5 text-emerald-300" /> {{ __('You are in this class') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if ($offering->description)
        <x-ui.card class="mt-6" :title="__('About')">
            <p class="whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ $offering->description }}</p>
        </x-ui.card>
    @endif

    {{-- Lessons --}}
    <section class="mt-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Lessons') }}</h2>

            @if ($months->count() > 1)
                <nav class="-mx-1 flex max-w-full gap-1 overflow-x-auto px-1 pb-1">
                    @foreach ($months as $m)
                        <a href="{{ route('tenant.offerings.show', ['offering' => $offering, 'month' => $m]) }}"
                           @class(['shrink-0 rounded-full px-3.5 py-1.5 text-sm font-medium transition', 'bg-ink-900 text-white' => $m === $month, 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => $m !== $month])>
                            {{ \App\Support\Month::label($m) }}
                        </a>
                    @endforeach
                </nav>
            @elseif ($month)
                <span class="text-sm text-slate-500">{{ \App\Support\Month::label($month) }}</span>
            @endif
        </div>

        @if ($rows->isEmpty())
            <x-ui.card class="mt-4">
                <x-ui.empty-state icon="play" :title="__('No lessons yet')" :description="__('Lessons will appear here when the teacher adds them.')" />
            </x-ui.card>
        @else
            @php
                $firstLocked = $rows->first(fn ($r) => $r['access']->denied());
            @endphp

            @if ($firstLocked && $student && $firstLocked['access']->reason === \App\Services\Access\AccessDecision::UNPAID_MONTH)
                <div class="mt-4 flex flex-col gap-3 rounded-2xl bg-accent-50 px-5 py-4 ring-1 ring-accent-100 sm:flex-row sm:items-center sm:justify-between">
                    <p class="flex items-center gap-2 text-sm font-medium text-accent-800">
                        <x-ui.icon name="wallet" class="size-5" /> {{ $firstLocked['access']->message() }}
                    </p>
                    <x-ui.button type="button" variant="brand" size="sm" disabled>{{ __('Pay for :month', ['month' => \App\Support\Month::name($firstLocked['access']->month)]) }} · {{ __('soon') }}</x-ui.button>
                </div>
            @endif

            <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($rows as $row)
                    @php
                        $lesson = $row['lesson'];
                        $open = $row['access']->allowed;
                    @endphp
                    <li>
                        @if ($open)
                            <a href="{{ route('tenant.lessons.show', $lesson) }}" class="group flex gap-4 rounded-2xl bg-white p-3 shadow-card ring-1 ring-slate-200/70 transition hover:ring-brand/40">
                                <span class="relative aspect-video w-32 shrink-0 overflow-hidden rounded-xl bg-slate-900">
                                    <img src="{{ $lesson->thumbnailUrl() }}" alt="" loading="lazy" class="size-full object-cover opacity-90 transition group-hover:scale-105">
                                    <span class="absolute inset-0 grid place-items-center"><span class="grid size-9 place-items-center rounded-full bg-white/90 text-ink-900 shadow"><x-ui.icon name="play" class="size-5" /></span></span>
                                </span>
                                <span class="min-w-0 py-1">
                                    <span class="line-clamp-2 text-sm font-semibold text-slate-900 group-hover:text-brand">{{ $lesson->title }}</span>
                                    @if ($lesson->visibility === \App\Enums\LessonVisibility::Public)
                                        <x-ui.badge color="green" class="mt-2">{{ __('Free preview') }}</x-ui.badge>
                                    @elseif ($lesson->duration_minutes)
                                        <span class="mt-2 block text-xs text-slate-500">{{ __(':n min', ['n' => $lesson->duration_minutes]) }}</span>
                                    @endif
                                </span>
                            </a>
                        @else
                            <div class="flex gap-4 rounded-2xl bg-white/60 p-3 ring-1 ring-slate-200/70">
                                <span class="grid aspect-video w-32 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-slate-200 to-slate-100 text-slate-400">
                                    <x-ui.icon name="shield" class="size-6" />
                                </span>
                                <span class="min-w-0 py-1">
                                    <span class="line-clamp-2 text-sm font-semibold text-slate-500">{{ $lesson->title }}</span>
                                    <span class="mt-2 block text-xs text-slate-400">{{ $row['access']->message() }}</span>
                                </span>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.student>
