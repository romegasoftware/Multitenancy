<?php

namespace RomegaDigital\Multitenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RomegaDigital\Multitenancy\Contracts\Tenant;

class Multitenancy
{
    /**
     * The tenant model as defined in the config file.
     *
     * @var string
     */
    protected $tenantClass;

    /**
     * The current tenant model.
     *
     * @var RomegaDigital\Multitenancy\Contracts\Tenant
     */
    protected $tenant = null;

    /**
     * Models that need scopes before the app fully boots
     * they will be processed at a later time.
     *
     * @var collect
     */
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
     * Sets the Tenant to a Tenant Model.
     *
     * @param RomegaDigital\Multitenancy\Contracts\Tenant $tenant
     *
     * @return $this
     */
    public function setTenant(Tenant $tenant)
    {
        $this->tenant = $tenant;

        return $this;
    }

    /**
     * Applies applicable tenant scopes to model or if not booted yet
     * store for deferment.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     *
     * @return void|null
     */
    public function applyTenantScope(Model $model)
    {
        if (is_null($this->tenant)) {
            $this->deferredModels->push($model);

            return;
        }

        if ($this->tenant->domain === 'admin') {
            return;
        }

        $model->addGlobalScope('tenant', function (Builder $builder) {
            $builder->where('tenant_id', '=', $this->tenant->id);
        });
    }

    /**
     * Applies applicable tenant id to model on create.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     *
     * @return void|null
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
     *
     * @return void
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
     * Determines how best to process the URL based
     * on config and then returns the appropriate
     * subdomain text.
     *
     * @return string
     */
    public function getCurrentSubDomain() : string
    {
        $baseURL = config('multitenancy.base_url');

        if ($baseURL != null) {
            return $this->getSubDomainBasedOnBaseURL($baseURL);
        } else {
            return $this->getSubDomainBasedOnHTTPHost();
        }
    }

    /**
     * Parses the request to pull out the first element separated
     * by `.` in the $_SERVER['HTTP_HOST'].
     *
     * ex:
     * test.domain.com returns test
     * test2.test.domain.com returns test2
     *
     * @return string
     */
    protected function getSubDomainBasedOnHTTPHost() : string
    {
        $currentDomain = app('request')->getHost();

        // Get rid of the TLD and root domain
        // ex: masterdomain.test.example.com returns
        // [ masterdomain, test ]
        $subdomains = explode('.', $currentDomain, -2);

        // Combine multiple level of domains into 1 string
        // ex: back to masterdomain.test
        $subdomain = implode($subdomains, '.');

        return $subdomain;
    }

    /**
     * Parses the request and removes the portion of the URL
     * that matches the Base URL as defined in the config file.
     *
     * ex:
     * baseURL = app.domain.com
     * test2.app.domain.com returns test2
     *
     * @return string
     */
    protected function getSubDomainBasedOnBaseURL(string $baseURL) : string
    {
        $currentDomain = app('request')->getHost();

        //Remove the base domain from the currentDomain string
        $subdomain = str_replace($baseURL, '', $currentDomain);

        // If the last element is a period, remove it
        // Necessary to run this check, incase we're
        // processing the base domain.
        if (substr($subdomain, -1) == '.') {
            $subdomain = substr($subdomain, 0, -1);
        }

        return $subdomain;
    }

    /**
     * Returns tenant from request subdomain.
     *
     * @return \RomegaDigital\Multitenancy\Contracts\Tenant
     */
    public function receiveTenantFromRequest()
    {
        $domain = $this->getCurrentSubDomain();

        return $this->getTenantClass()::findByDomain($domain);
    }
}
