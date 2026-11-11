{{--
    Student portal shell: mobile-first, top bar with the teacher's brand,
    bottom tab bar on phones, inline tabs on desktop.
--}}
@props(['title'])

@php
    $teacher = app(\App\Tenancy\CurrentTeacher::class)->require();
    $tabs = [
        ['label' => __('Home'), 'icon' => 'home', 'route' => 'student.dashboard'],
        ['label' => __('Lessons'), 'icon' => 'play', 'route' => null],
        ['label' => __('Live'), 'icon' => 'video', 'route' => null],
        ['label' => __('Payments'), 'icon' => 'wallet', 'route' => null],
    ];
@endphp

<x-layouts.base :title="$title" :brand="$teacher->display_name" style="--color-brand: {{ $teacher->brandColor() }}">
    <div class="min-h-full pb-20 sm:pb-0">
        <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/85 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-5xl items-center gap-4 px-4 sm:px-6">
                <a href="{{ route('student.dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <x-ui.avatar :initials="$teacher->initials()" :color="$teacher->brandColor()" size="sm" />
                    <span class="truncate text-base font-semibold text-slate-900">{{ $teacher->display_name }}</span>
                </a>

                <nav class="ml-6 hidden flex-1 items-center gap-1 sm:flex">
                    @foreach ($tabs as $tab)
                        @if ($tab['route'])
                            <a href="{{ route($tab['route']) }}" @class([
                                'rounded-lg px-3 py-2 text-sm font-medium transition',
                                'bg-slate-100 text-slate-900' => request()->routeIs($tab['route']),
                                'text-slate-600 hover:text-slate-900' => ! request()->routeIs($tab['route']),
                            ])>{{ $tab['label'] }}</a>
                        @else
                            <span class="cursor-default rounded-lg px-3 py-2 text-sm font-medium text-slate-400" title="{{ __('Coming soon') }}">{{ $tab['label'] }}</span>
                        @endif
                    @endforeach
                </nav>

                <div class="ml-auto flex items-center gap-1">
                    <span class="relative rounded-full p-2 text-slate-500" title="{{ __('Notifications') }}">
                        <x-ui.icon name="bell" class="size-5" />
                    </span>

                    <div x-data="{ open: false }" class="relative">
                        <button type="button" class="flex items-center gap-1 rounded-full p-0.5 hover:ring-2 hover:ring-slate-200" x-on:click="open = ! open" x-on:click.outside="open = false">
                            <x-ui.avatar :initials="auth()->user()->initials()" size="sm" color="var(--color-ink-700)" />
                        </button>
                        <div x-cloak x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-56 rounded-2xl bg-white p-2 shadow-float ring-1 ring-slate-200">
                            <div class="px-3 py-2">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ \App\Support\Phone::display(auth()->user()->phone) }}</p>
                            </div>
                            <form method="POST" action="{{ route('student.logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <x-ui.icon name="logout" class="size-4" /> {{ __('Log out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
            <x-ui.flash class="mb-6" />
            {{ $slot }}
        </main>

        {{-- Bottom tab bar (phones) --}}
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur sm:hidden">
            <div class="grid grid-cols-4">
                @foreach ($tabs as $tab)
                    @php
                        $active = $tab['route'] && request()->routeIs($tab['route']);
                    @endphp
                    @if ($tab['route'])
                        <a href="{{ route($tab['route']) }}" @class(['flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium', 'text-brand' => $active, 'text-slate-500' => ! $active])>
                            <x-ui.icon :name="$tab['icon']" class="size-5" />
                            {{ $tab['label'] }}
                        </a>
                    @else
                        <span class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium text-slate-300">
                            <x-ui.icon :name="$tab['icon']" class="size-5" />
                            {{ $tab['label'] }}
                        </span>
                    @endif
                @endforeach
            </div>
        </nav>
    </div>
</x-layouts.base>
