<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\Entities;

use App\Modules\Employee\Domain\Enums\EmployeeStatus;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Support\ValueObjects\Identity\NationalCode;
use DateTimeImmutable;

final class Employee
{
    public function __construct(
        public readonly ?EmployeeId $id,
        public readonly IdentityUserId $identityId,
        public string $employeeCode,
        public string $firstName,
        public string $lastName,
        public NationalCode $nationalCode,
        public ?DepartmentId $departmentId,
        public DateTimeImmutable $hireDate,
        public int $baseLotteryScore,
        public EmployeeStatus $status,
    ) {}

    public static function createNew(
        IdentityUserId $identityId,
        string $employeeCode,
        string $firstName,
        string $lastName,
        NationalCode $nationalCode,
        DateTimeImmutable $hireDate,
    ): self {
        return new self(
            id: null,
            identityId: $identityId,
            employeeCode: $employeeCode,
            firstName: $firstName,
            lastName: $lastName,
            nationalCode: $nationalCode,
            departmentId: null,
            hireDate: $hireDate,
            baseLotteryScore: 0,
            status: EmployeeStatus::Active,
        );
    }

    public function assignId(EmployeeId $id): self
    {
        return new self(
            id: $id,
            identityId: $this->identityId,
            employeeCode: $this->employeeCode,
            firstName: $this->firstName,
            lastName: $this->lastName,
            nationalCode: $this->nationalCode,
            departmentId: $this->departmentId,
            hireDate: $this->hireDate,
            baseLotteryScore: $this->baseLotteryScore,
            status: $this->status,
        );
    }

    public function deactivate(): void
    {
        $this->status = EmployeeStatus::Inactive;
    }

    public function activate(): void
    {
        $this->status = EmployeeStatus::Active;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function requireId(): EmployeeId
    {
        if ($this->id === null) {
            throw new \LogicException('Employee identifier is not assigned.');
        }

        return $this->id;
    }
}
