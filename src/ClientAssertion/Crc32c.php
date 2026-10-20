<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion;

/**
 * CRC-32C (Castagnoli), the checksum Cloud KMS uses for its integrity fields.
 */
final class Crc32c
{
    public static function of(string $bytes): int
    {
        return (int) hexdec(hash('crc32c', $bytes));
    }
}
