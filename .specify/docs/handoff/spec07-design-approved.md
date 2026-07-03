# spec07 Design Approval Record

**Recorded:** 2026-07-01  
**Authority:** Product / Tech governance  

**Checkpoint:** `spec07-design-review` = **PASS WITH CONDITIONS**  
**Tag:** `spec07-design-approved` (pending git tag at governance discretion)

---

## Decision

| Level | Status |
| ----- | ------ |
| **Design** | **Approved with conditions** |
| **Tasks** (`tasks.md`) | **Approved** — structural remediation applied |
| **Implementation** | **NOT authorized** |
| **Code** (migrations, module, tests) | **NOT authorized** |

**spec07 Allocation & Occupancy — Phase 0 design artifacts and task plan approved.**

Implementation requires separate **`spec07-implementation-authorization.md`** after Implementation Authorization review.

---

## Remediation conditions — disposition

| ID | Condition | Status |
| -- | --------- | ------ |
| **B-01** | Phase 0 design artifacts (`data-model.md`, `contracts/`) | **Resolved** — artifacts created under `specs/007-allocation-checkin/` |
| **B-02** | T046 `DormitoryReadContract` supplier mapping | **Resolved** — corrected to `App\Modules\Dormitory\Application\Contracts\DormitoryReadContract`; null stub until spec04; Request module port excluded for occupancy logic |
| **B-03** | Design Approval handoff artifact | **Resolved** — this document |
| **N-01** | MVP labeling consistency in `tasks.md` | **Resolved** — MVP = Phases 0–5 (T001–T052); Phases 6–8 Post-MVP |

---

## Pending conditions (non-blocking for Design Approval)

| ID | Item | Status |
| -- | ---- | ------ |
| UD-01 | Event payload contracts (Allocation / Dormitory / CheckIn) | Open |
| UD-02 | Reconciliation engine | Out of scope |
| UD-03 | Voucher input contract shape | Open |
| UD-07 | spec04 not implemented — live Dormitory ports | Runtime sequencing; stubs acceptable for Wave A |
| UD-10 | `RequestLifecycleCommandPort` payload | Open — stub acceptable Wave 1 |
| UD-11 | Employee `ActiveAllocationReadPort` wiring | Open — cross-spec integration follow-up |
| — | `spec07-implementation-authorization.md` | **Created** — Wave 1A T006–T052 ([`spec07-implementation-authorization.md`](./spec07-implementation-authorization.md)) |
| — | Git tag `spec07-design-approved` | Not applied |

---

## Design artifacts (approved)

| Artifact | Path |
| -------- | ---- |
| Architecture specification | `specs/007-allocation-checkin/spec.md` |
| Plan | `specs/007-allocation-checkin/plan.md` |
| Tasks | `specs/007-allocation-checkin/tasks.md` |
| Data model | `specs/007-allocation-checkin/data-model.md` |
| Contracts | `specs/007-allocation-checkin/contracts/` |
| Architecture freeze | `.specify/governance/freeze/architecture-freeze-spec07.md` |

---

## Design review gates

| Gate | Result |
| ---- | ------ |
| Architecture boundary (CD-014, CD-015) | ✅ PASS |
| Data ownership | ✅ PASS |
| Contract direction (read vs command) | ✅ PASS |
| Dormitory supplier mapping (R7) | ✅ PASS (post B-02) |
| Dependency direction | ✅ PASS |
| No architecture drift | ✅ PASS |

---

## Boundary decisions (frozen at design)

| Decision | Design resolution |
| -------- | ----------------- |
| CD-014 | Allocation owns assignment; Dormitory owns physical markers; signals via ports |
| CD-015 | CheckIn/CheckOut separate module; operational transitions only |
| R5 / R6 | Request + Lottery read-only upstream |
| R7 | `DormitoryReadContract` from spec04; `AllocationPhysicalStatePort` outbound |
| OA-05-03 | `RequestLifecycleCommandPort` producer stub; payload UD-10 |
| CD-016 | `VoucherIssuancePort` trigger facts only — null stub Wave 1 |

---

## MVP scope (authorized planning boundary)

| Scope | Task IDs |
| ----- | -------- |
| **MVP** | T001–T052 (Phases 0–5) — design artifacts, setup, foundational, US1–US3 |
| **Post-MVP** | T053–T074 (Phases 6–8) — CheckIn/CheckOut, downstream suppliers, polish |

---

## Protected status

| Scope | State |
| ----- | ----- |
| Architecture freeze | **APPROVED** — immutable |
| spec04 Dormitory implementation | **Not authorized** — Wave 2 runtime dependency |
| spec08–spec11 | **Not authorized** |
| Workflow | **Deferred** |

---

## References

- [`architecture-freeze-spec07.md`](../governance/freeze/architecture-freeze-spec07.md)
- [`catalog-decisions.md`](../catalog-decisions.md) CD-014, CD-015
- [`context-map.md`](../context-map.md) R5, R6, R7
- [`spec-catalog.md`](../spec-catalog.md) — spec07 Planned
