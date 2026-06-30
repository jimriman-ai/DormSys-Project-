<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Domain\Enums\ApprovalStage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reads per-stage auto-approval flags from the settings table (R-09 / AP-08).
 */
final class AutoApprovalSettingsReader
{
    public function isEnabled(ApprovalStage $stage): bool
    {
        if (! Schema::hasTable('settings')) {
            return false;
        }

        $value = DB::table('settings')
            ->where('key', $this->keyForStage($stage))
            ->value('value');

        if ($value === null) {
            return false;
        }

        return $this->normalizeBool($value);
    }

    private function normalizeBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return $value !== 0;
        }

        if (! is_string($value)) {
            return false;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $this->normalizeBool($decoded);
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function keyForStage(ApprovalStage $stage): string
    {
        return match ($stage) {
            ApprovalStage::DepartmentManager => 'request.approval.auto.department_manager',
            ApprovalStage::HR => 'request.approval.auto.hr',
            ApprovalStage::DormitoryManager => 'request.approval.auto.dormitory_manager',
            ApprovalStage::DormitoryUnit => 'request.approval.auto.dormitory_unit',
        };
    }
}
