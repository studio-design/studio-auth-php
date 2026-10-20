<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion;

use RuntimeException;

/**
 * Messages carry no key material, signatures or assertions: the SDK cannot
 * know where a consumer logs them.
 */
final class ClientAssertionException extends RuntimeException
{
    public static function unsupportedPublicKey(string $reason): self
    {
        return new self('Unsupported public key: '.$reason.'.');
    }

    public static function noSigningKeyVersion(string $cryptoKeyName): self
    {
        return new self(sprintf('No ENABLED, HSM-protected, EC P-256, non-imported key version exists under %s.', $cryptoKeyName));
    }

    public static function kmsIntegrityCheckFailed(string $field): self
    {
        return new self(sprintf('Cloud KMS response failed the %s integrity check; the request or response was corrupted in transit.', $field));
    }

    public static function malformedSignature(int $bytes): self
    {
        return new self(sprintf('ES256 signature must be 64 octets (R||S), got %d.', $bytes));
    }
}
