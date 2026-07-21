<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\CheckIn\Application;

use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Application\Services\GetOpenCheckInByAllocationAction;
use App\Modules\CheckIn\Domain\Models\CheckInRecord;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\MockeryTest;
use Tests\TestCase;

final class GetOpenCheckInByAllocationActionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_returns_open_record_from_repository(): void
    {
        $allocationId = '11111111-1111-7111-8111-111111111111';
        $record = CheckInRecord::open(
            $allocationId,
            '22222222-2222-7222-8222-222222222222',
            new DateTimeImmutable('2026-07-01 12:00:00'),
        );

        $records = MockeryTest::mock(CheckInRecordRepositoryContract::class);
        MockeryTest::expectOnce($records, 'findOpenByAllocationId')
            ->with($allocationId)
            ->andReturn($record);

        $result = (new GetOpenCheckInByAllocationAction($records))->execute($allocationId);

        $this->assertSame($record, $result);
    }

    #[Test]
    public function it_returns_null_when_repository_has_no_open_record(): void
    {
        $allocationId = '11111111-1111-7111-8111-111111111111';

        $records = MockeryTest::mock(CheckInRecordRepositoryContract::class);
        MockeryTest::expectOnce($records, 'findOpenByAllocationId')
            ->with($allocationId)
            ->andReturn(null);

        $result = (new GetOpenCheckInByAllocationAction($records))->execute($allocationId);

        $this->assertNull($result);
    }
}
