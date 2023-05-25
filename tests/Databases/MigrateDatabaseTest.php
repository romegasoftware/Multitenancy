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
            'deleted_at',
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
            'deleted_at',
        ], $columns);
    }
}
