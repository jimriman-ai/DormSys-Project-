<?php

declare(strict_types=1);

use App\Modules\Allocation\Infrastructure\Providers\AllocationServiceProvider;
use App\Modules\Audit\Infrastructure\Providers\AuditServiceProvider;
use App\Modules\Dormitory\Infrastructure\Providers\DormitoryServiceProvider;
use App\Modules\Employee\Infrastructure\Providers\EmployeeServiceProvider;
use App\Modules\Employee\Presentation\Providers\EmployeePresentationServiceProvider;
use App\Modules\Identity\Infrastructure\Providers\IdentityServiceProvider;
use App\Modules\Identity\Presentation\Providers\IdentityPresentationServiceProvider;
use App\Modules\Lottery\Infrastructure\Providers\LotteryServiceProvider;
use App\Modules\Notification\Infrastructure\Providers\NotificationServiceProvider;
use App\Modules\Reporting\Infrastructure\Providers\ReportingServiceProvider;
use App\Modules\Request\Infrastructure\Providers\RequestServiceProvider;
use App\Modules\Voucher\Infrastructure\Providers\VoucherServiceProvider;
use App\Modules\Workflow\Infrastructure\Providers\WorkflowServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    IdentityServiceProvider::class,
    IdentityPresentationServiceProvider::class,
    EmployeeServiceProvider::class,
    EmployeePresentationServiceProvider::class,
    RequestServiceProvider::class,
    WorkflowServiceProvider::class,
    DormitoryServiceProvider::class,
    AllocationServiceProvider::class,
    LotteryServiceProvider::class,
    VoucherServiceProvider::class,
    NotificationServiceProvider::class,
    AuditServiceProvider::class,
    ReportingServiceProvider::class,
];
