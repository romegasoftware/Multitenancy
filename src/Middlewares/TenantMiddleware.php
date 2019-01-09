<?php

namespace RomegaDigital\Multitenancy\Middlewares;

use Closure;
use Illuminate\Http\Request;
use RomegaDigital\Multitenancy\Exceptions\UnauthorizedException;

class TenantMiddleware
{
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
        if (app('auth')->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $domain = app('multitenancy')->getCurrentSubDomain();
        $tenant = app('multitenancy')->getTenantClass()::findByDomain($domain);

        if($tenant && $tenant->users->contains(app('auth')->user()->id)) {

        	app('multitenancy')->setTenant($tenant)->applyTenantScopeToDeferredModels();

        	return $next($request);
        }

    	throw UnauthorizedException::forDomain($tenant->domain);
	}
}