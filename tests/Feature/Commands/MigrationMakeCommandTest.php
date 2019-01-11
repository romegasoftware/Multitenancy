<?php

namespace RomegaDigital\Multitenancy\Tests\Feature\Commands;

use Illuminate\Support\Facades\File;
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
            ->expectsOutput('Multitenancy migration created successfully.');
    }
}
