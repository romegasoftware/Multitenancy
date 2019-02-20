<?php

namespace RomegaDigital\Multitenancy\Middleware;

use Closure;
use Illuminate\Http\Request;
use RomegaDigital\Multitenancy\Contracts\Tenant;
use RomegaDigital\Multitenancy\Exceptions\UnauthorizedException;
use RomegaDigital\Multitenancy\Multitenancy;

class TenantMiddleware
{
    /**
     * @var RomegaDigital\Multitenancy\Multitenancy
     */
    protected $multitenancy;

    /**
     * Create new TenantMiddleware instance.
     *
     * @param RomegaDigital\Multitenancy\Multitenancy $multitenancy
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
     *
     * @throws \RomegaDigital\Multitenancy\Exceptions\UnauthorizedException
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $tenant = $this->multitenancy->receiveTenantFromRequest();

        if (!$this->authorizedToAccessTenant($tenant)) {
            throw UnauthorizedException::forDomain($tenant->domain);
        }

        $this->multitenancy->setTenant($tenant)->applyTenantScopeToDeferredModels();

        return $next($request);
    }

    /**
     * Check if user is authorized to access tenant's domain.
     *
     * @param \RomegaDigital\Multitenancy\Contracts\Tenant $tenant
     *
     * @return bool
     */
    protected function authorizedToAccessTenant(Tenant $tenant)
    {
        return $tenant && $tenant->users->contains(auth()->user()->id);
    }
}
