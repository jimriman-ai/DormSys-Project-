<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
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
            ? 'voucher.issued'
            : 'voucher.state_changed';

        $payload = $transition->payload;
        $voucherId = $transition->voucherId->value;

        $this->auditRecording->record(AuditEntryDto::fromArray([
            'correlationId' => $this->correlationId($transition, $eventType),
            'eventType' => $eventType,
            'entityType' => self::ENTITY_TYPE,
            'entityId' => $voucherId,
            'actorType' => 'system',
            'actorId' => $this->actorId($payload),
            'sourceContext' => self::SOURCE_CONTEXT,
            'oldValues' => $transition->fromState === null
                ? null
                : ['lifecycle_state' => $transition->fromState->value],
            'newValues' => ['lifecycle_state' => $transition->toState->value],
            'metadata' => [
                'voucher_correlation_id' => $transition->correlationId->value,
                'employee_id' => $payload['employee_id'] ?? null,
                'request_id' => $payload['request_id'] ?? null,
                'upstream_source' => $payload['upstream_source'] ?? null,
            ],
            'occurredAt' => $transition->occurredAt->format('Y-m-d H:i:s.u'),
        ]));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function actorId(array $payload): string
    {
        $upstream = $payload['upstream_source'] ?? null;

        return match ($upstream) {
            TriggerSource::Lottery->value => 'system:lottery_draw',
            TriggerSource::Allocation->value => 'system:reserve_promotion',
            default => 'system:scheduler',
        };
    }

    private function correlationId(VoucherLifecycleTransition $transition, string $eventType): string
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
            $eventType,
            $outcomeToken,
        );
    }
}
