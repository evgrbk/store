<?php

namespace App\Providers;

use App\Services\GoodsImport\Vendors\UrunlerParser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use App\Services\GoodsImport\ParserService;

class GoodsImportServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->provides() as $service) {
            $this->app->singleton($service, function () use ($service) {
                return new $service();
            });
        }

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            ParserService::class,
            UrunlerParser::class
        ];
    }
}
