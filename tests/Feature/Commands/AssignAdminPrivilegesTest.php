<?php

namespace RomegaDigital\Multitenancy\Tests\Feature\Commands;

use Spatie\Permission\Models\Role;
use RomegaDigital\Multitenancy\Models\Tenant;
use RomegaDigital\Multitenancy\Tests\TestCase;
use RomegaDigital\Multitenancy\Tests\Fixtures\User;

class AssignAdminPrivilegesTest extends TestCase
{
    /** @test */
    public function it_throws_an_error_and_exits_if_the_given_user_model_class_is_not_found()
    {
        $this->artisan('multitenancy:super-admin', [
                'identifier' => 'test@user.com',
            ])
            ->expectsOutput('User model \App\Models\User can not be found!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_throws_an_error_and_exits_if_no_user_model_is_found()
    {
        $this->artisan('multitenancy:super-admin', [
                'identifier' => 'fail@user.com',
                '--model' => config('multitenancy.user_model'),
            ])
            ->expectsOutput('User with email `fail@user.com` can not be found!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_throws_an_error_and_exits_if_no_super_adminitration_role_is_found()
    {
        $this->artisan('multitenancy:super-admin', [
                'identifier' => 'test@user.com',
                '--model' => config('multitenancy.user_model'),
            ])
            ->expectsOutput('Role with name `Super Administrator` can not be found!')
            ->expectsOutput('*     Did you already run `multitenancy:install` command?     *')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_throws_an_error_and_exits_if_no_admin_tenant_is_found()
    {
        Role::create(['name' => 'Super Administrator']);

        $tenant = Tenant::findByDomain('admin');
        $tenant->domain = 'testadmin';
        $tenant->save();

        $this->artisan('multitenancy:super-admin', [
            'identifier' => 'test@user.com',
            '--model' => config('multitenancy.user_model'),
        ])
        ->expectsOutput('Tenant with domain `admin` can not be found!')
        ->expectsOutput('*     Did you already run `multitenancy:install` command?     *')
        ->assertExitCode(0);
    }

    /** @test */
    public function it_assigns_super_administrator_role_and_admin_tenant_to_given_user()
    {
        Role::create(['name' => 'Super Administrator']);

        $this->artisan('multitenancy:super-admin', [
                'identifier' => 'test@user.com',
                '--model' => config('multitenancy.user_model'),
            ])
            ->expectsOutput('User with email test@user.com granted Super-Administration rights.')
            ->assertExitCode(1);

        $user = User::whereEmail('test@user.com')->first();
        $this->assertTrue($user->hasRole('Super Administrator'));
    }
}
