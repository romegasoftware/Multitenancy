<?php

namespace RomegaDigital\Multitenancy\Tests\Fixtures;

use Illuminate\Auth\Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use RomegaDigital\Multitenancy\Traits\HasTenants;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthorizableContract, AuthenticatableContract
{
    use HasRoles, HasTenants, Authorizable, Authenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email','name','password'];
    public $timestamps = false;
    protected $table = 'users';
    public $guard_name = 'web';
}
