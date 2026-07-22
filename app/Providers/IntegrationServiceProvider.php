<?php

declare(strict_types=1);

namespace App\Providers;

use App\Integrations\Allocation\ApprovedRequestReadBridge;
use App\Integrations\Allocation\DormitoryAssignabilityReadBridge;
use App\Integrations\Allocation\PhysicalStateSignalBridge;
use App\Integrations\Allocation\RequestLifecycleCommandBridge;
use App\Integrations\Audit\SpatieAuditPermissionReadBridge;
use App\Integrations\CheckIn\AllocationAssignmentReadBridge;
use App\Integrations\CheckIn\RequestStayLifecycleCommandBridge;
use App\Integrations\Notification\RequestApprovalNotificationDelivery;
use App\Integrations\Notification\RequestApprovalNotificationSubscriber;
use App\Integrations\Reporting\AuditHistorySourceReadBridge;
use App\Integrations\Reporting\ReportingArchiveVisibilityBridge;
use App\Integrations\Request\DormitoryReadBridge;
use App\Integrations\Request\EmployeeEligibilityBridge;
use App\Integrations\Request\PendingRequestReadBridge;
use App\Integrations\Request\Stage1ApproverIdentityReadBridge;
use App\Integrations\Workflow\RequestApprovalAutoSettingsBridge;
use App\Integrations\Workflow\RequestApprovalCommandBridge;
use App\Integrations\Workflow\StageRoleAuthorizationBridge;
use App\Modules\Allocation\Application\Contracts\Ports\ApprovedRequestReadPort;
use App\Modules\Allocation\Application\Contracts\Ports\DormitoryReadPort;
use App\Modules\Allocation\Application\Contracts\Ports\PhysicalStateSignalPort;
use App\Modules\Allocation\Application\Contracts\RequestLifecycleCommandPort;
use App\Modules\Allocation\Application\Services\ProposedAllocationConsumer;
use App\Modules\Audit\Application\Contracts\AuditPermissionReadPort;
use App\Modules\CheckIn\Application\Contracts\AllocationAssignmentReadPort;
use App\Modules\CheckIn\Application\Contracts\RequestStayLifecycleCommandPort;
use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;
use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort;
use App\Modules\Reporting\Application\Contracts\Ports\AuditHistorySourceReadPort;
use App\Modules\Reporting\Application\Contracts\Ports\ReportingArchiveVisibilityPort;
use App\Modules\Request\Application\Contracts\DormitoryReadContract;
use App\Modules\Request\Application\Contracts\Internal\RequestEligibilityGatewayContract;
use App\Modules\Request\Application\Contracts\Stage1ApproverIdentityReadContract;
use App\Modules\Workflow\Application\Contracts\RequestApprovalAutoSettingsPort;
use App\Modules\Workflow\Application\Contracts\RequestApprovalCommandPort;
use App\Modules\Workflow\Application\Contracts\StageRoleAuthorizationPort;
use Illuminate\Support\ServiceProvider;

final class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApprovedRequestReadPort::class, ApprovedRequestReadBridge::class);
        $this->app->singleton(DormitoryReadPort::class, DormitoryAssignabilityReadBridge::class);
        $this->app->singleton(PhysicalStateSignalPort::class, PhysicalStateSignalBridge::class);
        $this->app->singleton(RequestLifecycleCommandPort::class, RequestLifecycleCommandBridge::class);
        $this->app->singleton(AllocationAssignmentReadPort::class, AllocationAssignmentReadBridge::class);
        $this->app->singleton(RequestStayLifecycleCommandPort::class, RequestStayLifecycleCommandBridge::class);
        $this->app->singleton(RequestEligibilityGatewayContract::class, EmployeeEligibilityBridge::class);
        $this->app->singleton(PendingRequestReadPort::class, PendingRequestReadBridge::class);
        $this->app->singleton(ProposedAllocationPort::class, ProposedAllocationConsumer::class);
        $this->app->singleton(DormitoryReadContract::class, DormitoryReadBridge::class);
        $this->app->singleton(Stage1ApproverIdentityReadContract::class, Stage1ApproverIdentityReadBridge::class);
        $this->app->singleton(RequestApprovalCommandPort::class, RequestApprovalCommandBridge::class);
        $this->app->singleton(RequestApprovalAutoSettingsPort::class, RequestApprovalAutoSettingsBridge::class);
        $this->app->singleton(StageRoleAuthorizationPort::class, StageRoleAuthorizationBridge::class);
        $this->app->singleton(AuditPermissionReadPort::class, SpatieAuditPermissionReadBridge::class);
        $this->app->singleton(AuditHistorySourceReadPort::class, AuditHistorySourceReadBridge::class);
        $this->app->singleton(ReportingArchiveVisibilityPort::class, ReportingArchiveVisibilityBridge::class);
        $this->app->singleton(RequestApprovalNotificationDelivery::class);
        $this->app->singleton(RequestApprovalNotificationSubscriber::class);
    }
}
