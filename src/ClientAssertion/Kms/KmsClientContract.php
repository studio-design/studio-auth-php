<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion\Kms;

use Google\ApiCore\ApiException;
use Google\Cloud\Kms\V1\AsymmetricSignRequest;
use Google\Cloud\Kms\V1\AsymmetricSignResponse;
use Google\Cloud\Kms\V1\CryptoKeyVersion;
use Google\Cloud\Kms\V1\PublicKey;

interface KmsClientContract
{
    /**
     * @return iterable<CryptoKeyVersion>
     *
     * @throws ApiException
     */
    public function listCryptoKeyVersions(string $cryptoKeyName): iterable;

    /**
     * @throws ApiException
     */
    public function getPublicKey(string $cryptoKeyVersionName): PublicKey;

    /**
     * @throws ApiException
     */
    public function asymmetricSign(AsymmetricSignRequest $request): AsymmetricSignResponse;
}
