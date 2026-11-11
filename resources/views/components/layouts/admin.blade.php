@props(['title', 'subtitle' => null])

<x-layouts.panel :title="$title" :subtitle="$subtitle" page-title="NexoraLMS Admin" logout-route="admin.logout">
    <x-slot:brand>
        <a href="{{ route('admin.dashboard') }}"><x-ui.logo dark /></a>
    </x-slot:brand>

    <x-slot:nav>
        <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-400">{{ __('Platform') }}</p>
        <x-ui.nav-link :href="route('admin.dashboard')" icon="layout" :active="request()->routeIs('admin.dashboard')">{{ __('Dashboard') }}</x-ui.nav-link>
        <x-ui.nav-link :href="route('admin.teachers.index')" icon="users" :active="request()->routeIs('admin.teachers.*')">{{ __('Teachers') }}</x-ui.nav-link>
        <x-ui.nav-link icon="receipt" soon>{{ __('Bills') }}</x-ui.nav-link>
        <x-ui.nav-link icon="video" soon>{{ __('Zoom rooms') }}</x-ui.nav-link>
        <x-ui.nav-link icon="settings" soon>{{ __('Plans & settings') }}</x-ui.nav-link>
        <x-ui.nav-link icon="shield" soon>{{ __('Audit log') }}</x-ui.nav-link>
    </x-slot:nav>

    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset

    {{ $slot }}
</x-layouts.panel>
