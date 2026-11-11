<x-layouts.auth
    :teacher="$currentTeacher"
    :title="__('Teacher panel')"
    :subtitle="__('Log in to manage your classes and students.')"
    :panel-title="__('Everything for your classes.')"
    :panel-text="__('Lessons, live classes, students and payments — managed in one place.')"
>
    <form method="POST" action="{{ route('manage.login.store') }}" class="space-y-5">
        @csrf

        <x-ui.input name="login" :label="__('Email or mobile number')" placeholder="you@example.com" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus />

        <div class="space-y-1.5">
            <x-ui.input name="password" type="password" :label="__('Password')" autocomplete="current-password" required />
            <div class="text-right">
                <a href="{{ route('manage.password.request') }}" class="text-sm font-medium text-accent-700 hover:underline">{{ __('Forgot password?') }}</a>
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-accent-600 focus:ring-accent-500">
            {{ __('Keep me logged in') }}
        </label>

        <x-ui.button block size="lg">{{ __('Log in') }}</x-ui.button>
    </form>
</x-layouts.auth>
