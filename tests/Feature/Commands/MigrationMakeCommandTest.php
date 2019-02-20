<?php

namespace RomegaDigital\Multitenancy\Tests\Feature\Commands;

use RomegaDigital\Multitenancy\Tests\TestCase;

class MigrationMakeCommandTest extends TestCase
{
    public $setupTestDatabase = false;

    /** @test */
    public function it_adds_a_new_migration_with_tenant_id_to_the_specified_table()
    {
        $this->mock(\Illuminate\Filesystem\Filesystem::class)
            ->makePartial()
            ->shouldReceive('put')
            ->once();

        $this->artisan('multitenancy:migration', ['name' => 'testproducts'])
            ->expectsOutput('Multitenancy migration created successfully.')
            ->assertExitCode(1);
    }

    /** @test **/
    public function it_can_handle_multiword_names()
    {
        $this->mock(\Illuminate\Filesystem\Filesystem::class)
            ->makePartial()
            ->shouldReceive('put')
            ->with(\Mockery::any(), \Mockery::pattern('/AddTenantIDColumnToTestNameTable/'))
            ->once();

        $this->artisan('multitenancy:migration', ['name' => 'test_name']);
    }
}
