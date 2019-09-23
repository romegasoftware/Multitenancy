<?php

namespace RomegaDigital\Multitenancy\Traits;

use RomegaDigital\Multitenancy\Multitenancy;

trait HasTenants
{
    /**
     * Add the current tenant to the created user tenants.
     */
    public static function bootHasTenants()
    {
        static::created(function ($model) {
            $ignoreTenantOnUserCreation = config('multitenancy.ignore_tenant_on_user_creation');

            if (! request()->has(Multitenancy::TENANT_SET_HEADER) || $ignoreTenantOnUserCreation) {
                return;
            }

            $model->tenants()->save(
                resolve(Multitenancy::class)->currentTenant()
            );
        });
    }

    /**
     * The model belongs to many tenants.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tenants()
    {
        return $this->belongsToMany(config('multitenancy.tenant_model'))
            ->withTimestamps();
    }
}
