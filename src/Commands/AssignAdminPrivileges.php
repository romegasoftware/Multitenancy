<?php

namespace RomegaDigital\Multitenancy\Commands;

use Illuminate\Console\Command;
use RomegaDigital\Multitenancy\Contracts\Tenant;
use RomegaDigital\Multitenancy\Exceptions\TenantDoesNotExist;
use RomegaDigital\Multitenancy\Multitenancy;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

class AssignAdminPrivileges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'multitenancy:super-admin
                                {identifier : Unique property identifying the user}
                                {--M|model=\App\User : Model to query the user}
                                {--C|column=email : Property column name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give a user super-admin rights and add him to the `admin` tenant';

    /**
     * Multitenancy Service Class.
     *
     * @var RomegaDigital\Multitenancy\Multitenancy
     */
    protected $multitenancy;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Multitenancy $multitenancy)
    {
        parent::__construct();

        $this->multitenancy = $multitenancy;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $column = $this->option('column');
        $userModel = $this->option('model');
        $identifier = $this->argument('identifier');

        if (!class_exists($userModel)) {
            return $this->error('User model '.$userModel.' can not be found!');
        }

        if (!$user = $this->getUser($userModel, $column, $identifier)) {
            return 0;
        }

        if (!$adminRole = $this->getAdminRole()) {
            return 0;
        }

        if (!$adminTenant = $this->getAdminTenant()) {
            return 0;
        }

        $user->assignRole($adminRole);
        $user->tenants()->save($adminTenant);

        $this->info('User with '.$column.' '.$user->{$column}.' granted Super-Administration rights.');

        return 1;
    }

    /**
     * Get user model data.
     *
     * @param string $userModel
     * @param string $column
     * @param string $identifier
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    protected function getUser($userModel, $column, $identifier)
    {
        if (!$user = $userModel::where($column, $identifier)->first()) {
            return $this->modelNotFound('User', $column, $identifier);
        }

        return $user;
    }

    /**
     * Get admin role.
     *
     * @return Spatie\Permission\Contracts\Role
     */
    protected function getAdminRole()
    {
        try {
            return Role::findByName('Super Administrator');
        } catch (RoleDoesNotExist $exception) {
            return $this->cancel('Role', 'name', 'Super Administrator');
        }
    }

    /**
     * Get admin tenant.
     *
     * @return RomegaDigital\Multitenancy\Contracts\Tenant
     */
    protected function getAdminTenant()
    {
        try {
            return $this->multitenancy->getTenantClass()::findByDomain('admin');
        } catch (TenantDoesNotExist $exception) {
            return $this->cancel('Tenant', 'domain', 'admin');
        }
    }

    /**
     * Cancel the command due to errors.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param string                             $column
     * @param string                             $identifier
     *
     * @return bool
     */
    protected function cancel($model, $column, $identifier)
    {
        $this->modelNotFound($model, $column, $identifier);
        $this->line('');
        $this->alert('Did you already run `multitenancy:install` command?');

        return false;
    }

    /**
     * Write an error for a model which can not be found.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param string                             $column
     * @param string                             $identifier
     *
     * @return void
     */
    protected function modelNotFound($model, $column, $identifier)
    {
        $this->error("$model with $column `$identifier` can not be found!");
    }
}
