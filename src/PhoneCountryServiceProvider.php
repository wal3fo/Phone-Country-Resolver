<?php

namespace Wal3fo\PhoneCountry;

use Illuminate\Support\ServiceProvider;

class PhoneCountryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge default config
        $this->mergeConfigFrom(__DIR__ . '/../config/phone-country.php', 'phone-country');

        $this->app->singleton(PhoneCountryService::class, function ($app) {
            return new PhoneCountryService();
        });
    }

    public function boot(): void
    {
        // Allow users to publish the config file
        $this->publishes([
            __DIR__ . '/../config/phone-country.php' => config_path('phone-country.php'),
        ], 'phone-country-config');
    }
}
