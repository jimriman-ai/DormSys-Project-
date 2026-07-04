<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditPrincipalContextPort;
use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use DateTimeImmutable;
use DateTimeZone;

final class IdentityAuditEmitter
{
    private const string SOURCE_CONTEXT = 'identity';

    private const string ENTITY_TYPE = 'identity_user';

    public function __construct(
        private readonly AuditRecordingContract $auditRecording,
        private readonly AuditPrincipalContextPort $principalContext,
    ) {}

    public function recordUserCreated(UserId $userId, DateTimeImmutable $occurredAt): void
    {
        $this->record(
            correlationId: $this->correlationId($userId, 'identity.user_created', 'created'),
            eventType: 'identity.user_created',
            userId: $userId,
            oldValues: null,
            newValues: ['status' => UserStatus::Active->value],
            metadata: ['lifecycleAction' => 'created'],
            occurredAt: $occurredAt,
        );
    }

    public function recordUserDeactivated(UserId $userId, DateTimeImmutable $occurredAt): void
    {
        $this->record(
            correlationId: $this->correlationId($userId, 'identity.user_deactivated', 'deactivated'),
            eventType: 'identity.user_deactivated',
            userId: $userId,
            oldValues: ['status' => UserStatus::Active->value],
            newValues: ['status' => UserStatus::Disabled->value],
            metadata: ['lifecycleAction' => 'deactivated'],
            occurredAt: $occurredAt,
        );
    }

    public function recordRoleAssigned(UserId $userId, string $roleName, DateTimeImmutable $occurredAt): void
    {
        $this->record(
            correlationId: $this->correlationId($userId, 'identity.role_changed', 'assigned:'.$roleName),
            eventType: 'identity.role_changed',
            userId: $userId,
            oldValues: null,
            newValues: ['role' => $roleName],
            metadata: ['assignmentAction' => 'assigned'],
            occurredAt: $occurredAt,
        );
    }

    public function recordRoleRevoked(UserId $userId, string $roleName, DateTimeImmutable $occurredAt): void
    {
        $this->record(
            correlationId: $this->correlationId($userId, 'identity.role_changed', 'revoked:'.$roleName),
            eventType: 'identity.role_changed',
            userId: $userId,
            oldValues: ['role' => $roleName],
            newValues: null,
            metadata: ['assignmentAction' => 'revoked'],
            occurredAt: $occurredAt,
        );
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    private function record(
        string $correlationId,
        string $eventType,
        UserId $userId,
        ?array $oldValues,
        ?array $newValues,
        ?array $metadata,
        DateTimeImmutable $occurredAt,
    ): void {
        $this->auditRecording->record(AuditEntryDto::fromArray([
            'correlationId' => $correlationId,
            'eventType' => $eventType,
            'entityType' => self::ENTITY_TYPE,
            'entityId' => $userId->value,
            'actorType' => $this->actorType(),
            'actorId' => $this->actorId(),
            'sourceContext' => self::SOURCE_CONTEXT,
            'oldValues' => $oldValues,
            'newValues' => $newValues,
            'metadata' => $metadata,
            'occurredAt' => $occurredAt->format('Y-m-d H:i:s.u'),
        ]));
    }

    private function actorType(): string
    {
        return $this->principalContext->currentPrincipalId() !== null ? 'user' : 'system';
    }

    private function actorId(): string
    {
        return $this->principalContext->currentPrincipalId() ?? 'system:scheduler';
    }

    private function correlationId(UserId $userId, string $eventType, string $outcomeToken): string
    {
        return sprintf(
            '%s:%s:%s:%s:%s',
            self::SOURCE_CONTEXT,
            self::ENTITY_TYPE,
            $userId->value,
            $eventType,
            $outcomeToken,
        );
    }

    public static function occurredNow(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
