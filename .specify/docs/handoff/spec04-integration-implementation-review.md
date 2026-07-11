# Spec04 Phase 4 Integration Implementation Review

## Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `ACCEPTED_FOR_PHASE_4_CLOSEOUT` |
| **Spec** | `004-accommodation-resource` |
| **Reviewed phase** | Spec04 Backend Implementation Phase 4 – Integration Implementation |
| **Review result** | `ACCEPTED_FOR_PHASE_4_CLOSEOUT` |
| **Decision date** | 2026-07-11 |

**Acceptance statement:**

Phase 4 implementation matches authorization.

The implementation is limited to Request-to-Dormitory existence validation.

The implementation may proceed to Spec04 Backend Closeout review.

This review accepts Phase 4 integration implementation only. It does **not** perform Spec04 Backend Closeout, reopen Phases 1–3C, authorize Allocation/CheckIn wiring, or authorize Request feature-suite remediation.

---

## Reviewed Implementation Files

| Path | Role |
| ---- | ---- |
| `app/Modules/Request/Infrastructure/Adapters/DormitoryReadAdapter.php` | Live adapter (created) |
| `app/Modules/Request/Infrastructure/Providers/RequestServiceProvider.php` | Binding swap Null → live adapter (modified) |
| `tests/Feature/Modules/Request/DormitoryReadIntegrationTest.php` | Narrow Phase 4 integration tests (created) |
| `app/Modules/Request/Infrastructure/Adapters/NullDormitoryReadAdapter.php` | Retained; unused by default binding (inspected) |
| `app/Modules/Request/Application/Contracts/DormitoryReadContract.php` | Consumer contract unchanged (inspected) |
| `app/Modules/Dormitory/Application/Contracts/DormitoryStructureReadContract.php` | Supplier read contract unchanged (inspected) |
| `app/Modules/Dormitory/Application/Services/DormitoryStructureReadService.php` | Public read service unchanged (inspected) |
| `app/Modules/Dormitory/Infrastructure/Providers/DormitoryServiceProvider.php` | Existing `DormitoryStructureReadContract` binding (inspected) |
| `bootstrap/providers.php` | Provider registration order (inspected) |

No Domain, Persistence, Mutation, Allocation, CheckIn, Workflow, route/controller/UI, or notification files were part of the Phase 4 delta.

---

## Reviewed Governance Artifacts

- `spec04-integration-implementation-contract.md`
- `spec04-integration-implementation-lock.md`
- `spec04-integration-implementation-execution-prompt.md`
- `spec04-integration-implementation-authorization.md` (`AUTHORIZED_FOR_PHASE_4_IMPLEMENTATION`)
- `spec04-integration-implementation-oq-resolution.md` (OQ-4-001 / OQ-4-003 / OQ-4-006 resolved for Request ↔ `getDormitoryDetail`)

---

## Scope Compliance Result

**PASS**

| Check | Result |
| ----- | ------ |
| Only Request → Dormitory existence validation | Confirmed |
| No Allocation logic | Confirmed |
| No Check-in logic | Confirmed |
| No workflow ownership | Confirmed |
| No UI / controller / route / notification changes | Confirmed |
| No unrelated refactor | Confirmed — provider change limited to binding target class |
| Deferred OQs not implemented (Allocation / CheckIn / events / external) | Confirmed |

Authorized mapping implemented:

`Request\Application\Contracts\DormitoryReadContract::siteExists`

→ `DormitoryStructureReadContract::getDormitoryDetail` nullability.

---

## Contract Compliance Result

**PASS**

| Check | Result |
| ----- | ------ |
| `DormitoryReadContract` unchanged | Confirmed (`siteExists(...): bool` only) |
| `DormitoryStructureReadContract` unchanged | Confirmed |
| `DormitoryStructureReadService` public contract unchanged | Confirmed |
| No new Dormitory Application Read method | Confirmed |
| No Dormitory Application Mutation contract change | Confirmed |
| No Domain / Persistence behavior change | Confirmed |

---

## Adapter Behavior Result

**PASS**

Inspected adapter:

```php
return $this->dormitories->getDormitoryDetail($dormitorySiteId) !== null;
```

| Check | Result |
| ----- | ------ |
| Detail present → `true` | Confirmed (code + integration test) |
| Detail `null` → `false` | Confirmed (code + integration test) |
| Does not use `listDormitories` | Confirmed |
| Does not use hierarchy list methods | Confirmed |
| No status / capacity / bed / hierarchy semantics | Confirmed — pure nullability mapping |
| Sync-only (OQ-4-002) | Confirmed — no events/jobs |
| Pass-through only (OQ-4-009) | Confirmed |

