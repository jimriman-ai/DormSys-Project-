# Contract: Voucher Read (supplier) — code mirror

**Spec:** spec08 External Accommodation  
**Source:** `app/Modules/Voucher/Application/Contracts/VoucherReadContract.php`  
**Status:** Mirror of implemented interface (SGAP-04) — no new design

```php
interface VoucherReadContract
{
    public function getById(string $voucherId): ?VoucherReadProjection;

    public function findByCode(string $code): ?VoucherReadProjection;

    /**
     * @return list<VoucherReadProjection>
     */
    public function listForEmployee(string $employeeId, ?string $lifecycleState = null): array;
}
```

Projection DTO: `App\Modules\Voucher\Application\DTOs\VoucherReadProjection`.
