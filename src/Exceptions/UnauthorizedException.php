<?php

namespace RomegaDigital\Multitenancy\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
	/**
	 * A user does not have valid permissions to access
	 * the supplied domain.
	 */
	public static function forDomain(string $domain)
	{
		$message = "The authenticated user does not have access to domain `{$domain}`.";

		$exception = new static(403, $message, null, []);

		return $exception;
	}

	/**
	 * A user is not logged in.
	 */
	public static function notLoggedIn(): self
	{
	    return new static(403, 'User is not logged in.', null, []);
	}


}