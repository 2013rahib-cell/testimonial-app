<div class="space-y-6">
    {{-- 3. Visual Upgrade Banner / Notification Bar for Free Plan Users --}}
    @if (! $isPro && ! $bannerDismissed)
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500/15 via-orange-500/10 to-indigo-500/15 border border-amber-400/30 p-5 shadow-sm dark:from-amber-950/40 dark:via-orange-950/30 dark:to-indigo-950/40 dark:border-amber-500/30">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3.5">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/20 text-amber-600 dark:bg-amber-400/20 dark:text-amber-400">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-amber-500/15 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-400/15 dark:text-amber-300">
                                Free Plan
                            </span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $spacesCount }}/{{ $maxSpaces }} Spaces used &bull; Max {{ $maxPerSpace }} testimonials/space
                            </span>
                        </div>
                        <h3 class="mt-1 text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            Unlock Unlimited Growth with Testimonial Pro
                        </h3>
                        <p class="mt-0.5 text-sm text-zinc-600 dark:text-zinc-400">
                            Upgrade for just <strong class="text-zinc-900 dark:text-zinc-200">$9.99/mo</strong> to get up to 25 spaces, 1,000 testimonials per space, video testimonials, and custom branding.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 sm:self-center">
                    <a
                        href="#upgrade"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:from-amber-500 hover:to-orange-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                    >
                        <span>Upgrade to Pro</span>
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                    <button
                        type="button"
                        wire:click="dismissBanner"
                        class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-200/50 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        title="Dismiss notification"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- 1. Summary Counters --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Testimonials --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Testimonials</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100" data-test="total-testimonials-count">
                    {{ number_format($metrics['totalTestimonials']) }}
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">reviews</span>
            </div>
            <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $metrics['approvedCount'] }}</span> approved
            </div>
        </div>

        {{-- Total Unique Customers --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unique Customers</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100" data-test="unique-customers-count">
                    {{ number_format($metrics['uniqueCustomers']) }}
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">senders</span>
            </div>
            <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                Verified submitters
            </div>
        </div>

        {{-- Active Spaces --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Active Spaces</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ $metrics['activeSpacesCount'] }}
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">/ {{ $maxSpaces }} allowed</span>
            </div>
            <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                Collection endpoints
            </div>
        </div>

        {{-- Average Rating --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Average Rating</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ $metrics['averageRating'] > 0 ? $metrics['averageRating'] : 'N/A' }}
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">out of 5.0</span>
            </div>
            <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                Customer satisfaction
            </div>
        </div>
    </div>

    {{-- 2. Time-Series Graph with Day/Week Density Filters --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Testimonials Over Time</h2>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                        {{ $timeSeries['total'] }} collected
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                    Track the volume of feedback collected across your spaces
                </p>
            </div>

            {{-- Density Filters --}}
            <div class="flex items-center rounded-lg bg-zinc-100 p-1 dark:bg-zinc-800" role="group" aria-label="Density filters">
                <button
                    type="button"
                    wire:click="setDensity('day')"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition {{ $density === 'day' ? 'bg-white text-zinc-900 shadow-xs dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    data-test="density-day-button"
                >
                    Daily (14 Days)
                </button>
                <button
                    type="button"
                    wire:click="setDensity('week')"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition {{ $density === 'week' ? 'bg-white text-zinc-900 shadow-xs dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    data-test="density-week-button"
                >
                    Weekly (8 Weeks)
                </button>
            </div>
        </div>

        {{-- Graph Area --}}
        <div class="mt-6">
            <div class="relative w-full">
                {{-- SVG Time Series Chart --}}
                <div class="w-full overflow-hidden">
                    <svg viewBox="0 0 600 160" class="h-48 w-full overflow-visible" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#6366F1" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#6366F1" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>

                        {{-- Horizontal Grid Lines --}}
                        <line x1="20" y1="20" x2="580" y2="20" stroke="currentColor" class="text-zinc-100 dark:text-zinc-800" stroke-width="1" stroke-dasharray="4 4" />
                        <line x1="20" y1="80" x2="580" y2="80" stroke="currentColor" class="text-zinc-100 dark:text-zinc-800" stroke-width="1" stroke-dasharray="4 4" />
                        <line x1="20" y1="140" x2="580" y2="140" stroke="currentColor" class="text-zinc-200 dark:text-zinc-700" stroke-width="1" />

                        @if (! empty($timeSeries['areaPath']))
                            {{-- Shaded Area --}}
                            <path d="{{ $timeSeries['areaPath'] }}" fill="url(#chartGradient)" />

                            {{-- Trend Line --}}
                            <path d="{{ $timeSeries['svgPath'] }}" fill="none" stroke="#6366F1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />

                            {{-- Data Points --}}
                            @foreach ($timeSeries['points'] as $point)
                                <g class="group cursor-pointer">
                                    <circle
                                        cx="{{ $point['x'] }}"
                                        cy="{{ $point['y'] }}"
                                        r="4"
                                        class="fill-white stroke-indigo-600 transition group-hover:r-6 dark:fill-zinc-900 dark:stroke-indigo-400"
                                        stroke-width="2.5"
                                    />
                                    <title>{{ $point['label'] }}: {{ $point['count'] }} testimonial{{ $point['count'] === 1 ? '' : 's' }}</title>
                                </g>
                            @endforeach
                        @else
                            <text x="300" y="80" text-anchor="middle" class="fill-zinc-400 text-xs">No testimonial data collected yet</text>
                        @endif
                    </svg>
                </div>

                {{-- X-Axis Labels --}}
                <div class="mt-3 flex justify-between px-2 text-[11px] font-medium text-zinc-400 dark:text-zinc-500">
                    @foreach ($timeSeries['points'] as $point)
                        <span class="truncate text-center" style="width: {{ 100 / count($timeSeries['points']) }}%;">
                            {{ $point['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
