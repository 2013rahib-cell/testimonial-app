<div class="max-w-5xl mx-auto space-y-6 py-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-200 pb-5 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-1">
                <a href="{{ route('spaces.index') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Your Spaces</a>
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                <span class="text-zinc-700 dark:text-zinc-200">{{ $space->name }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                Testimonial Inbox
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Review, curate, and manage testimonials for <strong class="text-zinc-700 dark:text-zinc-300">{{ $space->name }}</strong>.
            </p>
        </div>

        {{-- Cap indicator --}}
        <div class="flex items-center gap-3 shrink-0">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                {{ $isAtCap ? 'bg-amber-500/10 text-amber-700 dark:text-amber-300' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                {{ $totalCount }} / {{ $collectionCap }} collected
                @if($isAtCap) · Cap reached @endif
            </span>
            @if($space->enable_ratings)
                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                    <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Ratings on
                </span>
            @endif
        </div>
    </div>

    {{-- Flash message --}}
    @if(session('status'))
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    {{-- Cap warning --}}
    @if($isAtCap)
        <div class="rounded-xl border border-amber-300 bg-amber-50/70 p-4 dark:border-amber-900/60 dark:bg-amber-950/30">
            <div class="flex items-center gap-3">
                <svg class="size-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                <p class="text-xs text-amber-800 dark:text-amber-200">
                    This space has reached its <strong>{{ $collectionCap }}-testimonial cap</strong>. New public submissions are paused until you delete some or upgrade your plan.
                </p>
            </div>
        </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="flex items-center gap-1 overflow-x-auto pb-1" role="tablist">
        @foreach([
            ['key' => 'all',      'label' => 'All',      'count' => $totalCount],
            ['key' => 'pending',  'label' => 'Pending',  'count' => $pendingCount],
            ['key' => 'approved', 'label' => 'Approved', 'count' => $approvedCount],
            ['key' => 'featured', 'label' => 'Favorites','count' => $featuredCount],
            ['key' => 'hidden',   'label' => 'Hidden',   'count' => $hiddenCount],
        ] as $tab)
            <button
                type="button"
                wire:click="setFilter('{{ $tab['key'] }}')"
                role="tab"
                aria-selected="{{ $filter === $tab['key'] ? 'true' : 'false' }}"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition whitespace-nowrap
                    {{ $filter === $tab['key']
                        ? 'bg-indigo-600 text-white shadow-sm'
                        : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200' }}"
            >
                {{ $tab['label'] }}
                <span class="rounded-full px-1.5 py-0.5 text-xs font-semibold
                    {{ $filter === $tab['key'] ? 'bg-white/20 text-white' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                    {{ $tab['count'] }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- Testimonials list --}}
    @if($testimonials->isEmpty())
        <div class="rounded-2xl border-2 border-dashed border-zinc-200 p-12 text-center dark:border-zinc-800">
            <svg class="mx-auto size-10 text-zinc-300 dark:text-zinc-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
            </svg>
            <p class="mt-4 text-sm font-medium text-zinc-500 dark:text-zinc-400">No testimonials found for this filter.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($testimonials as $testimonial)
                <div
                    wire:key="testimonial-{{ $testimonial->id }}"
                    class="group relative rounded-2xl border bg-white shadow-xs transition
                        {{ $testimonial->is_featured ? 'border-indigo-300 dark:border-indigo-700' : 'border-zinc-200 dark:border-zinc-800' }}
                        {{ $testimonial->is_hidden ? 'opacity-60' : '' }}
                        dark:bg-zinc-900"
                    x-data="{ menuOpen: false }"
                >
                    {{-- Featured pin ribbon --}}
                    @if($testimonial->is_featured)
                        <div class="absolute -top-px left-4 inline-flex items-center gap-1 rounded-b-md bg-indigo-600 px-2 py-0.5 text-[10px] font-semibold text-white">
                            <svg class="size-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            Pinned
                        </div>
                    @endif

                    <div class="p-5 {{ $testimonial->is_featured ? 'pt-7' : 'pt-5' }}">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">

                            {{-- Customer Info --}}
                            <div class="flex items-start gap-3 min-w-0">
                                {{-- Avatar --}}
                                <div class="shrink-0">
                                    @if($testimonial->submitter_avatar_path)
                                        <img
                                            src="{{ Storage::disk('public')->url($testimonial->submitter_avatar_path) }}"
                                            alt="{{ $testimonial->submitter_name }}"
                                            class="size-10 rounded-full object-cover ring-2 ring-zinc-100 dark:ring-zinc-800"
                                        >
                                    @else
                                        <div class="size-10 rounded-full bg-indigo-100 dark:bg-indigo-950/60 flex items-center justify-center text-indigo-700 dark:text-indigo-300 text-sm font-bold">
                                            {{ strtoupper(substr($testimonial->submitter_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Name / company / meta --}}
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 truncate">
                                        {{ $testimonial->submitter_name }}
                                    </p>
                                    @if($testimonial->submitter_company || $testimonial->submitter_role)
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                                            {{ collect([$testimonial->submitter_role, $testimonial->submitter_company])->filter()->implode(' · ') }}
                                        </p>
                                    @endif
                                    @if($testimonial->submitter_email)
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500 truncate">{{ $testimonial->submitter_email }}</p>
                                    @endif
                                    @if($testimonial->submitter_social_url)
                                        <a href="{{ $testimonial->submitter_social_url }}" target="_blank" rel="nofollow noopener noreferrer" class="block text-xs text-indigo-600 hover:underline dark:text-indigo-400 truncate">
                                            {{ \Illuminate\Support\Str::after($testimonial->submitter_social_url, '://') }}
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Right-side controls --}}
                            <div class="flex items-center gap-2 shrink-0">

                                {{-- Status badge --}}
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold
                                    {{ $testimonial->status === \App\Enums\TestimonialStatus::Approved
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'
                                        : ($testimonial->status === \App\Enums\TestimonialStatus::Rejected
                                            ? 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300'
                                            : 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300') }}">
                                    {{ $testimonial->status->label() }}
                                </span>

                                @if($testimonial->is_hidden)
                                    <span class="inline-flex items-center rounded-full bg-zinc-200 px-2 py-0.5 text-[11px] font-semibold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                        Hidden
                                    </span>
                                @endif

                                {{-- ★ Favorite toggle --}}
                                <button
                                    type="button"
                                    wire:click="toggleFavorite({{ $testimonial->id }})"
                                    title="{{ $testimonial->is_featured ? 'Unpin from top' : 'Pin to top (Favorite)' }}"
                                    class="rounded-lg p-1.5 transition
                                        {{ $testimonial->is_featured
                                            ? 'text-indigo-600 dark:text-indigo-400'
                                            : 'text-zinc-400 hover:text-indigo-500 dark:hover:text-indigo-400' }}"
                                >
                                    <svg class="size-5" fill="{{ $testimonial->is_featured ? 'currentColor' : 'none' }}" viewBox="0 0 20 20" stroke="{{ $testimonial->is_featured ? 'none' : 'currentColor' }}" stroke-width="1.5">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>

                                {{-- ♥ Wall of Love / Approve toggle --}}
                                <button
                                    type="button"
                                    wire:click="toggleApproval({{ $testimonial->id }})"
                                    title="{{ $testimonial->status === \App\Enums\TestimonialStatus::Approved ? 'Remove from Wall of Love (unapprove)' : 'Add to Wall of Love (approve for public embedding)' }}"
                                    class="rounded-lg p-1.5 transition
                                        {{ $testimonial->status === \App\Enums\TestimonialStatus::Approved
                                            ? 'text-rose-500 dark:text-rose-400'
                                            : 'text-zinc-400 hover:text-rose-500 dark:hover:text-rose-400' }}"
                                >
                                    <svg class="size-5" fill="{{ $testimonial->status === \App\Enums\TestimonialStatus::Approved ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                </button>

                                {{-- ⋯ Ellipsis menu --}}
                                <div class="relative" @click.outside="menuOpen = false">
                                    <button
                                        type="button"
                                        @click="menuOpen = !menuOpen"
                                        class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200 transition"
                                        title="More options"
                                    >
                                        <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>

                                    <div
                                        x-show="menuOpen"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        x-cloak
                                        class="absolute right-0 z-10 mt-1 w-44 rounded-xl border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                    >
                                        {{-- Edit --}}
                                        <button
                                            type="button"
                                            @click="menuOpen = false"
                                            wire:click="startEdit({{ $testimonial->id }})"
                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                        >
                                            <svg class="size-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                            Edit Text
                                        </button>

                                        {{-- Hide / Unhide --}}
                                        <button
                                            type="button"
                                            @click="menuOpen = false"
                                            wire:click="toggleHidden({{ $testimonial->id }})"
                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                        >
                                            @if($testimonial->is_hidden)
                                                <svg class="size-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                Show Publicly
                                            @else
                                                <svg class="size-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                                Hide from Public
                                            @endif
                                        </button>

                                        <div class="my-1 border-t border-zinc-100 dark:border-zinc-800"></div>

                                        {{-- Delete --}}
                                        <button
                                            type="button"
                                            @click="menuOpen = false"
                                            wire:click="deleteTestimonial({{ $testimonial->id }})"
                                            wire:confirm="Delete this testimonial permanently? This cannot be undone."
                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Star Rating --}}
                        @if($space->enable_ratings && $testimonial->rating !== null)
                            <div class="mt-3 flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="size-4 {{ $i <= $testimonial->rating ? 'text-amber-400' : 'text-zinc-200 dark:text-zinc-700' }}"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="ml-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $testimonial->rating }}/5</span>
                            </div>
                        @endif

                        {{-- Testimonial text or inline editor --}}
                        @if($editingId === $testimonial->id)
                            <div class="mt-3">
                                <textarea
                                    wire:model="editContent"
                                    rows="4"
                                    class="w-full rounded-lg border border-indigo-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 dark:border-indigo-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    placeholder="Testimonial content…"
                                ></textarea>
                                @error('editContent')
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <div class="mt-2 flex items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="saveEdit"
                                        class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                                    >
                                        Save
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="cancelEdit"
                                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @else
                            <p class="mt-3 text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed">
                                {{ $testimonial->content }}
                            </p>
                        @endif

                        {{-- Footer meta --}}
                        <div class="mt-4 flex items-center justify-between text-xs text-zinc-400 dark:text-zinc-500 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <span>{{ $testimonial->created_at?->diffForHumans() }}</span>
                            <span class="uppercase tracking-wide font-medium">{{ $testimonial->type->label() }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
