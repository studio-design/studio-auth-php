<?php

declare(strict_types=1);

namespace Studio\Auth\Exception;

use stdClass;
use Studio\Auth\ApiException;
use Throwable;

/**
 * Thrown when the API responds with HTTP 401 Unauthorized.
 *
 * Indicates a missing, malformed, or expired authentication credential.
 * Consumers should refresh the access token and retry, or surface an
 * authentication challenge to the end user.
 */
class UnauthorizedException extends ApiException
{
    public function __construct(
        string $message = '',
        ?array $responseHeaders = [],
        stdClass|string|null $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 401, $responseHeaders, $responseBody, $previous);
    }
}
