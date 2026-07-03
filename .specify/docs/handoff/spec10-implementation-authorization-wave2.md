# spec10 Implementation Authorization — Wave 2

**Recorded:** 2026-07-02  
**Authority:** Product / Tech governance  

---

## Authorization Header

| Field | Value |
| ----- | ----- |
| **Spec** | spec10 — Audit Trail & Traceability |
| **Feature** | audit-trail |
| **Wave** | **Wave 2 — Upstream Integration (M1)** |
| **Authorized Scope** | **T028–T032** only |
| **Authorization Type** | Controlled Continuation Implementation Authorization |
| **authorization-status** | `revoked` |
| **Authorization Status** | **CLOSED** |
| **execution-state** | `CLOSED` |
| **authorized-by** | Governance Review / Activation |
| **closure-date** | 2026-07-02 |
| **revocation-reason** | Wave 2 program closure — executable scope exhausted (T028–T032 complete; CP-A4.1 PASS) |
| **effective-date** | 2026-07-02 |
| **supersedes** | [`.specify/docs/handoff/spec10-implementation-authorization-wave1b.md`](./spec10-implementation-authorization-wave1b.md) *(active execution authority only)* |
| **superseded-by** | — |
| **lifecycle-reference** | `.specify/governance/_meta/authority-model.md` §4–§5 |
| **Governance review** | [`spec10-wave2-governance-review.md`](./spec10-wave2-governance-review.md) — **PASS WITH CONDITIONS** |
| **Authorization basis** | Wave 2 governance approval + handoff readiness |
| **Predecessor waves** | Wave 1A **CLOSED** (T001–T021); Wave 1B **CLOSED** (T022–T027) |
| **Task baseline** | `specs/010-audit-trail/tasks.md` |

**Normative scope fields**

```text
authorization-status: revoked
execution-state: CLOSED
authorized-scope: Wave 2 — T028–T032 (complete)
active-execution-scope: none
executable-forward-scope: —
blocked-scope: T033–T040
completed-scope: T001–T032
entry-point: T028 (historical)
exit-point: T032 + CP-A4.1 PASS (satisfied)
exit-gate: CP-A4.1 PASS
next_stop_boundary: T032 (satisfied)
current_authorization: none (Wave 2 closed)
closure-record: spec10-wave2-implementation-closure.md
next-candidate-scope: Wave 3 — T033–T040
authority-constraints: program closed; no forward implementation under Wave 2; T033+ requires separate authorization
```

---

## Status

**Implementation Authorized** — **CLOSED** (Wave 2 complete; program scope T028–T032 exhausted)

**Authorization status:** `revoked`  
**Closure record:** [`spec10-wave2-implementation-closure.md`](./spec10-wave2-implementation-closure.md)  
**Wave:** **Wave 2** — Upstream Integration (M1) — **CLOSED**

This record historically granted controlled execution authority for the **initial upstream integration slice (M1)**. Forward execution under Wave 2 is **terminated**.

**This record does NOT authorize:**

- T001–T027 (historical — **CLOSED**; no reopening)
- **T033–T040** (retention, bridge, config formalization, program closeout)
- Presentation UI (OA-10-05)
- Notification delivery audit (R-08)
- `ActivityLogAuditBridge` (T037) or `activity_bridge_enabled` activation
- Retention archive job / `config/audit.php` (T033–T038)
- Request / Lottery / Allocation / CheckIn / Notification producer wiring (M4 deferred)
- Modification of closed programs (**spec07**, **spec09**) beyond read-only conformity
- Voucher (**spec08**) lifecycle logic changes beyond adapter audit emission

---

## Wave 2 Intent Boundary

| Intent | Tasks |
| ------ | ----- |
| Identity audit producer adapters | T028 |
| Voucher audit producer adapter | T029 |
| Identity integration verification | T030 |
| Voucher integration verification | T031 |
| R10 producer-double verification | T032 |
| **Exit** | **CP-A4.1 PASS** after T032 |

