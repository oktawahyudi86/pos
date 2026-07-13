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
        if ($this->app->runningInConsole()) {
            return;
        }

        $request = request();

        if (! $request->hasHeader('Host')) {
            return;
        }

        $host = $request->getHost();

        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return;
        }

        $configuredUrl = rtrim((string) config('app.url'), '/');
        $isLocalConfiguredUrl = str_contains($configuredUrl, '127.0.0.1')
            || str_contains($configuredUrl, 'localhost');

        if ($isLocalConfiguredUrl || $this->app->environment('production')) {
            URL::forceRootUrl($request->getSchemeAndHttpHost());
        }

        if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
