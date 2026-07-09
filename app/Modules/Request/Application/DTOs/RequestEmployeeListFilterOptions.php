<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\DTOs;

use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryUnitState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Domain\States\SubmittedState;

final class RequestEmployeeListFilterOptions
{
    /** @var list<string> */
    public const SORT_FIELDS = [
        'submitted_at',
        'code',
        'status',
        'check_in_date',
        'check_out_date',
    ];

    /**
     * @return list<string>
     */
    public static function statusValues(): array
    {
        return [
            DraftState::$name,
            SubmittedState::$name,
            PendingDepartmentManagerState::$name,
            PendingHRState::$name,
            PendingDormitoryManagerState::$name,
            PendingDormitoryUnitState::$name,
            ApprovedState::$name,
            RejectedState::$name,
            CancelledState::$name,
        ];
    }
}
