<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Services;

use App\Modules\System\Application\Contracts\SettingsReadContract;

final class AuditRetentionSettingsReader
{
    public const string SETTINGS_KEY = 'audit.retention_months';

    public const int DEFAULT_RETENTION_MONTHS = 84;

    public function __construct(
        private readonly SettingsReadContract $settings,
    ) {}

    public function retentionMonths(): int
    {
        $value = $this->settings->getValue(self::SETTINGS_KEY);

        if ($value !== null) {
            return $this->normalizeMonths($value);
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
