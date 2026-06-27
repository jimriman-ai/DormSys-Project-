<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

use App\Modules\Request\Domain\Entities\RequestApproval;
use App\Modules\Request\Domain\ValueObjects\RequestId;

interface RequestApprovalRepositoryContract
{
    public function append(RequestApproval $approval): RequestApproval;

    public function countForRequest(RequestId $requestId): int;

    /**
     * @return list<RequestApproval>
     */
    public function listForRequest(RequestId $requestId): array;
}
