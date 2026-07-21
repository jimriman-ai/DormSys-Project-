<?php

declare(strict_types=1);

namespace App\Application\Mutation\Registry;

use App\Application\Auth\EstablishApiSessionFromCredentialLoginAction;
use App\Application\Auth\GetCurrentAuthUserAction;
use App\Application\Auth\LoginUserAction;
use App\Application\Auth\LogoutUserAction;
use App\Modules\Audit\Application\Services\QueryAuditHistoryAction;
use App\Modules\Audit\Application\Services\RecordAuditAction;
use App\Modules\CheckIn\Application\Services\GetOpenCheckInByAllocationAction;
use App\Modules\Dormitory\Application\Services\GetEmployeeAssignedDormitoryAction;
use App\Modules\Dormitory\Application\Services\ListEmployeeAssignedDormitoriesAction;
use App\Modules\Reporting\Application\Services\QueryActorAuditTimelineAction;
use App\Modules\Reporting\Application\Services\QueryAuditWindowSummaryAction;
use App\Modules\Reporting\Application\Services\QueryComplianceExportAction;
use App\Modules\Reporting\Application\Services\QueryCorrelationBundleAction;
use App\Modules\Reporting\Application\Services\QueryEntityAuditTimelineAction;
use App\Modules\Reporting\Application\Services\QuerySecurityActorActivityAction;
use App\Modules\Request\Application\Services\ApproveStage1RequestAction;
use App\Modules\Request\Application\Services\AssignStage1ApproverSnapshotAction;
use App\Modules\Request\Application\Services\ListPendingStage1RequestsAction;
use App\Modules\Request\Application\Services\RejectStage1RequestAction;

final class ExemptMutationActionRegistry
{
    /**
     * Trusted internal writes and read-only query actions exempt from MPEP.
     *
     * @var list<class-string>
     */
    private const EXEMPT = [
        ApproveStage1RequestAction::class,
        AssignStage1ApproverSnapshotAction::class,
        ListPendingStage1RequestsAction::class,
        RejectStage1RequestAction::class,
        GetOpenCheckInByAllocationAction::class,
        ListEmployeeAssignedDormitoriesAction::class,
        GetEmployeeAssignedDormitoryAction::class,
        RecordAuditAction::class,
        QueryAuditHistoryAction::class,
        QueryEntityAuditTimelineAction::class,
        QueryActorAuditTimelineAction::class,
        QueryCorrelationBundleAction::class,
        QueryAuditWindowSummaryAction::class,
        QuerySecurityActorActivityAction::class,
        QueryComplianceExportAction::class,
        LoginUserAction::class,
        LogoutUserAction::class,
        GetCurrentAuthUserAction::class,
        EstablishApiSessionFromCredentialLoginAction::class,
    ];

    public static function isExempt(string $className): bool
    {
        return in_array($className, self::EXEMPT, true);
    }

    /**
     * @return list<class-string>
     */
    public static function all(): array
    {
        return self::EXEMPT;
    }
}
