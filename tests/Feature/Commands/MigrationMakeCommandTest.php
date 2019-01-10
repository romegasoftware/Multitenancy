<?php

namespace RomegaDigital\Multitenancy\Tests\Feature\Commands;

use RomegaDigital\Multitenancy\Tests\TestCase;

class MigrationMakeCommandTest extends TestCase
{
    /** @test */
    public function it_adds_a_new_migration_with_tenant_id_to_the_specified_table()
    {
        $this->artisan('multitenancy:migration', ['name' => 'products'])
            ->expectsOutput('Tenant-Migration created successfully.');
    }
}
