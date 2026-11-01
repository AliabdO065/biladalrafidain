<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {Schema::defaultStringLength(191);
        if (filled(config('app.public_path'))) {
            $this->app->usePublicPath(config('app.public_path'));
        }
        Paginator::useBootstrapFive();
        $this->loadJsonTranslationsFrom(lang_path('operations'));
        if ($this->app->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        //
    }
}
