<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
     * When accessed via IP (e.g. http://192.168.1.x:8000), use that host for URLs
     * so redirects and cookies stay on the same address and login works.
     */
    public function boot(): void
    {
        if (!app()->runningInConsole() && app()->environment(['local', 'testing'])) {
            $request = request();
            if ($request && $request->getHttpHost()) {
                URL::forceRootUrl($request->getScheme() . '://' . $request->getHttpHost());
            }
        }
    }
}
