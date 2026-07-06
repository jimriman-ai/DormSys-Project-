<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Console;

use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Console\Command;

/**
 * CLI exposure for a pending (not yet MPEP-adopted) request create mutation.
 * This command is not a governed adopted mutation surface.
 */
class CreatePersonalRequestCommand extends Command
{
    protected $signature = 'request:create-personal
        {employee_id : Submitting employee UUID}
        {dormitory_id : Target dormitory site UUID}
        {--check-in= : Check-in date (Y-m-d)}
        {--check-out= : Check-out date (Y-m-d)}';

    protected $description = 'Create a draft Personal accommodation request.';

    public function handle(CreatePersonalRequestAction $action): int
    {
        $request = $action->execute(
            employeeId: EmployeeReferenceId::fromString((string) $this->argument('employee_id')),
            dormitoryId: DormitorySiteId::fromString((string) $this->argument('dormitory_id')),
            checkInDate: new DateTimeImmutable((string) $this->option('check-in'), new DateTimeZone('UTC')),
            checkOutDate: new DateTimeImmutable((string) $this->option('check-out'), new DateTimeZone('UTC')),
        );

        $this->info('Request created: '.$request->requireId()->value);
        $this->line('Code: '.(string) $request->code);

        return self::SUCCESS;
    }
}
