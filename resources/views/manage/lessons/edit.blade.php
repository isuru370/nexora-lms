<x-layouts.manage :title="__('Edit lesson')" :subtitle="$offering->title">
    <form method="POST" action="{{ route('manage.lessons.update', [$offering, $lesson]) }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <x-ui.card>
            <div class="grid gap-6 sm:grid-cols-[14rem_1fr]">
                <div class="aspect-video overflow-hidden rounded-xl bg-slate-900 ring-1 ring-slate-200">
                    <iframe src="{{ $lesson->embedUrl() }}" class="size-full" allow="encrypted-media; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="space-y-5">
                    <x-ui.input name="title" :label="__('Title')" :value="$lesson->title" required />
                    <x-ui.input name="video" :label="__('YouTube link')" :value="'https://youtu.be/'.$lesson->video_ref" required />
                </div>
            </div>
            <x-ui.textarea name="description" class="mt-5" :label="__('Description')" :value="$lesson->description" :max="2000" rows="3" :placeholder="__('Optional notes for students')" />
        </x-ui.card>

        <x-ui.card :title="__('Access')">
            <div class="space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    @if ($offering->isClass())
                        <x-ui.input name="month" type="month" :label="__('Month')" :value="$lesson->month" required />
                    @endif
                    <x-ui.select name="visibility" :label="__('Who can watch')" :options="\App\Http\Controllers\Manage\LessonController::visibilityOptions()" :value="$lesson->visibility->value" />
                    <x-ui.input name="available_until" type="date" :label="__('Available until')" :value="$lesson->available_until?->toDateString()" :hint="__('Optional')" />
                    <x-ui.input name="duration_minutes" type="number" min="1" :label="__('Length (minutes)')" :value="$lesson->duration_minutes" :hint="__('Optional')" />
                </div>
                <x-ui.toggle name="is_published" :checked="$lesson->is_published" :label="__('Published')" :hint="__('Hidden lessons are not shown to students.')" />
            </div>
        </x-ui.card>

        <div class="flex items-center justify-between gap-3">
            <x-ui.button type="button" variant="ghost" class="text-red-600 hover:bg-red-50 hover:text-red-700"
                onclick="if (confirm(@js(__('Delete this lesson?')))) document.getElementById('delete-lesson').submit()">{{ __('Delete') }}</x-ui.button>
            <div class="flex gap-3">
                <x-ui.button :href="route('manage.classes.show', $offering)" variant="secondary">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button icon="check">{{ __('Save') }}</x-ui.button>
            </div>
        </div>
    </form>

    <form id="delete-lesson" method="POST" action="{{ route('manage.lessons.destroy', [$offering, $lesson]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts.manage>
