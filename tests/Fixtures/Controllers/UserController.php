<?php

namespace RomegaDigital\Multitenancy\Tests\Fixtures\Controllers;

use Closure;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;
use RomegaDigital\Multitenancy\Models\Tenant;
use RomegaDigital\Multitenancy\Tests\Fixtures\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use RomegaDigital\Multitenancy\Middleware\TenantMiddleware;

class UserController extends \Illuminate\Routing\Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware([
            function ($request, Closure $next) {
                $route = app('router')->getCurrentRoute();
                Assert::assertSame(UserController::class, get_class($route->getController()));

                return $next($request);
            },
            TenantMiddleware::class,
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->has('tenant')) {
            return User::create([
                'email' => $request->email,
            ]);
        }

        $user = new User([
            'email' => $request->email,
        ]);

        resolve(Tenant::class)->find($request->tenant)->first()->users()->save($user);

        return $user;
    }
}