**Migration phase:** M1 — explicit `AuditRecordingContract` in critical Application Actions. Interim dual-path with Spatie `activity_log` is **accepted**; no deduplication guarantee until M3.

---

## Authorized Scope

### Task map

| Task | Authorized work |
| ---- | ---------------- |
| T028 | Identity — emit audit entries via `AuditRecordingContract` from allowed Application actions |
| T029 | Voucher — adapter mapping lifecycle transitions to `AuditEntryDto` |
| T030 | `tests/Feature/Modules/Audit/IdentityAuditIntegrationTest.php` |
| T031 | `tests/Feature/Modules/Audit/VoucherAuditIntegrationTest.php` |
| T032 | Producer test-double — R10 path without Audit importing producer Infrastructure |

### Identity action allow-list (T028)

| Action | Event types |
| ------ | ----------- |
| `AssignRoleToUserAction` | `identity.role_changed` |
| `RevokeRoleFromUserAction` | `identity.role_changed` |
| `CreateUserAction` | user lifecycle (contract vocabulary) |
| `DeactivateUserAction` | user lifecycle (contract vocabulary) |

**Correlation convention:** [`contracts/audit-entry-dto.md`](../../specs/010-audit-trail/contracts/audit-entry-dto.md) — `{sourceContext}:{entityType}:{entityId}:{eventType}:{outcomeToken}`

**Actor attribution:** authenticated principal UUID for user actions; system tokens per `system-actor-tokens.md` where applicable.

### File allow-list (adapter touch points only)

| Area | Permitted paths |
| ---- | ---------------- |
| Identity Application | `app/Modules/Identity/Application/Services/CreateUserAction.php` |
| | `app/Modules/Identity/Application/Services/DeactivateUserAction.php` |
| | `app/Modules/Identity/Application/Services/AssignRoleToUserAction.php` |
| | `app/Modules/Identity/Application/Services/RevokeRoleFromUserAction.php` |
| | `app/Modules/Identity/Application/` — **new adapter/helper classes only** |
| Voucher Application | `app/Modules/Voucher/Application/` — **adapter classes only** |
| Voucher Infrastructure | `app/Modules/Voucher/Infrastructure/Adapters/` — if adapter placed here |
| Audit tests | `tests/Feature/Modules/Audit/IdentityAuditIntegrationTest.php` |
| | `tests/Feature/Modules/Audit/VoucherAuditIntegrationTest.php` |
| | `tests/Feature/Modules/Audit/` — T032 producer-double test only |
| Audit module | `app/Modules/Audit/` — **no upstream imports**; minimal contract-compliance fixes only |

**Prohibited paths:** Request, Lottery, Allocation, CheckIn, Notification modules; voucher lifecycle action logic changes beyond audit adapter invocation.

---

## Blocked Scope (Hard Enforcement)

| Scope | Status |
| ----- | ------ |
| **T001–T027** | **CLOSED** — historical; no reopening |
| **T033–T040** | **BLOCKED** — separate authorization required |
| UI / presentation (OA-10-05) | **BLOCKED** |
| Notification audit (R-08) | **BLOCKED** |
| Retention / archive (T033–T036) | **BLOCKED** |
| RecordsActivity bridge (T037) | **BLOCKED** — must remain inactive |
| Formal `config/audit.php` (T038) | **BLOCKED** |
| PHPStan/Pint program closeout (T039–T040) | **BLOCKED** |
| Upstream domain logic changes outside adapter seams | **BLOCKED** |
| spec07 / spec09 modification | **BLOCKED** |

---

## Execution Constraints (Hard)

| Constraint | Enforcement |
| ---------- | ----------- |
| **Adapter-only** | Identity/Voucher invoke `AuditRecordingContract` only; facts-only DTO mapping |
| **R10 frozen** | Audit module remains **downstream-only** |
| **No upstream Infrastructure imports in Audit** | `AuditBoundaryTest` must remain PASS |
| **AP-06 append-only** | No audit content UPDATE/DELETE semantics |
| **After-commit (production)** | Existing `RecordAuditAction` after-commit rule preserved |
| **Closed-program protection** | Voucher adapter-only; **HALT** on lifecycle logic changes |
| **No cross-domain lifecycle mutation** | Audit emission only; no upstream state changes via Audit |
| **HALT boundary** | **T032** — T033+ requires separate authorization |

