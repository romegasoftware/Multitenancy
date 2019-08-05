<?php

namespace RomegaDigital\Multitenancy\Tests\Feature;

use Illuminate\Http\Response;
use RomegaDigital\Multitenancy\Exceptions\TenantDoesNotExist;
use RomegaDigital\Multitenancy\Middleware\TenantMiddleware;
use RomegaDigital\Multitenancy\Tests\TestCase;

class MultitenancyTest extends TestCase
{
    protected $tenantMiddleware;

    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['router']->get('/login', function () {
            return 'login';
        })->name(config('multitenancy.redirect_route'));
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->tenantMiddleware = new TenantMiddleware(app('auth'), app('multitenancy'));
    }

    protected function buildRequest($domain)
    {
        app('request')->headers->set('HOST', $domain.'.example.com');

        return $this->tenantMiddleware->handle(app('request'), function () {
            return (new Response())->setContent('<html></html>');
        });
    }

    /** @test */
    public function it_returns_the_current_tenant_when_set_by_middleware()
    {
        $this->actingAs($this->testUser);

        $this->testTenant->users()->sync($this->testUser);

        $this->buildRequest($this->testTenant->domain);

        $this->assertEquals($this->testTenant->domain, app('multitenancy')->currentTenant()->domain);
    }

    /** @test */
    public function it_throws_exception_when_tenant_not_set()
    {
        $this->actingAs($this->testUser);

        $this->testTenant->users()->sync($this->testUser);

        try {
            $this->buildRequest('testdomain');
            app('multitenancy')->currentTenant();
            $this->fail('Expected exception not thrown');
        } catch (TenantDoesNotExist $e) {
            $this->assertEquals('There is no tenant at domain `testdomain`.', $e->getMessage());
        }
    }

    /** @test */
    public function it_throws_exception_when_tenant_not_set_never_touched_middleware()
    {
        $this->actingAs($this->testUser);

        $this->testTenant->users()->sync($this->testUser);

        try {
            app('multitenancy')->currentTenant();
            $this->fail('Expected exception not thrown');
        } catch (TenantDoesNotExist $e) {
            $this->assertEquals('There is no tenant at domain ``.', $e->getMessage());
        }
    }
}
