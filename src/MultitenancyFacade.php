<?php

namespace RomegaDigital\Multitenancy;

use Illuminate\Support\Facades\Facade;

class MultitenancyFacade extends Facade
{
	/**
	 * Access the facade.
	 *
	 * @return string
	 */
    protected static function getFacadeAccessor()
    {
        return 'multitenancy';
    }
}