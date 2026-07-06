<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\CheckIn\Application\Contracts\CheckInCommandPort;
use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Domain\CheckInOperationRoles;
use App\Modules\CheckIn\Domain\Events\CheckedIn;
use App\Modules\CheckIn\Domain\Events\CheckedOut;
use App\Modules\CheckIn\Infrastructure\Persistence\Models\CheckInRecordModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;

function createCheckInOperator(): string
{
    Role::findOrCreate(CheckInOperationRoles::OPERATOR, config('auth.defaults.guard', 'web'));

    $user = createIdentityUserThroughMutation(
        'Check-In Operator',
        'operator-'.uniqid('', true).'@example.com',
    );

    assignRoleThroughMutation(
        $user->requireId(),
        CheckInOperationRoles::OPERATOR,
    );

    return $user->requireId()->value;
}

it('checks in and out on an active allocation', function (): void {
    Event::fake([CheckedIn::class, CheckedOut::class]);

    $allocation = app(CreateAllocationAction::class)->execute(
        personId: UuidGenerator::uuid7(),
        bedId: UuidGenerator::uuid7(),
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    );

    $operatorId = createCheckInOperator();
    $allocationId = $allocation->requireId()->value;

    asCheckInOperator($operatorId, fn () => app(CheckInCommandPort::class)->checkIn($allocationId, $operatorId));

    $openRecord = app(CheckInRecordRepositoryContract::class)->findOpenByAllocationId($allocationId);

    expect($openRecord)->not->toBeNull();
    expect($openRecord?->operatorId)->toBe($operatorId);
    expect($openRecord?->isCheckedOut())->toBeFalse();

    Event::assertDispatched(CheckedIn::class);

    asCheckInOperator($operatorId, fn () => app(CheckInCommandPort::class)->checkOut($allocationId, $operatorId));

    $closedRecord = CheckInRecordModel::query()
        ->where('allocation_id', $allocationId)
        ->first();

    expect($closedRecord)->not->toBeNull();
    expect($closedRecord?->checked_out_at)->not->toBeNull();
    expect(app(CheckInRecordRepositoryContract::class)->findOpenByAllocationId($allocationId))->toBeNull();

    Event::assertDispatched(CheckedOut::class);
});
