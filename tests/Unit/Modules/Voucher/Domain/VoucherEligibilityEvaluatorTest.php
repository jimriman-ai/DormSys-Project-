<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Voucher\Domain;

use App\Modules\Voucher\Domain\Enums\AccommodationClassification;
use App\Modules\Voucher\Domain\Enums\DeferredReasonCode;
use App\Modules\Voucher\Domain\Enums\EligibilityOutcome;
use App\Modules\Voucher\Domain\Enums\IneligibilityReasonCode;
use App\Modules\Voucher\Domain\Enums\TriggerIntakeStatus;
use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\Models\VoucherIssuanceTrigger;
use App\Modules\Voucher\Domain\Services\VoucherEligibilityEvaluator;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\StayPeriod;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VoucherEligibilityEvaluatorTest extends TestCase
{
    private VoucherEligibilityEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new VoucherEligibilityEvaluator;
    }

    #[Test]
    public function it_evaluates_external_lottery_facts_as_eligible(): void
    {
        $trigger = $this->trigger(dormitoryId: '550e8400-e29b-41d4-a716-446655440000');

        $result = $this->evaluator->evaluate($trigger, AccommodationClassification::External);

        $this->assertSame(EligibilityOutcome::Eligible, $result['outcome']);
        $this->assertNotSame('', $result['rationale']);
    }

    #[Test]
    public function it_evaluates_missing_dormitory_as_ineligible(): void
    {
        $result = $this->evaluator->evaluate($this->trigger(), null);

        $this->assertSame(EligibilityOutcome::Ineligible, $result['outcome']);
        $this->assertContains(
            IneligibilityReasonCode::MissingDormitoryReference->value,
            $result['reasonCodes'],
        );
    }

    #[Test]
    public function it_evaluates_unknown_classification_as_deferred(): void
    {
        $trigger = $this->trigger(dormitoryId: '550e8400-e29b-41d4-a716-446655440000');

        $result = $this->evaluator->evaluate($trigger, null);

        $this->assertSame(EligibilityOutcome::Deferred, $result['outcome']);
        $this->assertContains(DeferredReasonCode::ClassificationPending->value, $result['reasonCodes']);
    }

    private function trigger(?string $dormitoryId = null): VoucherIssuanceTrigger
    {
        return new VoucherIssuanceTrigger(
            id: null,
            correlationId: CorrelationId::fromString('unit-test-corr'),
            employeeId: '550e8400-e29b-41d4-a716-446655440001',
            source: TriggerSource::Lottery,
            stayPeriod: StayPeriod::fromDates(
                new DateTimeImmutable('2026-09-01'),
                new DateTimeImmutable('2026-09-30'),
            ),
            status: TriggerIntakeStatus::Accepted,
            dormitoryId: $dormitoryId,
            requestId: null,
            upstreamFacts: [],
            issuancePathCompletedAt: null,
            supersededByTriggerId: null,
        );
    }
}
