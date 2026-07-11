# PHPStan Level 8 Remediation Report

**Date:** 2026-07-11  
**Status:** `PHPSTAN_REMEDIATION_COMPLETE`  
**Scope:** Quality remediation only — no feature, architecture, schema, or business-rule changes.

---

## 1. Original errors

From `php vendor/bin/phpstan analyse --no-progress` (16 findings):

| Group | Location | Issue |
|-------|----------|--------|
| A | `DormitoryStructureReadRepository` (`listDormitories`, `listBuildingsByDormitoryId`, `listFloorsByBuildingId`, `listRoomsByFloorId`, `listBedsByRoomId`) | Declared `list<DTO>`; PHPStan saw `array<int, DTO>` from `Collection::all()` |
| B | `DormitoryStructureWriteRepository::listBedsByRoomId` | Declared `list<array{…}>`; PHPStan saw `array<int, array{…}>` |
| C | `DormitoryStructureMutationTest` | Missing iterable value type on `seedMutationHierarchy()`; `findOrFail()` typed as `Model\|Collection` → undefined property access |
| D | `DormitoryReadIntegrationTest` | Redundant `toBeInstanceOf()` (Peststan already narrows container resolve to `DormitoryReadBridge`) |
| E | `DormitoryTest` | `method_exists()` on instance always true/false for known methods |

No group indicated an architecture or contract defect.

---

## 2. Root cause per group

### A / B — Repository `list<>` vs `array<int, …>`

Implementations already return sequential Eloquent-mapped arrays. `Illuminate\Support\Collection::all()` is stubbed as `array<TKey, TValue>`, not `list<TValue>`, so PHPStan cannot prove list shape. Normalization with `array_values(...)` matches the existing Reporting repository pattern and is behavior-identical for 0-based sequential keys.

### C — Mutation feature test types

Helper lacked a shaped `@return` array PHPDoc. Chained `findOrFail()->property` hit Eloquent’s `Model|Collection` union. Fixes are annotations + local typed locals only.

### D — Integration test

Peststan already types `app(DormitoryReadContract::class)` as `DormitoryReadBridge`, so `toBeInstanceOf(DormitoryReadBridge::class)` (and later `DormitoryReadContract`) was redundant. Assertion replaced with concrete `::class` equality.

### E — Domain unit test

`method_exists($instance, …)` is statically decided. Same design checks via `ReflectionClass::hasMethod()` preserve intent without impossible-type warnings.

---

## 3. Files changed

| File | Change |
|------|--------|
| `app/Modules/Dormitory/Infrastructure/Repositories/DormitoryStructureReadRepository.php` | Wrap mapped `->all()` with `array_values()` |
| `app/Modules/Dormitory/Infrastructure/Repositories/DormitoryStructureWriteRepository.php` | Same + mirror contract `@return list<…>` PHPDoc |
| `tests/Feature/Modules/Dormitory/Application/Mutation/DormitoryStructureMutationTest.php` | Shaped return PHPDoc; typed locals after `findOrFail` |
| `tests/Feature/Modules/Request/DormitoryReadIntegrationTest.php` | Class-name assertion instead of redundant `toBeInstanceOf` |
| `tests/Unit/Modules/Dormitory/Domain/DormitoryTest.php` | `ReflectionClass::hasMethod` instead of `method_exists` |

Unrelated: `php vendor/bin/pint --dirty` also touched `phpstan-bootstrap.php` (FQCN import style from prior severe-error work). No `phpstan.neon` ignore rules added.

---

## 4. Confirmation: no behavior change

- `array_values` on already sequential Eloquent result sets does not reorder or alter payloads.
- Test changes only improve static typing / assertion form; runtime expectations unchanged.
- No domain rules, contracts, schemas, or module boundaries modified.

---

## 5. PHPStan result

```text
php vendor/bin/phpstan analyse --no-progress --memory-limit=1G
```

**Result:** passed — **0 errors** (exit 0).

(Also verified via `php vendor/phpstan/phpstan/phpstan.phar analyse --no-progress --memory-limit=1G`.)

---

## 6. Test result

| Command | Result |
|---------|--------|
| `php artisan test --filter=DormitoryStructure` | **23 passed** (72 assertions) |
| `php artisan test --filter=DormitoryReadIntegration` | **3 passed** (3 assertions) |
| `php artisan test --filter=DormitoryTest` | **3 passed** (7 assertions) |

```text
php vendor/bin/pint --dirty
```

**Result:** exit 0 (formatting applied where needed).

---

## Verdict

**PHPSTAN_REMEDIATION_COMPLETE**
