<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;


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

    public function boot()
    {
        date_default_timezone_set('Asia/Jakarta');

        try {
            DB::connection()->statement("SET time_zone = '+07:00'");
        } catch (\Exception $e) {
            // boleh dikosongkan
        }

        // Bind public path for shared hosting (e.g., Hostinger)
        // Allows libraries like DomPDF to correctly resolve assets
        // Prefer explicit env, else auto-detect common hosting folder
        $customPublic = env('APP_PUBLIC_PATH');
        $autoPublic = 'public_html';
        $resolved = null;
        if (!empty($customPublic)) {
            $resolved = base_path($customPublic);
        } elseif (is_dir(base_path($autoPublic))) {
            $resolved = base_path($autoPublic);
        }
        if ($resolved) {
            $this->app->bind('path.public', function () use ($resolved) {
                return $resolved;
            });
        }
    }
}
