<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Request\Application\DTOs\RequestSummaryDTO;

final class RequestPrincipalEmployeeResolver
{
    public function __construct(
        private readonly MutationPrincipalContextPort $principalContext,
        private readonly EmployeeRepositoryContract $employees,
    ) {}

    public function requireEmployeeId(): string
    {
        $principalId = $this->principalContext->currentPrincipalId();

        if ($principalId === null || $principalId === '') {
            throw new UnauthorizedMutationException('Mutation requires an authorized principal.');
        }

        $employeeId = $this->employees->findEmployeeIdByIdentityUserId($principalId);

        if ($employeeId === null) {
            throw new UnauthorizedMutationException('Authenticated principal has no linked employee.');
        }

        return $employeeId;
    }

    public function assertOwnsSummary(RequestSummaryDTO $summary): void
    {
        if ($summary->employeeId !== $this->requireEmployeeId()) {
            throw new UnauthorizedMutationException('Mutation actor must own the request.');
        }
    }
}
