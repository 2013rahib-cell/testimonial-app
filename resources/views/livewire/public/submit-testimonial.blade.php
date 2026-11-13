<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-200 {{ $theme->pageBackground() }}">
    <div class="max-w-2xl mx-auto">
        {{-- Card Container --}}
        <div class="rounded-2xl border p-6 sm:p-10 backdrop-blur-md {{ $theme->cardClasses() }}">

            @if (! $space->is_active)
                <div class="text-center py-12">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h2 class="mt-4 text-xl font-bold">Space Inactive</h2>
                    <p class="mt-2 text-sm opacity-70">
                        This collection page is currently not accepting new testimonials.
                    </p>
                </div>
            @elseif ($submitted)
                <div class="text-center py-10 space-y-4">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/30">
                        <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight">Thank You!</h2>
                    <p class="text-sm opacity-80 max-w-md mx-auto">
                        Your testimonial for <strong>{{ $space->name }}</strong> has been submitted successfully and sent for review.
                    </p>

                    {{-- Funny confirmation meme / GIF --}}
                    <div class="my-6 flex justify-center">
                        <img
                            src="{{ asset('images/thank-you.gif') }}"
                            alt="Celebration GIF"
                            data-testid="confirmation-gif"
                            class="rounded-xl shadow-lg max-w-xs sm:max-w-sm w-full object-cover border border-current/10"
                        />
                    </div>

                    <div class="pt-4">
                        <button
                            type="button"
                            wire:click="submitAnother"
                            class="text-xs underline opacity-70 hover:opacity-100"
                        >
                            Submit another response
                        </button>
                    </div>
                </div>
            @else
                {{-- Header Section --}}
                <div class="text-center space-y-3 pb-8 border-b border-current/10">
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        {{ $space->title ?? $space->name }}
                    </h1>
                    @if ($space->subtitle)
                        <p class="text-sm opacity-80 max-w-lg mx-auto">
                            {{ $space->subtitle }}
                        </p>
                    @endif

                    @if ($space->ask)
                        <div class="mt-4 rounded-xl border border-current/10 bg-current/5 p-4 text-left">
                            <span class="block text-xs font-bold uppercase tracking-wider opacity-60">QUESTIONS:</span>
                            <p class="mt-1 text-sm whitespace-pre-line opacity-90">
                                {{ $space->ask }}
                            </p>
                        </div>
                    @endif
                </div>

                @error('form')
                    <div class="mt-6 rounded-lg bg-red-500/10 border border-red-500/20 p-3 text-sm text-red-500">
                        {{ $message }}
                    </div>
                @enderror

                {{-- Form Section --}}
                <form wire:submit="submit" class="mt-8 space-y-6">
                    {{-- 5-Star Rating Picker (if enabled) --}}
                    @if ($space->enable_ratings)
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider opacity-70">
                                Your Rating <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-2 flex items-center gap-1.5">
                                @for ($star = 1; $star <= 5; $star++)
                                    <button
                                        type="button"
                                        wire:click="setRating({{ $star }})"
                                        class="p-1 transition-transform hover:scale-125 focus:outline-none"
                                        title="{{ $star }} Star"
                                    >
                                        <svg
                                            class="size-8 {{ $rating >= $star ? 'text-amber-400 fill-amber-400' : 'text-zinc-400/40 fill-none' }} transition-colors"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                        </svg>
                                    </button>
                                @endfor
                                <span class="ml-2 text-xs font-bold opacity-80">{{ $rating }} of 5 Stars</span>
                            </div>
                            @error('rating') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Testimonial Content --}}
                    <div>
                        <label for="content" class="block text-xs font-semibold uppercase tracking-wider opacity-70">
                            Your Testimonial / Review <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="content"
                            wire:model="content"
                            rows="4"
                            placeholder="Write your honest review here..."
                            class="mt-1 block w-full rounded-xl border border-current/20 bg-current/5 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        ></textarea>
                        @error('content') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Submitter Name & Email (Always Required) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-xs font-semibold uppercase tracking-wider opacity-70">
                                Your Full Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                wire:model="name"
                                placeholder="Jane Doe"
                                class="mt-1 block w-full rounded-xl border border-current/20 bg-current/5 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            />
                            @error('name') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-wider opacity-70">
                                Your Email Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="email"
                                wire:model="email"
                                placeholder="jane@example.com"
                                class="mt-1 block w-full rounded-xl border border-current/20 bg-current/5 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            />
                            @error('email') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Company Name & Role (if enabled) --}}
                    @if ($space->collect_company)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="company" class="block text-xs font-semibold uppercase tracking-wider opacity-70">
                                    Company / Organization
                                    @if ($space->require_company) <span class="text-red-500">*</span> @endif
                                </label>
                                <input
                                    type="text"
                                    id="company"
                                    wire:model="company"
                                    placeholder="Acme Corp"
                                    class="mt-1 block w-full rounded-xl border border-current/20 bg-current/5 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    @required($space->require_company)
                                />
                                @error('company') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="role" class="block text-xs font-semibold uppercase tracking-wider opacity-70">
                                    Your Role / Title
                                </label>
                                <input
                                    type="text"
                                    id="role"
                                    wire:model="role"
                                    placeholder="e.g. Head of Product"
                                    class="mt-1 block w-full rounded-xl border border-current/20 bg-current/5 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                                @error('role') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    {{-- Social Links (if enabled) --}}
                    @if ($space->collect_social_links)
                        <div>
                            <label for="socialLinks" class="block text-xs font-semibold uppercase tracking-wider opacity-70">
                                Social Link / Website
                                @if ($space->require_social_links) <span class="text-red-500">*</span> @endif
                            </label>
                            <input
                                type="text"
                                inputmode="url"
                                id="socialLinks"
                                wire:model="socialLinks"
                                placeholder="linkedin.com/in/username or https://x.com/yourhandle"
                                class="mt-1 block w-full rounded-xl border border-current/20 bg-current/5 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                @required($space->require_social_links)
                            />
                            @error('socialLinks') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Avatar Upload (if enabled) --}}
                    @if ($space->collect_avatar)
                        <div>
                            <label for="avatar" class="block text-xs font-semibold uppercase tracking-wider opacity-70">
                                Profile Photo / Headshot
                                @if ($space->require_avatar) <span class="text-red-500">*</span> @endif
                            </label>
                            <input
                                type="file"
                                id="avatar"
                                wire:model="avatar"
                                accept="image/*"
                                class="mt-1 block w-full text-xs opacity-80 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-current/10 file:text-current hover:file:bg-current/20 cursor-pointer"
                                @required($space->require_avatar)
                            />
                            <div wire:loading wire:target="avatar" class="mt-1 text-xs opacity-70">Uploading photo…</div>
                            <p class="mt-1 text-[11px] opacity-60">JPG, PNG, GIF or WebP · max 2&nbsp;MB</p>
                            @error('avatar') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Social Sharing Consent Checkbox --}}
                    <div class="pt-2">
                        <label for="consent" class="flex items-start gap-3 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                id="consent"
                                wire:model="consent"
                                class="mt-0.5 h-4 w-4 rounded border-current/30 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            />
                            <span class="text-xs opacity-80 leading-relaxed">
                                I give permission to use this testimonial across social media channels and marketing materials. <span class="text-red-500">*</span>
                            </span>
                        </label>
                        @error('consent') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-4">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="submit,avatar"
                            class="disabled:opacity-60 disabled:cursor-wait w-full inline-flex items-center justify-center gap-2 rounded-xl py-3 px-6 text-sm font-bold shadow-md transition-all focus:outline-none focus:ring-2 {{ $theme->buttonClasses() }}"
                        >
                            <span wire:loading.remove wire:target="submit">
                                {{ $space->custom_cta ?: 'Send Testimonial' }}
                            </span>
                            <span wire:loading wire:target="submit">
                                Submitting...
                            </span>
                        </button>
                    </div>
                </form>
            @endif
        </div>

        {{-- Branding Footer --}}
        <div class="mt-6 text-center text-xs opacity-50">
            Powered by <strong>Testimonial Wall of Love</strong>
        </div>
    </div>
</div>
