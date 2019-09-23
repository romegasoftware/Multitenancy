<?php

namespace RomegaDigital\Multitenancy\Tests\Fixtures\Policies;

class ProductPolicy
{
    public function view()
    {
        return false;
    }
}
