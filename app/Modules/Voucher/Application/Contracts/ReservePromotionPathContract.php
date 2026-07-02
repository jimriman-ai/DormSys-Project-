<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Application\DTOs\ReservePromotionResultDto;
use App\Modules\Voucher\Application\DTOs\ReservePromotionTriggerFactsDto;

interface ReservePromotionPathContract
{
    public function processPromotion(ReservePromotionTriggerFactsDto $facts): ReservePromotionResultDto;
}
