<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use DateTimeImmutable;

final class CreatePersonalRequestAction
{
    public function __construct(
        private readonly RequestCodeGenerator $codeGenerator,
        private readonly RequestRepositoryContract $requests,
    ) {}

    public function execute(
        EmployeeReferenceId $employeeId,
        DormitorySiteId $dormitoryId,
        DateTimeImmutable $checkInDate,
        DateTimeImmutable $checkOutDate,
    ): Request {
        $request = Request::createDraft(
            code: $this->codeGenerator->generate(),
            employeeId: $employeeId,
            dormitoryId: $dormitoryId,
            type: RequestType::Personal,
            checkInDate: $checkInDate,
            checkOutDate: $checkOutDate,
        );

        return $this->requests->save($request);
    }
}
