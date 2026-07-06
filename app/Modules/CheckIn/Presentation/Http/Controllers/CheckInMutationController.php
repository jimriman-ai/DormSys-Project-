<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CheckIn\Application\Services\CheckInAction;
use App\Modules\CheckIn\Application\Services\CheckOutAction;
use App\Modules\CheckIn\Presentation\Http\Requests\CheckInAllocationRequest;
use App\Modules\CheckIn\Presentation\Http\Requests\CheckOutAllocationRequest;
use App\Modules\CheckIn\Presentation\Http\Responses\CheckInApiResponseFactory;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CheckInMutationController extends Controller
{
    public function __construct(
        private readonly CheckInAction $checkIn,
        private readonly CheckOutAction $checkOut,
    ) {}

    public function checkIn(CheckInAllocationRequest $request): JsonResponse
    {
        $record = $this->checkIn->execute(
            allocationId: (string) $request->validated('allocationId'),
            operatorId: $this->mutationPrincipalId($request),
        );

        return CheckInApiResponseFactory::success($record, Response::HTTP_CREATED);
    }

    public function checkOut(CheckOutAllocationRequest $request): JsonResponse
    {
        $record = $this->checkOut->execute(
            allocationId: (string) $request->validated('allocationId'),
            operatorId: $this->mutationPrincipalId($request),
        );

        return CheckInApiResponseFactory::success($record);
    }

    private function mutationPrincipalId(CheckInAllocationRequest|CheckOutAllocationRequest $request): string
    {
        return (string) $request->attributes->get('audit_principal_user_id');
    }
}
