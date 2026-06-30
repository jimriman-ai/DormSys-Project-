<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;

interface LotteryScoringConfigPort
{
    public function load(): ScoringConfig;
}
