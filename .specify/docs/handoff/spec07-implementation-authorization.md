# spec07 Implementation Authorization

**Recorded:** 2026-07-01  
**Authority:** Product / Tech governance  

---

## Status

**Implementation Authorized** — **SUPERSEDED** (Wave 1A closed; program complete)

**Authorization status:** `superseded`  
**superseded-by:** [`.specify/docs/handoff/spec07-implementation-authorization-wave1b.md`](./spec07-implementation-authorization-wave1b.md)  
**Wave:** **Wave 1A** — MVP implementation (Phases 1–5 code; Phase 0 design complete) — **CLOSED**

---

## Baseline

### Design baseline

| Reference | Value |
| --------- | ----- |
| Handoff | [`spec07-design-approved.md`](./spec07-design-approved.md) |
| Tag | `spec07-design-approved` (pending git tag at governance discretion) |
| Architecture freeze | [`.specify/governance/freeze/architecture-freeze-spec07.md`](../../governance/freeze/architecture-freeze-spec07.md) — **APPROVED** |

### Task baseline

| Reference | Value |
| --------- | ----- |
| Commit | `a12e32cc7fa3d2e193c176d76b959a4d69668e97` |
| File | `specs/007-allocation-checkin/tasks.md` |

**Prior authorization:** [`spec07-design-approved.md`](./spec07-design-approved.md)

---

## Authorized Scope

**authorization-status:** `superseded`  
**authorized-scope:** Wave 1A — T006–T052  
**superseded-by:** [`.specify/docs/handoff/spec07-implementation-authorization-wave1b.md`](./spec07-implementation-authorization-wave1b.md)

Implementation is authorized **only** for:

| Scope | Detail |
| ----- | ------ |
| Specification tree | `specs/007-allocation-checkin/` (implementation alignment only — no redesign without change request) |
| Bounded contexts | **Allocation**, **CheckIn/CheckOut** (scaffold + foundational schema only — operational transitions excluded until Wave 1B) |
| Tasks | **T006–T052** only — per `tasks.md` |
| Migrations | `database/migrations/modules/allocation/`, `database/migrations/modules/check_in/` |
| Code | `app/Modules/Allocation/`, `app/Modules/CheckIn/` |
| Tests | `tests/Feature/Modules/Allocation/`, `tests/Unit/Modules/Allocation/`, `tests/Feature/Modules/CheckIn/` (foundational only), `tests/Architecture/` (Allocation boundary tests within T006–T052 scope only) |
| Adapters | Approved contracts only — `RequestReadContract`, `LotteryResultReadContract`, `ProposedAllocationPort` consumers; `DormitoryReadContract` consumer with `NullDormitoryReadAdapter`; `AllocationPhysicalStatePort` producer adapter with test double / null until spec04 live |

### Contract-only exception (not Dormitory implementation)

Wave 1A may materialize **interface-only** PHP contract files under `app/Modules/Dormitory/Application/Contracts/` when required to bind T046–T047 adapters, strictly mirroring:

- `specs/004-accommodation-resource/contracts/dormitory-read-service.md`
- `specs/004-accommodation-resource/contracts/allocation-physical-state-port.md`

This is **not** spec04 implementation authorization. No Dormitory persistence, domain logic, repositories, or Livewire UI.

---

## Explicitly Excluded Scope

| Excluded | Reason |
| -------- | ------ |
| **T053–T074** | Post-MVP — requires separate Implementation Authorization (Wave 1B+) |
| **spec04 Dormitory implementation** | Planning Authorized only — full bounded-context implementation not authorized |
| **spec08 Voucher implementation** | Not authorized |
| **spec11 Reporting** | Not authorized |
| **spec09 Notification** | Not authorized |
| **Reconciliation engine (UD-02)** | Out of scope per spec07 |
| **Workflow module** | Deferred per catalog |
| **Livewire / Operator UI** | Out of spec07 Wave 1A scope |
| **`AllocationReadContract` supplier (T061+)** | Post-MVP — Wave 1B |
| **`RequestLifecycleCommandPort` production handoff (T063+)** | Post-MVP — Wave 1B |
| **`CheckInCommandPort` operational implementation (T053+)** | Post-MVP — Wave 1B |

---

## Wave Definition

