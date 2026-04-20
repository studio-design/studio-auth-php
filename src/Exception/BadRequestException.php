<?php

declare(strict_types=1);

namespace Studio\Auth\Exception;

use stdClass;
use Studio\Auth\ApiException;
use Throwable;

/**
 * Thrown when the API responds with HTTP 400 Bad Request.
 *
 * Typically indicates a malformed or invalid request (failed validation,
 * missing required fields, etc.). Consumers should treat this as a
 * programming error on the caller's side unless the API is known to evolve
 * validation rules independently.
 */
class BadRequestException extends ApiException
{
    public function __construct(
        string $message = '',
        ?array $responseHeaders = [],
        stdClass|string|null $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 400, $responseHeaders, $responseBody, $previous);
    }
}
