<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // On hosts that terminate TLS in front of the app (e.g. Railway),
        // force generated URLs/assets to use https so the browser doesn't
        // block them as mixed content.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
