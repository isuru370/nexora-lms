@php
    $editing = $offering->exists;
    $type = old('type', $offering->type?->value ?? 'class');
    $mediums = \App\Models\Offering::MEDIUMS;
@endphp

<x-layouts.manage :title="$editing ? __('Edit :title', ['title' => $offering->title]) : __('New class')">
    <form method="POST" action="{{ $editing ? route('manage.classes.update', $offering) : route('manage.classes.store') }}"
          class="mx-auto max-w-3xl space-y-6" x-data="{ type: @js($type) }">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        @unless ($editing)
            <x-ui.card :title="__('What are you creating?')">
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['value' => 'class', 'icon' => 'calendar', 'title' => __('Monthly class'), 'text' => __('Runs month by month. Students pay every month.')],
                        ['value' => 'course', 'icon' => 'book', 'title' => __('Course'), 'text' => __('Fixed content. Students pay once.')],
                    ] as $option)
                        <label class="relative flex cursor-pointer gap-3 rounded-2xl p-4 ring-1 transition"
                               x-bind:class="type === '{{ $option['value'] }}' ? 'bg-accent-50/60 ring-2 ring-accent-500' : 'ring-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="type" value="{{ $option['value'] }}" class="sr-only" x-model="type">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl"
                                  x-bind:class="type === '{{ $option['value'] }}' ? 'bg-accent-600 text-white' : 'bg-slate-100 text-slate-500'">
                                <x-ui.icon :name="$option['icon']" class="size-5" />
                            </span>
                            <span>
                                <span class="block font-semibold text-slate-900">{{ $option['title'] }}</span>
                                <span class="mt-0.5 block text-sm text-slate-500">{{ $option['text'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </x-ui.card>
        @endunless

        <x-ui.card :title="__('Details')">
            <div class="space-y-5">
                <x-ui.input name="title" :label="__('Title')" :value="$offering->title" :placeholder="__('e.g. 2027 A/L Physics Theory')" required />

                <div class="grid gap-5 sm:grid-cols-3">
                    <x-ui.input name="subject" :label="__('Subject')" :value="$offering->subject" list="subjects" :placeholder="__('e.g. Physics')" required />
                    <x-ui.input name="grade" :label="__('Grade / batch')" :value="$offering->grade" :placeholder="__('e.g. 2027 A/L')" />
                    <x-ui.select name="medium" :label="__('Medium')" :options="$mediums" :value="$offering->medium" :placeholder="__('Any')" />
                </div>
                <datalist id="subjects">
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject }}"></option>
                    @endforeach
                </datalist>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-ui.input name="fee" type="text" inputmode="decimal" :label="__('Fee')" prefix="LKR" :value="$offering->fee !== null ? rtrim(rtrim((string) $offering->fee, '0'), '.') : ''" required />
                        <p class="mt-1.5 text-sm text-slate-500" x-text="type === 'class' ? @js(__('Charged every month.')) : @js(__('Charged once.'))"></p>
                    </div>
                    <div x-show="type === 'course'" x-cloak>
                        <x-ui.input name="access_days" type="number" min="1" :label="__('Access for (days)')" :value="$offering->access_days" :hint="__('Leave empty for lifetime access.')" />
                    </div>
                </div>

                <x-ui.textarea name="description" :label="__('Description')" :value="$offering->description" :max="2000" rows="4"
                    :placeholder="__('What students will learn, class times, what is included…')" />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.input name="starts_on" type="date" :label="__('Starts on')" :value="$offering->starts_on?->toDateString()" :hint="__('Optional')" />
                    <x-ui.input name="ends_on" type="date" :label="__('Ends on')" :value="$offering->ends_on?->toDateString()" :hint="__('Optional')" />
                </div>
            </div>
        </x-ui.card>

        <div x-show="type === 'class'" x-cloak>
            <x-ui.card :title="__('Access settings')" :description="__('You decide what students can watch. You can change this any time.')">
                <div class="space-y-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.select name="old_lessons_access" :label="__('Lessons of months a student paid for')"
                            :options="collect(\App\Enums\OldLessonsAccess::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()"
                            :value="$offering->old_lessons_access?->value ?? 'forever'"
                            :hint="__('Can they keep watching them later?')" />

                        <x-ui.select name="grace_days" :label="__('Grace days')"
                            :options="collect(range(0, 7))->mapWithKeys(fn ($d) => [$d => $d === 0 ? __('No grace days') : trans_choice('{1} First day of the month|[2,*] First :count days of the month', $d, ['count' => $d])])->all()"
                            :value="$offering->grace_days ?? 0"
                            :hint="__('New month stays open for students who paid last month.')" />
                    </div>

                    <x-ui.toggle name="past_lessons_access" :checked="$offering->past_lessons_access"
                        :label="__('New students can watch earlier months')"
                        :hint="__('Paying for a month also opens all lessons before it (backlog).')" />
                </div>
            </x-ui.card>
        </div>
        <template x-if="type === 'course'">
            <div>
                <input type="hidden" name="old_lessons_access" value="forever">
                <input type="hidden" name="past_lessons_access" value="0">
                <input type="hidden" name="grace_days" value="0">
            </div>
        </template>

        <x-ui.card>
            <x-ui.toggle name="is_published" :checked="$offering->is_published"
                :label="__('Published')" :hint="__('Show it on your site. Turn off to keep it as a draft.')" />
        </x-ui.card>

        <div class="flex items-center justify-between gap-3">
            <div>
                @if ($editing)
                    <x-ui.button type="button" variant="ghost" class="text-red-600 hover:bg-red-50 hover:text-red-700"
                        onclick="if (confirm(@js(__('Delete this class and its lessons?')))) document.getElementById('delete-offering').submit()">{{ __('Delete') }}</x-ui.button>
                @endif
            </div>
            <div class="flex gap-3">
                <x-ui.button :href="$editing ? route('manage.classes.show', $offering) : route('manage.classes.index')" variant="secondary">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button icon="check">{{ $editing ? __('Save') : __('Create') }}</x-ui.button>
            </div>
        </div>
    </form>

    @if ($editing)
        <form id="delete-offering" method="POST" action="{{ route('manage.classes.destroy', $offering) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
</x-layouts.manage>
