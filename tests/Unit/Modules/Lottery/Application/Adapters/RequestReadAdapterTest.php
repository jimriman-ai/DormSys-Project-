<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Application\Adapters;

use App\Modules\Lottery\Application\Contracts\LotteryRequestReadPort;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RequestReadAdapterTest extends TestCase
{
    #[Test]
    public function it_returns_approved_lottery_registration_summary(): void
    {
        $requestId = UuidGenerator::uuid7();
        $employeeId = UuidGenerator::uuid7();
        $dormitoryId = UuidGenerator::uuid7();

        $requests = Mockery::mock(RequestReadContract::class);
        $requests->shouldReceive('listApprovedByType')
            ->once()
            ->with('lottery_registration')
            ->andReturn([
                new RequestSummaryDTO(
                    id: $requestId,
                    code: 'REQ-20260630-0001',
                    employeeId: $employeeId,
                    dormitoryId: $dormitoryId,
                    type: 'lottery_registration',
                    status: 'approved',
                    checkInDate: '2026-07-01',
                    checkOutDate: '2026-12-31',
                    submittedAt: '2026-06-30T00:00:00+00:00',
                ),
            ]);
        $this->app->instance(RequestReadContract::class, $requests);

        $result = app(LotteryRequestReadPort::class)->findApprovedLotteryRegistration(
            RequestReferenceId::fromString($requestId),
        );

        expect($result)->not->toBeNull();
        expect($result?->requestId)->toBe($requestId);
        expect($result?->employeeId)->toBe($employeeId);
        expect($result?->dormitoryId)->toBe($dormitoryId);
    }

    #[Test]
    public function it_returns_null_when_request_is_not_in_approved_lottery_list(): void
    {
        $requests = Mockery::mock(RequestReadContract::class);
        $requests->shouldReceive('listApprovedByType')
            ->once()
            ->with('lottery_registration')
            ->andReturn([]);
        $this->app->instance(RequestReadContract::class, $requests);

        $result = app(LotteryRequestReadPort::class)->findApprovedLotteryRegistration(
            RequestReferenceId::fromString(UuidGenerator::uuid7()),
        );

        expect($result)->toBeNull();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
