<?php

namespace RomegaDigital\Multitenancy\Tests;

use Illuminate\Support\Facades\Schema;
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

    public $setupTestDatabase = true;

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('multitenancy.user_model', '\RomegaDigital\Multitenancy\Tests\User');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
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
            PermissionServiceProvider::class
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

        if ($this->setupTestDatabase) {
            $this->setUpDatabase($this->app);

            $this->testUser = User::first();
            $this->testTenant = app(Tenant::class)->find(1);
            $this->testAdminTenant = app(Tenant::class)->find(2);
            $this->testProduct = Product::first();
        }
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $this->loadMigrationsFrom(realpath(__DIR__ . '/../migrations'));
        $this->artisan('migrate')->run();

        $app[Tenant::class]->create([
            'name' => 'Tenant Name',
            'domain' => 'masterdomain'
        ]);
        $app[Tenant::class]->create([
            'name' => 'Admin',
            'domain' => 'admin'
        ]);

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });
        User::create(['email' => 'test@user.com']);

        Schema::create('products', function (Blueprint $table) {
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
