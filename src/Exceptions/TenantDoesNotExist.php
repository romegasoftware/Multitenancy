<?php

namespace RomegaDigital\Multitenancy\Exceptions;

use InvalidArgumentException;

class TenantDoesNotExist extends InvalidArgumentException
{
    /**
     * A Tenant does not exist at the supplied domain.
     *
     * @param string $domain
     *
     * @return static
     */
    public static function forDomain(string $domain): self
    {
        return new static("There is no tenant at domain `{$domain}`.");
    }
}
