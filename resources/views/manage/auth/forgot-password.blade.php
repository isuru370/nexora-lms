<x-layouts.auth
    :teacher="$currentTeacher"
    :title="__('Forgot your password?')"
    :subtitle="__('Enter your email and we will send you a link to set a new one.')"
>
    <form method="POST" action="{{ route('manage.password.email') }}" class="space-y-5">
        @csrf

        <x-ui.input name="email" type="email" :label="__('Email')" autocomplete="email" required autofocus />

        <x-ui.button block size="lg">{{ __('Send reset link') }}</x-ui.button>
    </form>

    <x-slot:footer>
        <a href="{{ route('manage.login') }}" class="font-semibold text-ink-800 hover:underline">{{ __('Back to login') }}</a>
    </x-slot:footer>
</x-layouts.auth>
