<x-layouts.base :title="__('Site unavailable')" :brand="$teacher->display_name" class="bg-white">
    <main class="grid min-h-full place-items-center px-6 py-24">
        <div class="max-w-md text-center">
            <x-ui.avatar :initials="$teacher->initials()" :color="$teacher->brandColor()" size="lg" class="mx-auto" />
            <h1 class="mt-6 text-2xl font-semibold tracking-tight text-slate-900">{{ __('This site is temporarily unavailable') }}</h1>
            <p class="mt-3 text-slate-500">{{ __(':name\'s classes will be back soon. Please contact your teacher for details.', ['name' => $teacher->display_name]) }}</p>
            @if ($teacher->whatsapp)
                <x-ui.button :href="'https://wa.me/'.ltrim($teacher->whatsapp, '+')" target="_blank" rel="noopener" variant="secondary" class="mt-8">
                    <x-ui.icon name="whatsapp" class="size-4" /> {{ __('Contact on WhatsApp') }}
                </x-ui.button>
            @endif
        </div>
    </main>
</x-layouts.base>
