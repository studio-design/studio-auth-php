<?php

declare(strict_types=1);

namespace Studio\Auth\Exception;

use stdClass;
use Studio\Auth\ApiException;
use Throwable;

/**
 * Thrown when the API responds with HTTP 409 Conflict.
 *
 * Indicates a state conflict such as a unique-constraint violation, an
 * optimistic concurrency conflict, or an attempt to create a resource that
 * already exists. Consumers typically reconcile application state and retry.
 */
class ConflictException extends ApiException
{
    public function __construct(
        string $message = '',
        ?array $responseHeaders = [],
        stdClass|string|null $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 409, $responseHeaders, $responseBody, $previous);
    }
}
