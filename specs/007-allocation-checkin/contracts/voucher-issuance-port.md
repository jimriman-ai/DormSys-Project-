# Port: Voucher Issuance (trigger facts)

**Version:** 1.0.0  
**Spec:** spec07 Allocation & Occupancy (producer) / spec08 Voucher (consumer)  
**Direction:** Allocation → Voucher (R8)  
**Status:** Design — stub only; CD-016

---

## Purpose

Outbound trigger boundary when Allocation may initiate voucher evaluation. Allocation supplies **facts only**; Voucher owns eligibility and issuance lifecycle (CD-016).

Exact input contract deferred — UD-03.

---

## Interface (producer stub)

**Namespace:** `App\Modules\Allocation\Application\Contracts\VoucherIssuancePort`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Contracts;

interface VoucherIssuancePort
{
    /**
     * Submit trigger facts for voucher evaluation. Voucher decides issuance.
     * Payload shape TBD (UD-03).
     */
    public function submitTriggerFacts(array $facts): void;
}
```

---

## Rules

| Rule | Detail |
| ---- | ------ |
| CD-016 | No voucher policy in Allocation |
| Wave 1 | `NullVoucherIssuanceAdapter` — no-op |
| Owner | Voucher (spec08) owns issuance decisions |
