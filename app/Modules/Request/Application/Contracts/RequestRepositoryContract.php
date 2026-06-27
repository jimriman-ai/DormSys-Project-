<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\ValueObjects\RequestCode;
use App\Modules\Request\Domain\ValueObjects\RequestId;

interface RequestRepositoryContract
{
    public function save(Request $request): Request;

    public function findById(RequestId $id): ?Request;

    public function findByCode(RequestCode $code): ?Request;

    public function nextDailySequenceForUtcDate(string $datePart): int;
}
