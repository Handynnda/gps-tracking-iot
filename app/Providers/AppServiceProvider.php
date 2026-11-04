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
     */
    public function boot(): void
    {
        // =========================================================================
        // OTOMATISASI HTTPS:
        // Otomatis aktif jika sedang di Hosting (Production), Ngrok, atau reverse proxy HTTPS.
        // Otomatis nonaktif (HTTP biasa) jika sedang dibuka di Localhost (127.0.0.1:8000).
        // =========================================================================
        if (app()->environment('production') || 
            str_contains(request()->getHost(), 'ngrok') || 
            str_contains(request()->header('x-forwarded-host', ''), 'ngrok') ||
            str_contains(request()->header('x-forwarded-proto', ''), 'https')) {
            URL::forceScheme('https');
        }
    }
}