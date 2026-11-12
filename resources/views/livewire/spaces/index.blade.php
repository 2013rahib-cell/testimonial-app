<div class="max-w-6xl mx-auto space-y-6 py-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-200 pb-5 dark:border-zinc-800">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                Your Spaces
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Each space is an independent testimonial collection funnel with its own custom branding and shareable URL.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $isAtLimit ? 'bg-amber-500/10 text-amber-700 dark:text-amber-300' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                {{ $spacesCount }} / {{ $maxSpaces }} Spaces ({{ $isPro ? 'Pro' : 'Free' }})
            </span>

            @if ($isAtLimit)
                <button
                    type="button"
                    disabled
                    title="Plan limit reached. Upgrade to Pro to create more spaces."
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-zinc-400 px-4 py-2 text-sm font-semibold text-white cursor-not-allowed opacity-60"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Create Space</span>
                </button>
            @else
                <a
                    href="{{ route('spaces.create') }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                    wire:navigate
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Create Space</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Status Flash Message --}}
    @if (session('status'))
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    {{-- Free Tier Limit Notice if reached --}}
    @if (! $isPro && $isAtLimit)
        <div class="rounded-xl border border-amber-300 bg-amber-50/70 p-4 dark:border-amber-900/60 dark:bg-amber-950/30">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <svg class="size-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <p class="text-xs text-amber-800 dark:text-amber-200">
                        You have utilized all <strong>{{ $maxSpaces }} free spaces</strong>. Upgrade to <strong>Pro ($9.99/mo)</strong> to manage up to 25 spaces!
                    </p>
                </div>
                <a href="{{ route('settings.billing') }}" wire:navigate class="shrink-0 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 transition">
                    Upgrade to Pro
                </a>
            </div>
        </div>
    @endif

    {{-- Spaces List or Empty State --}}
    @if ($spaces->isEmpty())
        <div class="rounded-2xl border-2 border-dashed border-zinc-200 p-12 text-center dark:border-zinc-800">
            <div class="mx-auto flex size-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">No spaces yet</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                Create your first space to start collecting high-converting customer testimonials.
            </p>
            <div class="mt-6">
                <a
                    href="{{ route('spaces.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    wire:navigate
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Create Your First Space</span>
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($spaces as $space)
                <div class="flex flex-col justify-between rounded-xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div>
                        {{-- Top badges --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium capitalize {{ $space->is_active ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                {{ $space->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-xs text-zinc-400 capitalize">
                                Theme: {{ $space->theme->value ?? 'light' }}
                            </span>
                        </div>

                        {{-- Space Title & Name --}}
                        <div class="mt-3">
                            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $space->name }}
                            </h2>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                {{ $space->title ?? $space->description ?? 'No header title set' }}
                            </p>
                        </div>

                        {{-- URL Copy Card --}}
                        <div class="mt-4 rounded-lg bg-zinc-50 p-2.5 dark:bg-zinc-800/60" x-data="{ copied: false }">
                            <div class="flex items-center justify-between gap-2">
                                <span class="truncate font-mono text-xs text-zinc-600 dark:text-zinc-300">
                                    /s/{{ $space->slug }}
                                </span>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button
                                        type="button"
                                        @click="navigator.clipboard.writeText('{{ url('/s/' . $space->slug) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="rounded p-1 text-zinc-500 hover:bg-zinc-200 hover:text-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400"
                                        title="Copy public URL"
                                    >
                                        <svg x-show="!copied" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.849A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.599m7.332 0c.055.194.084.4.084.615v.005a2.25 2.25 0 01-2.25 2.25h-3.75a2.25 2.25 0 01-2.25-2.25v-.005c0-.215.029-.421.084-.615m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                        </svg>
                                        <span x-show="copied" x-cloak class="text-[10px] font-medium text-emerald-600 dark:text-emerald-400">Copied!</span>
                                    </button>
                                    <a
                                        href="{{ url('/s/' . $space->slug) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="rounded p-1 text-zinc-500 hover:bg-zinc-200 hover:text-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400"
                                        title="Open public page"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Metrics info --}}
                        <div class="mt-4 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <span>{{ $space->testimonials_count }} testimonials</span>
                            <span class="flex items-center gap-1">
                                Ratings: {{ $space->enable_ratings ? 'On' : 'Off' }}
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-5 flex items-center justify-between gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <button
                            type="button"
                            wire:click="toggleActive({{ $space->id }})"
                            class="text-xs font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200"
                        >
                            {{ $space->is_active ? 'Deactivate' : 'Activate' }}
                        </button>

                        <div class="flex items-center gap-3">
                            <a
                                href="{{ route('spaces.inbox', $space->slug) }}"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400"
                                wire:navigate
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" /></svg>
                                Inbox
                                @if($space->testimonials_count > 0)
                                    <span class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">{{ $space->testimonials_count }}</span>
                                @endif
                            </a>
                            <a
                                href="{{ route('spaces.edit', $space->slug) }}"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                                wire:navigate
                            >
                                Edit Settings
                            </a>
                            <button
                                type="button"
                                wire:click="deleteSpace({{ $space->id }})"
                                wire:confirm="Are you sure you want to delete this space? All associated testimonials will also be deleted."
                                class="text-xs text-red-500 hover:text-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
