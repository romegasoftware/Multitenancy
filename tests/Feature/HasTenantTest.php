<?php

namespace RomegaDigital\Multitenancy\Tests\Feature;

use RomegaDigital\Multitenancy\Tests\TestCase;
use RomegaDigital\Multitenancy\Contracts\Tenant;
use RomegaDigital\Multitenancy\Tests\Fixtures\User;
use RomegaDigital\Multitenancy\Tests\Fixtures\Controllers\UserController;

class HasTenantTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['router']->resource('users', UserController::class);
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
    public function it_adds_current_tenant_id_to_user_model_on_create()
    {
        $this->actingAs($this->testUser);
        $this->testTenant->users()->save($this->testUser);

        $this->post('users', [
                'email' => $email = 'another@user.com',
                'name' => 'UserName',
                'password' => 'PassWord',
            ])
            ->assertStatus(201);

        $this->assertContains($this->testTenant->id, User::whereEmail($email)->first()->tenants->pluck('id'));
    }

    /** @test */
    public function it_does_not_add_a_tenant_if_the_the_ignore_tenant_on_user_creation_is_set()
    {
        config(['multitenancy.ignore_tenant_on_user_creation' => true]);
        $this->testTenant->users()->save($this->testUser);
        $otherTenant = resolve(Tenant::class)->create([
            'name' => 'Other',
            'domain' => 'other',
        ]);

        $this->actingAs($this->testUser)
            ->post('users', [
                'email' => $email = 'with@tenant.com',
                'name' => 'UserName',
                'password' => 'PassWord',
                'tenant' => $otherTenant,
            ])
            ->assertStatus(201);

        $this->assertContains($otherTenant->id, $tenantIds = User::whereEmail($email)->first()->tenants->pluck('id'));
        $this->assertNotContains($this->testTenant->id, $tenantIds);
    }
}
