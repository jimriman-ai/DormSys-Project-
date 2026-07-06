<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lottery\Application\Contracts\LotteryProgramReadContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Lottery\Presentation\Http\Requests\EnrollLotteryRegistrationRequest;
use App\Modules\Lottery\Presentation\Http\Responses\LotteryApiResponseFactory;
use App\Modules\Lottery\Presentation\Http\Support\LotteryApiExceptionResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class LotteryFlowController extends Controller
{
    public function __construct(
        private readonly LotteryProgramReadContract $programs,
        private readonly LotteryResultReadContract $results,
        private readonly EnrollRegistrationAction $enrollRegistration,
    ) {}

    public function show(string $programId): JsonResponse
    {
        $summary = $this->programs->getProgramSummary(LotteryProgramId::fromString($programId));

        if ($summary === null) {
            return LotteryApiExceptionResponse::fromDomainException(
                new LotteryProgramNotFoundException('Lottery program not found.'),
            );
        }

        return response()->json([
            'success' => true,
            'data' => LotteryApiResponseFactory::serializeProgramSummary($summary),
        ]);
    }

    public function results(string $programId): JsonResponse
    {
        $summary = $this->programs->getProgramSummary(LotteryProgramId::fromString($programId));

        if ($summary === null) {
            return LotteryApiExceptionResponse::fromDomainException(
                new LotteryProgramNotFoundException('Lottery program not found.'),
            );
        }

        return LotteryApiResponseFactory::successResults(
            $this->results->resultsForProgram(LotteryProgramId::fromString($programId)),
        );
    }

    public function enroll(EnrollLotteryRegistrationRequest $request): JsonResponse
    {
        $programId = LotteryProgramId::fromString((string) $request->validated('programId'));

        $registration = $this->enrollRegistration->execute(
            $programId,
            RequestReferenceId::fromString((string) $request->validated('requestId')),
        );

        return LotteryApiResponseFactory::successRegistration($registration, Response::HTTP_CREATED);
    }
}
