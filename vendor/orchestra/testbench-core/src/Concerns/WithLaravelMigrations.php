<?php

namespace Orchestra\Testbench\Concerns;

use Orchestra\Testbench\Database\MigrateProcessor;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;

trait WithLaravelMigrations
{
    /**
     * Migrate Laravel's default migrations.
     *
     * @param  array|string  $database
     *
     * @return void
     */
    protected function loadLaravelMigrations($database = []): void
    {
        $options = is_array($database) ? $database : ['--database' => $database];

        $options['--path'] = 'migrations';

        $migrator = new MigrateProcessor($this, $options);

        $migrator->up();

        $this->app[ConsoleKernel::class]->setArtisan(null);

        $this->beforeApplicationDestroyed(function () use ($migrator) {
            $migrator->rollback();
        });
    }
}
