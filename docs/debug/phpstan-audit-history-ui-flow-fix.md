# PHPStan Fix — AuditHistoryUiFlowTest

**Date:** 2026-07-12  
**Scope:** PHPStan level 8 only (`method.notFound` at line 174)  
**Command:** `php vendor/bin/phpstan analyse --no-progress`

---

## Root cause

`Mockery::mock(...)->shouldReceive('query')` is typed as:

`Mockery\ExpectationInterface|Mockery\HigherOrderMessage`

`once()` exists on `Mockery\Expectation` / concrete expectation types, but **not** on the union PHPStan sees for the chained return of `shouldReceive()`. Calling `->once()` on that union triggers:

`Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::once().` (`method.notFound`)

This is a static-analysis typing gap around Mockery’s fluent API, not a runtime test defect.

---

## Exact change

**File:** `tests/Feature/Modules/Audit/AuditHistoryUiFlowTest.php`

Replaced the ambiguous Mockery chain with the repo’s existing typed helper (`Tests\Support\MockeryTest`), which narrows `shouldReceive()` to `Mockery\Expectation` before `once()`:

```php
$historyRead = MockeryTest::mock(AuditHistoryReadContract::class);
MockeryTest::expectOnce($historyRead, 'query')
    ->andReturn(new PaginatedAuditHistoryDto(
        items: [],
        total: 0,
        page: 1,
        perPage: 50,
        lastPage: 1,
    ));
app()->instance(AuditHistoryReadContract::class, $historyRead);
```

Also added explicit imports for `AuditHistoryReadContract`, `PaginatedAuditHistoryDto`, and `MockeryTest` (same types/behavior as before).

---

## Why production behavior was unaffected

- Only the **test** file changed.
- Expectation remains: `query` is received **once** and returns an empty `PaginatedAuditHistoryDto`.
- No application/module code, PHPStan config, baseline, or Mockery analysis settings were modified.

---

## Validation result

```text
php vendor/bin/phpstan analyse --no-progress
{"tool":"phpstan","result":"passed","errors":0}
```

**Confirmed:** Found 0 errors.