---

## Wiring Result

**PASS**

| Check | Result |
| ----- | ------ |
| `RequestServiceProvider` binds `DormitoryReadContract` → `DormitoryReadAdapter` | Confirmed |
| Adapter constructor receives `DormitoryStructureReadContract` | Confirmed |
| Supplier binding exists in `DormitoryServiceProvider` | Confirmed |
| DI conventions match existing singleton port/adapter pattern | Confirmed |
| `NullDormitoryReadAdapter` unused by default; still available for explicit test override | Confirmed |

Provider order note: `RequestServiceProvider` is registered before `DormitoryServiceProvider` in `bootstrap/providers.php`. This is **not** a defect — Laravel resolves constructor dependencies lazily on first `DormitoryReadContract` resolution, after all `register()` methods complete.

---

## Test Result

**PASS** (Phase 4 authorized suite)

### Phase 4 integration coverage

`tests/Feature/Modules/Request/DormitoryReadIntegrationTest.php` covers:

1. Container resolves live `DormitoryReadAdapter`
2. Existing dormitory row → `siteExists` = `true`
3. Missing dormitory id → `siteExists` = `false`

Tests are narrow and mapping-specific. No broad unrelated suite rewrites in the Phase 4 delta.

### Commands reviewed / re-run

Implementation report command (re-verified during this review):

```bash
php -d memory_limit=512M artisan test tests/Feature/Modules/Request/DormitoryReadIntegrationTest.php tests/Unit/Modules/Dormitory/Domain tests/Feature/Modules/Dormitory/Persistence tests/Feature/Modules/Dormitory/Application/Read tests/Feature/Modules/Dormitory/Application/Mutation
```

| Result | Value |
| ------ | ----- |
| Tests | 68 passed |
| Assertions | 159 |
| Exit code | 0 |
| Composition | 3 Phase 4 integration + Domain 31 + Persistence 11 + Read 14 + Mutation 9 |

Required lock regression baselines are preserved.

---

## Risks Or Follow-Up Notes

### 1. Expected Request create/submit behavior change (non-blocking for Phase 4)

Switching from `NullDormitoryReadAdapter` (UUID-format-only) to live existence validation is the **authorized** Phase 4 outcome.

Call sites that now depend on real dormitory rows:

- `SubmitRequestAction` — always validates `siteExists`
- `CreateMissionRequestAction` — validates on create
- `CreateFamilyDirectRequestAction` — validates on create

`CreatePersonalRequestAction` does **not** call `siteExists`; personal drafts may still be created with an arbitrary UUID, but **submit** fails if the dormitory does not exist.

### 2. Existing Request Feature tests may need seeding or stubbing (out of Phase 4 scope)

Many Request Feature tests still invent dormitory UUIDs without inserting a Dormitory persistence row. Spot-check during this review:

```bash
php -d memory_limit=512M artisan test tests/Feature/Modules/Request/PersonalRequestTest.php --filter="submits an eligible personal request"
```

Failed with: `Dormitory site does not exist.` (`SubmitRequestAction`).

This is **expected collateral** of the authorized live binding, not a Phase 4 adapter defect. Phase 4 authorization forbids broad unrelated test rewrites. Follow-up options (outside Phase 4 closeout gate):

- Seed a real dormitory in Request Feature helpers, or
- Explicitly bind `NullDormitoryReadAdapter` / a mock where isolation is intentional

### 3. Integration test seeds via Eloquent model

The Phase 4 test creates `DormitoryModel` rows directly for fixtures, matching existing Dormitory Read Feature test style. Acceptable for this slice; does not expose Eloquent through the Request Application contract.

### 4. No provider/container binding defect found

No namespace, import, or resolution issues observed for the authorized wiring.

---

## Final Review Decision

**`ACCEPTED_FOR_PHASE_4_CLOSEOUT`**

- Phase 4 implementation matches authorization.
- The implementation is limited to Request-to-Dormitory existence validation.
- The implementation may proceed to Spec04 Backend Closeout review.

### Blocking issues

**None.**

### Closeout allowance

**Yes** — Spec04 Backend Closeout review may proceed from this Phase 4 acceptance. Request Feature-suite seeding/stubbing remediation remains a separate follow-up and does not block Phase 4 acceptance.

---

## References

- `spec04-integration-implementation-contract.md`
- `spec04-integration-implementation-lock.md`
- `spec04-integration-implementation-execution-prompt.md`
- `spec04-integration-implementation-authorization.md`
- `spec04-integration-implementation-oq-resolution.md`
- `spec04-integration-implementation-governance-review.md`
