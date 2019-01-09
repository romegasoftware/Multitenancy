<?php

namespace RomegaDigital\Multitenancy\Tests\Stubs\Controllers;

use Closure;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;
use RomegaDigital\Multitenancy\Middlewares\TenantMiddleware;
use RomegaDigital\Multitenancy\Tests\Product;

class ProductController extends \Illuminate\Routing\Controller
{
	public function __construct()
	{
	    $this->middleware([
	    	function ($request, Closure $next) {
		        $route = app('router')->getCurrentRoute();
		        Assert::assertSame(ProductController::class, get_class($route->getController()));
		        return $next($request);
		    },
		    TenantMiddleware::class
		]);
	}
	public function index()
	{
		return Product::all();
	}

	public function store(Request $request)
	{
		return Product::create([
			'name' => $request->name
		]);
	}

}