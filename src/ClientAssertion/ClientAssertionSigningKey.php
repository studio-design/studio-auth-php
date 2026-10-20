<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion;

use Closure;

final class ClientAssertionSigningKey
{
    /**
     * @param  string  $kid  RFC 7638 thumbprint of the public key that verifies sign()
     * @param  Closure(string): string  $sign  returns the ES256 signature as R||S, 64 octets (RFC 7518 §3.4)
     */
    public function __construct(
        public readonly string $kid,
        private readonly Closure $sign,
    ) {}

    /**
     * @throws ClientAssertionException
     */
    public function sign(string $signingInput): string
    {
        return ($this->sign)($signingInput);
    }
}
