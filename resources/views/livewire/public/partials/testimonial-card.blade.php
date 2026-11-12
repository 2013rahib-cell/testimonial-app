<div
    class="rounded-2xl border p-5 shadow-xs transition
        {{ $isDark
            ? 'bg-zinc-900 border-zinc-800 text-zinc-100'
            : 'bg-white border-zinc-200 text-zinc-900' }}
        {{ $testimonial->is_featured ? ($isDark ? 'ring-1 ring-rose-500/50' : 'ring-1 ring-rose-400') : '' }}
        {{ $hasAnimations ? 'duration-300 hover:-translate-y-1 hover:shadow-lg' : '' }}"
>
    {{-- Header with Submitter & Badges --}}
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            @if ($testimonial->submitter_avatar_path)
                <img
                    src="{{ Storage::url($testimonial->submitter_avatar_path) }}"
                    alt="{{ $testimonial->submitter_name }}"
                    class="size-10 rounded-full object-cover shrink-0 ring-1 {{ $isDark ? 'ring-zinc-700' : 'ring-zinc-200' }}"
                >
            @else
                <div class="size-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0
                    {{ $isDark ? 'bg-indigo-950/80 text-indigo-300 ring-1 ring-indigo-800' : 'bg-indigo-100 text-indigo-700' }}">
                    {{ strtoupper(substr($testimonial->submitter_name, 0, 1)) }}
                </div>
            @endif

            <div class="min-w-0">
                <p class="text-sm font-semibold truncate {{ $isDark ? 'text-zinc-100' : 'text-zinc-900' }}">
                    {{ $testimonial->submitter_name }}
                </p>
                @if ($testimonial->submitter_role || $testimonial->submitter_company)
                    <p class="text-xs truncate {{ $isDark ? 'text-zinc-400' : 'text-zinc-500' }}">
                        {{ collect([$testimonial->submitter_role, $testimonial->submitter_company])->filter()->implode(' · ') }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Featured Heart Badge --}}
        @if ($testimonial->is_featured)
            <span class="inline-flex items-center gap-1 text-rose-500 shrink-0" title="Featured Testimonial">
                <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                </svg>
            </span>
        @endif
    </div>

    {{-- Star Rating --}}
    @if ($testimonial->rating)
        <div class="mt-3 flex items-center gap-1">
            @for ($i = 1; $i <= 5; $i++)
                <svg
                    class="size-4 {{ $i <= $testimonial->rating ? 'text-amber-400 fill-amber-400' : ($isDark ? 'text-zinc-700' : 'text-zinc-200') }}"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                </svg>
            @endfor
        </div>
    @endif

    {{-- Content --}}
    <p class="mt-3 text-sm leading-relaxed {{ $isDark ? 'text-zinc-300' : 'text-zinc-700' }}">
        "{{ $testimonial->content }}"
    </p>

    {{-- Video preview indicator if video type --}}
    @if ($testimonial->type === \App\Enums\TestimonialType::Video && $testimonial->video_url)
        <div class="mt-3 rounded-lg overflow-hidden border {{ $isDark ? 'border-zinc-800 bg-zinc-950' : 'border-zinc-100 bg-zinc-50' }} p-2 flex items-center gap-2">
            <svg class="size-4 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
            </svg>
            <span class="text-xs truncate text-indigo-600 dark:text-indigo-400 font-medium">Video Testimonial</span>
        </div>
    @endif

    {{-- Footer Meta --}}
    <div class="mt-4 flex items-center justify-between border-t pt-3 text-[11px] {{ $isDark ? 'border-zinc-800 text-zinc-500' : 'border-zinc-100 text-zinc-400' }}">
        <span>{{ $testimonial->created_at?->diffForHumans() }}</span>
        <span class="inline-flex items-center gap-1 font-medium text-emerald-600 dark:text-emerald-400">
            <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
            Verified
        </span>
    </div>
</div>
