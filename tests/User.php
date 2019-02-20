<?php

namespace RomegaDigital\Multitenancy\Tests;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use RomegaDigital\Multitenancy\Traits\HasTenants;
use Spatie\Permission\Traits\HasRoles;

class User extends Model implements AuthorizableContract, AuthenticatableContract
{
    use HasRoles, HasTenants, Authorizable, Authenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email'];
    public $timestamps = false;
    protected $table = 'users';
    public $guard_name = 'web';
}
