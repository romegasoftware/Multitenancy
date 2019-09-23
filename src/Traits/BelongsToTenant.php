<?php

namespace RomegaDigital\Multitenancy\Traits;

use RomegaDigital\Multitenancy\Multitenancy;

trait BelongsToTenant
{
    /**
     * The "booting" method of the tenant model.
     * This defines the query scopes and creation scopes.
     */
    public static function bootBelongsToTenant()
    {
        resolve(Multitenancy::class)->applyTenantScope(new static());

        static::creating(function ($model) {
            resolve(Multitenancy::class)->newModel($model);
        });
    }

    /**
     * The model belongs to a tenant.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(config('multitenancy.tenant_model'));
    }
}
