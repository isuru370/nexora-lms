<x-layouts.admin :title="__('Dashboard')">
    <x-slot:actions>
        <x-ui.button :href="route('admin.teachers.create')" icon="plus" size="sm">{{ __('New teacher') }}</x-ui.button>
    </x-slot:actions>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat :label="__('Teachers')" :value="number_format($stats['teachers'])" icon="users" />
        <x-ui.stat :label="__('On trial')" :value="number_format($stats['trial'])" icon="sparkles" tone="blue" />
        <x-ui.stat :label="__('Active (paying)')" :value="number_format($stats['active'])" icon="check-circle" tone="green" />
        <x-ui.stat :label="__('Students on platform')" :value="number_format($stats['students'])" icon="graduation" tone="accent" />
    </div>

    <x-ui.card class="mt-6" :title="__('Latest teachers')" :padding="false">
        <x-slot:actions>
            <x-ui.button :href="route('admin.teachers.index')" variant="ghost" size="sm">{{ __('View all') }}</x-ui.button>
        </x-slot:actions>

        @forelse ($latestTeachers as $teacher)
            <div class="flex items-center gap-4 border-b border-slate-100 px-5 py-3.5 last:border-0 sm:px-6">
                <x-ui.avatar :src="$teacher->photoUrl()" :initials="$teacher->initials()" :color="$teacher->brandColor()" size="sm" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-slate-900">{{ $teacher->display_name }}</p>
                    <a href="{{ $teacher->url() }}" target="_blank" class="truncate text-xs text-slate-500 hover:text-ink-700">{{ \App\Support\Domains::tenantHost($teacher->slug) }}</a>
                </div>
                <x-ui.badge :color="$teacher->status->color()">{{ $teacher->status->label() }}</x-ui.badge>
            </div>
        @empty
            <x-ui.empty-state icon="users" :title="__('No teachers yet')" :description="__('Create the first teacher site to get started.')">
                <x-ui.button :href="route('admin.teachers.create')" icon="plus">{{ __('New teacher') }}</x-ui.button>
            </x-ui.empty-state>
        @endforelse
    </x-ui.card>
</x-layouts.admin>
