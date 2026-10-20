<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion;

use DateTimeImmutable;
use DateTimeZone;
use JsonException;
use Psr\Clock\ClockInterface;

/**
 * Builds a private_key_jwt client assertion (RFC 7523 §2.2) for the token,
 * introspection and revocation endpoints of studio-auth.
 */
final class ClientAssertionBuilder
{
    public const ASSERTION_TYPE = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';

    /** draft-ietf-oauth-rfc7523bis §4: explicit typing for client authentication JWTs. */
    public const TYP = 'client-authentication+jwt';

    public const ALG = 'ES256';

    /** studio-auth verifies iat with zero leeway, so a clock slightly ahead of the server would otherwise be rejected. */
    public const IAT_SKEW_SECONDS = 10;

    public const LIFETIME_SECONDS = 60;

    private const ES256_SIGNATURE_BYTES = 64;

    public function __construct(
        private readonly string $clientId,
        private readonly string $issuer,
        private readonly ClientAssertionSignerContract $signer,
        private readonly ?ClockInterface $clock = null,
    ) {}

    /**
     * @throws ClientAssertionException
     * @throws JsonException
     */
    public function build(): string
    {
        $now = $this->now()->getTimestamp();
        $key = $this->signer->selectKey();

        $header = [
            'alg' => self::ALG,
            'typ' => self::TYP,
            'kid' => $key->kid,
        ];

        // draft-ietf-oauth-rfc7523bis §4: aud is the issuer identifier as the sole value.
        $claims = [
            'iss' => $this->clientId,
            'sub' => $this->clientId,
            'aud' => $this->issuer,
            'jti' => bin2hex(random_bytes(16)),
            'iat' => $now - self::IAT_SKEW_SECONDS,
            'exp' => $now + self::LIFETIME_SECONDS,
        ];

        $signingInput = self::encodeSegment($header).'.'.self::encodeSegment($claims);
        $signature    = $key->sign($signingInput);

        if (strlen($signature) !== self::ES256_SIGNATURE_BYTES) {
            throw ClientAssertionException::malformedSignature(strlen($signature));
        }

        return $signingInput.'.'.Base64Url::encode($signature);
    }

    private function now(): DateTimeImmutable
    {
        return $this->clock?->now() ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    /**
     * @param  array<string, string|int>  $segment
     *
     * @throws JsonException
     */
    private static function encodeSegment(array $segment): string
    {
        return Base64Url::encode(json_encode($segment, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
