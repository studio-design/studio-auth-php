<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion\Kms;

use DateTimeImmutable;
use DateTimeZone;
use Google\ApiCore\ApiException;
use Google\Cloud\Kms\V1\CryptoKeyVersion;
use Google\Cloud\Kms\V1\CryptoKeyVersion\CryptoKeyVersionAlgorithm;
use Google\Cloud\Kms\V1\CryptoKeyVersion\CryptoKeyVersionState;
use Google\Cloud\Kms\V1\ProtectionLevel;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;
use Studio\Auth\ClientAssertion\ClientAssertionException;
use Studio\Auth\ClientAssertion\Crc32c;
use Studio\Auth\ClientAssertion\EcPublicKeyJwk;

/**
 * The key versions of one Cloud KMS asymmetric key that may be published as a
 * JWKS and used to sign client assertions. Shared by the signer and the
 * relying party's JWKS endpoint so both apply the same rules.
 */
final class KmsKeyVersionInventory
{
    public const DEFAULT_TTL_SECONDS = 300;

    /** A new version is published for this long before it signs, so the authorization server has fetched it. */
    public const SIGNING_MIN_AGE_SECONDS = 86400;

    private const CACHE_KEY_PREFIX = 'studio-auth-php.kms-key-versions.v2.';

    /**
     * @param  CacheInterface  $cache  must keep an item set without a TTL until it is overwritten. PSR-16 lets a store
     *                                 apply its own default TTL instead, which would drop the last-known versions served while KMS is down.
     */
    public function __construct(
        private readonly KmsClientContract $kms,
        private readonly string $cryptoKeyName,
        private readonly CacheInterface $cache,
        private readonly int $ttlSeconds = self::DEFAULT_TTL_SECONDS,
        private readonly ?ClockInterface $clock = null,
    ) {}

    /**
     * Only versions generated inside the HSM are trusted: an imported version
     * carries key material that also exists outside KMS.
     */
    public static function isPublishable(CryptoKeyVersion $version): bool
    {
        return $version->getState() === CryptoKeyVersionState::ENABLED
            && $version->getProtectionLevel() === ProtectionLevel::HSM
            && $version->getAlgorithm() === CryptoKeyVersionAlgorithm::EC_SIGN_P256_SHA256
            && $version->getImportJob() === '';
    }

    /**
     * @return list<KmsKeyVersion> oldest first
     *
     * @throws ApiException when KMS is unreachable and nothing has been fetched yet
     * @throws ClientAssertionException
     */
    public function publishable(): array
    {
        $cached = $this->cache->get($this->cacheKey());
        $now    = $this->now()->getTimestamp();

        if (self::isCacheEntry($cached) && $now < $cached['fetched_at'] + $this->ttlSeconds) {
            return self::hydrate($cached['versions']);
        }

        try {
            $versions = $this->fetch();
        } catch (ApiException $e) {
            if (self::isCacheEntry($cached)) {
                return self::hydrate($cached['versions']);
            }

            throw $e;
        }

        $this->cache->set($this->cacheKey(), [
            'fetched_at' => $now,
            'versions'   => array_map(static fn (KmsKeyVersion $v): array => $v->toArray(), $versions),
        ]);

        return $versions;
    }

    /**
     * @throws ApiException
     * @throws ClientAssertionException
     */
    public function selectSigningVersion(): KmsKeyVersion
    {
        $versions = $this->publishable();

        if ($versions === []) {
            throw ClientAssertionException::noSigningKeyVersion($this->cryptoKeyName);
        }

        $now           = $this->now();
        $cutoffSeconds = $now->getTimestamp() - self::SIGNING_MIN_AGE_SECONDS;
        $cutoffNanos   = (int) $now->format('u') * 1000;
        $eligible      = array_values(array_filter(
            $versions,
            static fn (KmsKeyVersion $v): bool => $v->createdAtSeconds < $cutoffSeconds
                || ($v->createdAtSeconds === $cutoffSeconds && $v->createdAtNanos <= $cutoffNanos),
        ));

        if ($eligible !== []) {
            return $eligible[count($eligible) - 1];
        }

        return $versions[0];
    }

    /**
     * @return array{keys: list<array{kty: 'EC', crv: 'P-256', x: string, y: string, use: 'sig', alg: 'ES256', kid: string}>}
     *
     * @throws ApiException
     * @throws ClientAssertionException
     */
    public function jwks(): array
    {
        return ['keys' => array_map(static fn (KmsKeyVersion $v): array => $v->jwk, $this->publishable())];
    }

    /**
     * @return list<KmsKeyVersion>
     *
     * @throws ApiException
     * @throws ClientAssertionException
     */
    private function fetch(): array
    {
        $versions = [];

        foreach ($this->kms->listCryptoKeyVersions($this->cryptoKeyName) as $version) {
            if (! self::isPublishable($version)) {
                continue;
            }

            $createTime = $version->getCreateTime();

            if ($createTime === null) {
                continue;
            }

            $publicKey = $this->kms->getPublicKey($version->getName());

            if ($publicKey->getName() !== $version->getName()) {
                throw ClientAssertionException::kmsIntegrityCheckFailed('name');
            }

            if (Crc32c::of($publicKey->getPem()) !== (int) $publicKey->getPemCrc32CUnwrapped()) {
                throw ClientAssertionException::kmsIntegrityCheckFailed('pem_crc32c');
            }

            $versions[] = new KmsKeyVersion(
                $version->getName(),
                (int) $createTime->getSeconds(),
                $createTime->getNanos(),
                EcPublicKeyJwk::fromPem($publicKey->getPem()),
            );
        }

        usort($versions, static fn (KmsKeyVersion $a, KmsKeyVersion $b): int => $a->compareCreationOrder($b));

        return $versions;
    }

    /**
     * @phpstan-assert-if-true array{fetched_at: int, versions: list<array{name: string, created_at_seconds: int, created_at_nanos: int, jwk: array{kty: 'EC', crv: 'P-256', x: string, y: string, use: 'sig', alg: 'ES256', kid: string}}>} $cached
     */
    private static function isCacheEntry(mixed $cached): bool
    {
        return is_array($cached) && is_int($cached['fetched_at'] ?? null) && is_array($cached['versions'] ?? null);
    }

    /**
     * @param  list<array{name: string, created_at_seconds: int, created_at_nanos: int, jwk: array{kty: 'EC', crv: 'P-256', x: string, y: string, use: 'sig', alg: 'ES256', kid: string}}>  $versions
     * @return list<KmsKeyVersion>
     */
    private static function hydrate(array $versions): array
    {
        return array_map(static fn (array $v): KmsKeyVersion => KmsKeyVersion::fromArray($v), $versions);
    }

    /** PSR-16 §1.2 reserves {}()/\@: and guarantees only 64-character keys, so the whole key is a hex digest. */
    private function cacheKey(): string
    {
        return hash('sha256', self::CACHE_KEY_PREFIX.$this->cryptoKeyName);
    }

    private function now(): DateTimeImmutable
    {
        return $this->clock?->now() ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
