<?php

namespace RomegaDigital\Multitenancy;

use Illuminate\Support\ServiceProvider;
use RomegaDigital\Multitenancy\Contracts\Tenant as TenantContract;

class MultitenancyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/../migrations'));

        $this->publishes([
            __DIR__.'/../config/multitenancy.php' => config_path('multitenancy.php'),
        ], 'config');

        $this->registerModelBindings();

        $this->app->singleton(Multitenancy::class, function () {
            return new Multitenancy();
        });
        
        $this->app->alias(Multitenancy::class, 'multitenancy');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/multitenancy.php',
            'multitenancy'
        );
    }

    protected function registerModelBindings()
    {
        $this->app->bind(TenantContract::class, $this->app->config['multitenancy.tenant_model']);
    }
}
