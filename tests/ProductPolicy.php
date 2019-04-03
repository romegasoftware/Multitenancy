<?php

namespace RomegaDigital\Multitenancy\Tests;

class ProductPolicy
{
    public function view()
    {
        return false;
    }
}
