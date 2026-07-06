<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Domain\Exceptions\NoOpenCheckInRecordException;
use App\Modules\CheckIn\Presentation\Http\Responses\CheckInApiResponseFactory;
use App\Modules\CheckIn\Presentation\Http\Support\CheckInApiExceptionResponse;
use Illuminate\Http\JsonResponse;

final class CheckInFlowController extends Controller
{
    public function __construct(
        private readonly CheckInRecordRepositoryContract $records,
    ) {}

    public function show(string $allocationId): JsonResponse
    {
        $openRecord = $this->records->findOpenByAllocationId($allocationId);

        if ($openRecord === null) {
            return CheckInApiExceptionResponse::fromDomainException(
                new NoOpenCheckInRecordException('No open check-in record exists for this allocation.'),
            );
        }

        return CheckInApiResponseFactory::success($openRecord);
    }
}
