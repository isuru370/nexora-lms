<x-layouts.manage :title="__('Dashboard')">
    <x-slot:actions>
        <x-ui.button :href="route('tenant.home')" target="_blank" variant="secondary" size="sm" icon="external" class="hidden sm:inline-flex">{{ __('View site') }}</x-ui.button>
    </x-slot:actions>

    @if ($trialDaysLeft !== null && $teacher->status === \App\Enums\TeacherStatus::Trial)
        <div class="mb-6 flex flex-col gap-3 rounded-2xl bg-hero px-5 py-4 text-white sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="grid size-9 place-items-center rounded-xl bg-white/15 text-white"><x-ui.icon name="sparkles" class="size-5" /></span>
                <div>
                    <p class="text-sm font-semibold">{{ trans_choice('{0} Your free trial ends today|{1} 1 day left in your free trial|[2,*] :count days left in your free trial', $trialDaysLeft, ['count' => $trialDaysLeft]) }}</p>
                    <p class="text-xs text-ink-200">{{ __('All features are open during the trial.') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat :label="__('Income this month')" value="LKR 0" :hint="__('Payments start in Phase 2')" icon="wallet" tone="accent" />
        <x-ui.stat :label="__('Paid students')" value="0" :hint="__('This month')" icon="check-circle" tone="green" />
        <x-ui.stat :label="__('Registered students')" :value="number_format($studentsCount)" icon="users" />
        <x-ui.stat :label="__('Next live class')" value="—" :hint="__('Nothing scheduled')" icon="video" tone="blue" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2" :title="__('Newest students')" :padding="false">
            @forelse ($latestStudents as $student)
                <div class="flex items-center gap-4 border-b border-slate-100 px-5 py-3.5 last:border-0 sm:px-6">
                    <x-ui.avatar :initials="$student->user->initials()" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $student->user->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ \App\Support\Phone::display($student->user->phone) }} @if ($student->grade) · {{ $student->grade }} @endif</p>
                    </div>
                    <x-ui.badge color="ink">{{ $student->student_code }}</x-ui.badge>
                </div>
            @empty
                <x-ui.empty-state icon="users" :title="__('No students yet')" :description="__('Share your site link. Students register there and appear here.')" />
            @endforelse
        </x-ui.card>

        <x-ui.card :title="__('Get started')">
            @php
                $brandDone = $teacher->logo_path || $teacher->photo_path || $teacher->brand_color;
                $isOwner = auth()->user()->role === \App\Enums\Role::Teacher;
            @endphp
            <ul class="space-y-3 text-sm">
                <li>
                    @if ($isOwner && ! $brandDone)
                        <a href="{{ route('manage.settings') }}" class="group flex items-center gap-3 text-slate-700 hover:text-accent-700">
                            <span class="size-5 shrink-0 rounded-full ring-2 ring-slate-200 group-hover:ring-accent-300"></span>
                            {{ __('Add your photo, logo and colour') }}
                            <x-ui.icon name="arrow-right" class="ml-auto size-4 text-slate-400 group-hover:text-accent-600" />
                        </a>
                    @else
                        <span class="flex items-center gap-3 {{ $brandDone ? 'text-slate-400 line-through' : 'text-slate-500' }}">
                            @if ($brandDone)
                                <span class="grid size-5 shrink-0 place-items-center rounded-full bg-emerald-500 text-white"><x-ui.icon name="check" class="size-3" /></span>
                            @else
                                <span class="size-5 shrink-0 rounded-full ring-2 ring-slate-200"></span>
                            @endif
                            {{ __('Add your photo, logo and colour') }}
                        </span>
                    @endif
                </li>
                @foreach ([__('Create your first class'), __('Add your first lesson')] as $step)
                    <li class="flex items-center gap-3 text-slate-500">
                        <span class="size-5 shrink-0 rounded-full ring-2 ring-slate-200"></span>
                        {{ $step }}
                        <x-ui.badge class="ml-auto">{{ __('Soon') }}</x-ui.badge>
                    </li>
                @endforeach
                <li class="flex items-center gap-3 text-slate-700">
                    <span class="size-5 shrink-0 rounded-full ring-2 ring-slate-200"></span>
                    {{ __('Share your site link') }}
                </li>
            </ul>

            <div class="mt-5 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200/70" x-data="{ copied: false }">
                <p class="text-xs font-medium text-slate-500">{{ __('Your site') }}</p>
                <div class="mt-1 flex items-center gap-2">
                    <span class="flex-1 truncate text-sm font-medium text-ink-800">{{ $teacher->url() }}</span>
                    <button type="button" class="rounded-lg px-2 py-1 text-xs font-semibold text-ink-700 hover:bg-white"
                        x-on:click="navigator.clipboard.writeText(@js($teacher->url())); copied = true; setTimeout(() => copied = false, 2000)"
                        x-text="copied ? @js(__('Copied')) : @js(__('Copy'))"></button>
                </div>
                <x-ui.button
                    :href="'https://wa.me/?text='.rawurlencode(__('Join my online classes: :url', ['url' => $teacher->url()]))"
                    target="_blank" rel="noopener" variant="secondary" size="sm" block class="mt-3">
                    <x-ui.icon name="whatsapp" class="size-4" /> {{ __('Share on WhatsApp') }}
                </x-ui.button>
            </div>
        </x-ui.card>
    </div>
</x-layouts.manage>
