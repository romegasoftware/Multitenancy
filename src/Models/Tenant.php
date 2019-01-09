<?php

namespace RomegaDigital\Multitenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use RomegaDigital\Multitenancy\Contracts\Tenant as TenantContract;
use RomegaDigital\Multitenancy\Exceptions\TenantDoesNotExist;

class Tenant extends Model implements TenantContract
{
    protected $fillable = [
    	'name',
    	'domain',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('multitenancy.table_names.tenants'));
    }


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(config('multitenancy.user_model'));
    }

    /**
     * Find a permission by its domain
     *
     * @param string $domain
     *
     * @throws \RomegaDigital\Multitenancy\Exceptions\TenantDoesNotExist
     *
     * @return \RomegaDigital\Multitenancy\Contracts\Tenant
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
