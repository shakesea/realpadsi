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
    }



}
