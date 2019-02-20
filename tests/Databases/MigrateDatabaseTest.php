<?php

namespace RomegaDigital\Multitenancy\Tests\Databases;

use RomegaDigital\Multitenancy\Tests\TestCase;

class MigrateDatabaseTest extends TestCase
{
    /** @test */
    public function it_runs_the_migrations()
    {
        $columns = \Schema::getColumnListing('tenants');
        $this->assertEquals([
            'id',
            'name',
            'domain',
            'created_at',
            'updated_at',
        ], $columns);

        $columns = \Schema::getColumnListing('tenant_user');
        $this->assertEquals([
            'id',
            'tenant_id',
            'user_id',
            'created_at',
            'updated_at',
        ], $columns);
    }

    /** @test **/
    public function it_has_factory()
    {
        $tenant = factory(\RomegaDigital\Multitenancy\Models\Tenant::class)->create();
        $compare = \RomegaDigital\Multitenancy\Models\Tenant::latest('id')->first();

        $this->assertEquals($compare->id, $tenant->id);
        $this->assertEquals($compare->name, $tenant->name);
        $this->assertEquals($compare->domain, $tenant->domain);
    }
}
