<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-200 pb-5 dark:border-zinc-800">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                Wall of Love Embed Generator
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Customize your social proof widget, preview in real-time, and copy embed code for your website.
            </p>
        </div>

        @if ($selectedSpace)
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('wall.show', $selectedSpace->slug) }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-500 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-400"
                >
                    <span>Public Wall Page</span>
                </a>
                <a
                    href="{{ $embedUrl }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.25V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                    <span>Open Standalone</span>
                </a>
            </div>
        @endif
    </div>

    @if ($spaces->isEmpty())
        {{-- No Spaces Empty State --}}
        <div class="rounded-2xl border-2 border-dashed border-zinc-200 p-12 text-center dark:border-zinc-800">
            <svg class="mx-auto size-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-13.5 18v-2.25z" />
            </svg>
            <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">No spaces created yet</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Create a space first to collect and curate customer testimonials.</p>
            <div class="mt-6">
                <a
                    href="{{ route('spaces.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500"
                    wire:navigate
                >
                    Create your first space
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Left Column: Customization Controls & Embed Snippets --}}
            <div class="lg:col-span-5 space-y-6">
                {{-- 1. Space Selection Card --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <label for="space-select" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Select Space
                    </label>
                    <select
                        id="space-select"
                        wire:model.live="selectedSpaceId"
                        class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        @foreach ($spaces as $space)
                            <option value="{{ $space->id }}">{{ $space->name }} ({{ $space->slug }})</option>
                        @endforeach
                    </select>

                    @if ($selectedSpace)
                        <div class="mt-3 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>Approved reviews: <strong class="text-zinc-700 dark:text-zinc-200">{{ $approvedCount }}</strong></span>
                            <a href="{{ route('spaces.inbox', $selectedSpace) }}" class="text-indigo-600 hover:underline dark:text-indigo-400" wire:navigate>
                                Manage Inbox &rarr;
                            </a>
                        </div>
                    @endif
                </div>

                {{-- 2. Customization Options Card --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                        Widget Styling
                    </h2>

                    {{-- Layout Style: Masonry vs Carousel --}}
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Layout Style
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                wire:click="setLayout('masonry')"
                                class="flex items-center justify-center gap-2 rounded-xl border p-3 text-xs font-semibold transition
                                    {{ $layout === 'masonry'
                                        ? 'border-indigo-600 bg-indigo-50/70 text-indigo-700 dark:border-indigo-500 dark:bg-indigo-950/40 dark:text-indigo-300 ring-1 ring-indigo-600'
                                        : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-300 dark:hover:bg-zinc-800' }}"
                            >
                                <svg class="size-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 4.25A2.25 2.25 0 014.25 2h2.5A2.25 2.25 0 019 4.25v11.5A2.25 2.25 0 016.75 18h-2.5A2.25 2.25 0 012 15.75V4.25zM11 4.25A2.25 2.25 0 0113.25 2h2.5A2.25 2.25 0 0118 4.25v6.5A2.25 2.25 0 0115.75 13h-2.5A2.25 2.25 0 0111 10.75V4.25z"/>
                                </svg>
                                <span>Masonry Grid</span>
                            </button>

                            <button
                                type="button"
                                wire:click="setLayout('carousel')"
                                class="flex items-center justify-center gap-2 rounded-xl border p-3 text-xs font-semibold transition
                                    {{ $layout === 'carousel'
                                        ? 'border-indigo-600 bg-indigo-50/70 text-indigo-700 dark:border-indigo-500 dark:bg-indigo-950/40 dark:text-indigo-300 ring-1 ring-indigo-600'
                                        : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-300 dark:hover:bg-zinc-800' }}"
                            >
                                <svg class="size-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 4.75A2.75 2.75 0 014.75 2h10.5A2.75 2.75 0 0118 4.75v10.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25V4.75zM4.75 3.5c-.69 0-1.25.56-1.25 1.25v10.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25V4.75c0-.69-.56-1.25-1.25-1.25H4.75z"/>
                                    <path d="M8.22 8.72a.75.75 0 011.06 0l2 2a.75.75 0 010 1.06l-2 2a.75.75 0 01-1.06-1.06L9.69 11.25 8.22 9.78a.75.75 0 010-1.06z"/>
                                </svg>
                                <span>Carousel Slider</span>
                            </button>
                        </div>
                    </div>

                    {{-- Theme Toggle: Dark mode vs Light mode --}}
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Theme Mode
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                wire:click="setTheme('light')"
                                class="flex items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-semibold transition
                                    {{ $theme === 'light'
                                        ? 'border-indigo-600 bg-indigo-50/70 text-indigo-700 dark:border-indigo-500 dark:bg-indigo-950/40 dark:text-indigo-300 ring-1 ring-indigo-600'
                                        : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-300 dark:hover:bg-zinc-800' }}"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                </svg>
                                <span>Light Mode</span>
                            </button>

                            <button
                                type="button"
                                wire:click="setTheme('dark')"
                                class="flex items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-semibold transition
                                    {{ $theme === 'dark'
                                        ? 'border-indigo-600 bg-indigo-50/70 text-indigo-700 dark:border-indigo-500 dark:bg-indigo-950/40 dark:text-indigo-300 ring-1 ring-indigo-600'
                                        : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-300 dark:hover:bg-zinc-800' }}"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                                </svg>
                                <span>Dark Mode</span>
                            </button>
                        </div>
                    </div>

                    {{-- Background Color Picker --}}
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Background Color
                        </label>
                        <div class="flex items-center gap-3">
                            <input
                                type="color"
                                wire:model.live="backgroundColor"
                                class="size-9 cursor-pointer rounded-lg border border-zinc-300 bg-transparent p-0.5 dark:border-zinc-700"
                            >
                            <input
                                type="text"
                                wire:model.live.blur="backgroundColor"
                                aria-label="Background color hex value"
                                placeholder="#ffffff"
                                class="w-32 rounded-lg border border-zinc-300 px-3 py-1.5 text-xs font-mono text-zinc-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                            {{-- Color presets --}}
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="setBackgroundColor('#ffffff')" class="size-6 rounded-full border border-zinc-300 bg-white shadow-xs" title="White"></button>
                                <button type="button" wire:click="setBackgroundColor('#f8fafc')" class="size-6 rounded-full border border-zinc-300 bg-slate-50 shadow-xs" title="Slate 50"></button>
                                <button type="button" wire:click="setBackgroundColor('#09090b')" class="size-6 rounded-full border border-zinc-700 bg-zinc-950 shadow-xs" title="Zinc 950"></button>
                                <button type="button" wire:click="setBackgroundColor('#1e1b4b')" class="size-6 rounded-full border border-indigo-900 bg-indigo-950 shadow-xs" title="Deep Indigo"></button>
                                <button type="button" wire:click="setBackgroundColor('transparent')" class="size-6 rounded-full border border-dashed border-zinc-400 bg-transparent text-[9px] flex items-center justify-center font-bold text-zinc-500" title="Transparent">&empty;</button>
                            </div>
                        </div>
                    </div>

                    {{-- Animation Effects Toggle --}}
                    <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-medium text-zinc-800 dark:text-zinc-200">Animation Effects</span>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Smooth hover lift and transition animations on cards.</p>
                            </div>
                            <button
                                type="button"
                                wire:click="toggleAnimations"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none
                                    {{ $animations ? 'bg-indigo-600' : 'bg-zinc-300 dark:bg-zinc-700' }}"
                                role="switch"
                                aria-checked="{{ $animations ? 'true' : 'false' }}"
                            >
                                <span
                                    class="pointer-events-none inline-block size-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out
                                        {{ $animations ? 'translate-x-4' : 'translate-x-0' }}"
                                ></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 3. Copyable Embed Snippets Card --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4" x-data="{ tab: 'iframe' }">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                            Embed Code
                        </h2>
                        {{-- Tab selector --}}
                        <div class="flex rounded-lg bg-zinc-100 p-0.5 dark:bg-zinc-800">
                            <button
                                type="button"
                                @click="tab = 'iframe'"
                                :class="tab === 'iframe' ? 'bg-white text-zinc-900 shadow-xs dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 dark:text-zinc-400'"
                                class="rounded-md px-2.5 py-1 text-xs font-medium transition"
                            >
                                HTML iFrame
                            </button>
                            <button
                                type="button"
                                @click="tab = 'script'"
                                :class="tab === 'script' ? 'bg-white text-zinc-900 shadow-xs dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 dark:text-zinc-400'"
                                class="rounded-md px-2.5 py-1 text-xs font-medium transition"
                            >
                                JavaScript
                            </button>
                        </div>
                    </div>

                    {{-- iFrame Snippet Box --}}
                    <div x-show="tab === 'iframe'" x-data="{ copied: false }" class="space-y-2">
                        <div class="relative">
                            <textarea
                                readonly
                                rows="3"
                                class="w-full rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-xs font-mono text-zinc-800 focus:outline-none dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300"
                            >{{ $iframeSnippet }}</textarea>
                            <button
                                type="button"
                                @click="
                                    navigator.clipboard.writeText($el.previousElementSibling.value);
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                "
                                class="absolute right-2.5 top-2.5 inline-flex items-center gap-1 rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition"
                            >
                                <span x-text="copied ? 'Copied!' : 'Copy Code'"></span>
                            </button>
                        </div>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                            Paste this snippet directly into any HTML page, Webflow, Notion, WordPress, or Shopify site.
                        </p>
                    </div>

                    {{-- JavaScript Snippet Box --}}
                    <div x-show="tab === 'script'" x-data="{ copied: false }" x-cloak class="space-y-2">
                        <div class="relative">
                            <textarea
                                readonly
                                rows="3"
                                class="w-full rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-xs font-mono text-zinc-800 focus:outline-none dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300"
                            >{{ $scriptSnippet }}</textarea>
                            <button
                                type="button"
                                @click="
                                    navigator.clipboard.writeText($el.previousElementSibling.value);
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                "
                                class="absolute right-2.5 top-2.5 inline-flex items-center gap-1 rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition"
                            >
                                <span x-text="copied ? 'Copied!' : 'Copy Code'"></span>
                            </button>
                        </div>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                            Lightweight dynamic script that automatically renders and scales the Wall of Love on your domain.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right Column: Real-Time Live Preview --}}
            <div class="lg:col-span-7 space-y-3">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Real-Time Live Preview
                        </h2>
                    </div>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ $layout === 'masonry' ? 'Masonry Grid' : 'Carousel Slider' }} &bull; {{ ucfirst($theme) }} Theme
                    </span>
                </div>

                {{-- Live Preview Mock Browser Window --}}
                <div class="rounded-2xl border border-zinc-200 bg-white shadow-md overflow-hidden dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Browser Mockup Header --}}
                    <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-100/80 px-4 py-2.5 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-1.5">
                            <span class="size-2.5 rounded-full bg-red-400"></span>
                            <span class="size-2.5 rounded-full bg-amber-400"></span>
                            <span class="size-2.5 rounded-full bg-emerald-400"></span>
                        </div>
                        <div class="flex items-center gap-1 rounded-md bg-white px-3 py-1 text-[11px] text-zinc-600 shadow-2xs dark:bg-zinc-800 dark:text-zinc-400 font-mono">
                            <svg class="size-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                            <span>your-website.com/wall-of-love</span>
                        </div>
                        <div class="text-[11px] text-zinc-400">
                            {{ $approvedCount }} reviews
                        </div>
                    </div>

                    {{-- Widget Preview Area --}}
                    <div
                        class="p-6 transition-colors duration-200 min-h-[380px] max-h-[620px] overflow-y-auto"
                        style="background-color: {{ $backgroundColor }};"
                        x-data="{
                            scrollLeft() {
                                if (this.$refs.previewCarousel) {
                                    this.$refs.previewCarousel.scrollBy({ left: -320, behavior: 'smooth' });
                                }
                            },
                            scrollRight() {
                                if (this.$refs.previewCarousel) {
                                    this.$refs.previewCarousel.scrollBy({ left: 320, behavior: 'smooth' });
                                }
                            }
                        }"
                    >
                        @if ($testimonials->isEmpty())
                            <div class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="rounded-full p-4 {{ $isDark ? 'bg-zinc-800 text-zinc-500' : 'bg-zinc-100 text-zinc-400' }}">
                                    <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-base font-semibold {{ $isDark ? 'text-zinc-200' : 'text-zinc-800' }}">
                                    No approved testimonials yet
                                </h3>
                                <p class="mt-1 text-xs max-w-sm {{ $isDark ? 'text-zinc-400' : 'text-zinc-500' }}">
                                    Only testimonials that are <strong>Approved (hearted)</strong> and <strong>not hidden</strong> will show on your Wall of Love.
                                </p>
                                @if ($selectedSpace)
                                    <div class="mt-4">
                                        <a
                                            href="{{ route('spaces.inbox', $selectedSpace) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500"
                                            wire:navigate
                                        >
                                            Go to Inbox &amp; Approve
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @elseif ($isCarousel)
                            {{-- Carousel Preview --}}
                            <div class="relative group">
                                <button
                                    type="button"
                                    @click="scrollLeft()"
                                    class="absolute -left-2 top-1/2 -translate-y-1/2 z-10 flex size-9 items-center justify-center rounded-full shadow-md transition
                                        {{ $isDark ? 'bg-zinc-800 text-zinc-200 hover:bg-zinc-700 border border-zinc-700' : 'bg-white text-zinc-700 hover:bg-zinc-50 border border-zinc-200' }}"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    @click="scrollRight()"
                                    class="absolute -right-2 top-1/2 -translate-y-1/2 z-10 flex size-9 items-center justify-center rounded-full shadow-md transition
                                        {{ $isDark ? 'bg-zinc-800 text-zinc-200 hover:bg-zinc-700 border border-zinc-700' : 'bg-white text-zinc-700 hover:bg-zinc-50 border border-zinc-200' }}"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>

                                <div
                                    x-ref="previewCarousel"
                                    class="flex gap-4 overflow-x-auto py-2 px-1 snap-x snap-mandatory scroll-smooth"
                                    style="scrollbar-width: none;"
                                >
                                    @foreach ($testimonials as $testimonial)
                                        <div class="w-72 sm:w-80 shrink-0 snap-start" wire:key="preview-card-{{ $testimonial->id }}">
                                            @include('livewire.public.partials.testimonial-card', [
                                                'testimonial' => $testimonial,
                                                'isDark' => $isDark,
                                                'hasAnimations' => $hasAnimations,
                                            ])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            {{-- Masonry Preview --}}
                            <div class="columns-1 sm:columns-2 gap-4 [column-fill:_balance]">
                                @foreach ($testimonials as $testimonial)
                                    <div class="break-inside-avoid mb-4" wire:key="preview-card-{{ $testimonial->id }}">
                                        @include('livewire.public.partials.testimonial-card', [
                                            'testimonial' => $testimonial,
                                            'isDark' => $isDark,
                                            'hasAnimations' => $hasAnimations,
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
