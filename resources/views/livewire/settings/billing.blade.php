<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Billing & Subscription') }}</flux:heading>

    <x-settings.layout :heading="__('Billing & Subscription')" :subheading="__('Manage your subscription tier, billing details, and plan limits')">
        <div class="my-6 w-full space-y-6">
            @if ($demoMode)
                <div class="flex items-center gap-2 rounded-xl border border-amber-300 bg-amber-50 px-4 py-2.5 text-xs text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
                    <span class="rounded bg-amber-500 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Demo mode</span>
                    <span>Stripe is not connected yet – upgrades use a demo checkout and nothing is charged.</span>
                </div>
            @endif

            {{-- Notification Messages --}}
            @if (session('status'))
                <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-medium text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200" role="status">
                    {{ session('status') }}
                </div>
            @elseif ($checkoutStatus === 'success')
                <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
                    <div class="flex items-center gap-2">
                        <svg class="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">{{ __('Payment successful! Welcome to Testimonial Pro ($9.99/mo).') }}</span>
                    </div>
                </div>
            @elseif ($checkoutStatus === 'cancelled')
                <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
                    <div class="flex items-center gap-2">
                        <svg class="size-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ __('Checkout was cancelled. You have not been charged.') }}</span>
                    </div>
                </div>
            @endif

            @if ($errorMessage)
                <div class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-900 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200">
                    <p>{{ $errorMessage }}</p>
                </div>
            @endif

            {{-- Current Plan Overview Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Current Plan</span>
                        <div class="mt-1 flex items-center gap-3">
                            <h3 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $this->currentPlan->label() }}
                                @if ($this->isPro)
                                    <span class="text-base font-normal text-zinc-500 dark:text-zinc-400">($9.99/mo)</span>
                                @endif
                            </h3>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $this->isPro ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300' : 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                {{ $this->subscriptionStatus->label() }}
                            </span>
                        </div>
                    </div>

                    @if ($this->isPro && $demoMode)
                        <flux:button wire:click="cancelDemoSubscription" wire:confirm="Cancel your Pro subscription and return to the Free plan?" variant="danger" icon="x-mark">
                            {{ __('Cancel subscription') }}
                        </flux:button>
                    @elseif ($this->isPro)
                        <flux:button wire:click="manageBilling" variant="primary" icon="credit-card">
                            {{ __('Manage Billing in Stripe') }}
                        </flux:button>
                    @else
                        <flux:button wire:click="checkout" variant="primary" class="bg-indigo-600 hover:bg-indigo-500">
                            {{ __('Upgrade to Pro ($9.99/mo)') }}
                        </flux:button>
                    @endif
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 border-t border-zinc-100 pt-4 sm:grid-cols-2 dark:border-zinc-800">
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Spaces Limit</div>
                        <div class="mt-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $this->user->spaces()->count() }} / {{ $this->user->maxSpaces() }} spaces used
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Testimonials Limit per Space</div>
                        <div class="mt-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            Up to {{ number_format($this->user->maxTestimonialsPerSpace()) }} testimonials
                        </div>
                    </div>
                </div>
            </div>

            {{-- Plan Comparison --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Free Plan --}}
                <div class="rounded-2xl border {{ ! $this->isPro ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-zinc-200 dark:border-zinc-700' }} bg-white p-6 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <h4 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Free</h4>
                        @if (! $this->isPro)
                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Active</span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <span class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100">$0</span>
                        <span class="text-sm text-zinc-500">/month</span>
                    </div>
                    <ul class="mt-6 space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
                        <li class="flex items-center gap-2">
                            <svg class="size-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Up to 3 Spaces
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            100 Testimonials per space
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Public collection page
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Standard Wall of Love embeds
                        </li>
                    </ul>
                </div>

                {{-- Pro Plan --}}
                <div class="rounded-2xl border {{ $this->isPro ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-zinc-200 dark:border-zinc-700' }} bg-white p-6 relative dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h4 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Pro Upgrade</h4>
                            <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">Popular</span>
                        </div>
                        @if ($this->isPro)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">Active</span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <span class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100">$9.99</span>
                        <span class="text-sm text-zinc-500">/month</span>
                    </div>
                    <ul class="mt-6 space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
                        <li class="flex items-center gap-2 font-medium text-zinc-900 dark:text-zinc-200">
                            <svg class="size-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Up to 25 Spaces
                        </li>
                        <li class="flex items-center gap-2 font-medium text-zinc-900 dark:text-zinc-200">
                            <svg class="size-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            1,000 Testimonials per space
                        </li>
                        <li class="flex items-center gap-2 font-medium text-zinc-900 dark:text-zinc-200">
                            <svg class="size-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Masonry & Carousel Embed Widgets
                        </li>
                        <li class="flex items-center gap-2 font-medium text-zinc-900 dark:text-zinc-200">
                            <svg class="size-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Custom branding, themes & colors
                        </li>
                    </ul>

                    <div class="mt-6">
                        @if (! $this->isPro)
                            <button
                                type="button"
                                wire:click="checkout"
                                class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition cursor-pointer"
                            >
                                {{ __('Upgrade to Pro ($9.99/mo)') }}
                            </button>
                        @elseif ($demoMode)
                            <button
                                type="button"
                                wire:click="cancelDemoSubscription"
                                wire:confirm="Cancel your Pro subscription and return to the Free plan?"
                                class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-center text-sm font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800 transition cursor-pointer"
                            >
                                {{ __('Cancel Subscription') }}
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="manageBilling"
                                class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-center text-sm font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800 transition cursor-pointer"
                            >
                                {{ __('Manage Subscription') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>
