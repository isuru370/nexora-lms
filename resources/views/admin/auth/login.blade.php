<x-layouts.auth
    :title="__('Admin login')"
    :subtitle="__('NexoraLMS platform administration.')"
    :panel-title="__('Run the platform.')"
    :panel-text="__('Teachers, bills, Zoom rooms and settings — all in one place.')"
>
    <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
        @csrf

        <x-ui.input name="email" type="email" :label="__('Email')" autocomplete="username" required autofocus />
        <x-ui.input name="password" type="password" :label="__('Password')" autocomplete="current-password" required />

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-ink-900 focus:ring-ink-500">
            {{ __('Keep me logged in') }}
        </label>

        <x-ui.button block size="lg">{{ __('Log in') }}</x-ui.button>
    </form>
</x-layouts.auth>
