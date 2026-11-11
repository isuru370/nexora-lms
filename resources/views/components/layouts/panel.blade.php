{{--
    Sidebar app shell shared by the Super Admin and the teacher panel.
    Slots: nav (sidebar links), brand (sidebar top), actions (page header buttons).
--}}
@props(['title', 'subtitle' => null, 'brandColor' => '#c9a227', 'pageTitle' => null, 'logoutRoute'])

<x-layouts.base :title="$title" :brand="$pageTitle" style="--color-brand: {{ $brandColor }}">
    <div x-data="{ sidebar: false }" class="min-h-full">
        {{-- Mobile sidebar backdrop --}}
        <div x-cloak x-show="sidebar" x-transition.opacity class="fixed inset-0 z-40 bg-navy-950/60 backdrop-blur-sm lg:hidden" x-on:click="sidebar = false"></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-navy-950 transition-transform duration-200 lg:translate-x-0"
            x-bind:class="sidebar && 'translate-x-0'"
        >
            <div class="flex h-16 items-center justify-between px-5">
                {{ $brand }}
                <button type="button" class="text-navy-300 hover:text-white lg:hidden" x-on:click="sidebar = false" aria-label="{{ __('Close menu') }}">
                    <x-ui.icon name="x" />
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                {{ $nav }}
            </nav>

            <div class="border-t border-white/5 p-3">
                <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                    <x-ui.avatar :initials="auth()->user()->initials()" size="sm" color="var(--color-navy-700)" class="ring-navy-800" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-navy-300">{{ auth()->user()->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route($logoutRoute) }}">
                        @csrf
                        <button type="submit" class="rounded-lg p-2 text-navy-300 hover:bg-white/5 hover:text-white" title="{{ __('Log out') }}">
                            <x-ui.icon name="logout" class="size-[18px]" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="lg:pl-72">
            <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200/70 bg-white/80 px-4 backdrop-blur sm:px-6 lg:px-8">
                <button type="button" class="-ml-1 rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" x-on:click="sidebar = true" aria-label="{{ __('Open menu') }}">
                    <x-ui.icon name="menu" />
                </button>
                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-lg font-semibold text-slate-900">{{ $title }}</h1>
                </div>
                @isset($actions)
                    <div class="flex items-center gap-2">{{ $actions }}</div>
                @endisset
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if ($subtitle)
                    <p class="mb-6 text-sm text-slate-500">{{ $subtitle }}</p>
                @endif

                <x-ui.flash class="mb-6" />

                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.base>
