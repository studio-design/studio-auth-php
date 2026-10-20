<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion\Kms;

use Closure;
use Google\ApiCore\ApiException;
use Google\Rpc\Code;
use InvalidArgumentException;
use LogicException;

/**
 * One deadline shared by every Cloud KMS call made inside run(): the metadata
 * server token fetch, each list page, each public key and the signature.
 * GAX retries are disabled, so the deadline also bounds what retries would add.
 */
final class KmsCallBudget
{
    private ?float $deadline = null;

    private readonly Closure $now;

    /**
     * @param  null|Closure(): float  $now  monotonic seconds; defaults to hrtime()
     */
    public function __construct(
        private readonly int $milliseconds,
        ?Closure $now = null,
    ) {
        if ($milliseconds < 1) {
            throw new InvalidArgumentException('KMS budget must be at least 1 millisecond.');
        }

        $this->now = $now ?? static fn (): float => hrtime(true) / 1_000_000_000;
    }

    /**
     * Starts the deadline unless one is already running; a nested run() keeps the outer one.
     *
     * @template T
     *
     * @param  Closure(): T  $operation
     * @return T
     */
    public function run(Closure $operation): mixed
    {
        if ($this->deadline !== null) {
            return $operation();
        }

        $this->deadline = ($this->now)() + $this->milliseconds / 1000;

        try {
            return $operation();
        } finally {
            $this->deadline = null;
        }
    }

    /**
     * @throws ApiException DEADLINE_EXCEEDED once the deadline has passed
     * @throws LogicException outside run()
     */
    public function remainingMillis(): int
    {
        if ($this->deadline === null) {
            throw new LogicException('KMS calls require an active budget; wrap them in run().');
        }

        $left = (int) floor(($this->deadline - ($this->now)()) * 1000);

        if ($left < 1) {
            throw new ApiException('KMS operation deadline exceeded', Code::DEADLINE_EXCEEDED, 'DEADLINE_EXCEEDED');
        }

        return $left;
    }

    /**
     * GAX call options for the next RPC.
     *
     * @return array{retrySettings: array{retriesEnabled: false, noRetriesRpcTimeoutMillis: int}}
     *
     * @throws ApiException
     * @throws LogicException
     */
    public function callOptions(): array
    {
        return ['retrySettings' => [
            'retriesEnabled'            => false,
            'noRetriesRpcTimeoutMillis' => $this->remainingMillis(),
        ]];
    }

    /**
     * Guzzle request options for the next HTTP request, capped by the remaining time.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     *
     * @throws ApiException
     * @throws LogicException
     */
    public function httpOptions(array $options): array
    {
        $left = $this->remainingMillis() / 1000.0;

        foreach (['timeout', 'connect_timeout'] as $key) {
            $existing      = is_numeric($options[$key] ?? null) ? (float) $options[$key] : 0.0;
            $options[$key] = $existing > 0 ? min($existing, $left) : $left;
        }

        $options['delay']           = 0;
        $options['allow_redirects'] = false;

        return $options;
    }
}
