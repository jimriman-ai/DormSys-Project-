<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

use App\Modules\Request\Domain\Entities\RequestMember;
use App\Modules\Request\Domain\ValueObjects\RequestId;

interface RequestMemberRepositoryContract
{
    public function append(RequestMember $member): RequestMember;

    /**
     * @return list<RequestMember>
     */
    public function listForRequest(RequestId $requestId): array;
}
