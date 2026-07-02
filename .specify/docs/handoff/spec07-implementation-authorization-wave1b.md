# Wave 1B Implementation Authorization — spec07

**Recorded:** 2026-07-01  
**Authority:** Product / Tech governance  

---

## Authorization Header

| Field | Value |
| ----- | ----- |
| **Record** | spec07 Wave 1B Implementation Authorization |
| **authorization-status** | `active` |
| **authorized-by** | Governance Review |
| **effective-date** | 2026-07-01 |
| **supersedes** | [`.specify/docs/handoff/spec07-implementation-authorization.md`](./spec07-implementation-authorization.md) (Wave 1A — active execution authority only) |
| **superseded-by** | — |
| **lifecycle-reference** | `.specify/governance/_meta/authority-model.md` §4–§5 |
| **Wave** | **Wave 1B** — Post-MVP completion (Phases 6–8) |
| **Design baseline** | [`spec07-design-approved.md`](./spec07-design-approved.md) |
| **Architecture freeze** | [`.specify/governance/freeze/architecture-freeze-spec07.md`](../../governance/freeze/architecture-freeze-spec07.md) — **APPROVED** (immutable) |
| **Task baseline** | `specs/007-allocation-checkin/tasks.md` |

**Normative scope fields**

```text
authorization-status: active
authorized-scope: Wave 1B — T072–T074
retroactive-acceptance-scope: Wave 1B — T053–T071
program-scope-boundary: Wave 1B — T053–T074
blocked-scope: —
```

---

## Governance Disposition

**Disposition:** **RETROACTIVE_ACCEPTANCE**

Reconciliation review classified Wave 1B repository state as **PARTIAL_UNAUTHORIZED_IMPLEMENTATION**: T053–T071 were delivered without active Implementation Authorization but align with approved `tasks.md`, `spec07-design-approved.md`, and frozen boundaries **CD-014** / **CD-015**.

Under this disposition:

- **T053–T071** are **accepted retroactively** as design-conformant pre-authorized implementation.
- **T053–T071 do not require re-implementation.**
- **T072–T074** remain the **only authorized forward execution** under this record.

This disposition does not grant authority outside spec07 Wave 1B (T053–T074).

---

## Authority Transition

| Prior state | Transition | Current state |
| ----------- | ---------- | ------------- |
| **Wave 1A** (`spec07-implementation-authorization.md`) | **CLOSED** — historically valid; execution authority superseded for remaining spec07 work | Remains the authoritative record for completed Wave 1A scope **T006–T052** |
| **Wave 1B** (T053–T074) | **ACTIVE** — this record | Forward execution authorized for **T072–T074** only |

**Rules**

- Wave 1A closure is **not** invalidated, erased, or reopened.
- Wave 1A deliverables **T006–T052** remain governed by the Wave 1A authorization record as historically closed work.
- Active implementation execution for spec07 is governed by **this record** until Wave 1B closure conditions are met.
- No spec08+ implementation authority is granted by this record.

---

## Authorized Scope

### Retroactively accepted (no forward execution)

| Task IDs | Phase | Status under this record |
| -------- | ----- | ------------------------ |
| **T053–T071** | 6–8 (US4, US5, partial polish) | **RETROACTIVE_ACCEPTANCE** — accepted; **do not re-implement** |

### Authorized forward execution

| Task ID | Phase | Authorized work |
| ------- | ----- | ----------------- |
| **T072** | 8 — Polish | PHPStan level 8 on `app/Modules/Allocation/` and `app/Modules/CheckIn/` |
| **T073** | 8 — Polish | Laravel Pint on `app/Modules/Allocation/` and `app/Modules/CheckIn/` |
| **T074** | 8 — Polish | Final reconciliation / closure alignment for spec07 Wave 1B and program handoff |

### Program boundary (prohibited beyond)

| Boundary | Value |
| -------- | ----- |
| **Maximum program scope** | Wave 1B — **T053–T074** |
| **Executable entry point** | **T072** (T053–T071 skipped by disposition) |
| **Executable exit point** | **T074** completion + verification pass |

### Code and test paths (unchanged from approved design)

| Area | Path |
| ---- | ---- |
| Allocation module | `app/Modules/Allocation/` |
| CheckIn module | `app/Modules/CheckIn/` |
| Tests | `tests/Unit/Modules/Allocation/`, `tests/Feature/Modules/Allocation/`, `tests/Feature/Modules/CheckIn/`, `tests/Architecture/AllocationBoundaryTest.php`, `tests/Architecture/CheckInBoundaryTest.php` |
| Specification tree | `specs/007-allocation-checkin/` (alignment only — no redesign without change request) |

---

## Execution Constraints

Implementation **MUST**:

- Treat **T053–T071** as complete under retroactive acceptance unless verification review proves non-conformance (see Allowed Changes Policy).
- Execute forward work **only** on **T072**, **T073**, and **T074** in that order unless T072/T073 parallel rules in `tasks.md` apply without scope expansion.
- Preserve approved design decisions (`data-model.md`, `contracts/`, architecture freeze).
- Use Application contracts only for cross-module integration — no cross-module Eloquent.
- Maintain **UD-07** stub path for spec04 (`NullDormitoryReadAdapter` / test doubles) — spec04 implementation **not** authorized.

Implementation **MUST NOT**:

