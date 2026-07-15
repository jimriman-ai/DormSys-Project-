# Contract: Accommodation Classification Read Port — code mirror

**Spec:** spec08  
**Source:** `app/Modules/Voucher/Application/Contracts/AccommodationClassificationReadPort.php`  
**Status:** Mirror of implemented interface (SGAP-04)

```php
interface AccommodationClassificationReadPort
{
    public function getClassification(string $dormitoryId): ?AccommodationClassification;
}
```

Enum: `App\Modules\Voucher\Domain\Enums\AccommodationClassification` (`external` | `internal`).  
Adapters in-repo: `StubAccommodationClassificationReadAdapter`, `InMemoryAccommodationClassificationReadAdapter`.
