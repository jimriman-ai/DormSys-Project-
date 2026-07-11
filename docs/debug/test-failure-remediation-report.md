# Test Failure Remediation Report

**Date:** 2026-07-11  
**Scope:** Green baseline restoration after Spec04 Request→Dormitory live `siteExists` wiring  
**Command:** `php -d memory_limit=512M artisan test`  
**Final result:** **passed** (1755 passed, 4 skipped, 11 risky)

---

## 1. Failure summary (before remediation)

### Architecture / boundary (3 failures)

| Test | Signal |
| ---- | ------ |
| `CrossModuleAdapterLocationTest` | Unregistered cross-module adapter: `app/Modules/Request/Infrastructure/Adapters/DormitoryReadAdapter.php` imports `DormitoryStructureReadContract` |
| `ForbiddenImportsScanTest` | Same unregistered adapter finding |
| `ModuleBoundaryTest` (`Request` infrastructure isolated from `Dormitory`) | `App\Modules\Request\Infrastructure` must not use `App\Modules\Dormitory\*` |

### Runtime / domain validation (many cascading failures)

| Signal | Location |
| ------ | -------- |
| `RequestValidationException: Dormitory site does not exist.` | `SubmitRequestAction` (also Mission/Family create paths) |

Observed across Request, Allocation, Lottery, Mutation, and Production feature suites that invented dormitory UUIDs without persisting a Dormitory row.

---

## 2. Root cause analysis

Two related root causes:

### A. Boundary placement (architecture)

Spec04 Phase 4 correctly introduced **live** Request→Dormitory existence validation, but placed the adapter in:

`app/Modules/Request/Infrastructure/Adapters/DormitoryReadAdapter.php`

and bound it from `RequestServiceProvider`.

Repository policy (`docs/architecture/integration-layer-policy.md`, `CrossModuleAdapterLocationTest`) requires **new** cross-module port implementations under `app/Integrations/` with binding in `IntegrationServiceProvider`. Adding the path to `architectureLegacyCrossModuleAdapterPaths()` was rejected as non-compliant (new debt without architecture approval).

### B. Fixture drift (runtime)

Live `siteExists()` correctly returns false for non-existent sites. Feature tests still used `UuidGenerator::uuid7()` as dormitory IDs (valid under former `NullDormitoryReadAdapter`, which only checked UUID format). Domain validation was **intentional** and must be preserved; fixtures needed alignment.

Evidence: `.specify/docs/handoff/spec04-integration-implementation-review.md` § Risks — documents this as expected Phase 4 collateral.

---

## 3. Exact files changed and why

### Production / composition (architecture-compliant live edge)

| File | Change |
| ---- | ------ |
| `app/Integrations/Request/DormitoryReadBridge.php` | **Added** — implements `DormitoryReadContract` via `DormitoryStructureReadContract` |
| `app/Modules/Request/Infrastructure/Adapters/DormitoryReadAdapter.php` | **Deleted** — illegal module-local cross-module adapter |
| `app/Providers/IntegrationServiceProvider.php` | Bind `DormitoryReadContract` → `DormitoryReadBridge` |
| `app/Modules/Request/Infrastructure/Providers/RequestServiceProvider.php` | Remove cross-module binding (composition root owns it) |
| `tests/Architecture/architecture.php` | Register `DormitoryReadContract` in `architectureIntegrationPortClasses()` |
| `docs/architecture/integration-layer-policy.md` | Document bridge + binding |
| `docs/architecture/known-exceptions-registry.md` | Wording: integration ports (count no longer fixed at five) |

### Test fixtures (preserve validation)

| File | Change |
| ---- | ------ |
| `tests/Feature/Modules/Request/support/dormitory-site.php` | **Added** — `createDormitorySiteForRequestTests()` seeds a real `DormitoryModel` |
| `tests/Pest.php` | Load dormitory-site helper |
| `tests/Feature/Modules/Request/DormitoryReadIntegrationTest.php` | Assert bridge type; keep existence true/false cases |
| Request / Allocation / Lottery / Mutation / Production Feature tests + HTTP helpers | Replace invented dormitory UUIDs with seeded sites where submit/create validates existence |

`NullDormitoryReadAdapter` retained for explicit isolation overrides; not used as default binding.

---

## 4. Security impact assessment

| Control | Status |
| ------- | ------ |
| AuthN / AuthZ / mutation principal middleware | Unchanged |
| `siteExists` domain validation | **Preserved** (still enforced on submit / mission / family create) |
| No allow-all / bypass / swallowed exceptions | Confirmed |
| No test-only production backdoors | Confirmed |

Security posture is unchanged or strengthened: existence checks remain authoritative; tests now exercise the real path.

---

## 5. Architecture compliance assessment

| Rule | Outcome |
| ---- | ------- |
| New cross-module adapters under `app/Integrations/` | Compliant (`DormitoryReadBridge`) |
| Bind cross-module ports in `IntegrationServiceProvider` only | Compliant |
| No new legacy registry entries | Compliant (debt not expanded) |
| Module Infrastructure isolation (`Request` ↛ `Dormitory`) | Compliant |
| Domain validation not weakened | Compliant |

---

## 6. Verification evidence

### Targeted (architecture + primary Request/Allocation paths)

```text
php artisan test --filter="CrossModuleAdapterLocationTest|ForbiddenImportsScanTest|ModuleBoundaryTest|AllocationIntegrationBoundaryTest|DormitoryReadIntegration|PersonalRequestTest|MissionRequestTest|FamilyDirectSnapshot"
→ passed (exit 0)
```

### Full suite

```text
php -d memory_limit=512M artisan test
→ {"tool":"pest","result":"passed","tests":1759,"passed":1755,"assertions":4944,"skipped":4,"risky":11}
```

### Formatting

```text
php vendor/bin/pint --dirty
→ applied unused-import / style fixes on touched tests (and pre-dirty Dormitory files in working tree)
```

---

## 7. Residual risks / follow-ups

1. **Fixture helper uses `DormitoryModel` in Request Feature support** — matches existing Phase 4 integration test style; long-term may prefer a Dormitory Application mutation fixture API to avoid Infrastructure imports in consumer tests.
2. **Risky tests (11)** — pre-existing Pest “risky” classifications; not introduced by this remediation.
3. **`NullDormitoryReadAdapter`** — still available; ensure future tests that need UUID-only isolation bind it explicitly rather than inventing sites without intent.
4. **Prior occupy-on-assign gating** (`AssignmentOccupancyMarkerPolicy`) remains in tree from an earlier fix; orthogonal to this remediation.

---

## Decision summary

| Option considered | Verdict |
| ----------------- | ------- |
| Register adapter as legacy exception | Rejected — new debt without architecture approval |
| Weaken / remove `siteExists` | Rejected — security/domain regression |
| Skip failing tests | Rejected — fakes success |
| Relocate to Integrations + seed fixtures | **Chosen** — policy-compliant, smallest blast radius, preserves validation |
