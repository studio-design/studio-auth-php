<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion\Kms;

use Closure;
use Google\Cloud\Kms\V1\AsymmetricSignRequest;
use Google\Cloud\Kms\V1\AsymmetricSignResponse;
use Google\Cloud\Kms\V1\Client\KeyManagementServiceClient;
use Google\Cloud\Kms\V1\GetPublicKeyRequest;
use Google\Cloud\Kms\V1\ListCryptoKeyVersionsRequest;
use Google\Cloud\Kms\V1\PublicKey;

/**
 * Wraps the final KeyManagementServiceClient. The caller builds the client and
 * therefore chooses its credentials, e.g.
 * `new KeyManagementServiceClient(['credentials' => new GCECredentials()])`
 * to sign as the Cloud Run runtime service account instead of ADC.
 */
final readonly class KmsClientAdapter implements KmsClientContract
{
    /**
     * @param  null|Closure(): array<string, mixed>  $callOptionsFactory  GAX call options, evaluated before every RPC
     *                                                                    (each list page, public key and signature), e.g. KmsCallBudget::callOptions(...)
     */
    public function __construct(
        private KeyManagementServiceClient $client,
        private ?Closure $callOptionsFactory = null,
    ) {}

    public function listCryptoKeyVersions(string $cryptoKeyName): iterable
    {
        $request = ListCryptoKeyVersionsRequest::build($cryptoKeyName);

        do {
            $page = $this->client->listCryptoKeyVersions($request, $this->options())->getPage();

            foreach ($page->getResponseObject()->getCryptoKeyVersions() as $version) {
                yield $version;
            }

            $next = $page->getNextPageToken();
            $request->setPageToken($next);
        } while ($next !== '');
    }

    public function getPublicKey(string $cryptoKeyVersionName): PublicKey
    {
        return $this->client->getPublicKey(GetPublicKeyRequest::build($cryptoKeyVersionName), $this->options());
    }

    public function asymmetricSign(AsymmetricSignRequest $request): AsymmetricSignResponse
    {
        return $this->client->asymmetricSign($request, $this->options());
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return $this->callOptionsFactory === null ? [] : ($this->callOptionsFactory)();
    }
}
