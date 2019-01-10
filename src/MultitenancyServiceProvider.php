<?php

namespace RomegaDigital\Multitenancy;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use RomegaDigital\Multitenancy\Contracts\Tenant as TenantContract;

class MultitenancyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     *
     * @return void
     */
    public function boot(Filesystem $filesystem)
    {
        $this->loadMigrationsFrom(realpath(__DIR__ . '/../migrations'));

        $this->publishes([
            __DIR__ . '/../migrations/create_tenants_table.php' => $this->getMigrationFileName($filesystem),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../config/multitenancy.php' => config_path('multitenancy.php'),
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
            __DIR__ . '/../config/multitenancy.php',
            'multitenancy'
        );
    }

    /**
     * Register model bindings.
     *
     * @return void
     */
    protected function registerModelBindings()
    {
        $this->app->bind(TenantContract::class, $this->app->config['multitenancy.tenant_model']);
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path . '*_create_tenants_table.php');
            })->push($this->app->databasePath() . "/migrations/{$timestamp}_create_tenants_table.php")
            ->first();
    }
}
