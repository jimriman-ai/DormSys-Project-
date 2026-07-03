<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Adapters;

use App\Modules\Allocation\Application\Contracts\VoucherIssuancePort;

final class NullVoucherIssuanceAdapter implements VoucherIssuancePort
{
    public function submitTriggerFacts(array $facts): void {}
}
