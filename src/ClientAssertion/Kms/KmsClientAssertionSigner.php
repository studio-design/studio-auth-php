<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion\Kms;

use Google\ApiCore\ApiException;
use Google\Cloud\Kms\V1\AsymmetricSignRequest;
use Google\Cloud\Kms\V1\Digest;
use Google\Protobuf\Int64Value;
use Studio\Auth\ClientAssertion\ClientAssertionException;
use Studio\Auth\ClientAssertion\ClientAssertionSignerContract;
use Studio\Auth\ClientAssertion\ClientAssertionSigningKey;
use Studio\Auth\ClientAssertion\Crc32c;
use StudioDesign\EcdsaSignature\Curve;
use StudioDesign\EcdsaSignature\EcdsaSignature;

/**
 * Signs client assertions with Cloud KMS AsymmetricSign, verifying the
 * request and response CRC32C fields KMS returns for in-transit integrity.
 */
final readonly class KmsClientAssertionSigner implements ClientAssertionSignerContract
{
    public function __construct(
        private KmsClientContract $kms,
        private KmsKeyVersionInventory $inventory,
    ) {}

    /**
     * @throws ApiException
     * @throws ClientAssertionException
     */
    public function selectKey(): ClientAssertionSigningKey
    {
        $version = $this->inventory->selectSigningVersion();

        return new ClientAssertionSigningKey(
            $version->kid(),
            fn (string $signingInput): string => $this->signWith($version, $signingInput),
        );
    }

    /**
     * @throws ApiException
     * @throws ClientAssertionException
     */
    private function signWith(KmsKeyVersion $version, string $signingInput): string
    {
        $digest = hash('sha256', $signingInput, true);

        $request = (new AsymmetricSignRequest)
            ->setName($version->name)
            ->setDigest((new Digest)->setSha256($digest))
            ->setDigestCrc32C(new Int64Value(['value' => Crc32c::of($digest)]));

        $response = $this->kms->asymmetricSign($request);

        if (! $response->getVerifiedDigestCrc32C()) {
            throw ClientAssertionException::kmsIntegrityCheckFailed('verified_digest_crc32c');
        }

        if ($response->getName() !== $version->name) {
            throw ClientAssertionException::kmsIntegrityCheckFailed('name');
        }

        $signature = $response->getSignature();

        if ($signature === '' || Crc32c::of($signature) !== (int) $response->getSignatureCrc32CUnwrapped()) {
            throw ClientAssertionException::kmsIntegrityCheckFailed('signature_crc32c');
        }

        return EcdsaSignature::fromDer($signature, Curve::fromJoseAlg('ES256'))->toRaw();
    }
}
