<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion\Kms;

final readonly class KmsKeyVersion
{
    /**
     * @param  array{kty: 'EC', crv: 'P-256', x: string, y: string, use: 'sig', alg: 'ES256', kid: string}  $jwk
     */
    public function __construct(
        public string $name,
        public int $createdAtSeconds,
        public int $createdAtNanos,
        public array $jwk,
    ) {}

    public function compareCreationOrder(self $other): int
    {
        $timeOrder = ($this->createdAtSeconds <=> $other->createdAtSeconds)
            ?: ($this->createdAtNanos <=> $other->createdAtNanos);

        if ($timeOrder !== 0) {
            return $timeOrder;
        }

        $number      = basename($this->name);
        $otherNumber = basename($other->name);

        return (strlen($number) <=> strlen($otherNumber)) ?: strcmp($number, $otherNumber);
    }

    public function kid(): string
    {
        return $this->jwk['kid'];
    }

    /**
     * @return array{name: string, created_at_seconds: int, created_at_nanos: int, jwk: array{kty: 'EC', crv: 'P-256', x: string, y: string, use: 'sig', alg: 'ES256', kid: string}}
     */
    public function toArray(): array
    {
        return ['name' => $this->name, 'created_at_seconds' => $this->createdAtSeconds, 'created_at_nanos' => $this->createdAtNanos, 'jwk' => $this->jwk];
    }

    /**
     * @param  array{name: string, created_at_seconds: int, created_at_nanos: int, jwk: array{kty: 'EC', crv: 'P-256', x: string, y: string, use: 'sig', alg: 'ES256', kid: string}}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['name'], $data['created_at_seconds'], $data['created_at_nanos'], $data['jwk']);
    }
}
