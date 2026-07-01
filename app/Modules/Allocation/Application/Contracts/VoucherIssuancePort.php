<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Contracts;

interface VoucherIssuancePort
{
    /**
     * Submit trigger facts for voucher evaluation. Voucher decides issuance.
     * Payload shape TBD (UD-03).
     *
     * @param  array<string, mixed>  $facts
     */
    public function submitTriggerFacts(array $facts): void;
}
