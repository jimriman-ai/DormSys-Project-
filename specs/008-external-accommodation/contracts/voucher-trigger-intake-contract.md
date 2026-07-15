# Contract: Voucher Trigger Intake — code mirror

**Spec:** spec08  
**Source:** `app/Modules/Voucher/Application/Contracts/VoucherTriggerIntakeContract.php`  
**Status:** Mirror of implemented interface (SGAP-04)

```php
interface VoucherTriggerIntakeContract
{
    public function accept(InboundTriggerFactsDto $facts): VoucherIssuanceTrigger;
}
```

DTO: `App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto`.
