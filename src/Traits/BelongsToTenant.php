<?php

namespace RomegaDigital\Multitenancy\Traits;

use Illuminate\Database\Eloquent\Builder;
use RomegaDigital\Multitenancy\Multitenancy;

trait BelongsToTenant
{
	protected static $multitenancy; 

    public static function bootBelongsToTenant()
    {
        static::$multitenancy = app(Multitenancy::class);
        static::$multitenancy->applyTenantScope(new static());

        static::creating(function ($model) {
            static::$multitenancy->newModel($model);
        });
    }

    /**
     * Define the relationship ownership to the Tenant model
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
	public function tenant()
	{
	    return $this->belongsTo(config('multitenancy.tenant_model'));
	}
}