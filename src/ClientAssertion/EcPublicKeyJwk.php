<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion;

final class EcPublicKeyJwk
{
    /** RFC 7518 §6.2.1.2: P-256 coordinates are a fixed 32 octets; openssl omits leading zero octets. */
    private const P256_COORDINATE_BYTES = 32;

    /**
     * @return array{kty: 'EC', crv: 'P-256', x: string, y: string, use: 'sig', alg: 'ES256', kid: string}
     *
     * @throws ClientAssertionException
     */
    public static function fromPem(string $pem): array
    {
        $key = openssl_pkey_get_public($pem);

        if ($key === false) {
            throw ClientAssertionException::unsupportedPublicKey('the PEM could not be parsed as a public key');
        }

        $details = openssl_pkey_get_details($key);

        if ($details === false
            || ($details['type'] ?? null) !== OPENSSL_KEYTYPE_EC
            || ($details['ec']['curve_name'] ?? null) !== 'prime256v1') {
            throw ClientAssertionException::unsupportedPublicKey('only EC P-256 (prime256v1) public keys are supported');
        }

        $jwk = [
            'kty' => 'EC',
            'crv' => 'P-256',
            'x'   => self::coordinate($details['ec']['x']),
            'y'   => self::coordinate($details['ec']['y']),
        ];

        return $jwk + [
            'use' => 'sig',
            'alg' => 'ES256',
            'kid' => JwkThumbprint::ofEcPublicKey($jwk),
        ];
    }

    private static function coordinate(string $bytes): string
    {
        return Base64Url::encode(str_pad($bytes, self::P256_COORDINATE_BYTES, "\0", STR_PAD_LEFT));
    }
}
