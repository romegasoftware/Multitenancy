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
            if ($model->tenants->count() > 0) {
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
