<x-layouts.base title="{{ __('Online classes for Sri Lankan teachers') }}" class="bg-white">
    <div class="bg-hero relative overflow-hidden">
        <div class="bg-grid pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]"></div>

        <header class="relative mx-auto flex h-20 max-w-6xl items-center justify-between px-5 sm:px-8">
            <x-ui.logo dark />
            <x-ui.button :href="route('admin.login')" variant="ghost" size="sm" class="text-ink-100 hover:bg-white/10 hover:text-white">
                {{ __('Admin login') }}
            </x-ui.button>
        </header>

        <section class="relative mx-auto max-w-6xl px-5 pt-14 pb-24 sm:px-8 sm:pt-20 sm:pb-32">
            <x-ui.badge color="accent" class="bg-white/10 text-white ring-white/20">
                <x-ui.icon name="sparkles" class="size-3.5" /> {{ __('Built for Sri Lanka') }}
            </x-ui.badge>

            <h1 class="mt-6 max-w-3xl text-4xl font-semibold leading-[1.1] tracking-tight text-white sm:text-6xl">
                {{ __('Your own online class site.') }}
                <span class="text-gradient">{{ __('Ready in minutes.') }}</span>
            </h1>

            <p class="mt-6 max-w-xl text-lg leading-relaxed text-ink-200">
                {{ __('Video lessons, Zoom live classes and monthly fee collection under your own name — simple for you, simple for your students.') }}
            </p>

            <div class="mt-10 flex flex-wrap items-center gap-3">
                <x-ui.button variant="light" size="lg" type="button" disabled>
                    {{ __('Start 1-month free trial') }}
                    <x-ui.icon name="arrow-right" class="size-4" />
                </x-ui.button>
                <span class="text-sm text-ink-300">{{ __('Self sign-up opens soon.') }}</span>
            </div>
        </section>
    </div>

    <section class="mx-auto grid max-w-6xl gap-5 px-5 py-16 sm:grid-cols-3 sm:px-8">
        @foreach ([
            ['icon' => 'play', 'title' => __('Video lessons'), 'text' => __('Paste a YouTube link. Only students who paid can watch.')],
            ['icon' => 'video', 'title' => __('Live classes'), 'text' => __('Zoom provided by Nexora. Students join with one tap.')],
            ['icon' => 'wallet', 'title' => __('Fee collection'), 'text' => __('Monthly payments, reminders and a clear list of who has paid.')],
        ] as $feature)
            <div class="rounded-2xl bg-white p-6 shadow-card ring-1 ring-slate-200/70">
                <span class="grid size-11 place-items-center rounded-xl bg-gradient-to-br from-accent-700 to-accent-500 text-white shadow-sm shadow-accent-600/30">
                    <x-ui.icon :name="$feature['icon']" class="size-5" />
                </span>
                <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $feature['title'] }}</h3>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $feature['text'] }}</p>
            </div>
        @endforeach
    </section>
</x-layouts.base>
