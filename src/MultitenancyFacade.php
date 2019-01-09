<?php

namespace RomegaDigital\Multitenancy;

use Illuminate\Support\Facades\Facade;

class MultitenancyFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'multitenancy';
    }
}