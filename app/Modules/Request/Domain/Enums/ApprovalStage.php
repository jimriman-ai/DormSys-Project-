<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Enums;

enum ApprovalStage: string
{
    case DepartmentManager = 'department_manager';
    case HR = 'hr';
    case DormitoryManager = 'dormitory_manager';
    case DormitoryUnit = 'dormitory_unit';
}
