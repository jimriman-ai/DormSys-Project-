<?php

declare(strict_types=1);

namespace App\Modules\Employee\Presentation\Console;

use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Support\ValueObjects\Identity\NationalCode;
use DateTimeImmutable;
use Illuminate\Console\Command;

class CreateEmployeeCommand extends Command
{
    protected $signature = 'employee:create
        {identity_id : Identity user UUID (CD-012 reference, no FK)}
        {--code= : Employee code}
        {--first-name= : First name}
        {--last-name= : Last name}
        {--national-code= : Iranian national code}
        {--hire-date= : Hire date (Y-m-d)}';

    protected $description = 'Create an Employee profile linked to an Identity user (requires existing identity:user-create).';

    public function handle(CreateEmployeeAction $action): int
    {
        $employee = $action->execute(
            identityId: UserId::fromString((string) $this->argument('identity_id')),
            employeeCode: (string) $this->option('code'),
            firstName: (string) $this->option('first-name'),
            lastName: (string) $this->option('last-name'),
            nationalCode: NationalCode::fromString((string) $this->option('national-code')),
            hireDate: new DateTimeImmutable((string) $this->option('hire-date')),
        );

        $this->info('Employee created: '.$employee->requireId()->value);

        return self::SUCCESS;
    }
}
