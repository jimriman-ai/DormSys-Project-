<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Allocation\Application\Contracts\AllocationReadContract;
use App\Modules\Allocation\Domain\Exceptions\AllocationNotFoundException;
use App\Modules\Allocation\Presentation\Http\Support\AllocationApiExceptionResponse;
use Illuminate\Http\JsonResponse;

final class AllocationFlowController extends Controller
{
    public function __construct(
        private readonly AllocationReadContract $allocations,
    ) {}

    public function show(string $allocationId): JsonResponse
    {
        $detail = $this->allocations->getAllocationDetail($allocationId);

        if ($detail === null) {
            return AllocationApiExceptionResponse::fromAllocationException(
                new AllocationNotFoundException('Allocation not found.'),
            );
        }

        return response()->json([
            'success' => true,
            'data' => $detail,
        ]);
    }

    public function activeForPerson(string $personId): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->allocations->getActiveAllocationsForPerson($personId),
        ]);
    }
}
