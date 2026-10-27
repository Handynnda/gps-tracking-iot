<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// Ubah BARIS ini tidak menjadi komen AGAR NGROK BISA MEMBACA CSS
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
        // Hapus tanda komentar jika ingin menjalankan Ngrok:
        // =========================================================================
        
         URL::forceScheme('https');

         if (str_contains(request()->getHost(), 'ngrok') || str_contains(request()->header('x-forwarded-host', ''), 'ngrok')) {
             URL::forceScheme('https');
         }
        
    }
}