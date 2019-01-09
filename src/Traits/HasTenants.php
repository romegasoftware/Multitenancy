<?php

namespace RomegaDigital\Multitenancy\Traits;

trait HasTenants
{
	public function tenants()
	{
	    return $this->belongsToMany(config('multitenancy.tenant_model'));
	}
}