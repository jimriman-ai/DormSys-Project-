<?php

declare(strict_types=1);

use App\Http\Controllers\EmployeeRecordController;
use App\Http\Controllers\Web\AuthSessionController;
use App\Modules\Audit\Presentation\Providers\AuditPresentationServiceProvider;
use App\Modules\Auth\Presentation\Livewire\EmployeeLogin;
use App\Modules\DormitoryAdmin\DormitoryManagerDashboard;
use App\Modules\DormitoryAdmin\DormitoryUnitManagerDashboard;
use App\Modules\Employee\Presentation\Providers\EmployeePresentationServiceProvider;
use App\Modules\Notification\Presentation\Providers\NotificationPresentationServiceProvider;
use App\Modules\Request\Presentation\Livewire\PersonalRequestFormPage;
use App\Modules\Request\Presentation\Livewire\RequestListPage;
use App\Modules\Request\Presentation\Livewire\RequestShowPage;
use App\Modules\Request\Presentation\Providers\RequestPresentationServiceProvider;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:api')->group(function (): void {
    Route::get('/login', [AuthSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthSessionController::class, 'store']);

    // F2 / W-01 employee-auth-ui — Livewire login (does not replace legacy /login).
    Route::get('/employee/login', EmployeeLogin::class)->name('employee.login');
});

Route::prefix('dormitory-admin')
    ->middleware(['auth:identity'])
    ->name('dormitory-admin.')
    ->group(function (): void {
        Route::middleware(['identity.role:dormitory-manager'])
            ->get('/', DormitoryManagerDashboard::class)
            ->name('manager');

        Route::middleware(['identity.role:dormitory-unit-manager'])
            ->get('/unit', DormitoryUnitManagerDashboard::class)
            ->name('unit-manager');
    });

// transitional — remove in cleanup WP
Route::match(['get', 'post'], '/dormitory/requests/create', static fn () => redirect()->route('requests.create'))
    ->middleware(['auth:identity'])
    ->name('dormitory.requests.create');

// [PERMIT-ID: IMPL-PERMIT-01] §2.2 — Spec04 dual URL boundaries (IMP-Q-01 A).
Route::prefix('employee/requests')
    ->middleware(['auth:identity', 'identity.role:employee'])
    ->name('employee.requests.')
    ->group(RequestPresentationServiceProvider::employeeRequestWebRoutePath());

Route::prefix('approvals/stage1')
    ->middleware(['auth:identity', 'identity.role:'.App\Shared\Auth\IdentityRoleGuard::ROLE_DORMITORY_MANAGER])
    ->name('approvals.stage1.')
    ->group(RequestPresentationServiceProvider::stage1ApprovalWebRoutePath());

// UI-A1 L6-R1 Amend: accept api OR identity principal (dormitory-admin is auth:identity-only).
Route::post('/logout', [AuthSessionController::class, 'destroy'])
    ->middleware('auth:api,identity')
    ->name('logout');

Route::middleware(['auth:api', 'request.mutation.principal', 'audit.principal'])->group(function (): void {
    Route::redirect('/', '/requests')->name('home');

    Route::prefix('requests')->group(function (): void {
        Route::get('/', RequestListPage::class)->name('requests.index');
        Route::get('/create', PersonalRequestFormPage::class)->name('requests.create');
        Route::get('/{requestId}', RequestShowPage::class)
            ->whereUuid('requestId')
            ->name('requests.show');
    });

    Route::prefix('notifications')
        ->group(NotificationPresentationServiceProvider::notificationWebRoutePath());

    Route::prefix('employees')
        ->group(EmployeePresentationServiceProvider::employeeWebRoutePath());

    // D-L6-4-C2: production employee_records surface — auth:api + PEP via FormRequest/controller.
    // (Project uses auth:api Identity UserModel; not web `auth`+`verified`.)
    Route::resource('employee-records', EmployeeRecordController::class)
        ->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::prefix('audit')
        ->group(AuditPresentationServiceProvider::auditWebRoutePath());
});
