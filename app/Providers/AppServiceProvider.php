<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\CatalogSetting;

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
        Schema::defaultStringLength(191);

        // Bagikan $appSetting secara global ke seluruh view blade
        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('catalog_settings')) {
                    $view->with('appSetting', CatalogSetting::current());
                }
            } catch (\Throwable $e) {
                // Jangan crash saat database offline atau saat migrasi awal
            }
        });
    }
}

