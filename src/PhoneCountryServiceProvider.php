<?php

namespace Wal3fo\PhoneCountry;

use Illuminate\Support\ServiceProvider;

class PhoneCountryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PhoneCountryService::class, function ($app) {
            return new PhoneCountryService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
