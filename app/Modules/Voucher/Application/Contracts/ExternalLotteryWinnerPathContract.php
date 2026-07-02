<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Application\DTOs\ExternalLotteryWinnerBatchDto;
use App\Modules\Voucher\Application\DTOs\ExternalLotteryWinnerBatchResultDto;

interface ExternalLotteryWinnerPathContract
{
    public function processWinnerBatch(ExternalLotteryWinnerBatchDto $batch): ExternalLotteryWinnerBatchResultDto;
}
