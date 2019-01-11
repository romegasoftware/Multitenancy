<?php

namespace RomegaDigital\Multitenancy\Tests;

use Illuminate\Database\Schema\Blueprint;
use RomegaDigital\Multitenancy\Contracts\Tenant;
use Spatie\Permission\PermissionServiceProvider;
use RomegaDigital\Multitenancy\MultitenancyFacade;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use RomegaDigital\Multitenancy\MultitenancyServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected $testUser;
    protected $testTenant;
    protected $testAdminTenant;
    protected $testProduct;

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('multitenancy.user_model', '\RomegaDigital\Multitenancy\Tests\User');
    }

    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            MultitenancyServiceProvider::class,
            // PermissionServiceProvider::class
        ];
    }

    /**
     * Load package alias
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Multitenancy' => MultitenancyFacade::class,
        ];
    }

    public function setUp()
    {
        parent::setUp();
        $this->setUpDatabase($this->app);

        $this->testUser = User::first();
        $this->testTenant = app(Tenant::class)->find(1);
        $this->testAdminTenant = app(Tenant::class)->find(2);
        $this->testProduct = Product::first();
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        include_once __DIR__ . '/../migrations/create_tenants_table.php.stub';

        (new \CreateTenantsTable())->up();

        $app[Tenant::class]->create([
            'name' => 'Tenant Name',
            'domain' => 'masterdomain'
        ]);
        $app[Tenant::class]->create([
            'name' => 'Admin',
            'domain' => 'admin'
        ]);

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });
        User::create(['email' => 'test@user.com']);

        $app['db']->connection()->getSchemaBuilder()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');
            $table->softDeletes();
        });
        Product::create([
            'name' => 'Product 1',
            'tenant_id' => '1'
        ]);
    }
}
