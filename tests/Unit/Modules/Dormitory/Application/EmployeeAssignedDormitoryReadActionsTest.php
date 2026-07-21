<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dormitory\Application;

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadRepositoryContract;
use App\Modules\Dormitory\Application\DTOs\DormitoryDetailData;
use App\Modules\Dormitory\Application\DTOs\DormitorySummaryData;
use App\Modules\Dormitory\Application\Services\GetEmployeeAssignedDormitoryAction;
use App\Modules\Dormitory\Application\Services\ListEmployeeAssignedDormitoriesAction;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\MockeryTest;
use Tests\TestCase;

final class EmployeeAssignedDormitoryReadActionsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function list_action_delegates_to_repository(): void
    {
        $userId = '11111111-1111-7111-8111-111111111111';
        $summaries = [
            new DormitorySummaryData('22222222-2222-7222-8222-222222222222', 'A', 'Alpha', 'available'),
        ];

        $reads = MockeryTest::mock(DormitoryStructureReadRepositoryContract::class);
        MockeryTest::expectOnce($reads, 'listAssignedDormitoriesForUser')
            ->with($userId)
            ->andReturn($summaries);

        $result = (new ListEmployeeAssignedDormitoriesAction($reads))->execute($userId);

        $this->assertSame($summaries, $result);
    }

    #[Test]
    public function get_action_delegates_to_repository(): void
    {
        $userId = '11111111-1111-7111-8111-111111111111';
        $dormitoryId = '22222222-2222-7222-8222-222222222222';
        $detail = new DormitoryDetailData($dormitoryId, 'A', 'Alpha', 'available');

        $reads = MockeryTest::mock(DormitoryStructureReadRepositoryContract::class);
        MockeryTest::expectOnce($reads, 'findAssignedDormitoryDetailForUser')
            ->with($userId, $dormitoryId)
            ->andReturn($detail);

        $result = (new GetEmployeeAssignedDormitoryAction($reads))->execute($userId, $dormitoryId);

        $this->assertSame($detail, $result);
    }

    #[Test]
    public function get_action_returns_null_when_repository_has_no_assigned_detail(): void
    {
        $userId = '11111111-1111-7111-8111-111111111111';
        $dormitoryId = '22222222-2222-7222-8222-222222222222';

        $reads = MockeryTest::mock(DormitoryStructureReadRepositoryContract::class);
        MockeryTest::expectOnce($reads, 'findAssignedDormitoryDetailForUser')
            ->with($userId, $dormitoryId)
            ->andReturn(null);

        $result = (new GetEmployeeAssignedDormitoryAction($reads))->execute($userId, $dormitoryId);

        $this->assertNull($result);
    }
}
