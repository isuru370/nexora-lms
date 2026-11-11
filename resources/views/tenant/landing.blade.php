<x-layouts.base :title="__('Online classes')" :brand="$teacher->display_name" style="--color-brand: {{ $teacher->brandColor() }}" class="bg-white">
    <header class="absolute inset-x-0 top-0 z-10">
        <div class="mx-auto flex h-20 max-w-6xl items-center justify-between px-5 sm:px-8">
            <span class="flex items-center gap-3">
                <x-ui.avatar :initials="$teacher->initials()" :color="$teacher->brandColor()" size="sm" class="ring-white/20" />
                <span class="text-base font-semibold text-white">{{ $teacher->display_name }}</span>
            </span>
            <div class="flex items-center gap-2">
                @auth
                    <x-ui.button :href="route('student.dashboard')" variant="gold" size="sm">{{ __('My dashboard') }}</x-ui.button>
                @else
                    <x-ui.button :href="route('student.login')" variant="ghost" size="sm" class="text-navy-100 hover:bg-white/10 hover:text-white">{{ __('Log in') }}</x-ui.button>
                    <x-ui.button :href="route('student.register')" variant="gold" size="sm">{{ __('Register') }}</x-ui.button>
                @endauth
            </div>
        </div>
    </header>

    <section class="bg-hero relative overflow-hidden">
        <div class="bg-grid pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]"></div>
        <div class="relative mx-auto max-w-6xl px-5 pt-36 pb-24 text-center sm:px-8 sm:pt-44 sm:pb-32">
            <x-ui.avatar :initials="$teacher->initials()" :color="$teacher->brandColor()" size="xl" class="mx-auto ring-4 ring-white/10" />
            <h1 class="mt-6 text-4xl font-semibold tracking-tight text-white sm:text-5xl">{{ $teacher->display_name }}</h1>
            @if ($teacher->subject_line)
                <p class="mt-3 text-lg text-navy-200">{{ $teacher->subject_line }}</p>
            @endif

            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                <x-ui.button :href="route('student.register')" variant="gold" size="lg">
                    {{ __('Join a class') }} <x-ui.icon name="arrow-right" class="size-4" />
                </x-ui.button>
                @if ($teacher->whatsapp)
                    <x-ui.button :href="'https://wa.me/'.ltrim($teacher->whatsapp, '+')" target="_blank" rel="noopener" variant="ghost" size="lg" class="text-white ring-1 ring-white/20 hover:bg-white/10 hover:text-white">
                        <x-ui.icon name="whatsapp" class="size-4" /> {{ __('WhatsApp') }}
                    </x-ui.button>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-5 py-16 sm:px-8">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">{{ __('Classes') }}</h2>
        <x-ui.card class="mt-6">
            <x-ui.empty-state icon="book" :title="__('Classes are coming soon')" :description="__('This teacher is setting up their classes. Register now to be the first to know.')">
                <x-ui.button :href="route('student.register')" variant="brand">{{ __('Register') }}</x-ui.button>
            </x-ui.empty-state>
        </x-ui.card>
    </section>

    <footer class="border-t border-slate-100 py-8 text-center text-sm text-slate-500">
        {{ __('Powered by') }} <span class="font-semibold text-navy-900">Nexora<span class="text-gold-600">LMS</span></span>
    </footer>
</x-layouts.base>
