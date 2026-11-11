{{--
    Split-screen auth layout used by every login / register page.
    Left: brand panel (Nexora, or the teacher's brand on a teacher site).
    Right: the form. On mobile only the form shows, with a compact header.
--}}
@props([
    'title',
    'subtitle' => null,
    'teacher' => null,
    'panelTitle' => null,
    'panelText' => null,
])

@php
    $brandName = $teacher?->display_name ?? config('app.name');
    $brandColor = $teacher?->brandColor() ?? '#dc2626';
@endphp

<x-layouts.base :title="$title" :brand="$brandName" style="--color-brand: {{ $brandColor }}" class="bg-white">
    <div class="flex min-h-full">
        {{-- Brand panel --}}
        <aside class="bg-hero relative hidden w-[44%] max-w-xl overflow-hidden lg:flex lg:flex-col lg:justify-between lg:p-12">
            <div class="bg-grid pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_top_left,black,transparent_70%)]"></div>

            <div class="relative">
                @if ($teacher)
                    <x-teacher.brand :teacher="$teacher" dark size="md" />
                @else
                    <x-ui.logo dark />
                @endif
            </div>

            <div class="relative">
                <h2 class="text-3xl font-semibold leading-tight tracking-tight text-white">
                    {{ $panelTitle ?? __('Teach online. Get paid on time.') }}
                </h2>
                <p class="mt-4 max-w-md text-base leading-relaxed text-ink-200">
                    {{ $panelText ?? __('Classes, video lessons, live Zoom sessions and fee collection — in one simple place.') }}
                </p>
            </div>

            <p class="relative text-sm text-ink-300">
                @if ($teacher)
                    {{ __('Powered by') }} <span class="font-semibold text-white">NexoraLMS</span>
                @else
                    &copy; {{ now()->year }} NexoraLMS
                @endif
            </p>
        </aside>

        {{-- Form --}}
        <main class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-10 lg:px-16">
            <div class="mx-auto w-full max-w-sm">
                <div class="mb-8 lg:hidden">
                    @if ($teacher)
                        <x-teacher.brand :teacher="$teacher" size="md" />
                    @else
                        <x-ui.logo />
                    @endif
                </div>

                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-2 text-sm text-slate-500">{{ $subtitle }}</p>
                @endif

                <x-ui.flash class="mt-6" />

                <div class="mt-8">
                    {{ $slot }}
                </div>

                @isset($footer)
                    <div class="mt-8 text-center text-sm text-slate-500">{{ $footer }}</div>
                @endisset
            </div>
        </main>
    </div>
</x-layouts.base>
