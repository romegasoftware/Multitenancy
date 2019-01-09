<?php

namespace RomegaDigital\Multitenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RomegaDigital\Multitenancy\Contracts\Tenant;

class Multitenancy
{
	/** @var string */
	protected $tenantClass;

	/** @var int */
	protected $tenant = null;

	/** @var collect */
	protected $deferredModels;

	/**
	 * Multitenancy constructor.
	 */
	public function __construct()
	{
	    $this->tenantClass = config('multitenancy.tenant_model');
	    $this->deferredModels = collect();
	}

	/**
	 * Sets the Tenant to a Tenant Model
	 */
	public function setTenant(Tenant $tenant)
	{
		$this->tenant = $tenant;
		return $this;
	}

	/**
	 * Applies applicable tenant scopes to model or if not booted yet
	 * store for deferment.
	 */
	public function applyTenantScope(Model $model)
	{
		if (is_null($this->tenant)) {
		    $this->deferredModels->push($model);
		    return;
		}

		if($this->tenant->domain == 'admin') {
			return;
		}

	    $model->addGlobalScope('tenant', function (Builder $builder) {
		    $builder->where('tenant_id', '=', $this->tenant->id);
	    });
	}

	/**
	 * Applies applicable tenant id to model on create
	 */
	public function newModel(Model $model)
	{
		if (is_null($this->tenant)) {
		    $this->deferredModels->push($model);
		    return;
		}

    	if (!isset($model->tenant_id)) {
    	    $model->setAttribute('tenant_id', $this->tenant->id);
    	}
	}

	/**
	 * Applies applicable tenant scope to deferred model booted 
	 * before tenants setup.
	 */
	public function applyTenantScopeToDeferredModels()
	{
	    $this->deferredModels->each(function ($model) {
	    	$this->applyTenantScope($model);
	    });

	    $this->deferredModels = collect();
	}

    /**
     * Get an instance of the tenant class.
     *
     * @return \RomegaDigital\Multitenancy\Contracts\Tenant
     */
    public function getTenantClass(): Tenant
    {
        return app($this->tenantClass);
    }

    /**
     * Parses the request to pull out the first element separated
     * by `.` in the $_SERVER['HTTP_HOST']. 
     * 
     * ex:
     * test.domain.com returns test
     * test2.test.domain.com returns test2
     */
	public function getCurrentSubDomain() : string
	{
		$currentDomain = app('request')->getHost();

		// Get rid of the TLD and root domain
		// ex: masterdomain.test.example.com returns
		// [ masterdomain, test ]
        $subdomains = explode('.', $currentDomain,-2);

        // Combine multiple level of domains into 1 string
        // ex: back to masterdomain.text
        $subdomain = implode($subdomains,'.');

	    return $subdomain;
	}


}