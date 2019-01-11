<?php

namespace RomegaDigital\Multitenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use RomegaDigital\Multitenancy\Contracts\Tenant as TenantContract;
use RomegaDigital\Multitenancy\Exceptions\TenantDoesNotExist;

class Tenant extends Model implements TenantContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    	'name',
    	'domain',
    ];

    /**
     * Create new Tenant instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('multitenancy.table_names.tenants'));
    }

    /**
     * A Tenant belongs to many users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(config('multitenancy.user_model'));
    }

    /**
     * Find a Tenant by its domain.
     *
     * @param string $domain
     * @return \RomegaDigital\Multitenancy\Contracts\Tenant
     *
     * @throws \RomegaDigital\Multitenancy\Exceptions\TenantDoesNotExist
     */
    public static function findByDomain(string $domain): TenantContract
    {
        $tenant = static::where(['domain' => $domain])->first();

        if (! $tenant) {
            throw TenantDoesNotExist::forDomain($domain);
        }

        return $tenant;
    }

}
