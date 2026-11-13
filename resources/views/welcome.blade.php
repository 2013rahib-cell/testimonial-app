<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => __('Collect & embed customer testimonials')])
    </head>
    <body class="min-h-screen bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        {{-- Top navigation --}}
        <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold">
                <span class="flex size-8 items-center justify-center rounded-lg bg-rose-500 text-white">
                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                </span>
                <span>{{ config('app.name', 'Wall of Love') }}</span>
            </a>

            <nav class="flex items-center gap-2 text-sm">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-zinc-900 px-4 py-2 font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                        {{ __('Go to dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        {{ __('Log in') }}
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-lg bg-zinc-900 px-4 py-2 font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                            {{ __('Start free') }}
                        </a>
                    @endif
                @endauth
            </nav>
        </header>

        <main>
            {{-- Hero --}}
            <section class="mx-auto max-w-4xl px-6 pb-16 pt-12 text-center sm:pt-20">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-medium text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300">
                    {{ __('Social proof in minutes') }}
                </span>
                <h1 class="mt-6 text-4xl font-extrabold tracking-tight sm:text-6xl">
                    {{ __('Turn happy customers into a') }}
                    <span class="bg-gradient-to-r from-rose-500 to-indigo-500 bg-clip-text text-transparent">{{ __('Wall of Love') }}</span>
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">
                    {{ __('Share one link to collect testimonials, curate the best ones from a single inbox, and embed a beautiful widget on any website with a copy-paste snippet.') }}
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ auth()->check() ? route('spaces.create') : (Route::has('register') ? route('register') : route('login')) }}"
                       class="w-full rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 hover:bg-indigo-500 sm:w-auto">
                        {{ auth()->check() ? __('Create a space') : __('Get started for free') }}
                    </a>
                    <a href="#how-it-works" class="w-full rounded-xl border border-zinc-300 px-6 py-3 text-sm font-semibold hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-900 sm:w-auto">
                        {{ __('See how it works') }}
                    </a>
                </div>
                <p class="mt-4 text-xs text-zinc-500">{{ __('Free plan: 3 spaces · 100 testimonials per space · no credit card') }}</p>
            </section>

            {{-- How it works --}}
            <section id="how-it-works" class="border-y border-zinc-200 bg-zinc-50 py-20 dark:border-zinc-800 dark:bg-zinc-900/50">
                <div class="mx-auto max-w-6xl px-6">
                    <h2 class="text-center text-3xl font-bold tracking-tight">{{ __('Three steps to social proof') }}</h2>
                    <div class="mt-12 grid gap-6 md:grid-cols-3">
                        @foreach ([
                            ['1', __('Create a Space'), __('Set a title, the question you want answered, a theme, and choose which fields to collect: company, social link, photo or star rating.')],
                            ['2', __('Collect & curate'), __('Share your public link. Submissions land in your inbox, where you favorite, approve (heart), edit, hide or delete them.')],
                            ['3', __('Embed anywhere'), __('Pick a masonry grid or carousel, light or dark, and paste the iframe or script snippet into any site or CMS.')],
                        ] as [$num, $heading, $body])
                            <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                                <span class="flex size-9 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">{{ $num }}</span>
                                <h3 class="mt-4 text-lg font-semibold">{{ $heading }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $body }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Pricing --}}
            <section class="mx-auto max-w-4xl px-6 py-20">
                <h2 class="text-center text-3xl font-bold tracking-tight">{{ __('Simple pricing') }}</h2>
                <div class="mt-12 grid gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-zinc-200 p-8 dark:border-zinc-800">
                        <h3 class="text-lg font-semibold">{{ __('Free') }}</h3>
                        <p class="mt-2"><span class="text-4xl font-extrabold">$0</span><span class="text-sm text-zinc-500">/{{ __('month') }}</span></p>
                        <ul class="mt-6 space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <li>✓ {{ __('Up to 3 spaces') }}</li>
                            <li>✓ {{ __('100 testimonials per space') }}</li>
                            <li>✓ {{ __('Masonry & carousel embeds') }}</li>
                        </ul>
                    </div>
                    <div class="rounded-2xl border-2 border-indigo-500 p-8">
                        <h3 class="text-lg font-semibold">{{ __('Pro') }}</h3>
                        <p class="mt-2"><span class="text-4xl font-extrabold">$9.99</span><span class="text-sm text-zinc-500">/{{ __('month') }}</span></p>
                        <ul class="mt-6 space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <li>✓ {{ __('Up to 25 spaces') }}</li>
                            <li>✓ {{ __('1,000 testimonials per space') }}</li>
                            <li>✓ {{ __('No upgrade banners') }}</li>
                        </ul>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-zinc-200 py-8 text-center text-xs text-zinc-500 dark:border-zinc-800">
            &copy; {{ date('Y') }} {{ config('app.name', 'Wall of Love') }}
        </footer>
    </body>
</html>
