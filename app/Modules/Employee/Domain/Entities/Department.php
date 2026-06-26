<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\Entities;

use App\Modules\Employee\Domain\Enums\DepartmentStatus;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

final class Department
{
    public function __construct(
        public readonly ?DepartmentId $id,
        public string $name,
        public string $code,
        public ?EmployeeId $managerId,
        public ?DepartmentId $parentId,
        public int $lotteryPriority,
        public DepartmentStatus $status,
    ) {}

    public static function createNew(
        string $name,
        string $code,
        ?EmployeeId $managerId = null,
        ?DepartmentId $parentId = null,
        int $lotteryPriority = 0,
    ): self {
        return new self(
            id: null,
            name: $name,
            code: $code,
            managerId: $managerId,
            parentId: $parentId,
            lotteryPriority: $lotteryPriority,
            status: DepartmentStatus::Active,
        );
    }

    public function assignId(DepartmentId $id): self
    {
        return new self(
            id: $id,
            name: $this->name,
            code: $this->code,
            managerId: $this->managerId,
            parentId: $this->parentId,
            lotteryPriority: $this->lotteryPriority,
            status: $this->status,
        );
    }

    public function deactivate(): void
    {
        $this->status = DepartmentStatus::Inactive;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function requireId(): DepartmentId
    {
        if ($this->id === null) {
            throw new \LogicException('Department identifier is not assigned.');
        }

        return $this->id;
    }
}
