<?php

declare(strict_types=1);

namespace App\Infrastructure\Session;

use Illuminate\Session\DatabaseSessionHandler;

/**
 * Pins database session row metadata to the credential (web) guard only.
 *
 * Auth principals for application mutations live in the session payload under
 * guard-specific login keys; sessions.user_id must remain bigint-compatible.
 */
final class CredentialUserDatabaseSessionHandler extends DatabaseSessionHandler
{
    protected function userId()
    {
        if ($this->container === null || ! $this->container->bound('auth')) {
            return null;
        }

        $id = $this->container->make('auth')->guard('web')->id();

        if (is_int($id)) {
            return $id;
        }

        if (is_string($id) && ctype_digit($id)) {
            return (int) $id;
        }

        return null;
    }
}
