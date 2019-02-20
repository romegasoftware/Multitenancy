<?php

namespace RomegaDigital\Multitenancy\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use RomegaDigital\Multitenancy\Models\Tenant;
use RomegaDigital\Multitenancy\Tests\Product;
use RomegaDigital\Multitenancy\Tests\ProductPolicy;
use RomegaDigital\Multitenancy\Tests\TestCase;
use Spatie\Permission\Models\Role;

class GateTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['router']->resource('products', 'RomegaDigital\Multitenancy\Tests\Stubs\Controllers\ProductController');

        Gate::policy(Product::class, ProductPolicy::class);
    }

    /**
     * Turn the given URI into a fully qualified URL.
     *
     * @param string $uri
     *
     * @return string
     */
    protected function prepareUrlForRequest($uri)
    {
        $uri = "http://admin.localhost.com/{$uri}";

        return trim($uri, '/');
    }

    /** @test **/
    public function it_does_not_allow_regular_user()
    {
        $this->actingAs($this->testUser);
        $this->testAdminTenant->users()->save($this->testUser);

        $product = Product::create([
            'name'      => 'Another Tenants Product',
            'tenant_id' => Tenant::create([
                'name'   => 'Another Tenant',
                'domain' => 'anotherdomain',
            ])->id,
        ]);

        $response = $this->get('products/'.$product->id);

        $response->assertForbidden();
    }

    /** @test **/
    public function it_does_not_allow_super_administrator_not_tied_to_admin_subdomain()
    {
        Role::create(['name' => 'Super Administrator']);
        $this->actingAs($this->testUser);
        $this->testUser->assignRole('Super Administrator');

        $product = Product::create([
            'name'      => 'Another Tenants Product',
            'tenant_id' => Tenant::create([
                'name'   => 'Another Tenant',
                'domain' => 'anotherdomain',
            ])->id,
        ]);

        $response = $this->get('products/'.$product->id);

        $response->assertForbidden();
    }

    /** @test **/
    public function it_does_allow_super_administrator_tied_to_domain()
    {
        Role::create(['name' => 'Super Administrator']);
        $this->actingAs($this->testUser);
        $this->testUser->assignRole('Super Administrator');
        $this->testAdminTenant->users()->save($this->testUser);

        $product = Product::create([
            'name'      => 'Another Tenants Product',
            'tenant_id' => Tenant::create([
                'name'   => 'Another Tenant',
                'domain' => 'anotherdomain',
            ])->id,
        ]);

        $response = $this->get('products/'.$product->id);

        $response->assertOK();
    }
}
