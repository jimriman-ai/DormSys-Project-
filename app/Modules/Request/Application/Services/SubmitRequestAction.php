<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\DormitoryReadContract;
use App\Modules\Request\Application\Contracts\Internal\RequestEligibilityGatewayContract;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Events\RequestSubmitted;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestNotEligibleException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class SubmitRequestAction
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
        private readonly RequestEligibilityGatewayContract $eligibility,
        private readonly DormitoryReadContract $dormitoryRead,
    ) {}

    public function execute(RequestId $requestId): Request
    {
        $request = $this->requests->findById($requestId);

        if ($request === null) {
            throw new RequestNotFoundException('Request not found.');
        }

        if (! $request->isDraft()) {
            throw new InvalidRequestTransitionException('Only draft requests can be submitted.');
        }

        $this->validateDates($request);
        $this->validateTypeRules($request);

        $eligibility = $this->eligibility->computeRequestEligibility(
            $request->employeeId->value,
            $request->requireId()->value,
        );

        if (! $eligibility->eligible) {
            throw new RequestNotEligibleException(
                reasonCodes: $eligibility->reasonCodes,
            );
        }

        if (! $this->dormitoryRead->siteExists($request->dormitoryId->value)) {
            throw new RequestValidationException('Dormitory site does not exist.');
        }

        $submittedAt = now('UTC')->toDateTimeImmutable();
        $submitted = $request->markSubmitted($submittedAt);

        return DB::transaction(function () use ($submitted): Request {
            $persisted = $this->requests->save($submitted);

            Event::dispatch(RequestSubmitted::forRequest(
                requestId: $persisted->requireId()->value,
                employeeId: $persisted->employeeId->value,
                status: $persisted->status,
            ));

            return $persisted;
        });
    }

    public function validateDates(Request $request): void
    {
        if ($request->checkOutDate <= $request->checkInDate) {
            throw new RequestValidationException('Check-out date must be after check-in date.');
        }

        $today = now('UTC')->startOfDay()->toDateTimeImmutable();

        if ($request->checkInDate < $today) {
            throw new RequestValidationException('Check-in date cannot be in the past.');
        }
    }

    private function validateTypeRules(Request $request): void
    {
        // Personal and LotteryRegistration have no child rows at submit (R-12).
    }
}
