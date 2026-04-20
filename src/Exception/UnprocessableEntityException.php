<?php

declare(strict_types=1);

namespace Studio\Auth\Exception;

use stdClass;
use Studio\Auth\ApiException;
use Throwable;

/**
 * Thrown when the API responds with HTTP 422 Unprocessable Entity.
 *
 * Indicates the request was syntactically valid but failed business-rule
 * validation. The associated ProblemDetails payload (if present) typically
 * enumerates the individual field-level violations.
 */
class UnprocessableEntityException extends ApiException
{
    public function __construct(
        string $message = '',
        ?array $responseHeaders = [],
        stdClass|string|null $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 422, $responseHeaders, $responseBody, $previous);
    }
}
