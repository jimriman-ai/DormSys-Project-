<?php

declare(strict_types=1);

namespace App\Modules\Employee\Presentation\Livewire;

use App\Modules\Employee\Application\Services\AssignDepartmentToEmployeeAction;
use App\Modules\Employee\Application\Services\CreateDepartmentAction;
use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Application\Services\DeactivateDepartmentAction;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Support\Presentation\Concerns\HandlesUiMutationFeedback;
use App\Support\ValueObjects\Identity\NationalCode;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
final class EmployeeHubPage extends Component
{
    use HandlesUiMutationFeedback;

    public string $identityId = '';

    public string $employeeCode = '';

    public string $firstName = '';

    public string $lastName = '';

    public string $nationalCode = '';

    public string $hireDate = '';

    public string $departmentName = '';

    public string $departmentCode = '';

    public string $managerId = '';

    public string $parentId = '';

    public string $lotteryPriority = '0';

    public string $assignEmployeeId = '';

    public string $assignDepartmentId = '';

    public string $deactivateDepartmentId = '';

    public ?string $returnedId = null;

    public ?string $successMessage = null;

    public bool $submitting = false;

    public function createEmployee(CreateEmployeeAction $createEmployee): void
    {
        $this->resetActionFeedback();
        $this->successMessage = null;
        $this->returnedId = null;
        $this->submitting = true;

        $validated = $this->validate([
            'identityId' => ['required', 'uuid'],
            'employeeCode' => ['required', 'string', 'max:100'],
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'nationalCode' => ['required', 'string', 'regex:/^\d{10}$/'],
            'hireDate' => ['required', 'date_format:Y-m-d'],
        ]);

        try {
            $employee = $createEmployee->execute(
                identityId: IdentityUserId::fromString($validated['identityId']),
                employeeCode: $validated['employeeCode'],
                firstName: $validated['firstName'],
                lastName: $validated['lastName'],
                nationalCode: NationalCode::fromString($validated['nationalCode']),
                hireDate: new DateTimeImmutable($validated['hireDate'], new DateTimeZone('UTC')),
            );

            $id = $employee->requireId()->value;
            $this->returnedId = $id;
            $this->successMessage = 'کارمند با موفقیت ایجاد شد.';
            $this->flashSuccess($this->successMessage.' شناسه: '.$id);

            $this->reset([
                'identityId',
                'employeeCode',
                'firstName',
                'lastName',
                'nationalCode',
                'hireDate',
            ]);
        } catch (Throwable $exception) {
            $this->captureMutationFailure($exception);
        } finally {
            $this->submitting = false;
        }
    }

    public function createDepartment(CreateDepartmentAction $createDepartment): void
    {
        $this->resetActionFeedback();
        $this->successMessage = null;
        $this->returnedId = null;
        $this->submitting = true;

        $this->managerId = trim($this->managerId);
        $this->parentId = trim($this->parentId);
        $this->lotteryPriority = trim($this->lotteryPriority) === '' ? '0' : trim($this->lotteryPriority);

        $validated = $this->validate([
            'departmentName' => ['required', 'string', 'max:200'],
            'departmentCode' => ['required', 'string', 'max:100'],
            'managerId' => ['nullable', 'uuid'],
            'parentId' => ['nullable', 'uuid'],
            'lotteryPriority' => ['required', 'integer'],
        ]);

        try {
            $department = $createDepartment->execute(
                name: $validated['departmentName'],
                code: $validated['departmentCode'],
                managerId: filled($validated['managerId'] ?? null)
                    ? EmployeeId::fromString((string) $validated['managerId'])
                    : null,
                parentId: filled($validated['parentId'] ?? null)
                    ? DepartmentId::fromString((string) $validated['parentId'])
                    : null,
                lotteryPriority: (int) $validated['lotteryPriority'],
            );

            $id = $department->requireId()->value;
            $this->returnedId = $id;
            $this->successMessage = 'دپارتمان با موفقیت ایجاد شد.';
            $this->flashSuccess($this->successMessage.' شناسه: '.$id);

            $this->reset([
                'departmentName',
                'departmentCode',
                'managerId',
                'parentId',
            ]);
            $this->lotteryPriority = '0';
        } catch (Throwable $exception) {
            $this->captureMutationFailure($exception);
        } finally {
            $this->submitting = false;
        }
    }

    public function assignDepartment(AssignDepartmentToEmployeeAction $assignDepartment): void
    {
        $this->resetActionFeedback();
        $this->successMessage = null;
        $this->returnedId = null;
        $this->submitting = true;

        $validated = $this->validate([
            'assignEmployeeId' => ['required', 'uuid'],
            'assignDepartmentId' => ['required', 'uuid'],
        ]);

        try {
            $employee = $assignDepartment->execute(
                employeeId: EmployeeId::fromString($validated['assignEmployeeId']),
                departmentId: DepartmentId::fromString($validated['assignDepartmentId']),
            );

            $id = $employee->requireId()->value;
            $this->returnedId = $id;
            $this->successMessage = 'دپارتمان به کارمند تخصیص داده شد.';
            $this->flashSuccess($this->successMessage.' شناسه کارمند: '.$id);

            $this->reset([
                'assignEmployeeId',
                'assignDepartmentId',
            ]);
        } catch (Throwable $exception) {
            $this->captureMutationFailure($exception);
        } finally {
            $this->submitting = false;
        }
    }

    public function deactivateDepartment(DeactivateDepartmentAction $deactivateDepartment): void
    {
        $this->resetActionFeedback();
        $this->successMessage = null;
        $this->returnedId = null;
        $this->submitting = true;

        $validated = $this->validate([
            'deactivateDepartmentId' => ['required', 'uuid'],
        ]);

        try {
            $department = $deactivateDepartment->execute(
                DepartmentId::fromString($validated['deactivateDepartmentId']),
            );

            $id = $department->requireId()->value;
            $this->returnedId = $id;
            $this->successMessage = 'دپارتمان غیرفعال شد.';
            $this->flashSuccess($this->successMessage.' شناسه: '.$id);

            $this->reset(['deactivateDepartmentId']);
        } catch (Throwable $exception) {
            $this->captureMutationFailure($exception);
        } finally {
            $this->submitting = false;
        }
    }

    public function render(): View
    {
        return view('livewire.employee.employee-hub-page');
    }
}
