<?php

declare(strict_types=1);

namespace Studio\Auth;

/**
 * The page where users manage their own password and two-factor authentication. Link to it; it takes no parameters,
 * and the Auth Server re-authenticates the user before showing it.
 */
final class SecuritySettings
{
    /**
     * @param  string  $host  the same value passed to Configuration::setHost()
     */
    public static function url(string $host): string
    {
        return rtrim($host, '/').'/account/security';
    }
}
