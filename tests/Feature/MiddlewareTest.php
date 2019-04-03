<?php

namespace RomegaDigital\Multitenancy\Tests\Feature;

use Illuminate\Http\Response;
use RomegaDigital\Multitenancy\Exceptions\TenantDoesNotExist;
use RomegaDigital\Multitenancy\Exceptions\UnauthorizedException;
use RomegaDigital\Multitenancy\Middleware\TenantMiddleware;
use RomegaDigital\Multitenancy\Tests\TestCase;
use Illuminate\Auth\AuthenticationException;

class MiddlewareTest extends TestCase
{
    protected $tenantMiddleware;
    protected $permissionMiddleware;
    protected $roleOrPermissionMiddleware;

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

        $app['router']->get('/login', function () {
            return 'login';
        })->name('login');
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->tenantMiddleware = new TenantMiddleware(app('auth'), app('multitenancy'));
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
        $this->actingAs($this->testUser);

        try {
            $this->buildRequest('testdomain');
            $this->fail('Expected exception not thrown');
        } catch (TenantDoesNotExist $e) { //Not catching a generic Exception or the fail function is also catched
            $this->assertEquals('There is no tenant at domain `testdomain`.', $e->getMessage());
        }
    }

    /** @test **/
    public function it_throws_error_if_user_is_not_part_of_tenant()
    {
        $this->actingAs($this->testUser);

        try {
            $this->buildRequest($this->testTenant->domain);
            $this->fail('Expected exception not thrown');
        } catch (UnauthorizedException $e) { //Not catching a generic Exception or the fail function is also catched
            $this->assertEquals(403, $e->getStatusCode());
            $this->assertEquals("The authenticated user does not have access to domain `{$this->testTenant->domain}`.", $e->getMessage());
        }
    }

    /** @test **/
    public function it_throws_error_if_user_is_not_logged_in()
    {
        try {
            $this->buildRequest($this->testTenant->domain);
            $this->fail('Expected exception not thrown');
        } catch (AuthenticationException $e) { //Not catching a generic Exception or the fail function is also catched
            $this->assertEquals('Unauthenticated.', $e->getMessage());
        }
    }

    /** @test **/
    public function it_allows_users_who_are_associated_with_a_valid_domain()
    {
        $this->actingAs($this->testUser);

        $this->testTenant->users()->sync($this->testUser);

        $this->assertEquals(
            $this->buildRequest($this->testTenant->domain)->getStatusCode(),
            200
        );
    }
}
