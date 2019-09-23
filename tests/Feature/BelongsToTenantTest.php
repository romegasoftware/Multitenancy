<?php

namespace RomegaDigital\Multitenancy\Tests\Feature;

use RomegaDigital\Multitenancy\Models\Tenant;
use RomegaDigital\Multitenancy\Tests\TestCase;
use RomegaDigital\Multitenancy\Tests\Fixtures\Product;
use RomegaDigital\Multitenancy\Tests\Fixtures\Controllers\ProductController;

class BelongsToTenantTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['router']->resource('products', ProductController::class);
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
        $uri = "http://{$this->testTenant->domain}.localhost.com/{$uri}";

        return trim($uri, '/');
    }

    /** @test */
    public function it_adds_current_tenant_id_to_model_on_create()
    {
        $this->actingAs($this->testUser);
        $this->testTenant->users()->save($this->testUser);

        $response = $this->post('products', [
            'name' => 'Another Tenants Product',
        ]);
        $response->assertStatus(201);
        $this->assertEquals(Product::first()->tenant_id, $this->testTenant->id);
    }

    /** @test */
    public function it_only_retrieves_records_scoped_to_current_subdomain()
    {
        $this->actingAs($this->testUser);
        $this->testTenant->users()->save($this->testUser);

        Product::create([
            'name' => 'Another Tenants Product',
            'tenant_id' => Tenant::create([
                'name' => 'Another Tenant',
                'domain' => 'anotherdomain',
            ])->id,
        ]);

        $response = $this->get('products');
        $response->assertStatus(200);
        $this->assertEquals(Product::where('tenant_id', $this->testTenant->id)->get(), $response->getContent());
    }

    /** @test **/
    public function it_retrieves_all_records_when_accessing_via_admin_subdomain()
    {
        $this->actingAs($this->testUser);
        $this->testAdminTenant->users()->save($this->testUser);
        $this->testTenant->domain = $this->testAdminTenant->domain;

        Product::create([
            'name' => 'Another Tenants Product',
            'tenant_id' => Tenant::create([
                'name' => 'Another Tenant',
                'domain' => 'anotherdomain',
            ])->id,
        ]);

        $response = $this->get('products');
        $response->assertStatus(200);
        $this->assertEquals(Product::withoutGlobalScopes()->get(), $response->getContent());
    }
}
