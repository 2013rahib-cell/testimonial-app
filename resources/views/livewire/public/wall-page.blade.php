<div class="min-h-screen px-4 py-12 sm:px-6 lg:px-8 {{ $theme->pageBackground() }}">
    <div class="mx-auto max-w-6xl">
        {{-- Header --}}
        <header class="text-center">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/10 px-3 py-1 text-xs font-semibold text-rose-500">
                <svg class="size-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                Wall of Love
            </span>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $space->name }}</h1>
            <p class="mt-2 text-sm opacity-70">
                {{ trans_choice('{0} No testimonials yet|{1} 1 testimonial|[2,*] :count testimonials', $testimonials->count(), ['count' => $testimonials->count()]) }}
                @if ($averageRating)
                    · ★ {{ number_format($averageRating, 1) }} / 5
                @endif
            </p>

            <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row" x-data="{ copied: false }">
                @if ($space->is_active)
                    <a href="{{ route('spaces.submit', $space->slug) }}"
                       class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold shadow-md {{ $theme->buttonClasses() }}">
                        Leave a testimonial
                    </a>
                @endif
                <button type="button"
                        @click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => copied = false, 2000)"
                        class="inline-flex items-center justify-center rounded-xl border border-current/20 px-5 py-2.5 text-sm font-semibold hover:bg-current/5">
                    <span x-text="copied ? 'Link copied!' : 'Copy link'">Copy link</span>
                </button>
            </div>
        </header>

        {{-- Wall --}}
        <main class="mt-12">
            @if ($testimonials->isEmpty())
                <div class="rounded-2xl border-2 border-dashed border-current/15 p-12 text-center">
                    <p class="text-sm opacity-70">No testimonials have been published here yet. Be the first!</p>
                </div>
            @else
                <div class="columns-1 gap-4 sm:columns-2 lg:columns-3">
                    @foreach ($testimonials as $testimonial)
                        <div class="mb-4 break-inside-avoid" wire:key="wall-{{ $testimonial->id }}">
                            @include('livewire.public.partials.testimonial-card', [
                                'testimonial' => $testimonial,
                                'isDark' => $isDark,
                                'hasAnimations' => true,
                            ])
                        </div>
                    @endforeach
                </div>
            @endif
        </main>

        <footer class="mt-12 text-center text-xs opacity-50">
            Powered by <a href="{{ route('home') }}" class="font-semibold hover:underline">{{ config('app.name') }}</a>
        </footer>
    </div>
</div>
