<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Employee\Domain;

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\Enums\EmployeeStatus;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Support\ValueObjects\Identity\NationalCode;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class EmployeeTest extends TestCase
{
    public function test_identity_id_remains_immutable_on_assign_id(): void
    {
        $identityId = IdentityUserId::fromString(Uuid::uuid7()->toString());
        $employee = Employee::createNew(
            identityId: $identityId,
            employeeCode: 'EMP-UNIT',
            firstName: 'Unit',
            lastName: 'Test',
            nationalCode: NationalCode::fromString('0499370899'),
            hireDate: new DateTimeImmutable('2024-01-01'),
        );

        $assigned = $employee->assignId(EmployeeId::fromString(Uuid::uuid7()->toString()));

        $this->assertSame($identityId->value, $assigned->identityId->value);
    }

    public function test_status_transitions(): void
    {
        $employee = $this->makeEmployee();

        $this->assertTrue($employee->isActive());

        $employee->deactivate();

        $this->assertSame(EmployeeStatus::Inactive, $employee->status);

        $employee->activate();

        $this->assertTrue($employee->isActive());
    }

    private function makeEmployee(): Employee
    {
        return Employee::createNew(
            identityId: IdentityUserId::fromString(Uuid::uuid7()->toString()),
            employeeCode: 'EMP-STATUS',
            firstName: 'Status',
            lastName: 'Test',
            nationalCode: NationalCode::fromString('0499370899'),
            hireDate: new DateTimeImmutable('2024-01-01'),
        );
    }
}
