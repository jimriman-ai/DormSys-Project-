<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\CreateAllocationFromRequestAction;
use App\Modules\Allocation\Application\Services\ReleaseAllocationAction;
use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use App\Modules\Allocation\Presentation\Http\Requests\CreateAllocationFromRequestRequest;
use App\Modules\Allocation\Presentation\Http\Requests\CreateAllocationRequest;
use App\Modules\Allocation\Presentation\Http\Requests\ReleaseAllocationRequest;
use App\Modules\Allocation\Presentation\Http\Responses\AllocationApiResponseFactory;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class AllocationMutationController extends Controller
{
    public function __construct(
        private readonly CreateAllocationAction $createAllocation,
        private readonly CreateAllocationFromRequestAction $createAllocationFromRequest,
        private readonly ReleaseAllocationAction $releaseAllocation,
    ) {}

    public function store(CreateAllocationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $created = $this->createAllocation->execute(
            personId: (string) $validated['personId'],
            bedId: (string) $validated['bedId'],
            start: new DateTimeImmutable((string) $validated['startDate'], new DateTimeZone('UTC')),
            end: new DateTimeImmutable((string) $validated['endDate'], new DateTimeZone('UTC')),
            method: AllocationMethod::Manual,
        );

        return AllocationApiResponseFactory::success($created, Response::HTTP_CREATED);
    }

    public function storeFromRequest(CreateAllocationFromRequestRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $created = $this->createAllocationFromRequest->execute(
            requestId: (string) $validated['requestId'],
            bedId: isset($validated['bedId']) ? (string) $validated['bedId'] : null,
        );

        return AllocationApiResponseFactory::success($created, Response::HTTP_CREATED);
    }

    public function release(ReleaseAllocationRequest $request): JsonResponse
    {
        $released = $this->releaseAllocation->execute(
            allocationId: (string) $request->validated('allocationId'),
            reason: (string) $request->validated('reason'),
        );

        return AllocationApiResponseFactory::success($released);
    }
}
