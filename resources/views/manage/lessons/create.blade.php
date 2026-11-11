<x-layouts.manage :title="__('Add lessons')" :subtitle="$offering->title">
    <form method="POST" action="{{ route('manage.lessons.store', $offering) }}" class="mx-auto max-w-3xl space-y-6"
          x-data="lessonLinks(@js(route('manage.youtube.lookup')), @js(old('links', '')))">
        @csrf

        <x-ui.card :title="__('YouTube links')" :description="__('Upload to YouTube as Unlisted, then paste the links here — one per line.')">
            <x-ui.textarea name="links" rows="5" x-model="text" x-on:input.debounce.500ms="refresh()"
                placeholder="https://youtu.be/…&#10;https://www.youtube.com/watch?v=…" />

            <ul class="mt-4 space-y-2" x-show="items.length" x-cloak>
                <template x-for="item in items" x-bind:key="item.line">
                    <li class="flex items-center gap-3 rounded-xl p-2 ring-1" x-bind:class="item.ok === false ? 'bg-red-50 ring-red-200' : 'bg-slate-50 ring-slate-200/70'">
                        <template x-if="item.thumbnail"><img x-bind:src="item.thumbnail" alt="" class="aspect-video w-24 rounded-md object-cover"></template>
                        <template x-if="! item.thumbnail"><span class="grid aspect-video w-24 place-items-center rounded-md bg-slate-200 text-xs text-slate-500" x-text="item.ok === false ? '✗' : '…'"></span></template>
                        <span class="min-w-0 flex-1 truncate text-sm" x-bind:class="item.ok === false ? 'text-red-700' : 'text-slate-700'"
                              x-text="item.ok === false ? @js(__('Not a YouTube link')) + ': ' + item.line : (item.title || @js(__('Title will be filled in automatically')))"></span>
                    </li>
                </template>
            </ul>
        </x-ui.card>

        <x-ui.card :title="__('Settings for these lessons')">
            <div class="space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    @if ($offering->isClass())
                        <x-ui.input name="month" type="month" :label="__('Month')" :value="$defaultMonth" required :hint="__('Students who paid for this month can watch.')" />
                    @endif
                    <x-ui.select name="visibility" :label="__('Who can watch')" :options="\App\Http\Controllers\Manage\LessonController::visibilityOptions()" value="paid" />
                </div>
                <x-ui.input name="available_until" type="date" :label="__('Available until')" :hint="__('Optional. After this date the lesson is locked for everyone.')" class="max-w-xs" />
                <x-ui.toggle name="is_published" :checked="true" :label="__('Publish now')" :hint="__('Turn off to add them hidden and publish later.')" />
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('manage.classes.show', $offering)" variant="secondary">{{ __('Cancel') }}</x-ui.button>
            <x-ui.button icon="check">{{ __('Add lessons') }}</x-ui.button>
        </div>
    </form>

    <script>
        function lessonLinks(lookupUrl, initial) {
            return {
                text: initial,
                items: [],
                cache: {},
                init() { this.refresh(); },
                refresh() {
                    const lines = this.text.split(/\r?\n/).map(l => l.trim()).filter(Boolean).slice(0, 50);
                    this.items = lines.map(line => this.cache[line] ?? { line, ok: null, title: null, thumbnail: null });
                    this.items.filter(i => i.ok === null && ! i.loading).forEach(item => {
                        item.loading = true;
                        fetch(lookupUrl + '?url=' + encodeURIComponent(item.line), { headers: { 'Accept': 'application/json' } })
                            .then(r => r.json())
                            .then(data => Object.assign(item, data.ok ? { ok: true, title: data.title, thumbnail: data.thumbnail } : { ok: false }))
                            .catch(() => Object.assign(item, { ok: false }))
                            .finally(() => { this.cache[item.line] = item; this.items = [...this.items]; });
                    });
                },
            };
        }
    </script>
</x-layouts.manage>
