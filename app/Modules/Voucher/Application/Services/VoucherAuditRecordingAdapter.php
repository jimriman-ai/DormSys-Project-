<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\ValueObjects\ActorReference;
use App\Modules\Audit\Domain\ValueObjects\CorrelationId;
use App\Modules\Audit\Domain\ValueObjects\EntityReference;
use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\Models\VoucherLifecycleTransition;

final class VoucherAuditRecordingAdapter
{
    private const string SOURCE_CONTEXT = 'voucher';

    private const string ENTITY_TYPE = 'voucher';

    public function __construct(
        private readonly AuditRecordingContract $auditRecording,
    ) {}

    public function recordTransition(VoucherLifecycleTransition $transition): void
    {
        $eventType = $transition->toState === VoucherLifecycleState::Issued
            ? AuditEventType::VoucherIssued
            : AuditEventType::VoucherStateChanged;

        $payload = $transition->payload;
        $voucherId = $transition->voucherId->value;

        $this->auditRecording->record(new AuditEntryDto(
            correlationId: CorrelationId::fromString($this->correlationId($transition, $eventType)),
            eventType: $eventType,
            entityReference: EntityReference::fromStrings(self::ENTITY_TYPE, $voucherId),
            actorReference: $this->actorReference($payload),
            sourceContext: self::SOURCE_CONTEXT,
            oldValues: $transition->fromState === null
                ? null
                : ['lifecycle_state' => $transition->fromState->value],
            newValues: ['lifecycle_state' => $transition->toState->value],
            metadata: [
                'voucher_correlation_id' => $transition->correlationId->value,
                'employee_id' => $payload['employee_id'] ?? null,
                'request_id' => $payload['request_id'] ?? null,
                'upstream_source' => $payload['upstream_source'] ?? null,
            ],
            occurredAt: $transition->occurredAt,
        ));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function actorReference(array $payload): ActorReference
    {
        $upstream = $payload['upstream_source'] ?? null;

        return match ($upstream) {
            TriggerSource::Lottery->value => new ActorReference(ActorType::System, 'system:lottery_draw'),
            TriggerSource::Allocation->value => new ActorReference(ActorType::System, 'system:reserve_promotion'),
            default => new ActorReference(ActorType::System, 'system:scheduler'),
        };
    }

    private function correlationId(VoucherLifecycleTransition $transition, AuditEventType $eventType): string
    {
        $outcomeToken = $transition->toState->value;

        if ($transition->fromState !== null) {
            $outcomeToken = $transition->fromState->value.'->'.$outcomeToken;
        }

        return sprintf(
            '%s:%s:%s:%s:%s',
            self::SOURCE_CONTEXT,
            self::ENTITY_TYPE,
            $transition->voucherId->value,
            $eventType->value,
            $outcomeToken,
        );
    }
}
