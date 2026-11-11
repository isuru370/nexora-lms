<x-layouts.student :title="$lesson->title">
    <a href="{{ route('tenant.offerings.show', ['offering' => $offering, 'month' => $lesson->month]) }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-900">
        <x-ui.icon name="arrow-right" class="size-4 rotate-180" /> {{ $offering->title }}
    </a>

    <div class="mt-4 grid gap-6 lg:grid-cols-[1fr_18rem]">
        <div>
            {{-- Player + moving watermark. Our own fullscreen keeps the watermark visible. --}}
            <div x-data="lessonPlayer()" x-ref="frame" class="group relative aspect-video overflow-hidden rounded-2xl bg-black shadow-float">
                <iframe src="{{ $lesson->embedUrl() }}&fs=0" class="absolute inset-0 size-full" title="{{ $lesson->title }}"
                        allow="autoplay; encrypted-media; picture-in-picture" referrerpolicy="strict-origin-when-cross-origin"></iframe>

                @if ($viewer)
                    <div class="pointer-events-none absolute select-none text-xs font-semibold text-white/35 drop-shadow transition-all duration-[2000ms] ease-in-out sm:text-sm"
                         x-bind:style="`top:${y}%;left:${x}%`">
                        {{ $viewer->name }} · {{ \App\Support\Phone::display($viewer->phone) }}
                    </div>
                @endif

                <button type="button" x-on:click="toggle()" class="absolute right-3 bottom-3 rounded-lg bg-black/60 p-2 text-white opacity-80 transition hover:opacity-100" title="{{ __('Full screen') }}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                </button>
            </div>

            <h1 class="mt-5 text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">{{ $lesson->title }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $offering->title }}
                @if ($lesson->month) · {{ \App\Support\Month::label($lesson->month) }} @endif
                @if ($lesson->duration_minutes) · {{ __(':n min', ['n' => $lesson->duration_minutes]) }} @endif
            </p>
            @if ($lesson->description)
                <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ $lesson->description }}</p>
            @endif
        </div>

        <aside>
            <x-ui.card :title="__('In this :scope', ['scope' => $lesson->month ? __('month') : __('course')])" :padding="false">
                <ul class="max-h-[28rem] divide-y divide-slate-100 overflow-y-auto">
                    @foreach ($siblings as $item)
                        @php
                            $current = $item['lesson']->is($lesson);
                        @endphp
                        <li>
                            @if ($item['open'] && ! $current)
                                <a href="{{ route('tenant.lessons.show', $item['lesson']) }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">
                                    <x-ui.icon name="play" class="size-4 shrink-0 text-slate-400" />
                                    <span class="line-clamp-2">{{ $item['lesson']->title }}</span>
                                </a>
                            @else
                                <span @class(['flex items-center gap-3 px-4 py-3 text-sm', 'bg-accent-50/60 font-semibold text-accent-800' => $current, 'text-slate-400' => ! $current])>
                                    <x-ui.icon :name="$current ? 'play' : 'shield'" class="size-4 shrink-0" />
                                    <span class="line-clamp-2">{{ $item['lesson']->title }}</span>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </aside>
    </div>

    <script>
        function lessonPlayer() {
            return {
                x: 8, y: 10,
                init() { setInterval(() => { this.x = 5 + Math.random() * 60; this.y = 5 + Math.random() * 80; }, 7000); },
                toggle() {
                    if (document.fullscreenElement) { document.exitFullscreen(); return; }
                    const el = this.$refs.frame;
                    (el.requestFullscreen || el.webkitRequestFullscreen || (() => {})).call(el);
                },
            };
        }
    </script>
</x-layouts.student>
