<x-layouts.auth
    :teacher="$currentTeacher"
    :title="__('Create your account')"
    :subtitle="__('It takes about a minute.')"
    :panel-title="__('Join :name\'s classes.', ['name' => $currentTeacher->display_name])"
    :panel-text="__('Register once, then pick your class and pay online.')"
>
    <form method="POST" action="{{ route('student.register.store') }}" class="space-y-5">
        @csrf

        <x-ui.input name="name" :label="__('Full name')" autocomplete="name" required autofocus />
        <x-ui.input name="phone" type="tel" :label="__('Mobile number')" placeholder="077 123 4567" inputmode="tel" autocomplete="tel" :hint="__('You will log in with this number.')" required />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.input name="school" :label="__('School')" :hint="__('Optional')" />
            <x-ui.input name="grade" :label="__('Grade / batch')" :placeholder="__('e.g. 2027 A/L')" :hint="__('Optional')" />
        </div>

        <x-ui.input name="password" type="password" :label="__('Password')" :hint="__('At least 8 characters.')" autocomplete="new-password" required />
        <x-ui.input name="password_confirmation" type="password" :label="__('Confirm password')" autocomplete="new-password" required />

        <x-ui.button variant="brand" block size="lg">{{ __('Create account') }}</x-ui.button>
    </form>

    <x-slot:footer>
        {{ __('Already registered?') }}
        <a href="{{ route('student.login') }}" class="font-semibold text-navy-800 hover:underline">{{ __('Log in') }}</a>
    </x-slot:footer>
</x-layouts.auth>
