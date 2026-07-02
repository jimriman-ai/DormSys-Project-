# Waves 4–5 Implementation Authorization — spec08

**Recorded:** 2026-07-02  
**Authority:** Product / Tech governance  

---

## Authorization Header

| Field | Value |
| ----- | ----- |
| **Spec** | spec08 — External Accommodation (Voucher) |
| **Wave** | Waves **4–5** |
| **Authorized Scope** | T018–T031 |
| **Authorization Type** | Retroactive Acceptance + Program Scope Regularization |
| **authorization-status** | `revoked` |
| **Authorization Status** | **CLOSED** |
| **closure-date** | 2026-07-02 |
| **revocation-reason** | Program closure — executable scope exhausted (T001–T031 complete; freeze PASS) |
| **authorized-by** | Governance Review |
| **Effective Date** | Immediate upon issuance |
| **effective-date** | 2026-07-02 |
| **supersedes** | [`.specify/docs/handoff/spec08-implementation-authorization.md`](./spec08-implementation-authorization.md) (Waves 1–3 — active execution authority only) |
| **superseded-by** | — |
| **lifecycle-reference** | `.specify/governance/_meta/authority-model.md` §4–§5 |
| **Design baseline** | `specs/008-external-accommodation/spec.md` (stabilized) |
| **Plan baseline** | `specs/008-external-accommodation/plan.md` (final) |
| **Task baseline** | `specs/008-external-accommodation/tasks.md` |
| **Nomination** | [`.specify/docs/handoff/spec08-nomination-record.md`](./spec08-nomination-record.md) |
| **Reconciliation reference** | BM-01 — authorization scope mismatch (T018–T031 implemented prior to record extension) |

**Normative scope fields**

```text
authorization-status: revoked
authorized-scope: Waves 4–5 — T018–T031 (complete)
executable-forward-scope: —
retroactive-acceptance-scope: Waves 4–5 — T018–T031 (accepted)
blocked-scope: —
active-execution-scope: none
authority-constraints: program closed; no forward implementation permitted; cannot authorize spec09+; cannot reopen T001–T031 without new authorization record
```

---

## Governance Disposition

**Selected Disposition:** **RETROACTIVE_ACCEPTANCE**

Repository reconciliation confirmed that Waves 4–5 implementation artifacts for **T018–T031** exist, align with approved task decomposition, stabilized `spec.md`, final `plan.md`, and established spec08 boundaries (CD-016, R8, CD-017).

These artifacts are formally accepted as **design-conformant implementation delivered prior to Waves 4–5 authorization**.

This authorization resolves the previously identified governance condition:

> **BM-01 — PARTIAL_UNAUTHORIZED_IMPLEMENTATION** (T018–T031 active outside T001–T017 authorization cap)

by bringing existing implementation into an explicit, governed execution baseline without scope expansion or boundary reinterpretation.

---

## Reconciliation Evidence

| Check | Result |
| ----- | ------ |
| `tasks.md` T018–T031 marked complete | ✅ Verified |
| Implementation artifacts present | ✅ `ProcessExternalLotteryWinnerAction`, `ProcessReservePromotionAction`, `VoucherReadService`, `OpenPlanningItemsRegistry` |
| Service provider bindings | ✅ Active contracts registered in `VoucherServiceProvider` |
| Feature tests (Waves 4–7 scope) | ✅ 53 feature tests across lottery, reserve, read, boundary closure paths |
| Architecture boundary tests | ✅ `VoucherBoundaryTest.php` — no upstream infrastructure imports (R8 / SC-005) |
| Voucher read isolation (CD-017) | ✅ `VoucherReadService` depends only on `VoucherReadRepositoryContract` |
| Upstream fact-supplier model | ✅ No `Lottery`, `Allocation`, or `Request` module imports in Voucher |
| UD-03 / UD-08 | ✅ Remain open per `OpenPlanningItemsRegistry` (T031) |
| spec07 | ✅ Not reopened |

**Verification note:** One read-access test (`VoucherReadAccessTest`) exhibited non-deterministic ordering when `issued_at` timestamps collide; re-run passes. Classified as test brittleness, not boundary or scope violation. Does not block retroactive acceptance.

---

## Authority Transition

| Prior state | Transition | Current state |
| ----------- | ---------- | ------------- |
| **Waves 1–3** (`spec08-implementation-authorization.md`) | **SUPERSEDED** — historically valid | Governs completed **T001–T017** only |
| **Waves 4–5** (this record) | **ACTIVE** — retroactive acceptance | Program scope **T018–T031** accepted; forward execution **none** |

- **Waves 1–3 (T001–T017):** remain historically **CLOSED** and valid under superseded record
- **Waves 4–5 (T018–T031):** retroactively accepted; no re-implementation required
- This record does **not** reopen Waves 1–3
- This record does **not** authorize spec09–spec11 or spec07 reopening

---

## Authorized Scope

### Retroactively Accepted Scope

The following tasks are accepted as implemented and **do not require re-implementation**:

