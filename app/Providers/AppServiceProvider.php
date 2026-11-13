<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        // Behind a proxy (trustProxies '*'), never let client-supplied Host /
        // X-Forwarded-Host headers decide generated URLs (e.g. password-reset links).
        $appUrl = (string) config('app.url');

        if (app()->isProduction() && $appUrl !== '') {
            URL::forceRootUrl($appUrl);

            // Force https only when the public URL is https (e.g. Render), so a plain
            // http container run (docker run … APP_URL=http://localhost) still works.
            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
