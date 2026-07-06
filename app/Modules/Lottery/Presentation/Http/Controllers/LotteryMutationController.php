<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lottery\Application\Services\CancelLotteryProgramAction;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Presentation\Http\Requests\CancelLotteryProgramRequest;
use App\Modules\Lottery\Presentation\Http\Requests\CreateLotteryProgramRequest;
use App\Modules\Lottery\Presentation\Http\Requests\LotteryProgramMutationRequest;
use App\Modules\Lottery\Presentation\Http\Responses\LotteryApiResponseFactory;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class LotteryMutationController extends Controller
{
    public function __construct(
        private readonly CreateLotteryProgramAction $createLotteryProgram,
        private readonly OpenRegistrationAction $openRegistration,
        private readonly CloseRegistrationAction $closeRegistration,
        private readonly LockLotteryProgramAction $lockLotteryProgram,
        private readonly ExecuteDrawAction $executeDraw,
        private readonly CancelLotteryProgramAction $cancelLotteryProgram,
    ) {}

    public function store(CreateLotteryProgramRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $created = $this->createLotteryProgram->execute(
            title: (string) $validated['title'],
            dormitoryId: DormitorySiteId::fromString((string) $validated['dormitoryId']),
            capacity: (int) $validated['capacity'],
            registrationStartsAt: new DateTimeImmutable((string) $validated['registrationStartsAt'], new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable((string) $validated['registrationEndsAt'], new DateTimeZone('UTC')),
        );

        return LotteryApiResponseFactory::successProgram($created, Response::HTTP_CREATED);
    }

    public function openRegistration(LotteryProgramMutationRequest $request): JsonResponse
    {
        $programId = LotteryProgramId::fromString((string) $request->validated('programId'));

        return LotteryApiResponseFactory::successProgram(
            $this->openRegistration->execute($programId),
        );
    }

    public function closeRegistration(LotteryProgramMutationRequest $request): JsonResponse
    {
        $programId = LotteryProgramId::fromString((string) $request->validated('programId'));

        return LotteryApiResponseFactory::successProgram(
            $this->closeRegistration->execute($programId),
        );
    }

    public function lock(LotteryProgramMutationRequest $request): JsonResponse
    {
        $programId = LotteryProgramId::fromString((string) $request->validated('programId'));

        return LotteryApiResponseFactory::successProgram(
            $this->lockLotteryProgram->execute($programId),
        );
    }

    public function draw(LotteryProgramMutationRequest $request): JsonResponse
    {
        $programId = LotteryProgramId::fromString((string) $request->validated('programId'));

        return LotteryApiResponseFactory::successProgram(
            $this->executeDraw->execute($programId),
        );
    }

    public function cancel(CancelLotteryProgramRequest $request): JsonResponse
    {
        $programId = LotteryProgramId::fromString((string) $request->validated('programId'));

        return LotteryApiResponseFactory::successProgram(
            $this->cancelLotteryProgram->execute($programId, (string) $request->validated('reason')),
        );
    }
}
