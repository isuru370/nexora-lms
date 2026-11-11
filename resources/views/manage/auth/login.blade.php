<x-layouts.auth
    :teacher="$currentTeacher"
    :title="__('Teacher panel')"
    :subtitle="__('Log in to manage your classes and students.')"
    :panel-title="__('Everything for your classes.')"
    :panel-text="__('Lessons, live classes, students and payments — managed in one place.')"
>
    <form method="POST" action="{{ route('manage.login.store') }}" class="space-y-5">
        @csrf

        <x-ui.input name="phone" type="tel" :label="__('Mobile number')" placeholder="077 123 4567" inputmode="tel" autocomplete="tel" required autofocus />
        <x-ui.input name="password" type="password" :label="__('Password')" autocomplete="current-password" required />

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-ink-900 focus:ring-ink-500">
            {{ __('Keep me logged in') }}
        </label>

        <x-ui.button block size="lg">{{ __('Log in') }}</x-ui.button>
    </form>
</x-layouts.auth>
