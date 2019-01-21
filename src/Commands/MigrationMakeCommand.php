<?php

namespace RomegaDigital\Multitenancy\Commands;

use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\GeneratorCommand;

class MigrationMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'multitenancy:migration {name : The name of the table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant migration file';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Multitenancy migration';

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct($files);

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $this->composer->dumpAutoloads();

        return 1;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/add_tenancy_to_table.stub';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        return str_replace(
            ['DummyTable', 'DummyTenantTable'],
            [lcfirst($this->getNameInput()), config('multitenancy.table_names.tenants')],
            $stub
        );
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = 'AddTenantIDColumnTo' . ucfirst($this->getNameInput()) . 'Table';

        return str_replace('DummyClass', $class, $stub);
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $timestamp = date('Y_m_d_His');
        $table = lcfirst($this->getNameInput());

        return $this->laravel->databasePath() . "/migrations/{$timestamp}_add_tenant_id_column_to_{$table}_table.php";
    }
}
