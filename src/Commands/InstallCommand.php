<?php

namespace RomegaDigital\Multitenancy\Commands;

use Illuminate\Console\Command;
use RomegaDigital\Multitenancy\Multitenancy;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'multitenancy:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform all steps necessary to setup package quickly';

    /**
     * Multitenancy Service Class.
     *
     * @var RomegaDigital\Multitenancy\Multitenancy
     */
    protected $multitenancy;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Multitenancy $multitenancy)
    {
        parent::__construct();

        $this->multitenancy = $multitenancy;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->handleMigrations();
        $this->addSuperAdminRole();
        $this->addAdminTenant();
    }

    /**
     * Publishes and migrates required migrations.
     *
     * @return void
     */
    protected function handleMigrations()
    {
        $this->info('Publishing required migrations...');

        $this->callSilent('vendor:publish', [
            '--provider' => 'Spatie\Permission\PermissionServiceProvider',
            '--tag' => ['migrations']
        ]);

        $this->callSilent('vendor:publish', [
            '--provider' => 'RomegaDigital\Multitenancy\MultitenancyServiceProvider',
            '--tag' => ['migrations']
        ]);

        $this->info('Migrations published!');

        $this->line('');
        $this->call('migrate');
        $this->line('');
    }

    /**
     * Creates a super admin role and 'can access admin' 
     * permission.
     *
     * @return void
     */
    protected function addSuperAdminRole()
    {
        $this->info('Adding `Super Administrator` Role...');

        $this->call('permission:create-role', [
            'name' => 'Super Administrator',
            'guard' => null,
            'permissions' => 'can access admin'
        ]);

        $this->line('');
    }

    /**
     * Creates the admin tenant model.
     *
     * @return void
     */
    protected function addAdminTenant()
    {
        $this->info('Adding `admin` domain...');

        $this->multitenancy->getTenantClass()::create([
            'name' => 'Admin Portal',
            'domain' => 'admin'
        ]);

        $this->info('Admin domain added successfully!');
    }
}