| Task IDs | Scope | Plan cluster |
| -------- | ----- | -------------- |
| **T018–T021** | External lottery winner path | PC-04 (US3) |
| **T022–T025** | Reserve promotion | PC-05 + PC-07 (US4) |
| **T026–T028** | Voucher read access | PC-06 (US5) |
| **T029–T031** | Boundary closure and open-item carry-forward | B7 |

**Status:** **RETROACTIVE_ACCEPTANCE** — read-only conformity verification unless blocking defect discovered through freeze gates.

### Combined Program Scope (spec08)

| Wave range | Task IDs | Authorization record | State |
| ---------- | -------- | -------------------- | ----- |
| Waves 1–3 | T001–T017 | `spec08-implementation-authorization.md` (superseded) | **CLOSED** |
| Waves 4–5 | T018–T031 | This record | **ACCEPTED** |
| **Full program** | **T001–T031** | Combined | **Implementation complete** |

### Implementation paths

| Area | Path |
| ---- | ---- |
| Voucher module | `app/Modules/Voucher/` |
| Migrations | `database/migrations/modules/voucher/` |
| Tests | `tests/Feature/Modules/Voucher/`, `tests/Unit/Modules/Voucher/`, `tests/Architecture/VoucherBoundaryTest.php` |

---

## Execution Constraints

**Do NOT:**

- revisit, rework, or reopen **T001–T031** without new authorization
- expand scope beyond **T001–T031**
- introduce new features, modules, or speculative enhancements
- redesign existing business logic
- start **spec09**, **spec10**, or **spec11**
- reopen or modify **spec07**
- resolve **UD-03** or **UD-08** under this record
- introduce cross-module Eloquent or direct foreign keys

**Stop conditions**

| Condition | Action |
| --------- | ------ |
| Forward implementation outside freeze reconciliation | **HALT** |
| Architectural deviation required | **HALT** — ADR / change request required |
| Blocking defect in retroactively accepted scope | **HALT** — minimal fix only per freeze policy |

---

## Frozen Boundaries (unchanged)

| Decision | Requirement |
| -------- | ----------- |
| **CD-016** | Voucher owns eligibility evaluation, issuance lifecycle, and reserve promotion; upstream supplies trigger facts only |
| **R8** | Lottery / Allocation → Voucher; facts only; no reverse ownership |
| **CD-017** | Voucher read paths are read-only; Reporting remains downstream projection consumer only |

---

## BM-01 Resolution

| Item | Prior state | Resolution |
| ---- | ----------- | ---------- |
| **BM-01** | T018–T031 implemented but unauthorized | **RESOLVED** — retroactive acceptance under this record |
| Authorization cap | T001–T017 only | Extended to **T001–T031** (combined across records) |
| Active execution | Mismatch | **Aligned** — `active-execution-scope: none`; implementation governed |

---

## Post-Acceptance State

| Item | Expected state |
| ---- | -------------- |
| **Waves 1–3 record** | **SUPERSEDED** (historical; T001–T017 valid) |
| **Waves 4–5 record** | **CLOSED** (`revoked`) — program scope T018–T031 accepted |
| **BM-01** | **RESOLVED** |
| **spec08 implementation** | **T001–T031 complete and governed** |
| **Post-implementation freeze** | **PASS** — [`spec08-implementation-closure.md`](./spec08-implementation-closure.md) |
| **spec08 program** | **FULLY CLOSED** |
| **spec09+** | **NOT AUTHORIZED** |
| **UD-03 / UD-08** | **OPEN** — carried forward per T031 |

---

## Program Closure Record

**Checkpoint:** `spec08-implementation-closure` = **RECORDED**  
**Closed:** 2026-07-02  
**Actor:** Governance Review  

| Item | State |
| ---- | ----- |
| Waves 1–3 | **CLOSED** (T001–T017) |
| Waves 4–5 | **CLOSED** (T018–T031) |
| Freeze retry | **PASS** |
| Active execution scope | **NONE** |
| spec09+ | **NOT AUTHORIZED** |

This record is **terminal**. No forward implementation is permitted under spec08 without a new Authorization Record.

See [`spec08-implementation-closure.md`](./spec08-implementation-closure.md) for full freeze verification record.

---

## References

- [`spec08-implementation-authorization.md`](./spec08-implementation-authorization.md) — Waves 1–3 (superseded)
- [`spec08-nomination-record.md`](./spec08-nomination-record.md)
- [`spec07-implementation-authorization-wave1b.md`](./spec07-implementation-authorization-wave1b.md) — precedent for retroactive acceptance
- [`catalog-decisions.md`](../catalog-decisions.md) CD-016, CD-017
- [`context-map.md`](../context-map.md) R8
- `specs/008-external-accommodation/spec.md`
- `specs/008-external-accommodation/plan.md`
- `specs/008-external-accommodation/tasks.md`
- `.specify/governance/execution-policy.md`
- `.specify/governance/_meta/authority-model.md` §4–§5

---

**End of authorization record.**
