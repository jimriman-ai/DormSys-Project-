# Contract: External Lottery Winner Path — code mirror

**Spec:** spec08  
**Source:** `app/Modules/Voucher/Application/Contracts/ExternalLotteryWinnerPathContract.php`  
**Status:** Mirror of implemented interface (SGAP-04)

```php
interface ExternalLotteryWinnerPathContract
{
    public function processWinnerBatch(ExternalLotteryWinnerBatchDto $batch): ExternalLotteryWinnerBatchResultDto;
}
```
