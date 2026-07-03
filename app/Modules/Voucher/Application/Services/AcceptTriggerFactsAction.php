<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Services;

use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerRepositoryContract;
use App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto;
use App\Modules\Voucher\Domain\Exceptions\DuplicateTriggerCorrelationException;
use App\Modules\Voucher\Domain\Models\VoucherIssuanceTrigger;

final class AcceptTriggerFactsAction implements VoucherTriggerIntakeContract
{
    public function __construct(
        private readonly VoucherTriggerRepositoryContract $triggers,
    ) {}

    public function accept(InboundTriggerFactsDto $facts): VoucherIssuanceTrigger
    {
        $existing = $this->triggers->findByCorrelationId($facts->correlationId);

        if ($existing !== null) {
            throw new DuplicateTriggerCorrelationException(
                'Duplicate upstream trigger correlation identifier rejected.',
            );
        }

        $trigger = VoucherIssuanceTrigger::accept(
            correlationId: $facts->correlationId,
            employeeId: $facts->employeeId,
            source: $facts->source,
            stayPeriod: $facts->stayPeriod,
            upstreamFacts: $facts->upstreamFacts,
            dormitoryId: $facts->dormitoryId,
            requestId: $facts->requestId,
        );

        $saved = $this->triggers->save($trigger);
        $triggerId = $saved->requireId();

        foreach ($this->triggers->findActiveOverlappingForEmployee($facts->employeeId, $facts->stayPeriod) as $overlapping) {
            if ($overlapping->requireId()->equals($triggerId)) {
                continue;
            }

            $this->triggers->save($overlapping->supersede($triggerId));
        }

        return $saved;
    }
}
