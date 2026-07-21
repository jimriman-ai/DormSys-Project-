<?php

declare(strict_types=1);

namespace App\Modules\System\Application\Contracts;

/**
 * System-owned read port for the shared `settings` table (D-SETTINGS-CONTRACT Option B / WP-SYS-01).
 * No Eloquent Settings model — Infrastructure uses query builder / DB::table only.
 */
interface SettingsReadContract
{
    /**
     * Returns the stored value for `$key`, or null when the table is absent or the key is missing.
     */
    public function getValue(string $key): mixed;
}
