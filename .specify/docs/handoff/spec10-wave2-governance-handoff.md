# spec10 Wave 2 Governance Handoff

**Recorded:** 2026-07-02  
**Authority:** Product / Tech governance  
**Decision class:** Governance handoff (non-operational)

---

## Document classification

| Property | Value |
| -------- | ----- |
| **Handoff status** | **SUPERSEDED** — Wave 2 **CLOSED** |
| **Grants Implementation Authorization** | **No** (historical handoff) |
| **Active authorization** | **NONE** — see [`spec10-wave2-implementation-closure.md`](./spec10-wave2-implementation-closure.md) |

This document recorded **readiness for Wave 2 authorization**. Execution authority is now granted by the active Wave 2 Implementation Authorization record.

---

## Current state (post-closure)

| Item | State |
| ---- | ----- |
| Wave 1A / 1B / 2 | **CLOSED** |
| Wave 2 governance review | **PASS WITH CONDITIONS** (historical) |
| Wave 2 closure | **RECORDED** — [`spec10-wave2-implementation-closure.md`](./spec10-wave2-implementation-closure.md) |
| Active execution scope | **NONE** |
| Wave 2 authorization | **REVOKED** — [`spec10-implementation-authorization-wave2.md`](./spec10-implementation-authorization-wave2.md) |
| Next handoff | [`spec10-wave3-governance-handoff.md`](./spec10-wave3-governance-handoff.md) |
| spec10 feature lifecycle | **AUTHORIZED** (feature level only) |

---

## Next candidate scope

| Field | Value |
| ----- | ----- |
| **Wave name** | **Wave 2 — Upstream Integration (M1)** |
| **Task range** | **T028–T032** |
| **Phase** | Phase 5 — Initial upstream integration slice |
| **Exit checkpoint** | **CP-A4.1** — Integration slice PASS |
| **Governance review** | **PASS WITH CONDITIONS** — [`spec10-wave2-governance-review.md`](./spec10-wave2-governance-review.md) |

### Task summary

| Task | Purpose |
| ---- | ------- |
| T028 | Identity — emit audit entries via `AuditRecordingContract` from Application actions |
| T029 | Voucher — adapter mapping lifecycle transitions to `AuditEntryDto` |
| T030 | `IdentityAuditIntegrationTest` — real Identity adapter verification |
| T031 | `VoucherAuditIntegrationTest` — real Voucher adapter verification |
| T032 | Producer test-double — R10 path without Audit importing producer Infrastructure |

---

## Dependency status

| Prerequisite | Status |
| ------------ | ------ |
| Wave 1A + 1B complete (T001–T027) | ✅ **SATISFIED** |
| CP-A4 PASS | ✅ **SATISFIED** |
| `AuditRecordingContract` operational | ✅ **SATISFIED** |
| `AuditBoundaryTest` operational | ✅ **SATISFIED** |
| Wave 1B closure recorded | ✅ **SATISFIED** |

---

## Readiness assessment

| Gate | Status |
| ---- | ------ |
| **Readiness status** | **READY_FOR_AUTHORIZATION_ACTIVATION** |
| **Governance decision** | **PASS WITH CONDITIONS** |
| **Blockers** | **none** |
| **Implementation permission (T028–T032)** | **YES** — active authorization issued 2026-07-02 |

---

## Mandatory conditions for next authorization record

The separate **`spec10-implementation-authorization-wave2.md`** (or equivalent) **must** embed:

### Scope containment

| Constraint | Requirement |
| ---------- | ----------- |
| **Authorized scope** | **T028–T032 only** |
| **Adapter-only** | Upstream modules invoke `AuditRecordingContract`; facts-only `AuditEntryDto` mapping |
| **HALT beyond T032** | T033+ requires separate authorization |
| **No bridge** | T037 blocked; `activity_bridge_enabled` must not be activated |
| **No retention** | T033–T036 blocked |
| **No UI** | OA-10-05 deferred |
| **No notification audit** | R-08 deferred |
| **No M4 wiring** | Request/Lottery/Allocation/CheckIn producer wiring **prohibited** |

### Closed-program protection

| Module | Policy |
| ------ | ------ |
| **Identity (spec02)** | Adapter-only changes; **HALT** on lifecycle/domain logic changes outside audit emission |
| **Voucher (spec08 CLOSED)** | Adapter-only seam; **HALT** on voucher lifecycle logic changes |
| **spec07 / spec09** | **No modification** |

