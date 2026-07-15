# Contract: Reserve Promotion Path — code mirror

**Spec:** spec08  
**Source:** `app/Modules/Voucher/Application/Contracts/ReservePromotionPathContract.php`  
**Status:** Mirror of implemented interface (SGAP-04)

```php
interface ReservePromotionPathContract
{
    public function processPromotion(ReservePromotionTriggerFactsDto $facts): ReservePromotionResultDto;
}
```
