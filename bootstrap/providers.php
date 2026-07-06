<?php

declare(strict_types=1);

use App\Modules\Allocation\Infrastructure\Providers\AllocationServiceProvider;
use App\Modules\Allocation\Presentation\Providers\AllocationPresentationServiceProvider;
use App\Modules\Audit\Infrastructure\Providers\AuditServiceProvider;
use App\Modules\CheckIn\Infrastructure\Providers\CheckInServiceProvider;
use App\Modules\Dormitory\Infrastructure\Providers\DormitoryServiceProvider;
use App\Modules\Employee\Infrastructure\Providers\EmployeeServiceProvider;
use App\Modules\Employee\Presentation\Providers\EmployeePresentationServiceProvider;
use App\Modules\Identity\Infrastructure\Providers\IdentityServiceProvider;
use App\Modules\Identity\Presentation\Providers\IdentityPresentationServiceProvider;
use App\Modules\Lottery\Infrastructure\Providers\LotteryServiceProvider;
use App\Modules\Lottery\Presentation\Providers\LotteryPresentationServiceProvider;
use App\Modules\Notification\Infrastructure\Providers\NotificationServiceProvider;
use App\Modules\Reporting\Infrastructure\Providers\ReportingServiceProvider;
use App\Modules\Reporting\Presentation\Providers\ReportingPresentationServiceProvider;
use App\Modules\Request\Infrastructure\Providers\RequestServiceProvider;
use App\Modules\Request\Presentation\Providers\RequestPresentationServiceProvider;
use App\Modules\Voucher\Infrastructure\Providers\VoucherServiceProvider;
use App\Modules\Workflow\Infrastructure\Providers\WorkflowServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AuthFoundationServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\IntegrationServiceProvider;
use App\Providers\MutationAuthorizationServiceProvider;
use Laravel\Horizon\HorizonApplicationServiceProvider;

$providers = [
    AppServiceProvider::class,
    AuthFoundationServiceProvider::class,
    MutationAuthorizationServiceProvider::class,
    IdentityServiceProvider::class,
    IdentityPresentationServiceProvider::class,
    EmployeeServiceProvider::class,
    EmployeePresentationServiceProvider::class,
    RequestServiceProvider::class,
    RequestPresentationServiceProvider::class,
    WorkflowServiceProvider::class,
    DormitoryServiceProvider::class,
    AllocationServiceProvider::class,
    AllocationPresentationServiceProvider::class,
    CheckInServiceProvider::class,
    LotteryServiceProvider::class,
    LotteryPresentationServiceProvider::class,
    VoucherServiceProvider::class,
    NotificationServiceProvider::class,
    AuditServiceProvider::class,
    ReportingServiceProvider::class,
    ReportingPresentationServiceProvider::class,
    IntegrationServiceProvider::class,
];

if (class_exists(HorizonApplicationServiceProvider::class)) {
    $providers[] = HorizonServiceProvider::class;
}

return $providers;
