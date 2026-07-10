<?php

declare(strict_types=1);

use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\Contracts\NotificationRepositoryContract;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Domain\Enums\DeliveryResultStatus;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Domain\Models\Notification;
use App\Modules\Notification\Domain\ValueObjects\CorrelationId;
use App\Modules\Notification\Domain\ValueObjects\NotificationId;
use App\Modules\Notification\Infrastructure\Adapters\InMemoryEmployeeExistenceReadAdapter;
use App\Modules\Notification\Infrastructure\Persistence\Models\NotificationLogModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function idempotencyActiveEmployees(string ...$employeeIds): void
{
    app()->instance(
        EmployeeExistenceReadPort::class,
        new InMemoryEmployeeExistenceReadAdapter(array_values($employeeIds)),
    );
}

it('returns duplicate status when the same correlation identifier is replayed', function (): void {
    $employeeId = UuidGenerator::uuid7();
    idempotencyActiveEmployees($employeeId);

    $intent = NotificationIntentDto::fromArray([
        'correlationId' => 'request:idempotency:replay:001',
        'notificationType' => NotificationType::RequestApproved->value,
        'recipientEmployeeId' => $employeeId,
        'title' => 'اعلان تکراری',
        'message' => 'پیام آزمایشی.',
        'sourceContext' => 'request',
        'priority' => 'standard',
        'occurredAt' => '2026-07-02T10:30:00Z',
    ]);

    $delivery = app(NotificationDeliveryContract::class);
    $first = $delivery->deliver($intent);
    $second = $delivery->deliver($intent);

    expect($first->status)->toBe(DeliveryResultStatus::Delivered);
    expect($second->status)->toBe(DeliveryResultStatus::Duplicate);
    expect($second->notificationId)->toBe($first->notificationId);
    expect(NotificationLogModel::query()->count())->toBe(1);
});

it('prevents duplicate rows when concurrent delivery races past the dedup pre-check', function (): void {
    $employeeId = UuidGenerator::uuid7();
    idempotencyActiveEmployees($employeeId);

    $inner = app(NotificationRepositoryContract::class);
    app()->instance(NotificationRepositoryContract::class, new class($inner) implements NotificationRepositoryContract
    {
        private int $suppressedDedupLookups = 0;

        public function __construct(private readonly NotificationRepositoryContract $inner) {}

        public function save(Notification $notification): Notification
        {
            return $this->inner->save($notification);
        }

        public function findByDedupKey(
            CorrelationId $correlationId,
            string $recipientEmployeeId,
            NotificationType $notificationType,
        ): ?Notification {
            if ($this->suppressedDedupLookups < 2) {
                $this->suppressedDedupLookups++;

                return null;
            }

            return $this->inner->findByDedupKey($correlationId, $recipientEmployeeId, $notificationType);
        }

        public function findByIdForRecipient(NotificationId $notificationId, string $recipientEmployeeId): ?Notification
        {
            return $this->inner->findByIdForRecipient($notificationId, $recipientEmployeeId);
        }

        public function listForRecipient(string $recipientEmployeeId, ?bool $unreadOnly = null, int $limit = 50): array
        {
            return $this->inner->listForRecipient($recipientEmployeeId, $unreadOnly, $limit);
        }

        public function listForRecipientPaginated(
            string $recipientEmployeeId,
            ?bool $unreadOnly,
            int $page,
            int $perPage,
        ): array {
            return $this->inner->listForRecipientPaginated($recipientEmployeeId, $unreadOnly, $page, $perPage);
        }

        public function countUnread(string $recipientEmployeeId): int
        {
            return $this->inner->countUnread($recipientEmployeeId);
        }

        public function archiveExpiredBefore(DateTimeImmutable $cutoff): int
        {
            return $this->inner->archiveExpiredBefore($cutoff);
        }
    });

    $intent = NotificationIntentDto::fromArray([
        'correlationId' => 'request:idempotency:race:001',
        'notificationType' => NotificationType::RequestApproved->value,
        'recipientEmployeeId' => $employeeId,
        'title' => 'اعلان همزمان',
        'message' => 'پیام آزمایشی.',
        'sourceContext' => 'request',
        'priority' => 'standard',
        'occurredAt' => '2026-07-02T10:30:00Z',
    ]);

    $delivery = app(NotificationDeliveryContract::class);
    $first = $delivery->deliver($intent);
    $second = $delivery->deliver($intent);

    expect($first->status)->toBe(DeliveryResultStatus::Delivered);
    expect($second->status)->toBe(DeliveryResultStatus::Duplicate);
    expect($second->notificationId)->toBe($first->notificationId);
    expect(NotificationLogModel::query()->count())->toBe(1);
});
