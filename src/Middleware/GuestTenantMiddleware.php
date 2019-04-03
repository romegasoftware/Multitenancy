<?php

namespace RomegaDigital\Multitenancy\Middleware;

use Closure;
use RomegaDigital\Multitenancy\Multitenancy;

class GuestTenantMiddleware
{
    /**
     * @var RomegaDigital\Multitenancy\Multitenancy
     */
    protected $multitenancy;

    /**
     * Create new TenantMiddleware instance.
     *
     * @param Illuminate\Contracts\Auth\Factory $auth
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
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $tenant = $this->multitenancy->receiveTenantFromRequest();

        $this->multitenancy->setTenant($tenant)->applyTenantScopeToDeferredModels();

        return $next($request);
    }
}
