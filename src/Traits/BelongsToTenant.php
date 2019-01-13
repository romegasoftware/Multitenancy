<?php

namespace RomegaDigital\Multitenancy\Traits;

use Illuminate\Database\Eloquent\Builder;
use RomegaDigital\Multitenancy\Multitenancy;

trait BelongsToTenant
{
    /**
     * The Multitenancy service provider.
     *
     * @var RomegaDigital\Multitenancy\Multitenancy
     */
	protected static $multitenancy; 

    /**
     * The "booting" method of the tenant model.
     * This defines the query scopes and creation scopes.
     *
     * @return void
     */
    public static function bootBelongsToTenant()
    {
        static::$multitenancy = app(Multitenancy::class);
        static::$multitenancy->applyTenantScope(new static());

        static::creating(function ($model) {
            static::$multitenancy->newModel($model);
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