@props(['title', 'subtitle' => null])

@php
    $teacher = app(\App\Tenancy\CurrentTeacher::class)->require();
@endphp

<x-layouts.panel :title="$title" :subtitle="$subtitle" :brand-color="$teacher->brandColor()" :page-title="$teacher->display_name" logout-route="manage.logout">
    <x-slot:brand>
        <a href="{{ route('manage.dashboard') }}" class="flex min-w-0 items-center gap-3">
            <x-ui.avatar :initials="$teacher->initials()" :color="$teacher->brandColor()" size="sm" class="ring-navy-800" />
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold text-white">{{ $teacher->display_name }}</span>
                <span class="block truncate text-xs text-navy-300">{{ __('Teacher panel') }}</span>
            </span>
        </a>
    </x-slot:brand>

    <x-slot:nav>
        <x-ui.nav-link :href="route('manage.dashboard')" icon="layout" :active="request()->routeIs('manage.dashboard')">{{ __('Dashboard') }}</x-ui.nav-link>

        <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-400">{{ __('Teaching') }}</p>
        <x-ui.nav-link icon="book" soon>{{ __('Classes') }}</x-ui.nav-link>
        <x-ui.nav-link icon="play" soon>{{ __('Lessons') }}</x-ui.nav-link>
        <x-ui.nav-link icon="video" soon>{{ __('Live classes') }}</x-ui.nav-link>
        <x-ui.nav-link icon="megaphone" soon>{{ __('Announcements') }}</x-ui.nav-link>

        <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-400">{{ __('Students & money') }}</p>
        <x-ui.nav-link icon="users" soon>{{ __('Students') }}</x-ui.nav-link>
        <x-ui.nav-link icon="wallet" soon>{{ __('Payments') }}</x-ui.nav-link>

        <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-400">{{ __('Site') }}</p>
        <x-ui.nav-link :href="route('tenant.home')" icon="external" target="_blank">{{ __('View my site') }}</x-ui.nav-link>
        <x-ui.nav-link icon="settings" soon>{{ __('Settings') }}</x-ui.nav-link>
    </x-slot:nav>

    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset

    {{ $slot }}
</x-layouts.panel>
