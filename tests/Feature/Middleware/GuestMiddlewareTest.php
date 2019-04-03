<?php

namespace RomegaDigital\Multitenancy\Tests\Feature\Middleware;

use Illuminate\Http\Response;
use RomegaDigital\Multitenancy\Tests\TestCase;
use RomegaDigital\Multitenancy\Exceptions\TenantDoesNotExist;
use RomegaDigital\Multitenancy\Middleware\GuestTenantMiddleware;

class GuestMiddlewareTest extends TestCase
{
    protected $tenantMiddleware;

    public function setUp(): void
    {
        parent::setUp();

        $this->tenantMiddleware = new GuestTenantMiddleware(app('multitenancy'));
    }

    protected function buildRequest($domain)
    {
        app('request')->headers->set('HOST', $domain . '.example.com');

        return $this->tenantMiddleware->handle(app('request'), function () {
            return (new Response())->setContent('<html></html>');
        });
    }

    /** @test */
    public function it_throws_error_if_domain_not_found()
    {
        try {
            $this->buildRequest('testdomain');
            $this->fail('Expected exception not thrown');
        } catch (TenantDoesNotExist $e) { //Not catching a generic Exception or the fail function is also catched
            $this->assertEquals('There is no tenant at domain `testdomain`.', $e->getMessage());
        }
    }

    /** @test **/
    public function it_allows_guest_users_to_access_tenant_scoped_requests()
    {
        $this->assertEquals(
            $this->buildRequest($this->testTenant->domain)->getStatusCode(),
            200
        );
    }
}
