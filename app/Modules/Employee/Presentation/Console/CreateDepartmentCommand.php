<?php

declare(strict_types=1);

namespace App\Modules\Employee\Presentation\Console;

use App\Modules\Employee\Application\Services\CreateDepartmentAction;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use Illuminate\Console\Command;

class CreateDepartmentCommand extends Command
{
    protected $signature = 'department:create
        {name : Department display name}
        {code : Unique department code}
        {--manager-id= : Optional manager employee UUID}
        {--parent-id= : Optional parent department UUID}
        {--lottery-priority=0 : Lottery priority weight}';

    protected $description = 'Create a department in the Employee organizational catalog.';

    public function handle(CreateDepartmentAction $action): int
    {
        $managerId = $this->option('manager-id');
        $parentId = $this->option('parent-id');

        $department = $action->execute(
            name: (string) $this->argument('name'),
            code: (string) $this->argument('code'),
            managerId: is_string($managerId) && $managerId !== ''
                ? EmployeeId::fromString($managerId)
                : null,
            parentId: is_string($parentId) && $parentId !== ''
                ? DepartmentId::fromString($parentId)
                : null,
            lotteryPriority: (int) $this->option('lottery-priority'),
        );

        $this->info('Department created: '.$department->requireId()->value);

        return self::SUCCESS;
    }
}
