<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class NotificationRetentionSettingsReader
{
    public const string SETTINGS_KEY = 'notification.retention_months';

    public const int DEFAULT_RETENTION_MONTHS = 24;

    public function retentionMonths(): int
    {
        if (! Schema::hasTable('settings')) {
            return self::DEFAULT_RETENTION_MONTHS;
        }

        $value = DB::table('settings')
            ->where('key', self::SETTINGS_KEY)
            ->value('value');

        if ($value === null) {
            return self::DEFAULT_RETENTION_MONTHS;
        }

        return $this->normalizeMonths($value);
    }

    private function normalizeMonths(mixed $value): int
    {
        if (is_int($value)) {
            return max(1, $value);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->normalizeMonths($decoded);
            }

            if (is_numeric($value)) {
                return max(1, (int) $value);
            }
        }

        return self::DEFAULT_RETENTION_MONTHS;
    }
}
