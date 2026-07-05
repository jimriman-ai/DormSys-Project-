<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Request\Application;

use App\Modules\Request\Application\Contracts\DormitoryReadContract;
use App\Modules\Request\Application\Contracts\Internal\RequestEligibilityGatewayContract;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestCode;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Carbon;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\MockeryTest;
use Tests\TestCase;

class SubmitDateValidationTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_rejects_a_past_check_in_date(): void
    {
        Carbon::setTestNow('2026-06-23 12:00:00');

        $action = $this->submitAction();
        $request = $this->draftRequest(
            checkInDate: new DateTimeImmutable('2026-06-22', new DateTimeZone('UTC')),
            checkOutDate: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
        );

        $this->expectException(RequestValidationException::class);
        $action->validateDates($request);
    }

    #[Test]
    public function it_rejects_check_out_on_or_before_check_in(): void
    {
        Carbon::setTestNow('2026-06-23 12:00:00');

        $action = $this->submitAction();
        $request = $this->draftRequest(
            checkInDate: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            checkOutDate: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
        );

        $this->expectException(RequestValidationException::class);
        $action->validateDates($request);
    }

    #[Test]
    public function it_accepts_valid_future_dates(): void
    {
        Carbon::setTestNow('2026-06-23 12:00:00');

        $action = $this->submitAction();
        $request = $this->draftRequest(
            checkInDate: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            checkOutDate: new DateTimeImmutable('2026-12-31', new DateTimeZone('UTC')),
        );

        $action->validateDates($request);
        $this->addToAssertionCount(1);
    }

    private function submitAction(): SubmitRequestAction
    {
        return new SubmitRequestAction(
            requests: MockeryTest::mock(RequestRepositoryContract::class),
            eligibility: MockeryTest::mock(RequestEligibilityGatewayContract::class),
            dormitoryRead: MockeryTest::mock(DormitoryReadContract::class),
        );
    }

    private function draftRequest(
        DateTimeImmutable $checkInDate,
        DateTimeImmutable $checkOutDate,
    ): Request {
        return new Request(
            id: RequestId::fromString(UuidGenerator::uuid7()),
            code: RequestCode::fromString('REQ-20260623-0001'),
            employeeId: EmployeeReferenceId::fromString(UuidGenerator::uuid7()),
            dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
            type: RequestType::Personal,
            checkInDate: $checkInDate,
            checkOutDate: $checkOutDate,
            status: DraftState::$name,
        );
    }
}
