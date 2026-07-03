# Wave 1B Implementation Authorization — spec07

**Recorded:** 2026-07-01  
**Authority:** Product / Tech governance  

---

## Authorization Header

| Field | Value |
| ----- | ----- |
| **Spec** | spec07 — Allocation & Occupancy |
| **Wave** | Wave 1B |
| **Authorized Scope** | T053–T074 |
| **Authorization Type** | Retroactive Acceptance + Completion Authorization |
| **authorization-status** | `revoked` |
| **Authorization Status** | **CLOSED** |
| **authorized-by** | Governance Review |
| **closure-date** | 2026-07-01 |
| **revocation-reason** | Program closure — executable scope exhausted (T072–T074 complete) |
| **Effective Date** | Immediate upon issuance |
| **effective-date** | 2026-07-01 |
| **supersedes** | [`.specify/docs/handoff/spec07-implementation-authorization.md`](./spec07-implementation-authorization.md) (Wave 1A — active execution authority only) |
| **superseded-by** | — |
| **lifecycle-reference** | `.specify/governance/_meta/authority-model.md` §4–§5 |
| **Design baseline** | [`spec07-design-approved.md`](./spec07-design-approved.md) |
| **Architecture freeze** | [`.specify/governance/freeze/architecture-freeze-spec07.md`](../../governance/freeze/architecture-freeze-spec07.md) — **APPROVED** (immutable) |
| **Task baseline** | `specs/007-allocation-checkin/tasks.md` |

**Normative scope fields**

```text
authorization-status: revoked
authorized-scope: Wave 1B — T053–T074 (complete)
executable-forward-scope: —
retroactive-acceptance-scope: Wave 1B — T053–T071 (accepted)
blocked-scope: —
active-execution-scope: none
authority-constraints: program closed; no forward implementation permitted; cannot authorize spec08+; cannot reopen T006–T074 without new authorization record
```

---

## Governance Disposition

**Selected Disposition:** **RETROACTIVE_ACCEPTANCE**

Repository reconciliation confirmed that Wave 1B implementation artifacts for **T053–T071** exist and align with approved task decomposition, approved design baseline, and established spec07 boundaries.

These artifacts are formally accepted as **design-conformant implementation delivered prior to Wave 1B authorization**.

This authorization resolves the previously identified governance condition:

> **PARTIAL_UNAUTHORIZED_IMPLEMENTATION**

by bringing the existing implementation into an explicit, governed execution baseline for final completion only.

---

## Authority Transition

- **Wave 1A (T006–T052):** remains historically **CLOSED** and valid as completed prior authorized scope
- **Wave 1B (T053–T074):** becomes the **active execution authority** for the remaining spec07 work
- This record does **not** reopen Wave 1A
- This record does **not** authorize any work outside T053–T074

| Prior state | Transition | Current state |
| ----------- | ---------- | ------------- |
| **Wave 1A** (`spec07-implementation-authorization.md`) | **CLOSED** — historically valid; active execution authority superseded | Governs completed **T006–T052** only |
| **Wave 1B** (this record) | **CLOSED** (`revoked`) | Program scope **T053–T074** complete; forward execution **none** |

---

## Program Closure Record

**Checkpoint:** `spec07-implementation-closure` = **RECORDED**  
**Closed:** 2026-07-01  
**Actor:** Governance Review  

| Item | State |
| ---- | ----- |
| Wave 1A | **CLOSED** (T006–T052) |
| Wave 1B | **CLOSED** (T053–T074) |
| T072 / T073 / T074 | **PASS** / **PASS** / **COMPLETE** |
| Active execution scope | **NONE** |
| spec08+ | **NOT AUTHORIZED** |

This record is **terminal**. No forward implementation is permitted under spec07 without a new Authorization Record.

---

## Authorized Scope

### Retroactively Accepted Scope

The following tasks are accepted as implemented and **do not require re-implementation**:

| Task IDs | Scope |
| -------- | ----- |
| **T053–T058** | CheckIn / CheckOut core domain |
| **T059–T060** | CheckIn / CheckOut feature tests |
| **T061–T067** | Supplier / adapter layer |
| **T068** | Request lifecycle handoff test |
| **T069–T071** | Architecture and integration tests |

**Status:** **RETROACTIVE_ACCEPTANCE** — read-only conformity verification only unless a blocking defect is discovered through completion gates.

### Remaining Authorized Execution Scope

The only remaining executable scope under this authorization is:

| Task ID | Authorized work |
| ------- | ----------------- |
| **T072** | PHPStan validation for Allocation and CheckIn modules |
| **T073** | Laravel Pint formatting enforcement for Allocation and CheckIn modules |
| **T074** | Final Wave 1B reconciliation, task/status alignment, and closure confirmation |

### Program boundary

| Boundary | Value |
| -------- | ----- |
| **Maximum program scope** | Wave 1B — **T053–T074** |
| **Executable entry point** | **T072** |
| **Executable exit point** | **T074** + verification pass |

### Implementation paths

