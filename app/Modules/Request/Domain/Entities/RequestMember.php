<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Entities;

use App\Modules\Request\Domain\ValueObjects\RequestId;

final class RequestMember
{
    public function __construct(
        public readonly ?string $id,
        public readonly RequestId $requestId,
        public readonly string $employeeId,
        public readonly bool $isLeader,
    ) {}
}
