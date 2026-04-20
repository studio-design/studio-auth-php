<?php

declare(strict_types=1);

namespace Studio\Auth\Exception;

use stdClass;
use Studio\Auth\ApiException;
use Throwable;

/**
 * Thrown when the API responds with HTTP 404 Not Found.
 *
 * Indicates the requested resource does not exist. Consumers treating a 404
 * as a routine race condition (e.g. a member removed between list and fetch)
 * can catch this narrowly and swallow without paging on-call.
 */
class NotFoundException extends ApiException
{
    public function __construct(
        string $message = '',
        ?array $responseHeaders = [],
        stdClass|string|null $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 404, $responseHeaders, $responseBody, $previous);
    }
}
