<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Application\Contracts\Ports\PhysicalStateSignalPort;
use App\Modules\Allocation\Domain\Events\AllocationReleased;
use App\Modules\Allocation\Domain\Exceptions\AllocationNotFoundException;
use App\Modules\Allocation\Domain\Models\Allocation;
use App\Modules\Allocation\Domain\ValueObjects\AllocationId;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class ReleaseAllocationAction
{
    public function __construct(
        private readonly AllocationRepositoryContract $allocations,
        private readonly PhysicalStateSignalPort $physicalState,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly AllocationMutationAuthorizationGate $allocationMutationAuth,
    ) {}

    public function execute(string $allocationId, string $reason): Allocation
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::ALLOCATION_RELEASE, [
            'allocationId' => $allocationId,
        ]);
        $this->allocationMutationAuth->assertRelease();

        $id = AllocationId::fromString($allocationId);
        $allocation = $this->allocations->findById($id);

        if ($allocation === null) {
            throw new AllocationNotFoundException('Allocation not found.');
        }

        $released = $allocation->release(
            reason: $reason,
            releasedAt: new DateTimeImmutable('now', new DateTimeZone('UTC')),
        );

        $persisted = DB::transaction(fn (): Allocation => $this->allocations->save($released));

        Event::dispatch(AllocationReleased::forAllocation($persisted));

        $this->physicalState->releaseBed(
            bedId: $persisted->bedId,
            signalReferenceId: $persisted->requireId()->value,
        );

        return $persisted;
    }
}
