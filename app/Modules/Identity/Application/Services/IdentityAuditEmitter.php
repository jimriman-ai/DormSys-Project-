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
use Ramsey\Uuid\Uuid;

final class IdentityAuditEmitter
{
    private const string SOURCE_CONTEXT = 'identity';

    private const string ENTITY_TYPE_USER = 'identity_user';

    private const string ENTITY_TYPE_ROLE = 'identity_role';

    public function __construct(
        private readonly AuditRecordingContract $auditRecording,
        private readonly AuditPrincipalContextPort $principalContext,
    ) {}

    public function recordUserCreated(UserId $userId, DateTimeImmutable $occurredAt): void
    {
        $this->record(
            correlationId: $this->correlationId(self::ENTITY_TYPE_USER, $userId->value, 'identity.user_created', 'created'),
            eventType: 'identity.user_created',
            entityType: self::ENTITY_TYPE_USER,
            entityId: $userId->value,
            oldValues: null,
            newValues: ['status' => UserStatus::Active->value],
            metadata: ['lifecycleAction' => 'created'],
            occurredAt: $occurredAt,
        );
    }

    public function recordUserDeactivated(UserId $userId, DateTimeImmutable $occurredAt): void
    {
        $this->record(
            correlationId: $this->correlationId(self::ENTITY_TYPE_USER, $userId->value, 'identity.user_deactivated', 'deactivated'),
            eventType: 'identity.user_deactivated',
            entityType: self::ENTITY_TYPE_USER,
            entityId: $userId->value,
            oldValues: ['status' => UserStatus::Active->value],
            newValues: ['status' => UserStatus::Disabled->value],
            metadata: ['lifecycleAction' => 'deactivated'],
            occurredAt: $occurredAt,
        );
    }

    public function recordRoleAssigned(UserId $userId, string $roleName, DateTimeImmutable $occurredAt): void
    {
        $this->record(
            correlationId: $this->correlationId(self::ENTITY_TYPE_USER, $userId->value, 'identity.role_changed', 'assigned:'.$roleName),
            eventType: 'identity.role_changed',
            entityType: self::ENTITY_TYPE_USER,
            entityId: $userId->value,
            oldValues: null,
            newValues: ['role' => $roleName],
            metadata: ['assignmentAction' => 'assigned'],
            occurredAt: $occurredAt,
        );
    }

    public function recordRoleRevoked(UserId $userId, string $roleName, DateTimeImmutable $occurredAt): void
    {
        $this->record(
            correlationId: $this->correlationId(self::ENTITY_TYPE_USER, $userId->value, 'identity.role_changed', 'revoked:'.$roleName),
            eventType: 'identity.role_changed',
            entityType: self::ENTITY_TYPE_USER,
            entityId: $userId->value,
            oldValues: ['role' => $roleName],
            newValues: null,
            metadata: ['assignmentAction' => 'revoked'],
            occurredAt: $occurredAt,
        );
    }

    public function recordRoleCreated(int $roleId, string $name, DateTimeImmutable $occurredAt): void
    {
        $entityId = $this->roleEntityUuid($roleId);

        $this->record(
            correlationId: $this->correlationId(self::ENTITY_TYPE_ROLE, $entityId, 'role.created', 'created:'.$name),
            eventType: 'role.created',
            entityType: self::ENTITY_TYPE_ROLE,
            entityId: $entityId,
            oldValues: null,
            newValues: ['name' => $name, 'roleId' => $roleId],
            metadata: ['lifecycleAction' => 'created'],
            occurredAt: $occurredAt,
        );
    }

    public function recordRoleUpdated(int $roleId, string $oldName, string $newName, DateTimeImmutable $occurredAt): void
    {
        $entityId = $this->roleEntityUuid($roleId);

        $this->record(
            correlationId: $this->correlationId(self::ENTITY_TYPE_ROLE, $entityId, 'role.updated', 'renamed:'.$newName),
            eventType: 'role.updated',
            entityType: self::ENTITY_TYPE_ROLE,
            entityId: $entityId,
            oldValues: ['name' => $oldName],
            newValues: ['name' => $newName, 'roleId' => $roleId],
            metadata: ['lifecycleAction' => 'updated'],
            occurredAt: $occurredAt,
        );
    }

    public function recordRoleDeleted(int $roleId, string $name, DateTimeImmutable $occurredAt): void
    {
        $entityId = $this->roleEntityUuid($roleId);

        $this->record(
            correlationId: $this->correlationId(self::ENTITY_TYPE_ROLE, $entityId, 'role.deleted', 'deleted:'.$name),
            eventType: 'role.deleted',
            entityType: self::ENTITY_TYPE_ROLE,
            entityId: $entityId,
            oldValues: ['name' => $name, 'roleId' => $roleId],
            newValues: null,
            metadata: ['lifecycleAction' => 'deleted'],
            occurredAt: $occurredAt,
        );
    }

    /**
     * @param  list<string>  $roleNames
     */
    public function recordUserRolesSynced(UserId $userId, array $roleNames, DateTimeImmutable $occurredAt): void
    {
        $this->record(
            correlationId: $this->correlationId(self::ENTITY_TYPE_USER, $userId->value, 'user.roles.synced', 'synced'),
            eventType: 'user.roles.synced',
            entityType: self::ENTITY_TYPE_USER,
            entityId: $userId->value,
            oldValues: null,
            newValues: ['roles' => $roleNames],
            metadata: ['assignmentAction' => 'synced'],
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
        string $entityType,
        string $entityId,
        ?array $oldValues,
        ?array $newValues,
        ?array $metadata,
        DateTimeImmutable $occurredAt,
    ): void {
        $this->auditRecording->record(AuditEntryDto::fromArray([
            'correlationId' => $correlationId,
            'eventType' => $eventType,
            'entityType' => $entityType,
            'entityId' => $entityId,
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

    private function correlationId(string $entityType, string $entityId, string $eventType, string $outcomeToken): string
    {
        return sprintf(
            '%s:%s:%s:%s:%s',
            self::SOURCE_CONTEXT,
            $entityType,
            $entityId,
            $eventType,
            $outcomeToken,
        );
    }

    private function roleEntityUuid(int $roleId): string
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, 'dormsys:identity_role:'.$roleId)->toString();
    }

    public static function occurredNow(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
