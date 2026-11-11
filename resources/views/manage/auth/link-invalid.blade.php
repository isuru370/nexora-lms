<x-layouts.base :title="__('Link expired')" class="bg-white">
    <main class="grid min-h-full place-items-center px-6 py-24">
        <div class="max-w-md text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-accent-50 text-accent-600 ring-1 ring-accent-100">
                <x-ui.icon name="link" class="size-7" />
            </span>
            <h1 class="mt-6 text-2xl font-semibold tracking-tight text-slate-900">{{ __('This link is no longer valid') }}</h1>
            <p class="mt-3 text-slate-500">{{ __('It may have expired or already been used. Ask for a new link, or reset your password from the login page.') }}</p>

            @if (app(\App\Tenancy\CurrentTeacher::class)->check())
                <div class="mt-8 flex justify-center gap-3">
                    <x-ui.button :href="route('manage.password.request')">{{ __('Get a new link') }}</x-ui.button>
                    <x-ui.button :href="route('manage.login')" variant="secondary">{{ __('Log in') }}</x-ui.button>
                </div>
            @endif
        </div>
    </main>
</x-layouts.base>
