<div class="max-w-5xl mx-auto space-y-8 py-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-200 pb-5 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                <a href="{{ route('spaces.index') }}" class="hover:text-zinc-900 dark:hover:text-zinc-200" wire:navigate>Spaces</a>
                <span>&rsaquo;</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-medium">Edit Space</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                Edit Space: {{ $space->name }}
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Update visual theme, collection questions, form field requirements, and URL.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ $this->publicUrl }}"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
            >
                <span>View Public Page</span>
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
            </a>
        </div>
    </div>

    {{-- Status Flash Message --}}
    @if (session('status'))
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-8">
        {{-- Section 1: Space Identity & Public Content --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                1. Space Identity & Header Content
            </h2>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Space Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        required
                    />
                    @error('name') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-1">
                    <label for="slug" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Unique Shareable URL Slug <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 flex rounded-lg shadow-xs">
                        <span class="inline-flex items-center rounded-s-lg border border-e-0 border-zinc-300 bg-zinc-50 px-3 text-xs text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
                            {{ url('/s') }}/
                        </span>
                        <input
                            type="text"
                            id="slug"
                            wire:model="slug"
                            class="block w-full flex-1 rounded-none rounded-e-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            required
                        />
                    </div>
                    @error('slug') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Header Title (Displayed on collection page) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        wire:model="title"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        required
                    />
                    @error('title') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="subtitle" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Subtitle / Introductory Message
                    </label>
                    <textarea
                        id="subtitle"
                        wire:model="subtitle"
                        rows="2"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    ></textarea>
                    @error('subtitle') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="ask" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        The "Ask" (Questions or prompts for the customer)
                    </label>
                    <textarea
                        id="ask"
                        wire:model="ask"
                        rows="3"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    ></textarea>
                    @error('ask') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Section 2: Visual Themes --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                2. Visual Theme
            </h2>

            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($this->themes as $themeOption)
                    <label
                        class="relative flex flex-col rounded-xl border p-4 cursor-pointer transition-all hover:scale-[1.02] {{ $theme === $themeOption->value ? 'border-indigo-600 ring-2 ring-indigo-500/30 dark:border-indigo-400' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600' }}"
                    >
                        <input
                            type="radio"
                            name="theme"
                            value="{{ $themeOption->value }}"
                            wire:model.live="theme"
                            class="sr-only"
                        />

                        <div class="h-20 w-full rounded-lg border p-2.5 flex flex-col justify-between shadow-inner {{ $themeOption->previewClasses() }}">
                            <div class="flex items-center justify-between">
                                <div class="size-2 rounded-full bg-indigo-500"></div>
                                <div class="h-1.5 w-8 rounded-full bg-current opacity-30"></div>
                            </div>
                            <div class="space-y-1">
                                <div class="h-1.5 w-14 rounded-full bg-current opacity-70"></div>
                                <div class="h-1 w-20 rounded-full bg-current opacity-30"></div>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $themeOption->label() }}
                            </span>
                            @if ($theme === $themeOption->value)
                                <svg class="size-4 text-indigo-600 dark:text-indigo-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
            @error('theme') <span class="mt-2 block text-xs text-red-500">{{ $message }}</span> @enderror
        </div>

        {{-- Section 3: Submission Form Fields & Toggles --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                3. Customer Form Fields & Requirement Settings
            </h2>

            <div class="mt-6 divide-y divide-zinc-200 dark:divide-zinc-800">
                {{-- Name (Locked) --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-4 gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Customer Name</span>
                            <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                Always Required
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">The full name of the reviewer.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Visible &amp; Required
                    </span>
                </div>

                {{-- Email (Locked) --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-4 gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Customer Email</span>
                            <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                Always Required
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Customer's verified email address.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Visible &amp; Required
                    </span>
                </div>

                {{-- Company Name & Role --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-4 gap-4">
                    <div>
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Company Name &amp; Title</span>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Collect the customer's organization and job title.</p>
                    </div>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            <input
                                type="checkbox"
                                wire:model.live="collectCompany"
                                class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800"
                            />
                            <span>Collect</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium {{ $collectCompany ? 'text-zinc-700 dark:text-zinc-300' : 'text-zinc-400 opacity-50 cursor-not-allowed' }}">
                            <input
                                type="checkbox"
                                wire:model.live="requireCompany"
                                @disabled(! $collectCompany)
                                class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800"
                            />
                            <span>Make Required</span>
                        </label>
                    </div>
                </div>

                {{-- Social Links --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-4 gap-4">
                    <div>
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Social Profile Links</span>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Collect Twitter, LinkedIn or personal portfolio URLs.</p>
                    </div>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            <input
                                type="checkbox"
                                wire:model.live="collectSocialLinks"
                                class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800"
                            />
                            <span>Collect</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium {{ $collectSocialLinks ? 'text-zinc-700 dark:text-zinc-300' : 'text-zinc-400 opacity-50 cursor-not-allowed' }}">
                            <input
                                type="checkbox"
                                wire:model.live="requireSocialLinks"
                                @disabled(! $collectSocialLinks)
                                class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800"
                            />
                            <span>Make Required</span>
                        </label>
                    </div>
                </div>

                {{-- Profile Photo / Submitter Avatar --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-4 gap-4">
                    <div>
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Profile Photo / Avatar</span>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Allow customers to upload their photo or avatar.</p>
                    </div>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            <input
                                type="checkbox"
                                wire:model.live="collectAvatar"
                                class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800"
                            />
                            <span>Collect</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium {{ $collectAvatar ? 'text-zinc-700 dark:text-zinc-300' : 'text-zinc-400 opacity-50 cursor-not-allowed' }}">
                            <input
                                type="checkbox"
                                wire:model.live="requireAvatar"
                                @disabled(! $collectAvatar)
                                class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800"
                            />
                            <span>Make Required</span>
                        </label>
                    </div>
                </div>

                {{-- 5-Star Ratings Toggle --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-4 gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">5-Star Star Ratings</span>
                            <div class="flex text-amber-400 text-xs">
                                &#9733;&#9733;&#9733;&#9733;&#9733;
                            </div>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Allow customers to rate your product on a 1-to-5 star rating scale.</p>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            <input
                                type="checkbox"
                                wire:model.live="enableRatings"
                                class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800"
                            />
                            <span>Enable 5-Star Ratings</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Extra Options & Active Status --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                4. Status &amp; Additional Settings
            </h2>
            <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="websiteUrl" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Website / Company URL
                    </label>
                    <input
                        type="url"
                        id="websiteUrl"
                        wire:model="websiteUrl"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    @error('websiteUrl') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="customCta" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Submit Button Text (CTA)
                    </label>
                    <input
                        type="text"
                        id="customCta"
                        wire:model="customCta"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    @error('customCta') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <input
                            type="checkbox"
                            wire:model.live="isActive"
                            class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800"
                        />
                        <span>Space is Active (Visitors can view and submit reviews)</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
            <a
                href="{{ route('spaces.index') }}"
                class="rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
                wire:navigate
            >
                Cancel
            </a>
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
            >
                <span wire:loading.remove wire:target="save">Save Changes</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