- Reopen, rework, or re-execute **T006–T052** (Wave 1A closed).
- Re-implement **T053–T071** except minimal fixes strictly required by T072/T073 gates.
- Execute any task **outside T053–T074**.
- Implement **spec04** Dormitory bounded context.
- Implement **spec08** Voucher, **spec09** Notification, **spec10** Audit module work, or **spec11** Reporting projections.
- Change **CD-014**, **CD-015**, or architecture freeze records.
- Modify `spec.md` or `plan.md` without change request.
- Introduce cross-module Eloquent or direct foreign keys.

**Stop conditions**

| Condition | Action |
| --------- | ------ |
| Forward work outside **T072–T074** | **HALT** |
| Any task outside **T053–T074** program boundary | **HALT** |
| Architectural deviation required | **HALT** — ADR / change request required |
| Retroactive acceptance review fails conformance | **HALT** — governance review required before execution |

---

## Allowed Changes Policy

**Permitted without scope expansion**

- PHPStan-driven **minimal** type/safety fixes in `app/Modules/Allocation/` and `app/Modules/CheckIn/` required to satisfy **T072**.
- Pint-driven **format-only** changes in the same paths required to satisfy **T073**.
- **T074** documentation/status alignment in `tasks.md` and descriptive handoff snapshots only as required for closure verification — **not** redesign of `spec.md` / `plan.md`.

**Prohibited**

- Feature additions beyond approved `tasks.md` Wave 1B definitions.
- Re-implementation of retroactively accepted T053–T071 deliverables for stylistic or refactor reasons.
- Broad refactors unrelated to PHPStan/Pint gate resolution.
- Any change motivated by spec08+ or spec04 implementation.

---

## Verification and Closure Conditions

### T072 — PHPStan

- `composer run phpstan` (or project-equivalent) passes at **level 8** for:
  - `app/Modules/Allocation/`
  - `app/Modules/CheckIn/`

### T073 — Pint

- `composer run pint` passes with **zero** formatting violations for:
  - `app/Modules/Allocation/`
  - `app/Modules/CheckIn/`

### Retroactive acceptance verification (read-only)

- Confirm T053–T071 artifacts remain aligned to approved contracts and **CD-015** boundary (no re-implementation unless non-conformance found).
- Recommended test suites (environment permitting):
  - `tests/Feature/Modules/CheckIn/`
  - `tests/Unit/Modules/Allocation/`
  - `tests/Feature/Modules/Allocation/` (including US5 contract tests)
  - `tests/Architecture/CheckInBoundaryTest.php`

### T074 — Closure alignment

- `tasks.md` reflects Wave 1B completion state accurately.
- Descriptive snapshots may be updated; they **do not** substitute for this authorization record.

### Wave 1B closure

Wave 1B is **CLOSED** when **all** are true:

1. T072 complete (PHPStan level 8 — zero errors in scope paths).
2. T073 complete (Pint — zero violations in scope paths).
3. T074 complete (closure alignment recorded).
4. Retroactive acceptance verification **PASS** or documented exception with governance sign-off.
5. No open HALT conditions under this record.

### spec07 program closure (post Wave 1B)

spec07 implementation program is **CLOSED** when:

1. Wave 1A **CLOSED** (already satisfied).
2. Wave 1B **CLOSED** (conditions above).
3. No active Implementation Authorization remains for forward spec07 execution.
4. Next spec execution requires **separate** Implementation Authorization per canonical map.

---

## Risk Acceptance Statement

Governance **accepts** the following risks by approving **RETROACTIVE_ACCEPTANCE**:

- T053–T071 were produced without contemporaneous Implementation Authorization.
- Retroactive acceptance relies on design-conformance review rather than gate-by-gate authorized execution history.
- PHPStan/Pint gates (T072–T073) were not proven at authorization time for the full Wave 1B module surface.

Governance **does not accept**:

- Treating retroactive acceptance as blanket authority for future unauthorized work.
- Expanding spec07 beyond **T053–T074** without a new authorization record.
- Using this record to authorize spec08 or later specifications.

---

## Final Execution Directive

```text
Implementation may proceed on T072 only.
T053–T071 are retroactively accepted — do not re-implement.
Complete T072 → T073 → T074 sequentially per tasks.md polish rules.
HALT on any work outside T053–T074 program boundary.
HALT on any forward work outside T072–T074.
```

**Entry point:** **T072**  
**Exit point:** **T074** + verification and closure conditions satisfied

---

## Post-Completion State

Upon successful Wave 1B closure:

| Item | Expected state |
| ---- | -------------- |
| **spec07 Wave 1A** | **CLOSED** (historical record preserved) |
| **spec07 Wave 1B** | **CLOSED** |
| **spec07 program** | **Implementation complete** for authorized scope T006–T074 |
| **This record** | May transition to `superseded` when spec07 program closure is recorded; terminal for active execution |
| **spec08+** | **Not authorized** — requires separate Design Approval and Implementation Authorization |
| **spec04 Dormitory impl** | **Not authorized** — runtime dependency remains stubbed per UD-07 unless separately authorized |

---

## References

- [`spec07-implementation-authorization.md`](./spec07-implementation-authorization.md) — Wave 1A (superseded for active execution)
- [`spec07-design-approved.md`](./spec07-design-approved.md)
- [`architecture-freeze-spec07.md`](../../governance/freeze/architecture-freeze-spec07.md)
- [`catalog-decisions.md`](../catalog-decisions.md) CD-014, CD-015, CD-016
- `specs/007-allocation-checkin/tasks.md`
- `.specify/governance/execution-policy.md`
- `.specify/governance/_meta/authority-model.md` §4–§5
