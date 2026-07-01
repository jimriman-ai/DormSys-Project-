<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Application\Services\ProposedAllocationConsumer;
use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use App\Modules\Allocation\Domain\Enums\AllocationStatus;
use App\Modules\Allocation\Domain\ValueObjects\AllocationId;
use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('creates allocations from lottery proposed allocation payloads', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $registrationId = UuidGenerator::uuid7();
    $programId = UuidGenerator::uuid7();

    app(ProposedAllocationConsumer::class)->emitProposedAllocations([
        [
            'program_id' => $programId,
            'registration_id' => $registrationId,
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'rank' => 1,
        ],
    ]);

    $allocationId = DB::table('allocations')
        ->where('person_id', $employeeId)
        ->value('id');

    expect($allocationId)->not->toBeNull();

    $allocation = app(AllocationRepositoryContract::class)->findById(
        AllocationId::fromString((string) $allocationId),
    );

    expect($allocation)->not->toBeNull();
    expect($allocation->status)->toBe(AllocationStatus::Active);
    expect($allocation->method)->toBe(AllocationMethod::LotterySourced);
    expect($allocation->sourceLotteryResultId)->toBe($registrationId);
    expect($allocation->bedId)->toBe($dormitoryId);
});

it('binds proposed allocation port to the allocation consumer after boot', function (): void {
    expect(app(ProposedAllocationPort::class))->toBeInstanceOf(ProposedAllocationConsumer::class);
});
