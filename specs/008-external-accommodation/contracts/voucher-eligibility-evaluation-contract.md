# Contract: Voucher Eligibility Evaluation — code mirror

**Spec:** spec08  
**Source:** `app/Modules/Voucher/Application/Contracts/VoucherEligibilityEvaluationContract.php`  
**Status:** Mirror of implemented interface (SGAP-04)

```php
interface VoucherEligibilityEvaluationContract
{
    public function evaluateForTrigger(TriggerId $triggerId): VoucherEligibilityOutcome;
}
```