**Do NOT:**

- execute tasks **T033–T040**
- modify files outside the allow-list
- implement bridge, retention, or UI
- wire Request/Lottery/Allocation/CheckIn producers
- reopen Wave 1A or Wave 1B scope

**Stop conditions**

| Condition | Action |
| --------- | ------ |
| Forward work outside **T028–T032** | **HALT** |
| Change outside file allow-list | **HALT** |
| R10 or AP-06 violation | **HALT** |
| Voucher/Identity lifecycle logic change beyond audit emission | **HALT** |

---

## Exit Conditions (Wave 2 Completion)

Wave 2 is **COMPLETE** only when:

| Gate | Criterion |
| ---- | --------- |
| **G-01** | T028–T032 marked complete per `tasks.md` |
| **G-02** | **T030** and **T031** PASS (real adapter verification — **required**) |
| **G-03** | **T032** PASS (test-double — required but **not sufficient alone**) |
| **G-04** | `AuditBoundaryTest` PASS |
| **G-05** | Wave 1A/1B audit tests remain green |
| **G-06** | No scope drift beyond T032 |
| **G-07** | **CP-A4.1** recorded PASS |

---

## Authority Transition

| Item | State |
| ---- | ----- |
| **Wave 1A** | **CLOSED** — T001–T021 |
| **Wave 1B** | **CLOSED** — T022–T027 |
| **Wave 2 (this record)** | **CLOSED** — T028–T032 |
| **T033–T040** | **NOT AUTHORIZED** |
| spec07 / spec09 | **CLOSED** — no reopening |
| spec08 Voucher | **CLOSED** — Wave 2 adapter accepted; no further modification without authorization |

Upon **CP-A4.1 PASS** and T032 completion (satisfied 2026-07-02):

- Wave 2 authorization transitions to **`revoked`**
- Forward execution beyond T032 requires a **separate** Wave 3 Implementation Authorization record
- Next governance handoff: [`spec10-wave3-governance-handoff.md`](./spec10-wave3-governance-handoff.md)

---

## Final Execution Directive

**Wave 2 is CLOSED.** No forward execution is permitted under this record.

Historical directive (satisfied):

1. ~~Verify this record is **`active`** and `active-execution-scope` is **T028–T032**~~
2. ~~Execute **T028** → **T032** in dependency order per `tasks.md`~~
3. ~~**STOP** at **T032 + CP-A4.1 PASS**~~ ✅ **SATISFIED**

```text
Wave 2 status: CLOSED
active-execution-scope: none
blocked-scope: T033–T040
next-candidate-scope: Wave 3 — T033–T040
HALT on T033+ without Wave 3 Implementation Authorization.
```

Per `.specify/governance/execution-policy.md` § Pre-Execution Requirements: if this record is missing, revoked, or superseded, report:

> `Missing or invalid implementation authorization record.`

---

## References

- [`spec10-wave2-governance-review.md`](./spec10-wave2-governance-review.md)
- [`spec10-wave2-governance-handoff.md`](./spec10-wave2-governance-handoff.md)
- [`spec10-wave1b-implementation-closure.md`](./spec10-wave1b-implementation-closure.md)
- [`spec10-wave1a-implementation-closure.md`](./spec10-wave1a-implementation-closure.md)
- [`spec10-implementation-authorization-wave1b.md`](./spec10-implementation-authorization-wave1b.md)
- [`context-map.md`](../context-map.md) R10
- `specs/010-audit-trail/tasks.md`
- `.specify/governance/execution-policy.md`

---
- [`spec10-wave2-implementation-closure.md`](./spec10-wave2-implementation-closure.md)
- [`spec10-wave3-governance-handoff.md`](./spec10-wave3-governance-handoff.md)
