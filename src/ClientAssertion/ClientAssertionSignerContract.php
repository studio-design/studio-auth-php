<?php

declare(strict_types=1);

namespace Studio\Auth\ClientAssertion;

interface ClientAssertionSignerContract
{
    /**
     * Picks the key for one assertion. The builder takes both the header kid and
     * the signature from the returned key, so a rotation between two builds never
     * mixes one key's kid with another key's signature.
     *
     * @throws ClientAssertionException
     */
    public function selectKey(): ClientAssertionSigningKey;
}
