<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\Enums;

/**
 * Request Approval Workflow stages (v1). Values align with Request ApprovalStage strings.
 * OD-1: Request Approval Workflow only — not a generic stage vocabulary.
 */
enum RequestApprovalWorkflowStage: string
{
    case DepartmentManager = 'department_manager';
    case HR = 'hr';
    case DormitoryManager = 'dormitory_manager';
    case DormitoryUnit = 'dormitory_unit';

    /**
     * @return list<self>
     */
    public static function chainOrder(): array
    {
        return [
            self::DepartmentManager,
            self::HR,
            self::DormitoryManager,
            self::DormitoryUnit,
        ];
    }

    public function isFirst(): bool
    {
        return $this === self::DepartmentManager;
    }

    public function isLast(): bool
    {
        return $this === self::DormitoryUnit;
    }

    public function next(): ?self
    {
        $order = self::chainOrder();
        $index = array_search($this, $order, true);

        if ($index === false) {
            return null;
        }

        return $order[$index + 1] ?? null;
    }
}
