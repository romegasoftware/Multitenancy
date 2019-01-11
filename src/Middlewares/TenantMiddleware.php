<?php

namespace RomegaDigital\Multitenancy\Middlewares;

use Closure;
use Illuminate\Http\Request;
use RomegaDigital\Multitenancy\Multitenancy;
use RomegaDigital\Multitenancy\Models\Tenant;
use RomegaDigital\Multitenancy\Exceptions\UnauthorizedException;

class TenantMiddleware
{
    /**
     * @var RomegaDigital\Multitenancy\Multitenancy
     */
    protected $multitenancy;

    /**
     * Create new TenantMiddleware instance.
     *
     * @param Multitenancy $multitenancy
     */
    public function __construct(Multitenancy $multitenancy)
    {
        $this->multitenancy = $multitenancy;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $domain = $this->multitenancy->getCurrentSubDomain();
        $tenant = $this->multitenancy->getTenantClass()::findByDomain($domain);

        if (!$this->authorizedToAccessTenant($tenant)) {
            throw UnauthorizedException::forDomain($tenant->domain);
        }

        $this->multitenancy->setTenant($tenant)->applyTenantScopeToDeferredModels();

        return $next($request);
    }

    /**
     * Check if user is authorized to access tenant's domain.
     *
     * @param RomegaDigital\Multitenancy\Models\Tenant $tenant
     *
     * @return boolean
     */
    protected function authorizedToAccessTenant(Tenant $tenant)
    {
        return $tenant && $tenant->users->contains(auth()->user()->id);
    }
}
