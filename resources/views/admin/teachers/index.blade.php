<x-layouts.admin :title="__('Teachers')">
    <x-slot:actions>
        <x-ui.button :href="route('admin.teachers.create')" icon="plus" size="sm">{{ __('New teacher') }}</x-ui.button>
    </x-slot:actions>

    <form method="GET" class="mb-5 max-w-md">
        <x-ui.input name="q" :value="$search" :placeholder="__('Search by name, site or phone')" :prefix="null" aria-label="{{ __('Search') }}" />
    </form>

    <x-ui.card :padding="false">
        @if ($teachers->isEmpty())
            <x-ui.empty-state icon="users" :title="$search ? __('No teachers found') : __('No teachers yet')" :description="$search ? __('Try another name or phone number.') : __('Create the first teacher site to get started.')">
                @unless ($search)
                    <x-ui.button :href="route('admin.teachers.create')" icon="plus">{{ __('New teacher') }}</x-ui.button>
                @endunless
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-6 py-3">{{ __('Teacher') }}</th>
                            <th class="px-6 py-3">{{ __('Site') }}</th>
                            <th class="px-6 py-3">{{ __('Phone') }}</th>
                            <th class="px-6 py-3">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-right">{{ __('Students') }}</th>
                            <th class="px-6 py-3">{{ __('Trial ends') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($teachers as $teacher)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <x-ui.avatar :initials="$teacher->initials()" :color="$teacher->brandColor()" size="sm" />
                                        <div>
                                            <p class="font-medium text-slate-900">{{ $teacher->display_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $teacher->subject_line }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3.5">
                                    <a href="{{ $teacher->url() }}" target="_blank" class="inline-flex items-center gap-1 text-navy-700 hover:underline">
                                        {{ $teacher->slug }} <x-ui.icon name="external" class="size-3.5" />
                                    </a>
                                </td>
                                <td class="px-6 py-3.5 text-slate-600 tabular-nums">{{ \App\Support\Phone::display($teacher->phone) }}</td>
                                <td class="px-6 py-3.5"><x-ui.badge :color="$teacher->status->color()">{{ $teacher->status->label() }}</x-ui.badge></td>
                                <td class="px-6 py-3.5 text-right tabular-nums text-slate-700">{{ number_format($teacher->students_count) }}</td>
                                <td class="px-6 py-3.5 text-slate-600">{{ $teacher->trial_ends_on?->format('d M Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($teachers->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">{{ $teachers->links() }}</div>
            @endif
        @endif
    </x-ui.card>
</x-layouts.admin>
