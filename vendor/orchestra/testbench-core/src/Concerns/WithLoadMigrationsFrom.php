<?php

namespace Orchestra\Testbench\Concerns;

use Orchestra\Testbench\Database\MigrateProcessor;

trait WithLoadMigrationsFrom
{
    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @param  string|array  $paths
     *
     * @return void
     */
    protected function loadMigrationsFrom($paths): void
    {
        $options = is_array($paths) ? $paths : ['--path' => $paths];

        if (isset($options['--realpath']) && is_string($options['--realpath'])) {
            $options['--path'] = [$options['--realpath']];
        }

        $options['--realpath'] = true;

        $migrator = new MigrateProcessor($this, $options);
        $migrator->up();

        $this->beforeApplicationDestroyed(function () use ($migrator) {
            $migrator->rollback();
        });
    }
}
