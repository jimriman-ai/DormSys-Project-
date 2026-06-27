<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

use App\Modules\Request\Domain\Entities\MissionDetails;
use App\Modules\Request\Domain\ValueObjects\RequestId;

interface MissionDetailsRepositoryContract
{
    public function save(MissionDetails $details): MissionDetails;

    public function findForRequest(RequestId $requestId): ?MissionDetails;
}
