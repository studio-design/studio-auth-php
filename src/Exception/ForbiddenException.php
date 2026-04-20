<?php

declare(strict_types=1);

namespace Studio\Auth\Exception;

use stdClass;
use Studio\Auth\ApiException;
use Throwable;

/**
 * Thrown when the API responds with HTTP 403 Forbidden.
 *
 * The caller was authenticated successfully but lacks permission for the
 * requested operation. Consumers should surface a permission error to the
 * end user rather than retrying.
 */
class ForbiddenException extends ApiException
{
    public function __construct(
        string $message = '',
        ?array $responseHeaders = [],
        stdClass|string|null $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 403, $responseHeaders, $responseBody, $previous);
    }
}
