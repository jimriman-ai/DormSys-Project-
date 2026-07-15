# Contract: Voucher Lifecycle — code mirror

**Spec:** spec08  
**Source:** `app/Modules/Voucher/Application/Contracts/VoucherLifecycleContract.php`  
**Status:** Mirror of implemented interface (SGAP-04)

```php
interface VoucherLifecycleContract
{
    public function expire(VoucherId $voucherId, DateTimeImmutable $asOf): Voucher;

    public function archive(VoucherId $voucherId, DateTimeImmutable $archivedAt): Voucher;

    public function cancel(VoucherId $voucherId, DateTimeImmutable $occurredAt): Voucher;

    public function supersede(VoucherId $voucherId, DateTimeImmutable $occurredAt): Voucher;
}
```
