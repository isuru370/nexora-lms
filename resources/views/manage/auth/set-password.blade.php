<x-layouts.auth
    :teacher="$currentTeacher"
    :title="__('Set your password')"
    :subtitle="__('Hello :name, choose a password for your teacher panel.', ['name' => $user->name])"
    :panel-title="__('Welcome to NexoraLMS.')"
    :panel-text="__('One step left: choose a password, then start adding your classes.')"
>
    <div class="mb-6 rounded-xl bg-slate-50 px-4 py-3 text-sm ring-1 ring-slate-200/70">
        <p class="text-slate-500">{{ __('You will log in with') }}</p>
        <p class="mt-0.5 font-medium text-slate-900">{{ $user->email }}</p>
        @if ($user->phone)
            <p class="text-slate-500">{{ __('or') }} {{ \App\Support\Phone::display($user->phone) }}</p>
        @endif
    </div>

    <form method="POST" action="{{ $action }}" class="space-y-5">
        @csrf

        <x-ui.input name="password" type="password" :label="__('New password')" :hint="__('At least 8 characters.')" autocomplete="new-password" required autofocus />
        <x-ui.input name="password_confirmation" type="password" :label="__('Confirm password')" autocomplete="new-password" required />

        <x-ui.button block size="lg">{{ __('Save password and log in') }}</x-ui.button>
    </form>
</x-layouts.auth>
