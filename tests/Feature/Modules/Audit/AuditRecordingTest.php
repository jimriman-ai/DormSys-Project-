<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\Enums\AuditRecordStatus;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function auditEntryPayload(array $overrides = []): array
{
    $entityId = UuidGenerator::uuid7();
    $actorId = UuidGenerator::uuid7();

    return array_merge([
        'correlationId' => 'request:'.$entityId.':request.approved:deptmgr',
        'eventType' => AuditEventType::RequestApproved->value,
        'entityType' => 'request',
        'entityId' => $entityId,
        'actorType' => ActorType::User->value,
        'actorId' => $actorId,
        'sourceContext' => 'request',
        'oldValues' => ['status' => 'pending_hr'],
        'newValues' => ['status' => 'pending_dorm'],
        'metadata' => ['approvalStage' => 'hr'],
        'occurredAt' => '2026-07-02T10:30:00Z',
    ], $overrides);
}

it('records a request approved audit entry', function (): void {
    $payload = auditEntryPayload([
        'correlationId' => 'request:approved:001',
        'eventType' => AuditEventType::RequestApproved->value,
    ]);

    $result = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray($payload));

    expect($result->status)->toBe(AuditRecordStatus::Created);
    expect($result->auditLogId)->not->toBe('');

    $model = AuditLogModel::query()->findOrFail($result->auditLogId);
    expect($model->event_type)->toBe(AuditEventType::RequestApproved);
    expect($model->entity_type)->toBe('request');
    expect($model->actor_type)->toBe(ActorType::User);
    expect($model->old_values)->toBe(['status' => 'pending_hr']);
    expect($model->new_values)->toBe(['status' => 'pending_dorm']);
});

it('records an allocation created audit entry', function (): void {
    $entityId = UuidGenerator::uuid7();

    $result = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray(auditEntryPayload([
        'correlationId' => 'allocation:created:001',
        'eventType' => AuditEventType::AllocationCreated->value,
        'entityType' => 'allocation',
        'entityId' => $entityId,
        'sourceContext' => 'allocation',
        'newValues' => ['status' => 'active'],
        'oldValues' => null,
    ])));

    expect($result->status)->toBe(AuditRecordStatus::Created);

    $model = AuditLogModel::query()->findOrFail($result->auditLogId);
    expect($model->event_type)->toBe(AuditEventType::AllocationCreated);
    expect($model->entity_id)->toBe($entityId);
});

it('records a lottery executed audit entry', function (): void {
    $entityId = UuidGenerator::uuid7();

    $result = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray(auditEntryPayload([
        'correlationId' => 'lottery:executed:001',
        'eventType' => AuditEventType::LotteryExecuted->value,
        'entityType' => 'lottery_program',
        'entityId' => $entityId,
        'sourceContext' => 'lottery',
        'newValues' => ['status' => 'executed'],
    ])));

    expect($result->status)->toBe(AuditRecordStatus::Created);
    expect(AuditLogModel::query()->findOrFail($result->auditLogId)->event_type)
        ->toBe(AuditEventType::LotteryExecuted);
});

it('returns duplicate status when the same correlation id and payload are replayed', function (): void {
    $payload = auditEntryPayload(['correlationId' => 'request:dup:001']);

    $first = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray($payload));
    $second = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray($payload));

    expect($first->status)->toBe(AuditRecordStatus::Created);
    expect($second->status)->toBe(AuditRecordStatus::Duplicate);
    expect($second->auditLogId)->toBe($first->auditLogId);
    expect(AuditLogModel::query()->count())->toBe(1);
});

it('persists system actor lottery draw entries and makes them queryable by actor filter', function (): void {
    $entityId = UuidGenerator::uuid7();

    $result = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray(auditEntryPayload([
        'correlationId' => 'lottery:draw:system:001',
        'eventType' => AuditEventType::LotteryExecuted->value,
        'entityType' => 'lottery_program',
        'entityId' => $entityId,
        'actorType' => ActorType::System->value,
        'actorId' => 'system:lottery_draw',
        'sourceContext' => 'lottery',
        'metadata' => ['jobId' => 'ExecuteLotteryDrawJob'],
    ])));

    $model = AuditLogModel::query()->findOrFail($result->auditLogId);
    expect($model->actor_type)->toBe(ActorType::System);
    expect($model->actor_id)->toBe('system:lottery_draw');

    $found = AuditLogModel::query()
        ->where('actor_type', ActorType::System->value)
        ->where('actor_id', 'system:lottery_draw')
        ->whereKey($result->auditLogId)
        ->exists();

    expect($found)->toBeTrue();
});
