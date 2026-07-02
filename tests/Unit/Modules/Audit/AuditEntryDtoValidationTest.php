<?php

declare(strict_types=1);

use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\Exceptions\ValidationException;

/**
 * @return array<string, mixed>
 */
function validAuditEntryArray(): array
{
    $entityId = UuidGenerator::uuid7();
    $actorId = UuidGenerator::uuid7();

    return [
        'correlationId' => 'request:'.$entityId.':request.approved:deptmgr',
        'eventType' => AuditEventType::RequestApproved->value,
        'entityType' => 'request',
        'entityId' => $entityId,
        'actorType' => ActorType::User->value,
        'actorId' => $actorId,
        'sourceContext' => 'request',
        'oldValues' => ['status' => 'pending_hr'],
        'newValues' => ['status' => 'pending_dorm'],
        'metadata' => null,
        'occurredAt' => '2026-07-02T10:30:00Z',
    ];
}

it('rejects audit entries missing a correlation id', function (): void {
    $payload = validAuditEntryArray();
    unset($payload['correlationId']);

    AuditEntryDto::fromArray($payload);
})->throws(ValidationException::class);

it('rejects audit entries missing a source context', function (): void {
    $payload = validAuditEntryArray();
    unset($payload['sourceContext']);

    AuditEntryDto::fromArray($payload);
})->throws(ValidationException::class);

it('rejects audit entries with an invalid entity identifier', function (): void {
    $payload = validAuditEntryArray();
    $payload['entityId'] = 'not-a-uuid';

    AuditEntryDto::fromArray($payload);
})->throws(ValidationException::class);

it('rejects audit entries with an empty correlation id', function (): void {
    $payload = validAuditEntryArray();
    $payload['correlationId'] = '';

    AuditEntryDto::fromArray($payload);
})->throws(ValidationException::class);
