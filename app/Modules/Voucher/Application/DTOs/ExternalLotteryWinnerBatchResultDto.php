<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\DTOs;

use App\Modules\Voucher\Domain\Enums\ExternalLotteryBatchDisposition;

final readonly class ExternalLotteryWinnerBatchResultDto
{
    /**
     * @param  list<ExternalLotteryWinnerItemResultDto>  $winnerResults
     */
    public function __construct(
        public ExternalLotteryBatchDisposition $batchDisposition,
        public int $issuedCount,
        public array $winnerResults,
    ) {}

    public static function ignoredInternalProgram(): self
    {
        return new self(
            batchDisposition: ExternalLotteryBatchDisposition::IgnoredInternalProgram,
            issuedCount: 0,
            winnerResults: [],
        );
    }
}
