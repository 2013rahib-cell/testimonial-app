<div class="mx-auto w-full max-w-5xl py-4">
    {{-- Demo-mode notice --}}
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200" role="note">
        <svg class="mt-0.5 size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
        <div>
            <p class="font-semibold">Demo checkout – no real payment is made</p>
            <p class="mt-0.5">Card details are only validated and are never stored. Use a test card:
                <button type="button" class="font-mono underline decoration-dotted" wire:click="$set('cardNumber', '4242 4242 4242 4242')">4242 4242 4242 4242</button> (success) or
                <button type="button" class="font-mono underline decoration-dotted" wire:click="$set('cardNumber', '4000 0000 0000 0002')">4000 0000 0000 0002</button> (declined), any future date and any CVC.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-5">
        {{-- Order summary --}}
        <aside class="lg:col-span-2 lg:order-last">
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <a href="{{ route('settings.billing') }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-medium text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200">
                    &larr; Back to billing
                </a>
                <h2 class="mt-4 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Order summary</h2>
                <div class="mt-4 flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ config('app.name') }} Pro</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Billed monthly · cancel anytime</p>
                    </div>
                    <p class="font-semibold text-zinc-900 dark:text-zinc-100">$9.99</p>
                </div>
                <ul class="mt-5 space-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                    <li>✓ Up to 25 spaces</li>
                    <li>✓ 1,000 testimonials per space</li>
                    <li>✓ No upgrade banners</li>
                </ul>
                <dl class="mt-6 space-y-2 border-t border-zinc-200 pt-4 text-sm dark:border-zinc-700">
                    <div class="flex justify-between text-zinc-600 dark:text-zinc-300"><dt>Subtotal</dt><dd>$9.99</dd></div>
                    <div class="flex justify-between text-zinc-600 dark:text-zinc-300"><dt>Tax</dt><dd>$0.00</dd></div>
                    <div class="flex justify-between text-base font-bold text-zinc-900 dark:text-zinc-100"><dt>Due today</dt><dd>$9.99</dd></div>
                </dl>
            </div>
        </aside>

        {{-- Payment form --}}
        <form wire:submit="pay" class="space-y-5 lg:col-span-3" autocomplete="off" novalidate>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Upgrade to Pro</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Enter your payment details to start your subscription.</p>
            </div>

            @if ($paymentError)
                <div class="rounded-xl border border-rose-300 bg-rose-50 p-3 text-sm font-medium text-rose-800 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200" role="alert">
                    {{ $paymentError }}
                </div>
            @endif

            <flux:input wire:model="email" type="email" :label="__('Email')" required />

            {{-- Card details --}}
            <div x-data="{
                    formatNumber(e) {
                        const d = e.target.value.replace(/\D/g, '').slice(0, 19);
                        e.target.value = d.replace(/(.{4})/g, '$1 ').trim();
                        $wire.cardNumber = e.target.value;
                    },
                    formatExpiry(e) {
                        let d = e.target.value.replace(/\D/g, '').slice(0, 4);
                        if (d.length > 2) d = d.slice(0, 2) + '/' + d.slice(2);
                        e.target.value = d;
                        $wire.expiry = d;
                    }
                }">
                <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-white">{{ __('Card information') }}</label>
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-xs dark:border-white/10 dark:bg-white/10">
                    <div class="relative">
                        <input
                            id="cardNumber"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="1234 1234 1234 1234"
                            wire:model="cardNumber"
                            x-on:input="formatNumber($event)"
                            aria-label="Card number"
                            class="block w-full border-0 bg-transparent px-3 py-2.5 font-mono text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:text-white"
                        />
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold uppercase tracking-wider text-zinc-400">VISA · MC · AMEX</span>
                    </div>
                    <div class="grid grid-cols-2 border-t border-zinc-200 dark:border-white/10">
                        <input
                            id="expiry"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="MM / YY"
                            wire:model="expiry"
                            x-on:input="formatExpiry($event)"
                            aria-label="Expiration date"
                            class="block w-full border-0 border-r border-zinc-200 bg-transparent px-3 py-2.5 font-mono text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:border-white/10 dark:text-white"
                        />
                        <input
                            id="cvc"
                            type="text"
                            inputmode="numeric"
                            maxlength="4"
                            autocomplete="off"
                            placeholder="CVC"
                            wire:model="cvc"
                            aria-label="Security code"
                            class="block w-full border-0 bg-transparent px-3 py-2.5 font-mono text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:text-white"
                        />
                    </div>
                </div>
                @error('cardNumber') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                @error('expiry') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                @error('cvc') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <flux:input wire:model="cardholderName" :label="__('Name on card')" required />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:select wire:model="country" :label="__('Country or region')">
                    @foreach (['Bangladesh', 'India', 'Pakistan', 'United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'Singapore', 'Other'] as $c)
                        <flux:select.option :value="$c">{{ $c }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="postalCode" :label="__('Postal code')" placeholder="1212" />
            </div>

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" wire:target="pay">
                <span wire:loading.remove wire:target="pay">{{ __('Pay $9.99 and subscribe') }}</span>
                <span wire:loading wire:target="pay">{{ __('Processing payment…') }}</span>
            </flux:button>

            <p class="text-center text-xs text-zinc-500 dark:text-zinc-400">
                By subscribing you agree to be billed $9.99 every month until you cancel. (Demo mode: nothing is charged.)
            </p>
        </form>
    </div>
</div>