| Area | Path |
| ---- | ---- |
| Allocation module | `app/Modules/Allocation/` |
| CheckIn module | `app/Modules/CheckIn/` |
| Tests | `tests/Unit/Modules/Allocation/`, `tests/Feature/Modules/Allocation/`, `tests/Feature/Modules/CheckIn/`, `tests/Architecture/AllocationBoundaryTest.php`, `tests/Architecture/CheckInBoundaryTest.php` |

---

## Execution Constraints

**Do NOT:**

- revisit, rework, or reopen **T006–T052**
- expand scope beyond **T053–T074**
- introduce new features, new modules, or speculative enhancements
- redesign existing business logic
- start **spec08**, **spec09**, **spec10**, or **spec11**
- modify governance, authority, or specification-definition files as part of Wave 1B completion
- treat retroactively accepted implementation as implicitly authorizing future out-of-scope work
- implement **spec04** Dormitory bounded context
- introduce cross-module Eloquent or direct foreign keys

**Stop conditions**

| Condition | Action |
| --------- | ------ |
| Forward work outside **T072–T074** | **HALT** |
| Any task outside **T053–T074** program boundary | **HALT** |
| Architectural deviation required | **HALT** — ADR / change request required |
| Blocking defect in retroactively accepted scope | **HALT** — minimal fix only per Allowed Changes Policy |

---

## Allowed Changes Policy

Execution under this authorization is **completion-only**.

### For T053–T071

- Allowed mode: **read-only conformity verification**
- No rewrite or re-implementation is permitted unless a blocking defect is discovered directly through the allowed completion gates

### For T072–T073

Minimal code changes are permitted **only if strictly required** to:

- satisfy PHPStan
- satisfy Laravel Pint
- preserve green tests
- maintain Wave 1B boundary integrity

### Change limit

Any code changes must remain:

- minimal
- local to Wave 1B-related implementation paths
- non-expansive in scope
- non-architectural in intent

**Prohibited:** feature additions, stylistic re-implementation of T053–T071, broad refactors unrelated to gate resolution, changes motivated by spec04 or spec08+.

---

## Verification and Closure Conditions

Wave 1B will be considered **COMPLETE** only if all of the following are true:

- **T072** passes successfully (`composer run phpstan` — level 8, zero errors in `app/Modules/Allocation/` and `app/Modules/CheckIn/`)
- **T073** passes successfully (`composer run pint` — zero violations in the same paths)
- no critical static-analysis violations remain within the authorized Wave 1B paths
- no formatting violations remain within the authorized Wave 1B paths
- no regressions are introduced in:
  - Allocation module
  - CheckIn / CheckOut module
- **T074** reconciliation confirms final alignment between implementation state, task state, and authorization state

### spec07 program closure

spec07 is **FULLY CLOSED** when:

1. Wave 1A **CLOSED** (already satisfied)
2. Wave 1B **COMPLETE** (conditions above)
3. No active forward Implementation Authorization remains for spec07
4. Next implementation requires **separate** explicit governance authorization

---

## Risk Acceptance Statement

This authorization formally accepts the following governance reality:

- T053–T071 were implemented before Wave 1B authorization existed
- the issue was **authorization timing**, not scope invention or design divergence
- audit history must preserve this as **retroactive governance alignment**
- acceptance of existing implementation does **not** imply that unfinished gates (T072–T073) are already satisfied
- no scope expansion is permitted as a result of this acceptance

---

## Final Execution Directive

The next and only allowed execution under spec07 is:

1. complete **T072**
2. complete **T073**
3. perform **T074** final reconciliation and closure alignment
4. close **Wave 1B**
5. close **spec07**

```text
Entry point: T072
Exit point: T074 + verification and closure conditions satisfied
HALT on any work outside T053–T074 program boundary.
HALT on any forward work outside T072–T074.
```

---

## Post-Completion State

Upon successful completion of this authorization scope:

| Item | Expected state |
| ---- | -------------- |
| **Wave 1A** | **CLOSED** (historical record preserved) |
| **Wave 1B** | **CLOSED** |
| **spec07** | **FULLY CLOSED** |
| **Implementation** | Formally governed and fully reconciled for T006–T074 |
| **This record** | May transition to `superseded` on program closure; terminal for active execution |
| **Next scope** | May be prepared separately; **not authorized by this record** |
| **spec08+** | Requires new explicit governance authorization |
| **spec04 Dormitory impl** | **Not authorized** — UD-07 stub path remains unless separately authorized |

---

## References

- [`spec07-implementation-authorization.md`](./spec07-implementation-authorization.md) — Wave 1A (superseded for active execution)
- [`spec07-design-approved.md`](./spec07-design-approved.md)
- [`architecture-freeze-spec07.md`](../../governance/freeze/architecture-freeze-spec07.md)
- [`catalog-decisions.md`](../catalog-decisions.md) CD-014, CD-015, CD-016
- `specs/007-allocation-checkin/tasks.md`
- `.specify/governance/execution-policy.md`
- `.specify/governance/_meta/authority-model.md` §4–§5
