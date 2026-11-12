<div class="max-w-3xl mx-auto py-8">
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        {{-- Success Banner --}}
        <div class="bg-gradient-to-r from-emerald-500/15 via-teal-500/10 to-indigo-500/15 border-b border-emerald-500/20 p-8 text-center dark:from-emerald-950/40 dark:via-teal-950/30 dark:to-indigo-950/40">
            <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 dark:bg-emerald-600">
                <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-3xl">
                Your Space is Ready to Collect Testimonials!
            </h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 max-w-xl mx-auto">
                Congratulations! Space <strong>"{{ $space->name }}"</strong> has been created with a unique shareable collection link.
            </p>
        </div>

        <div class="p-8 space-y-6">
            {{-- Shareable Link Card --}}
            <div class="rounded-xl border border-zinc-200 bg-zinc-50/80 p-5 dark:border-zinc-800 dark:bg-zinc-800/40">
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    Your Unique Shareable Public URL
                </label>
                <div class="mt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-2" x-data="{ copied: false }">
                    <input
                        type="text"
                        readonly
                        value="{{ $publicUrl }}"
                        id="shareableUrl"
                        class="block w-full flex-1 rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm font-mono text-zinc-900 shadow-xs focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    />
                    <button
                        type="button"
                        @click="navigator.clipboard.writeText('{{ $publicUrl }}'); copied = true; setTimeout(() => copied = false, 2500)"
                        class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                    >
                        <span x-show="!copied" class="flex items-center gap-1.5">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.849A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.599m7.332 0c.055.194.084.4.084.615v.005a2.25 2.25 0 01-2.25 2.25h-3.75a2.25 2.25 0 01-2.25-2.25v-.005c0-.215.029-.421.084-.615m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                            </svg>
                            <span>Copy Link</span>
                        </span>
                        <span x-show="copied" x-cloak class="flex items-center gap-1.5 text-emerald-100">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>Copied!</span>
                        </span>
                    </button>
                    <a
                        href="{{ $publicUrl }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        <span>Visit Page</span>
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Summary of Space Settings --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                    <span class="block text-xs text-zinc-500">Theme</span>
                    <span class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100 capitalize">{{ $space->theme->value ?? 'light' }}</span>
                </div>
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                    <span class="block text-xs text-zinc-500">5-Star Ratings</span>
                    <span class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ $space->enable_ratings ? 'Enabled' : 'Disabled' }}</span>
                </div>
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                    <span class="block text-xs text-zinc-500">Company Name</span>
                    <span class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $space->collect_company ? ($space->require_company ? 'Required' : 'Optional') : 'Hidden' }}
                    </span>
                </div>
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                    <span class="block text-xs text-zinc-500">Profile Photo</span>
                    <span class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $space->collect_avatar ? ($space->require_avatar ? 'Required' : 'Optional') : 'Hidden' }}
                    </span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <a
                    href="{{ route('spaces.edit', $space->slug) }}"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    wire:navigate
                >
                    &larr; Customize Space Settings
                </a>

                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('spaces.index') }}"
                        class="rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white shadow-xs hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                        wire:navigate
                    >
                        View All Spaces
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
