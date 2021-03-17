<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Good;
use App\Observers\CategoryObserver;
use App\Observers\GoodsObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*if (config('app.debug')) {
            \DB::listen(function ($query) {
                logger($query->sql, $query->bindings);
            });
        }*/
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Category::observe(CategoryObserver::class);
        Good::observe(GoodsObserver::class);
    }
}
