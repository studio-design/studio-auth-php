<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion;

use JsonException;

final class JwkThumbprint
{
    /**
     * RFC 7638 §3: SHA-256 over the required members only, ordered
     * lexicographically, with no whitespace. §3.2 lists crv, kty, x, y for EC keys.
     *
     * @param  array<string, string>  $jwk
     *
     * @throws JsonException
     */
    public static function ofEcPublicKey(array $jwk): string
    {
        $canonical = json_encode(
            ['crv' => $jwk['crv'], 'kty' => $jwk['kty'], 'x' => $jwk['x'], 'y' => $jwk['y']],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
        );

        return Base64Url::encode(hash('sha256', $canonical, true));
    }
}
