<?php

declare(strict_types=1);

namespace App\Application\Mutation\Registry;

use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\CreateAllocationFromRequestAction;
use App\Modules\Allocation\Application\Services\ReleaseAllocationAction;
use App\Modules\Lottery\Application\Services\CancelLotteryProgramAction;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Notification\Application\Services\DeliverNotificationAction;
use App\Modules\Notification\Application\Services\MarkNotificationReadAction;
use App\Modules\Request\Application\Services\CreateFamilyDirectRequestAction;
use App\Modules\Request\Application\Services\CreateLotteryRegistrationRequestAction;
use App\Modules\Request\Application\Services\CreateMissionRequestAction;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Voucher\Application\Services\AcceptTriggerFactsAction;
use App\Modules\Voucher\Application\Services\EvaluateVoucherEligibilityAction;
use App\Modules\Voucher\Application\Services\IssueVoucherAction;
use App\Modules\Voucher\Application\Services\ProcessExternalLotteryWinnerAction;
use App\Modules\Voucher\Application\Services\ProcessReservePromotionAction;
use App\Modules\Voucher\Application\Services\VoucherLifecycleAction;

final class PendingMutationAuthorizationRegistry
{
    /**
     * Business mutation actions grandfathered until domain adoption waves add MPEP.
     *
     * @var list<class-string>
     */
    private const PENDING = [
        AcceptTriggerFactsAction::class,
        CancelLotteryProgramAction::class,
        CloseRegistrationAction::class,
        CreateAllocationAction::class,
        CreateAllocationFromRequestAction::class,
        CreateFamilyDirectRequestAction::class,
        CreateLotteryProgramAction::class,
        CreateLotteryRegistrationRequestAction::class,
        CreateMissionRequestAction::class,
        CreatePersonalRequestAction::class,
        DeliverNotificationAction::class,
        EnrollRegistrationAction::class,
        EvaluateVoucherEligibilityAction::class,
        ExecuteDrawAction::class,
        IssueVoucherAction::class,
        LockLotteryProgramAction::class,
        MarkNotificationReadAction::class,
        OpenRegistrationAction::class,
        ProcessExternalLotteryWinnerAction::class,
        ProcessReservePromotionAction::class,
        ReleaseAllocationAction::class,
        VoucherLifecycleAction::class,
    ];

    public static function isPending(string $className): bool
    {
        return in_array($className, self::PENDING, true);
    }

    /**
     * @return list<class-string>
     */
    public static function all(): array
    {
        return self::PENDING;
    }
}
