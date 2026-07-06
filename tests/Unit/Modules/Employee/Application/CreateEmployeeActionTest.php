<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Employee\Application;

use App\Modules\Employee\Domain\Events\EmployeeCreated;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Support\ValueObjects\Identity\NationalCode;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateEmployeeActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_employee_created_after_persist(): void
    {
        Event::fake([EmployeeCreated::class]);

        $user = createIdentityUserThroughMutation('Action Test User', 'action.test@example.com');
        $identityId = IdentityUserId::fromString($user->requireId()->value);

        $employee = createEmployeeThroughMutation(
            identityId: $identityId,
            employeeCode: 'EMP-ACTION',
            firstName: 'Action',
            lastName: 'Test',
            nationalCode: NationalCode::fromString('0499370899'),
            hireDate: new DateTimeImmutable('2024-01-01'),
        );

        $this->assertNotNull($employee->id);

        Event::assertDispatched(EmployeeCreated::class, function (EmployeeCreated $event) use ($employee, $identityId): bool {
            return $event->aggregateId === $employee->requireId()->value
                && ($event->payload['identity_id'] ?? null) === $identityId->value;
        });
    }
}
