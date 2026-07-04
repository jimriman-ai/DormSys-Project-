<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditLogRepositoryContract;
use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Application\DTOs\AuditRecordResultDto;
use App\Modules\Audit\Domain\Enums\AuditRecordStatus;
use App\Modules\Audit\Domain\Exceptions\AuditDuplicateConflictException;
use App\Modules\Audit\Domain\Models\AuditLog;
use App\Modules\Audit\Domain\ValueObjects\AuditLogId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class RecordAuditAction implements AuditRecordingContract
{
    public function __construct(
        private readonly AuditLogRepositoryContract $auditLogs,
        private readonly PayloadHashCalculator $payloadHashCalculator,
    ) {}

    public function record(AuditEntryDto $entry): AuditRecordResultDto
    {
        if (! $this->isRecordingEnabled()) {
            throw new \RuntimeException('Audit recording is disabled.');
        }

        $guarded = $this->payloadHashCalculator->guardSnapshots($entry);
        $payloadHash = $this->payloadHashCalculator->compute(new AuditEntryDto(
            correlationId: $entry->correlationId,
            eventType: $entry->eventType,
            entityReference: $entry->entityReference,
            actorReference: $entry->actorReference,
            sourceContext: $entry->sourceContext,
            oldValues: $guarded['oldValues'],
            newValues: $guarded['newValues'],
            metadata: $guarded['metadata'],
            occurredAt: $entry->occurredAt,
        ));

        $existing = $this->auditLogs->findByCorrelationId($entry->correlationId->value);

        if ($existing !== null) {
            if ($existing->payloadHash === $payloadHash) {
                return new AuditRecordResultDto(
                    auditLogId: $existing->requireId()->value,
                    status: AuditRecordStatus::Duplicate,
                    recordedAt: $existing->createdAt,
                );
            }

            throw new AuditDuplicateConflictException(
                'Duplicate correlation identifier with conflicting payload.',
            );
        }

        $createdAt = now('UTC')->toDateTimeImmutable();
        $auditLogId = AuditLogId::fromString(UuidGenerator::uuid7());

        $auditLog = AuditLog::record(
            correlationId: $entry->correlationId,
            eventType: $entry->eventType,
            entityReference: $entry->entityReference,
            actorReference: $entry->actorReference,
            sourceContext: $entry->sourceContext,
            oldValues: $guarded['oldValues'],
            newValues: $guarded['newValues'],
            metadata: $guarded['metadata'],
            payloadHash: $payloadHash,
            occurredAt: $entry->occurredAt,
            createdAt: $createdAt,
            id: $auditLogId,
        );

        if ($this->shouldPersistSynchronously()) {
            return $this->persist($auditLog);
        }

        DB::afterCommit(function () use ($auditLog): void {
            $this->persist($auditLog);
        });

        return new AuditRecordResultDto(
            auditLogId: $auditLogId->value,
            status: AuditRecordStatus::Created,
            recordedAt: $createdAt,
        );
    }

    private function persist(AuditLog $auditLog): AuditRecordResultDto
    {
        try {
            $saved = $this->auditLogs->insert($auditLog);
        } catch (QueryException $exception) {
            if (! $this->isDuplicateKeyViolation($exception)) {
                throw $exception;
            }

            $existing = $this->auditLogs->findByCorrelationId($auditLog->correlationId->value);

            if ($existing === null) {
                throw $exception;
            }

            if ($existing->payloadHash !== $auditLog->payloadHash) {
                throw new AuditDuplicateConflictException(
                    'Duplicate correlation identifier with conflicting payload.',
                );
            }

            return new AuditRecordResultDto(
                auditLogId: $existing->requireId()->value,
                status: AuditRecordStatus::Duplicate,
                recordedAt: $existing->createdAt,
            );
        }

        return new AuditRecordResultDto(
            auditLogId: $saved->requireId()->value,
            status: AuditRecordStatus::Created,
            recordedAt: $saved->createdAt,
        );
    }

    private function isRecordingEnabled(): bool
    {
        return (bool) config('audit.recording_enabled', true);
    }

    private function shouldPersistSynchronously(): bool
    {
        if ((bool) config('audit.sync_in_tests', false)) {
            return true;
        }

        return DB::transactionLevel() === 0;
    }

    private function isDuplicateKeyViolation(QueryException $exception): bool
    {
        return ($exception->errorInfo[0] ?? null) === '23505';
    }
}
