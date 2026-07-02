<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditLogRepositoryContract;
use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\Enums\AuditRecordStatus;
use App\Modules\Audit\Domain\Exceptions\AppendOnlyViolationException;
use App\Modules\Audit\Domain\Exceptions\AuditDuplicateConflictException;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function idempotencyAuditPayload(array $overrides = []): array
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

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
});

it('accepts duplicate replay and preserves a single audit row', function (): void {
    $payload = idempotencyAuditPayload(['correlationId' => 'request:idempotent:replay:001']);

    $first = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray($payload));
    $second = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray($payload));

    expect($first->status)->toBe(AuditRecordStatus::Created);
    expect($second->status)->toBe(AuditRecordStatus::Duplicate);
    expect($second->auditLogId)->toBe($first->auditLogId);
    expect(AuditLogModel::query()->count())->toBe(1);
});

it('rejects conflicting payloads for the same correlation id and preserves the original row', function (): void {
    $correlationId = 'request:conflict:001';
    $entityId = UuidGenerator::uuid7();

    $firstPayload = idempotencyAuditPayload([
        'correlationId' => $correlationId,
        'entityId' => $entityId,
        'newValues' => ['status' => 'pending_dorm'],
    ]);

    $first = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray($firstPayload));

    $conflictingPayload = idempotencyAuditPayload([
        'correlationId' => $correlationId,
        'entityId' => $entityId,
        'newValues' => ['status' => 'approved'],
    ]);

    expect(fn () => app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray($conflictingPayload)))
        ->toThrow(AuditDuplicateConflictException::class, 'Duplicate correlation identifier with conflicting payload.');

    expect(AuditLogModel::query()->count())->toBe(1);
    expect(AuditLogModel::query()->findOrFail($first->auditLogId)->new_values)
        ->toBe(['status' => 'pending_dorm']);
});

it('does not persist audit rows when the enclosing domain transaction rolls back', function (): void {
    config(['audit.sync_in_tests' => false]);

    $payload = idempotencyAuditPayload(['correlationId' => 'request:rollback:001']);

    try {
        DB::transaction(function () use ($payload): void {
            $result = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray($payload));

            expect($result->status)->toBe(AuditRecordStatus::Created);
            expect($result->auditLogId)->not->toBe('');

            throw new RuntimeException('force domain rollback');
        });
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('force domain rollback');
    }

    expect(AuditLogModel::query()->count())->toBe(0);
});

it('prevents audit log mutation through the eloquent model', function (): void {
    $result = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray(
        idempotencyAuditPayload(['correlationId' => 'request:immutable:001']),
    ));

    $model = AuditLogModel::query()->findOrFail($result->auditLogId);

    expect(fn () => $model->update(['source_context' => 'tampered']))
        ->toThrow(AppendOnlyViolationException::class, 'Audit log records are append-only.');

    expect(fn () => $model->delete())
        ->toThrow(AppendOnlyViolationException::class, 'Audit log records are append-only.');
});

it('exposes append-only repository semantics without update or delete operations', function (): void {
    $repository = app(AuditLogRepositoryContract::class);

    expect(method_exists($repository, 'update'))->toBeFalse();
    expect(method_exists($repository, 'delete'))->toBeFalse();
});
