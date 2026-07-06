<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\RequestPrincipalEmployeeResolver;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Presentation\Http\Requests\CreatePersonalRequestRequest;
use App\Modules\Request\Presentation\Http\Responses\RequestApiResponseFactory;
use App\Modules\Request\Presentation\Http\Support\RequestApiExceptionResponse;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestFlowController extends Controller
{
    public function __construct(
        private readonly CreatePersonalRequestAction $createPersonalRequest,
        private readonly RequestReadContract $requests,
        private readonly RequestPrincipalEmployeeResolver $principalEmployee,
    ) {}

    public function storePersonal(CreatePersonalRequestRequest $request): JsonResponse
    {
        $employeeId = $this->principalEmployee->requireEmployeeId();

        $validated = $request->validated();

        $created = $this->createPersonalRequest->execute(
            employeeId: EmployeeReferenceId::fromString($employeeId),
            dormitoryId: DormitorySiteId::fromString((string) $validated['dormitoryId']),
            checkInDate: new DateTimeImmutable((string) $validated['checkInDate'], new DateTimeZone('UTC')),
            checkOutDate: new DateTimeImmutable((string) $validated['checkOutDate'], new DateTimeZone('UTC')),
        );

        return RequestApiResponseFactory::success($created, Response::HTTP_CREATED);
    }

    public function indexMine(Request $request): JsonResponse
    {
        $employeeId = $this->principalEmployee->requireEmployeeId();

        return response()->json([
            'success' => true,
            'data' => array_map(
                static fn (RequestSummaryDTO $summary): array => RequestApiResponseFactory::serializeSummary($summary),
                $this->requests->listByEmployee($employeeId),
            ),
        ]);
    }

    public function show(Request $request, string $requestId): JsonResponse
    {
        $summary = $this->requests->getRequestSummary(RequestId::fromString($requestId));

        if ($summary === null) {
            return RequestApiExceptionResponse::fromDomainException(
                new RequestNotFoundException('Request not found.'),
            );
        }

        $this->principalEmployee->assertOwnsSummary($summary);

        return response()->json([
            'success' => true,
            'data' => RequestApiResponseFactory::serializeSummary($summary),
        ]);
    }
}
