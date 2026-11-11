<x-layouts.manage :title="__('Settings')">
    <form method="POST" action="{{ route('manage.settings.update') }}" enctype="multipart/form-data" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <x-ui.card :title="__('Profile')" :description="__('How students see you on your site.')">
            <div class="space-y-6">
                <div class="grid gap-6 sm:grid-cols-2">
                    <x-ui.image-upload name="photo" :label="__('Your photo')" shape="circle" :current="$teacher->photoUrl()" :hint="__('Square photo works best. Up to 2 MB.')" />
                    <x-ui.image-upload name="logo" :label="__('Logo')" shape="square" :current="$teacher->logoUrl()" :hint="__('Shown in your site header. PNG with transparent background is best.')" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.input name="display_name" :label="__('Display name')" :value="$teacher->display_name" required />
                    <x-ui.input name="subject_line" :label="__('Subject line')" :value="$teacher->subject_line" :placeholder="__('e.g. A/L Physics · Sinhala medium')" />
                </div>

                <x-ui.textarea name="bio" :label="__('Short description')" :value="$teacher->bio" :max="300"
                    :placeholder="__('e.g. Teaching A/L Physics in Sinhala medium for 10 years. Theory, revision and paper classes.')" />
            </div>
        </x-ui.card>

        @php
            $presets = ['#dc2626', '#e11d48', '#ea580c', '#d97706', '#059669', '#0891b2', '#2563eb', '#4f46e5', '#7c3aed', '#334155'];
        @endphp

        <x-ui.card :title="__('Brand colour')" :description="__('Used for buttons and highlights on your site.')">
            <div x-data="{ color: @js(old('brand_color', $teacher->brand_color ?? '#dc2626')) }" class="space-y-4">
                <input type="hidden" name="brand_color" x-bind:value="color">

                <div class="flex flex-wrap items-center gap-3">
                    @foreach ($presets as $preset)
                        <button type="button" class="size-9 rounded-full ring-offset-2 transition hover:scale-110"
                            style="background-color: {{ $preset }}"
                            x-bind:class="color === '{{ $preset }}' ? 'ring-2 ring-slate-900' : 'ring-1 ring-black/10'"
                            x-on:click="color = '{{ $preset }}'"
                            aria-label="{{ $preset }}"></button>
                    @endforeach

                    <label class="relative grid size-9 cursor-pointer place-items-center rounded-full bg-white ring-1 ring-slate-200" title="{{ __('Custom colour') }}">
                        <x-ui.icon name="plus" class="size-4 text-slate-500" />
                        <input type="color" class="absolute inset-0 size-full cursor-pointer opacity-0" x-model="color">
                    </label>
                </div>

                <div class="flex items-center gap-4 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200/70">
                    <span class="text-sm text-slate-500">{{ __('Preview') }}</span>
                    <span class="inline-flex h-10 items-center rounded-xl px-4 text-sm font-semibold text-white shadow-sm" x-bind:style="'background-color:' + color">{{ __('Join a class') }}</span>
                    <span class="font-mono text-xs text-slate-400" x-text="color"></span>
                </div>

                @error('brand_color')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-ui.card>

        <x-ui.card :title="__('Contact')" :description="__('Students tap the WhatsApp button on your site to reach you.')">
            <x-ui.input name="whatsapp" type="tel" :label="__('WhatsApp number')" :value="\App\Support\Phone::display($teacher->whatsapp)" placeholder="077 123 4567" inputmode="tel" class="max-w-sm" />
        </x-ui.card>

        <div class="sticky bottom-0 -mx-4 flex justify-end gap-3 border-t border-slate-200/70 bg-slate-50/90 px-4 py-4 backdrop-blur sm:mx-0 sm:rounded-2xl sm:border sm:px-5">
            <x-ui.button :href="route('tenant.home')" target="_blank" variant="secondary" icon="external">{{ __('View my site') }}</x-ui.button>
            <x-ui.button icon="check">{{ __('Save changes') }}</x-ui.button>
        </div>
    </form>
</x-layouts.manage>
