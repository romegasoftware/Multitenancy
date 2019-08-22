<?php

namespace RomegaDigital\Multitenancy\Traits;

trait HasTenants
{
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
