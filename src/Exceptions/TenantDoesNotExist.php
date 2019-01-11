<?php

namespace RomegaDigital\Multitenancy\Exceptions;

use InvalidArgumentException;

class TenantDoesNotExist extends InvalidArgumentException
{
	/**
	 * A Tenant does not exist at the supplied domain.
	 */
    public static function forDomain(string $domain)
    {
        return new static("There is no tenant at domain `{$domain}`.");
    }
}