### Identity action allow-list (T028)

Authorized audit emission touch points:

| Action | Event types (examples) |
| ------ | ---------------------- |
| `AssignRoleToUserAction` | `identity.role_changed` |
| `RevokeRoleFromUserAction` | `identity.role_changed` |
| `CreateUserAction` | user lifecycle (per contract vocabulary) |
| `DeactivateUserAction` | user lifecycle (per contract vocabulary) |

**Correlation convention:** per [`contracts/audit-entry-dto.md`](../../specs/010-audit-trail/contracts/audit-entry-dto.md) — `{sourceContext}:{entityType}:{entityId}:{eventType}:{outcomeToken}`

**Actor attribution:** authenticated principal UUID for user actions; system tokens per `system-actor-tokens.md` where applicable.

### File allow-list (adapter touch points only)

| Area | Permitted paths |
| ---- | ---------------- |
| Identity Application | `app/Modules/Identity/Application/Services/CreateUserAction.php` |
| | `app/Modules/Identity/Application/Services/DeactivateUserAction.php` |
| | `app/Modules/Identity/Application/Services/AssignRoleToUserAction.php` |
| | `app/Modules/Identity/Application/Services/RevokeRoleFromUserAction.php` |
| | `app/Modules/Identity/Application/` — **new adapter/helper classes only** if required for DTO mapping |
| Voucher Application | `app/Modules/Voucher/Application/` — **adapter classes only** (e.g. audit recording adapter) |
| Voucher Infrastructure | `app/Modules/Voucher/Infrastructure/Adapters/` — **if adapter placed here instead** |
| Audit tests | `tests/Feature/Modules/Audit/IdentityAuditIntegrationTest.php` |
| | `tests/Feature/Modules/Audit/VoucherAuditIntegrationTest.php` |
| | `tests/Feature/Modules/Audit/` — T032 producer-double test only |
| Audit module | `app/Modules/Audit/` — **no upstream imports**; minimal changes only if required for contract compliance |

**Prohibited paths:** Request, Lottery, Allocation, CheckIn, Notification module changes; voucher lifecycle action logic changes beyond audit adapter invocation.

### Verification requirements (exit gate)

| Gate | Mandatory evidence |
| ---- | ------------------ |
| **CP-A4.1** | T030 **and** T031 PASS (real adapter verification — **required**) |
| | T032 PASS (test-double — **required but not sufficient alone**) |
| | `AuditBoundaryTest` PASS (R10 preserved) |
| | Wave 1A/1B audit tests remain green |
| | No Audit upstream Infrastructure imports introduced |

### Recorded M1 assumption

Interim **dual-path** with existing Spatie `activity_log` is accepted for Wave 2 (plan M1). No deduplication guarantee across stores until M3. Bridge (T037) remains **inactive**.

---

## Explicitly excluded from Wave 2 handoff

| Scope | Status |
| ----- | ------ |
| **T033–T036** Retention/archive | Separate Wave 3A governance + authorization |
| **T037–T038** Bridge + `config/audit.php` | Separate Wave 3B governance + authorization |
| **T039–T040** PHPStan/Pint closeout | Separate Wave 3C governance + authorization |
| spec11 Reporting | Not authorized |

---

## Execution path (activated)

1. ~~Wave 1B closure recorded~~ ✅ **COMPLETE**
2. ~~Governance review for T028–T032~~ ✅ **PASS WITH CONDITIONS**
3. ~~Issue **`spec10-implementation-authorization-wave2.md`**~~ ✅ **ACTIVE**
4. `active-execution-scope: T028–T032` ✅ **SET**
5. **HALT** at **T032 + CP-A4.1 PASS** unless superseding authorization issued

```text
Entry point: T028
Exit point: T032 + CP-A4.1 PASS
active-execution-scope: T028–T032
blocked-scope: T033–T040
next_stop_boundary: T032
Implementation permission: YES (Wave 2 only)
```

---

## References

- [`spec10-wave1b-implementation-closure.md`](./spec10-wave1b-implementation-closure.md)
- [`spec10-wave2-governance-review.md`](./spec10-wave2-governance-review.md)
- [`spec10-implementation-authorization-wave1b.md`](./spec10-implementation-authorization-wave1b.md)
- `specs/010-audit-trail/tasks.md` — Wave 2 batch (T028–T032)
- [`context-map.md`](../context-map.md) R10

---

**End of governance handoff record.**
