<?php

namespace RomegaDigital\Multitenancy\Tests\Feature;

use RomegaDigital\Multitenancy\Exceptions\TenantDoesNotExist;
use RomegaDigital\Multitenancy\Models\Tenant;
use RomegaDigital\Multitenancy\Tests\TestCase;

class TenantTest extends TestCase
{
    /** @test */
    public function it_throws_an_exception_if_domain_not_found()
    {
        $this->expectException(TenantDoesNotExist::class);
        app(Tenant::class)->findByDomain('nonexistentdomain');
    }

    /** @test */
    public function it_is_retrievable_by_domain()
    {
        $permission_by_domain = app(Tenant::class)->findByDomain($this->testTenant->domain);
        $this->assertEquals($this->testTenant->id, $permission_by_domain->id);
    }
}
