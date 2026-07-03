<?php

declare(strict_types=1);

use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Domain\Enums\DeliveryPriority;
use App\Modules\Notification\Domain\Enums\DeliveryResultStatus;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Infrastructure\Adapters\InMemoryEmployeeExistenceReadAdapter;
use App\Modules\Notification\Infrastructure\Jobs\SendNotificationJob;
use App\Modules\Notification\Infrastructure\Persistence\Models\NotificationLogModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function registerActiveEmployees(string ...$employeeIds): void
{
    app()->instance(
        EmployeeExistenceReadPort::class,
        new InMemoryEmployeeExistenceReadAdapter($employeeIds),
    );
}

/**
 * @param  array<string, mixed>  $overrides
 */
function notificationIntentPayload(string $employeeId, array $overrides = []): array
{
    return array_merge([
        'correlationId' => 'request:'.UuidGenerator::uuid7().':approved',
        'notificationType' => NotificationType::RequestApproved->value,
        'recipientEmployeeId' => $employeeId,
        'title' => 'درخواست خوابگاه تأیید شد',
        'message' => 'درخواست شما تأیید شد.',
        'sourceContext' => 'request',
        'priority' => DeliveryPriority::Standard->value,
        'occurredAt' => '2026-07-02T10:30:00Z',
    ], $overrides);
}

it('delivers a request approved notification to the recipient inbox', function (): void {
    $employeeId = UuidGenerator::uuid7();
    registerActiveEmployees($employeeId);

    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray(notificationIntentPayload($employeeId, [
            'correlationId' => 'request:approved:001',
            'notificationType' => NotificationType::RequestApproved->value,
        ])),
    );

    expect($result->status)->toBe(DeliveryResultStatus::Delivered);
    expect($result->notificationId)->not->toBeNull();

    $model = NotificationLogModel::query()->findOrFail($result->notificationId);
    expect($model->recipient_employee_id)->toBe($employeeId);
    expect($model->notification_type)->toBe(NotificationType::RequestApproved);
    expect($model->read_at)->toBeNull();
});

it('delivers allocation successful notifications', function (): void {
    $employeeId = UuidGenerator::uuid7();
    registerActiveEmployees($employeeId);

    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray(notificationIntentPayload($employeeId, [
            'correlationId' => 'allocation:success:001',
            'notificationType' => NotificationType::AllocationSuccessful->value,
            'sourceContext' => 'allocation',
        ])),
    );

    expect($result->status)->toBe(DeliveryResultStatus::Delivered);
});

it('delivers lottery winner notifications', function (): void {
    $employeeId = UuidGenerator::uuid7();
    registerActiveEmployees($employeeId);

    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray(notificationIntentPayload($employeeId, [
            'correlationId' => 'lottery:winner:001',
            'notificationType' => NotificationType::LotteryWinner->value,
            'sourceContext' => 'lottery',
        ])),
    );

    expect($result->status)->toBe(DeliveryResultStatus::Delivered);
});

it('routes reserve promoted intents to the urgent queue', function (): void {
    Queue::fake();

    $employeeId = UuidGenerator::uuid7();
    $payload = notificationIntentPayload($employeeId, [
        'correlationId' => 'reserve:promoted:001',
        'notificationType' => NotificationType::ReservePromoted->value,
        'sourceContext' => 'lottery',
        'priority' => DeliveryPriority::Urgent->value,
    ]);

    SendNotificationJob::dispatch($payload);

    Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job): bool {
        return $job->queue === 'notifications-urgent';
    });
});

it('delivers reserve promoted notifications with urgent priority', function (): void {
    $employeeId = UuidGenerator::uuid7();
    registerActiveEmployees($employeeId);

    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray(notificationIntentPayload($employeeId, [
            'correlationId' => 'reserve:promoted:002',
            'notificationType' => NotificationType::ReservePromoted->value,
            'sourceContext' => 'lottery',
            'priority' => DeliveryPriority::Urgent->value,
        ])),
    );

    expect($result->status)->toBe(DeliveryResultStatus::Delivered);

    $model = NotificationLogModel::query()->findOrFail($result->notificationId);
    expect($model->priority)->toBe(DeliveryPriority::Urgent);
});

it('skips delivery for invalid recipients', function (): void {
    $employeeId = UuidGenerator::uuid7();
    registerActiveEmployees();

    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray(notificationIntentPayload($employeeId, [
            'correlationId' => 'request:invalid:001',
        ])),
    );

    expect($result->status)->toBe(DeliveryResultStatus::Skipped);
    expect($result->skipReason)->toBe('skipped_invalid_recipient');
    expect(NotificationLogModel::query()->count())->toBe(0);
});

it('returns duplicate status when the same intent is replayed', function (): void {
    $employeeId = UuidGenerator::uuid7();
    registerActiveEmployees($employeeId);

    $intent = NotificationIntentDto::fromArray(notificationIntentPayload($employeeId, [
        'correlationId' => 'request:dedup:001',
    ]));

    $first = app(NotificationDeliveryContract::class)->deliver($intent);
    $second = app(NotificationDeliveryContract::class)->deliver($intent);

    expect($first->status)->toBe(DeliveryResultStatus::Delivered);
    expect($second->status)->toBe(DeliveryResultStatus::Duplicate);
    expect($second->notificationId)->toBe($first->notificationId);
    expect(NotificationLogModel::query()->count())->toBe(1);
});

it('routes standard priority jobs to the notifications queue', function (): void {
    Queue::fake();

    $employeeId = UuidGenerator::uuid7();
    SendNotificationJob::dispatch(notificationIntentPayload($employeeId));

    Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job): bool {
        return $job->queue === 'notifications';
    });
});
