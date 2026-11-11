<x-layouts.admin :title="__('New teacher')">
    <form method="POST" action="{{ route('admin.teachers.store') }}" class="mx-auto max-w-2xl space-y-6"
          x-data="{ slug: @js(old('slug', '')), touched: @js(old('slug') !== null), name: @js(old('display_name', '')) }">
        @csrf

        <x-ui.card :title="__('Teacher site')" :description="__('This is what students see.')">
            <div class="space-y-5">
                <x-ui.input name="display_name" :label="__('Display name')" :placeholder="__('e.g. Kasun Perera')" required
                    x-model="name"
                    x-on:input="if (! touched) slug = name.toLowerCase().normalize('NFKD').replace(/[^a-z0-9]+/g, '').slice(0, 30)" />

                <x-ui.input name="subject_line" :label="__('Subject line')" :placeholder="__('e.g. A/L Physics · Sinhala medium')" :hint="__('Optional. Shown under the name on the site.')" />

                <div class="space-y-1.5">
                    <x-ui.input name="slug" :label="__('Site name')" required
                        :suffix="'.'.\App\Support\Domains::base()"
                        x-model="slug" x-on:input="touched = true"
                        autocomplete="off" spellcheck="false" />
                    <p class="text-sm text-slate-500" x-show="slug">
                        {{ __('Site address:') }}
                        <span class="font-medium text-ink-700" x-text="slug + '.{{ \App\Support\Domains::base() }}'"></span>
                    </p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card :title="__('Owner login')" :description="__('The teacher logs in to the panel with this phone number.')">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input name="owner_name" :label="__('Owner name')" required class="sm:col-span-2" />
                <x-ui.input name="phone" type="tel" :label="__('Mobile number')" placeholder="077 123 4567" inputmode="tel" required />
                <x-ui.input name="email" type="email" :label="__('Email')" :hint="__('Optional')" />
                <x-ui.input name="password" type="password" :label="__('Temporary password')" :hint="__('At least 8 characters. Share it with the teacher.')" autocomplete="new-password" required class="sm:col-span-2" />
            </div>
        </x-ui.card>

        <div class="flex items-center justify-end gap-3">
            <x-ui.button :href="route('admin.teachers.index')" variant="secondary">{{ __('Cancel') }}</x-ui.button>
            <x-ui.button icon="check">{{ __('Create teacher') }}</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
