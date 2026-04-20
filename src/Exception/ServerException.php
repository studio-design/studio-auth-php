<?php

declare(strict_types=1);

namespace Studio\Auth\Exception;

use InvalidArgumentException;
use stdClass;
use Studio\Auth\ApiException;
use Throwable;

/**
 * Thrown when the API responds with any HTTP 5xx status (500, 502, 503, 504, …).
 *
 * Indicates an upstream failure on the auth service side. Consumers should
 * treat all 5xx uniformly: retry with exponential backoff for idempotent
 * operations, and surface a generic "service unavailable" error otherwise.
 * Distinguishing between individual 5xx codes is rarely useful at the caller.
 */
class ServerException extends ApiException
{
    public function __construct(
        string $message = '',
        int $code = 500,
        ?array $responseHeaders = [],
        stdClass|string|null $responseBody = null,
        ?Throwable $previous = null,
    ) {
        if ($code < 500 || $code > 599) {
            throw new InvalidArgumentException(
                sprintf('ServerException requires a 5xx status code, got %d.', $code),
            );
        }
        parent::__construct($message, $code, $responseHeaders, $responseBody, $previous);
    }
}