| Wave | Task IDs | Phases | Deliverable |
| ---- | -------- | ------ | ----------- |
| **Wave 1A (authorized)** | **T006–T052** | 1–5 | Assignable Allocation with upstream adapters + Dormitory signal path (stubs OK per UD-07); CheckIn module scaffold + foundational schema |
| Wave 1B (not authorized) | T053–T074 | 6–8 | CheckIn/CheckOut operations, downstream suppliers, polish |

**Phase 0 (T001–T005):** Design artifacts — **complete** before this authorization; no further Phase 0 execution required.

### Wave 1A phase map

| Phase | Task IDs | User story |
| ----- | -------- | ---------- |
| 1 — Setup | T006–T011 | Shared infrastructure |
| 2 — Foundational | T012–T028 | Blocking prerequisites |
| 3 — US1 | T029–T038 | Allocation assignment authority |
| 4 — US2 | T039–T045 | Upstream Request + Lottery |
| 5 — US3 | T046–T052 | Dormitory integration (stub path) |

---

## Preconditions

The following **must** be satisfied before code execution begins:

| ID | Precondition | Status |
| -- | ------------ | ------ |
| P-01 | Architecture freeze APPROVED | ✅ |
| P-02 | Design Approval with remediation PASS | ✅ [`spec07-design-approved.md`](./spec07-design-approved.md) |
| P-03 | Phase 0 design artifacts complete (T001–T005) | ✅ |
| P-04 | `data-model.md` and `contracts/` approved | ✅ |
| P-05 | CD-014 and CD-015 boundaries frozen | ✅ |
| P-06 | Implementation Authorization record exists | ✅ (this document) |

---

## Execution Rules

Implementation **MUST**:

- Start from **T006** (Phase 0 complete)
- Follow **`tasks.md`** order and phase structure within Wave 1A
- Complete tasks **sequentially** unless marked `[P]` with no ordering dependency
- Preserve approved design decisions (`data-model.md`, `contracts/`)
- Use Application contracts only for cross-module integration — no cross-module Eloquent
- Bind `NullDormitoryReadAdapter` and test doubles for `AllocationPhysicalStatePort` until spec04 is live (UD-07)
- Use `RecordsActivity` on Allocation models for audit interim (per plan AP-06)
- Create **ADR / change request** before any architectural deviation

**No task skipping within Wave 1A.**

```text
Implementation may begin only from tasks.md T006.
No task skipping within authorized wave.
No redesign during implementation without ADR/change request.
```

---

## Stop Conditions

Execution **MUST HALT** when:

| Condition | Action |
| --------- | ------ |
| Task ID outside **T006–T052** reached | HALT — Wave 1B not authorized |
| spec04 Dormitory domain implementation attempted | HALT — excluded scope |
| Voucher, Reporting, or Notification module work attempted | HALT — excluded scope |
| Reconciliation engine (UD-02) implementation attempted | HALT — out of scope |
| Cross-module Eloquent or direct foreign keys introduced | HALT — fix per coding-rules |
| Architecture boundary change required | HALT — ADR / change request required |
| `App\Modules\Request\Application\Contracts\DormitoryReadContract` used for R7 occupancy logic | HALT — forbidden per B-02 remediation |

Per `.specify/governance/execution-policy.md` § Pre-Execution Requirements: if this authorization record is missing or `authorization-status` is not `active`/`partial`, report:

> `Missing or invalid implementation authorization record.`

---

## Dependency Assumptions

| Dependency | Status at authorization | Wave 1A usage |
| ---------- | ----------------------- | ------------- |
| spec01 Foundation | Approved | Module scaffold, kernel, `HasUuid`, `RecordsActivity` |
| spec02 Identity | Frozen Wave 1A | Auth baseline |
| spec03 Employee | MVP + Wave 1B US2 | No Employee module changes; `ActiveAllocationReadPort` remains null (UD-11) |
| spec04 Dormitory | Planning Authorized — **impl not authorized** | Contract-only interfaces if needed; `NullDormitoryReadAdapter`; no live supplier |
| spec05 Request | Implementation Authorized | `RequestReadContract` consumer — read-only |
| spec06 Lottery | Planned — stubs live | `LotteryResultReadContract`, `ProposedAllocationPort` consumers |
| spec08 Voucher | Not authorized | Excluded |
| spec09 Notification | Not authorized | Excluded |
| spec11 Reporting | Not authorized | Excluded |

