<div
    class="min-h-full w-full p-4 sm:p-6 transition-colors duration-200"
    style="background-color: {{ $bg }};"
    x-data="{
        {{-- Tell the host page (public/embed.js) how tall we are so the iframe can auto-size. --}}
        postHeight() {
            if (window.parent === window || ! this.$refs.content) return;
            const pad = parseFloat(getComputedStyle(this.$el).paddingTop) * 2;
            window.parent.postMessage({
                type: 'wol-resize',
                space: @js($space->slug),
                height: Math.ceil(this.$refs.content.offsetHeight + pad),
            }, '*');
        },
        init() {
            new ResizeObserver(() => this.postHeight()).observe(this.$refs.content);
            this.postHeight();
        },
        scrollLeft() {
            if (this.$refs.carousel) {
                this.$refs.carousel.scrollBy({ left: -340, behavior: 'smooth' });
            }
        },
        scrollRight() {
            if (this.$refs.carousel) {
                this.$refs.carousel.scrollBy({ left: 340, behavior: 'smooth' });
            }
        }
    }"
>
    <div x-ref="content">
    @if ($testimonials->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="rounded-full p-4 {{ $isDark ? 'bg-zinc-800 text-zinc-500' : 'bg-zinc-100 text-zinc-400' }}">
                <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-semibold {{ $isDark ? 'text-zinc-200' : 'text-zinc-800' }}">No testimonials yet</h3>
            <p class="mt-1 text-xs {{ $isDark ? 'text-zinc-400' : 'text-zinc-500' }}">Curated reviews from customers will show up here.</p>
        </div>
    @elseif ($isCarousel)
        {{-- Carousel Layout --}}
        <div class="relative group">
            {{-- Carousel Navigation Controls --}}
            <button
                type="button"
                @click="scrollLeft()"
                class="absolute -left-2 top-1/2 -translate-y-1/2 z-10 flex size-9 items-center justify-center rounded-full shadow-md transition
                    {{ $isDark ? 'bg-zinc-800 text-zinc-200 hover:bg-zinc-700 border border-zinc-700' : 'bg-white text-zinc-700 hover:bg-zinc-50 border border-zinc-200' }}"
                aria-label="Previous testimonials"
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
                aria-label="Next testimonials"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>

            <div
                x-ref="carousel"
                class="flex gap-4 overflow-x-auto py-2 px-1 snap-x snap-mandatory scroll-smooth no-scrollbar"
                style="scrollbar-width: none; -ms-overflow-style: none;"
            >
                @foreach ($testimonials as $testimonial)
                    <div class="w-80 sm:w-96 shrink-0 snap-start">
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
        {{-- Masonry Layout --}}
        <div class="columns-1 sm:columns-2 lg:columns-3 gap-4 [column-fill:_balance]">
            @foreach ($testimonials as $testimonial)
                <div class="break-inside-avoid mb-4">
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
