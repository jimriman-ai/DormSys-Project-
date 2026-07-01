<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Contracts;

use App\Modules\CheckIn\Domain\Models\CheckInRecord;
use App\Modules\CheckIn\Domain\ValueObjects\CheckInRecordId;

interface CheckInRecordRepositoryContract
{
    public function save(CheckInRecord $record): CheckInRecord;

    public function findById(CheckInRecordId $id): ?CheckInRecord;
}
