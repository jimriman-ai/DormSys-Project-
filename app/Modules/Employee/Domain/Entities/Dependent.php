<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\Entities;

use App\Modules\Employee\Domain\Enums\DependentRelationship;
use App\Modules\Employee\Domain\ValueObjects\DependentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Support\ValueObjects\Identity\NationalCode;

final class Dependent
{
    public function __construct(
        public readonly ?DependentId $id,
        public readonly EmployeeId $employeeId,
        public string $firstName,
        public string $lastName,
        public DependentRelationship $relationship,
        public ?int $age,
        public ?NationalCode $nationalCode,
    ) {}

    public static function createNew(
        EmployeeId $employeeId,
        string $firstName,
        string $lastName,
        DependentRelationship $relationship,
        ?int $age = null,
        ?NationalCode $nationalCode = null,
    ): self {
        return new self(
            id: null,
            employeeId: $employeeId,
            firstName: $firstName,
            lastName: $lastName,
            relationship: $relationship,
            age: $age,
            nationalCode: $nationalCode,
        );
    }

    public function assignId(DependentId $id): self
    {
        return new self(
            id: $id,
            employeeId: $this->employeeId,
            firstName: $this->firstName,
            lastName: $this->lastName,
            relationship: $this->relationship,
            age: $this->age,
            nationalCode: $this->nationalCode,
        );
    }

    public function updateDetails(
        string $firstName,
        string $lastName,
        DependentRelationship $relationship,
        ?int $age = null,
        ?NationalCode $nationalCode = null,
    ): void {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->relationship = $relationship;
        $this->age = $age;
        $this->nationalCode = $nationalCode;
    }

    public function belongsTo(EmployeeId $employeeId): bool
    {
        return $this->employeeId->value === $employeeId->value;
    }

    public function requireId(): DependentId
    {
        if ($this->id === null) {
            throw new \LogicException('Dependent identifier is not assigned.');
        }

        return $this->id;
    }
}
