<?php

declare(strict_types=1);

namespace App\Application\Mutation\Registry;

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
use App\Modules\Workflow\Application\Services\ApplyRequestApprovalAutoApprovalsAction;
use App\Modules\Workflow\Application\Services\DecideRequestApprovalStageAction;
use App\Modules\Workflow\Application\Services\StartRequestApprovalWorkflowAction;

final class PendingMutationAuthorizationRegistry
{
    /**
     * Business mutation actions grandfathered until domain adoption waves add MPEP.
     *
     * Workflow Start/Decide/AutoApprovals are nested orchestration under Request
     * Submit/Approve/Reject paths that already enforce MPEP at the entry action.
     *
     * @var list<class-string>
     */
    private const PENDING = [
        AcceptTriggerFactsAction::class,
        ApplyRequestApprovalAutoApprovalsAction::class,
        CreateFamilyDirectRequestAction::class,
        CreateLotteryRegistrationRequestAction::class,
        CreateMissionRequestAction::class,
        CreatePersonalRequestAction::class,
        DecideRequestApprovalStageAction::class,
        DeliverNotificationAction::class,
        EvaluateVoucherEligibilityAction::class,
        IssueVoucherAction::class,
        MarkNotificationReadAction::class,
        ProcessExternalLotteryWinnerAction::class,
        ProcessReservePromotionAction::class,
        StartRequestApprovalWorkflowAction::class,
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