### Open dependencies (carried — stub path acceptable)

| ID | Item | Wave 1A handling |
| -- | ---- | ---------------- |
| UD-01 | Event payload contracts | Emit domain events per tasks; full serialization deferred |
| UD-07 | spec04 not live | Null / test-double adapters mandatory |
| UD-10 | `RequestLifecycleCommandPort` payload | Excluded from Wave 1A (T063+ Post-MVP) |
| UD-11 | Employee allocation read wiring | Deferred — `AllocationReadContract` is Post-MVP |

---

## Protected Boundaries

The implementation **MUST NOT**:

| Prohibited | Reason |
| ---------- | ------ |
| Change spec01 kernel | Platform foundation frozen |
| Change CD-014 / CD-015 | Closed catalog decisions |
| Change Architecture freeze record | Immutable for spec07 |
| Implement spec04 Dormitory bounded context | Not authorized |
| Implement CheckIn operational transitions (T053+) | Wave 1B — not authorized |
| Implement `AllocationReadContract` supplier (T061+) | Wave 1B — not authorized |
| Implement Voucher policy or issuance | spec08 — CD-016 owner |
| Implement Reporting projections | spec11 — CD-017 read-only consumer |
| Implement Notification delivery | spec09 — not authorized |
| Implement reconciliation (UD-02) | Out of scope |
| Modify `spec.md` or `plan.md` without change request | Architecture frozen |
| Cross-module Eloquent queries | Constitution / coding-rules |

---

## Boundary Rules

### Allocation owns (Wave 1A)

- Assignment authority (`Allocation`, `AllocationItem`)
- Person-level overlap prevention (BR-02)
- Upstream read consumption (Request, Lottery)
- Outbound physical-marker **signals** via `AllocationPhysicalStatePort` adapter

### Allocation does NOT own

| Concern | Owner |
| ------- | ----- |
| Physical bed markers | Dormitory (spec04) |
| Operational check-in/out | CheckIn/CheckOut (Wave 1B) |
| Request lifecycle state | Request (spec05) |
| Lottery rules | Lottery (spec06) |
| Voucher eligibility | Voucher (spec08) |

### CheckIn/CheckOut (Wave 1A scope)

- Module scaffold, migration path, foundational entities only
- **No** `CheckInCommandPort` operational implementation until Wave 1B

---

## Required Dependency Direction

### Allowed

```text
Allocation → RequestReadContract              (read-only)
Allocation → LotteryResultReadContract          (read-only)
Allocation → ProposedAllocationPort             (read-only consumer)
Allocation → DormitoryReadContract              (read — null stub)
Allocation → AllocationPhysicalStatePort        (produce signals — test double)
CheckIn      → (foundational schema only; no operational ports in Wave 1A)
```

### Forbidden

```text
Allocation → request_* / lottery_* / dormitory_* tables   (direct Eloquent / SQL)
Allocation → Request lifecycle mutation                  (Wave 1B only)
CheckIn    → assignment creation or modification         (CD-015)
Allocation → Request\DormitoryReadContract for R7        (B-02)
```

**Normative contracts:** `specs/007-allocation-checkin/contracts/`

---

## Protected Status (unchanged)

| Scope | State |
| ----- | ----- |
| spec04 Dormitory implementation | **Not authorized** |
| spec08–spec11 | **Not authorized** |
| Wave 1B (T053–T074) | **Not authorized** |
| Workflow | **Deferred** |
| Architecture freeze | **APPROVED — immutable** |

---

## Final Gate

After this authorization:

**Implementation may begin at T006.**

No spec07 code implementation was authorized before this document.

---

## References

- [`spec-catalog.md`](../spec-catalog.md) — spec07 Implementation Authorized
- [`spec07-design-approved.md`](./spec07-design-approved.md)
- [`spec07-implementation-state.md`](./spec07-implementation-state.md)
- [`architecture-freeze-spec07.md`](../../governance/freeze/architecture-freeze-spec07.md)
- [`catalog-decisions.md`](../catalog-decisions.md) CD-014, CD-015
- `specs/007-allocation-checkin/tasks.md` — T006–T052
