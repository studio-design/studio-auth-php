<?php

declare(strict_types=1);

namespace Studio\Auth\Exception;

use stdClass;
use Studio\Auth\ApiException;
use Throwable;

/**
 * Thrown when the API responds with HTTP 429 Too Many Requests.
 *
 * The caller has exceeded an allowed request rate. Consumers should back off
 * and retry after the window indicated by the `Retry-After` response header
 * (retrievable via {@see ApiException::getResponseHeaders()}).
 */
class RateLimitException extends ApiException
{
    public function __construct(
        string $message = '',
        ?array $responseHeaders = [],
        stdClass|string|null $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 429, $responseHeaders, $responseBody, $previous);
    }
}
