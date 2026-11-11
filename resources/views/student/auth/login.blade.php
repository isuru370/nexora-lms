<x-layouts.auth
    :teacher="$currentTeacher"
    :title="__('Welcome back')"
    :subtitle="__('Log in with your mobile number.')"
    :panel-title="__('Your classes, lessons and payments.')"
    :panel-text="__('Watch lessons, join live classes and pay fees — all from one place.')"
>
    <form method="POST" action="{{ route('student.login.store') }}" class="space-y-5">
        @csrf

        <x-ui.input name="phone" type="tel" :label="__('Mobile number')" placeholder="077 123 4567" inputmode="tel" autocomplete="tel" required autofocus />
        <x-ui.input name="password" type="password" :label="__('Password')" autocomplete="current-password" required />

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" checked class="size-4 rounded border-slate-300 text-navy-900 focus:ring-navy-500">
            {{ __('Keep me logged in') }}
        </label>

        <x-ui.button variant="brand" block size="lg">{{ __('Log in') }}</x-ui.button>
    </form>

    <x-slot:footer>
        {{ __('New here?') }}
        <a href="{{ route('student.register') }}" class="font-semibold text-navy-800 hover:underline">{{ __('Create an account') }}</a>
    </x-slot:footer>
</x-layouts.auth>
