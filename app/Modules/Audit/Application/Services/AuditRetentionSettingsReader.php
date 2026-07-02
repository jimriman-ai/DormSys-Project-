<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AuditRetentionSettingsReader
{
    public const string SETTINGS_KEY = 'audit.retention_months';

    public const int DEFAULT_RETENTION_MONTHS = 84;

    public function retentionMonths(): int
    {
        if (Schema::hasTable('settings')) {
            $value = DB::table('settings')
                ->where('key', self::SETTINGS_KEY)
                ->value('value');

            if ($value !== null) {
                return $this->normalizeMonths($value);
            }
        }

        return $this->normalizeMonths(config('audit.retention_months', self::DEFAULT_RETENTION_MONTHS));
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
