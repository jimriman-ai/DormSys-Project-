# spec08 Implementation Authorization

**Recorded:** 2026-07-01  
**Authority:** Product / Tech governance  

---

## Authorization Header

| Field | Value |
| ----- | ----- |
| **Spec** | spec08 — External Accommodation (Voucher) |
| **Wave** | Waves **1–3** (initial authorized execution) |
| **Authorized Scope** | T001–T017 |
| **Authorization Type** | Controlled Initial Implementation Authorization |
| **authorization-status** | `active` |
| **Authorization Status** | **ACTIVE** — Waves 1–3 only |
| **authorized-by** | Governance Review |
| **Effective Date** | Immediate upon issuance |
| **effective-date** | 2026-07-01 |
| **supersedes** | — |
| **superseded-by** | — |
| **lifecycle-reference** | `.specify/governance/_meta/authority-model.md` §4–§5 |
| **Design baseline** | `specs/008-external-accommodation/spec.md` (stabilized) |
| **Plan baseline** | `specs/008-external-accommodation/plan.md` (final) |
| **Task baseline** | `specs/008-external-accommodation/tasks.md` |
| **Nomination** | [`.specify/docs/handoff/spec08-nomination-record.md`](./spec08-nomination-record.md) |

**Normative scope fields**

```text
authorization-status: active
authorized-scope: Waves 1–3 — T001–T017
executable-forward-scope: Wave 1 (EB-1) entry — T001
maximum-authorized-scope: Wave 3 exit — T017 + CP-W3 PASS
future-continuation-scope: Waves 4–5 — T018–T031 (NOT AUTHORIZED by this record)
active-execution-scope: Waves 1–3 — T001–T017
blocked-scope: Waves 4–5; spec07 reopening; UD-03/UD-08 resolution during execution
authority-constraints: CD-016 and R8 frozen; behavior-level tasks only; no artifact redesign
```

---

## Status

**Implementation Authorized** — **ACTIVE** (Waves 1–3 only)

This record grants controlled execution authority for the **initial Voucher program slice**: trigger intake, eligibility evaluation, and issuance lifecycle with transition recording.

**This record does NOT authorize:**

- Waves 4–5 (T018–T031)
- Architecture freeze issuance (not yet approved for spec08)
- Resolution of **UD-03** or **UD-08**
- Reopening or modification of **spec07**
- spec09, spec10, spec11 implementation
- spec04 Dormitory implementation

---

## Baseline

### Specification baseline

| Reference | Value |
| --------- | ----- |
| Specification | [`specs/008-external-accommodation/spec.md`](../../specs/008-external-accommodation/spec.md) — **stabilized** |
| Plan | [`specs/008-external-accommodation/plan.md`](../../specs/008-external-accommodation/plan.md) — **final** |
| Tasks | [`specs/008-external-accommodation/tasks.md`](../../specs/008-external-accommodation/tasks.md) — **approved for implementation preparation** |

### Frozen boundaries (immutable for this authorization)

| Decision | Requirement |
| -------- | ----------- |
| **CD-016** | Voucher owns eligibility evaluation and issuance lifecycle; upstream supplies trigger facts only |
| **R8** | Lottery / Allocation → Voucher; facts only; no reverse ownership |
| **CD-017** | Downstream read consumers (Reporting) have no write authority — reference only in Wave 1–3 |

### Prior governance

| Reference | State |
| --------- | ----- |
| [`spec08-nomination-record.md`](./spec08-nomination-record.md) | Active |
| spec07 program | **CLOSED** — [`spec07-implementation-authorization-wave1b.md`](./spec07-implementation-authorization-wave1b.md) |
| Active execution scope (global) | spec07: **none**; spec08: **Waves 1–3** per this record |

---

## Authorized Scope

Implementation is authorized **only** for:

| Scope | Detail |
| ----- | ------ |
| **Bounded context** | **Voucher** (spec08) — external accommodation credentials only |
| **Tasks** | **T001–T017** only — per `tasks.md` |
| **Waves** | **Wave 1**, **Wave 2**, **Wave 3** only |
| **Execution batches** | **EB-1** through **EB-4** (inclusive) |
| **User stories (authorized)** | US1 (eligibility), US2 (issuance lifecycle + transition recording) |
| **Specification alignment** | Implementation must conform to stabilized `spec.md`; no scope expansion |

### Wave and batch map (authorized)

| Wave | Execution batch(es) | Task IDs | Behavior slice |
| ---- | ------------------- | -------- | -------------- |
| **Wave 1** | **EB-1** — Trigger intake & idempotency | T001–T004 | Accept upstream facts; correlation; dedupe; overlap guard (PC-01, PC-08) |
| **Wave 2** | **EB-2** — Eligibility evaluation | T005–T010 | Evaluation outcomes; external classification (PC-02) |
| **Wave 3** | **EB-3** — Issuance lifecycle | T011–T016 | Code generation; uniqueness; validity; archival (PC-03) |
| **Wave 3** | **EB-4** — Transition recording | T017 | Material lifecycle transitions for downstream (PC-07) |

### Future continuation scope (NOT authorized)

The following are **defined for planning continuity only**. They are **not** active execution authority under this record:

| Wave | Execution batch(es) | Task IDs | Status |
| ---- | ------------------- | -------- | ------ |
| Wave 4 | EB-5 (lottery path), EB-6 (reserve promotion), EB-7 (read access) | T018–T028 | **NOT AUTHORIZED** — requires separate authorization |
| Wave 5 | EB-8 (boundary verification) | T029–T031 | **NOT AUTHORIZED** — requires separate authorization |

**Progression beyond Wave 3 requires a separate follow-up Implementation Authorization record or explicit governance checkpoint confirmation.** This record does not auto-extend.

---

## Explicitly Excluded Scope

| Excluded | Reason |
| -------- | ------ |
| **T018–T031** | Waves 4–5 — not authorized by this record |
| **spec07** Allocation / CheckIn | **CLOSED** — no reopening; trigger facts from Allocation reference only |
| Internal dormitory assignment | Out of scope — Allocation-owned (spec07) |
| Check-in / check-out | Out of scope — CheckIn-owned (spec07) |
| Physical dormitory inventory | Out of scope — Dormitory (spec04) |
| Lottery execution / scoring | Out of scope — Lottery (spec06) |
| Request lifecycle | Out of scope — Request (spec05) |
| Notification delivery | Out of scope — spec09 |
| Audit storage implementation | Out of scope — spec10 |
| Reporting projections | Out of scope — spec11 |
| **UD-03** resolution | Open — stub/synthetic trigger facts only during Waves 1–3 |
| **UD-08** resolution | Open — default expiration behavior only; policy not closed |
| Workflow engine | Deferred |
| Employee / operator presentation (OA-08-05) | Deferred follow-on |

---

## Preconditions

The following **must** be satisfied before code execution begins:

| ID | Precondition | Status |
| -- | ------------ | ------ |
| P-01 | spec08 `spec.md` stabilized and review-approved | ✅ |
| P-02 | spec08 `plan.md` final | ✅ |
| P-03 | spec08 `tasks.md` approved for implementation preparation | ✅ |
| P-04 | Implementation Authorization Model defined (batch/wave/stop/checkpoints) | ✅ |
| P-05 | CD-016 and R8 boundaries frozen | ✅ |
| P-06 | spec07 **CLOSED**; no active spec07 execution scope | ✅ |
| P-07 | This Implementation Authorization record issued (`authorization-status: active`) | ✅ |
| P-08 | spec08 nomination active | ✅ [`spec08-nomination-record.md`](./spec08-nomination-record.md) |

---

## Execution Entry and Exit

| Boundary | Value |
| -------- | ----- |
| **Entry point** | **Wave 1** — **EB-1** — **T001** |
| **Exit point (this authorization)** | **Wave 3** complete — **T017** + **CP-W3** PASS |
| **Maximum authorized task range** | **T001–T017** |
| **Next unauthorized task** | **T018** (Wave 4 — HALT without follow-up authorization) |

```text
Entry point: T001 (Wave 1 / EB-1)
Exit point: T017 + CP-W3 PASS
HALT on T018+ without separate authorization.
HALT on any work outside T001–T017 program boundary.
```

---

## Execution Rules

Implementation **MUST**:

- Start from **T001** (Wave 1 / EB-1)
- Follow **`tasks.md`** phase and dependency order within **T001–T017**
- Complete waves **sequentially**: Wave 1 → Wave 2 → Wave 3
- Pass mandatory validation checkpoint after each wave before proceeding
- Preserve stabilized `spec.md` and final `plan.md` semantics — no redesign
- Implement **behavior-level tasks only** — no scope expansion beyond FR mapping in `tasks.md`
- Keep all eligibility and issuance authority in **Voucher** (CD-016)
- Consume upstream trigger facts from Lottery and Allocation only — no upstream policy (R8)
- Use **stub or synthetic trigger facts** for upstream integration until **UD-03** is separately authorized for resolution
- Apply **default expiration behavior** for T014 without closing **UD-08**
- Use accommodation catalog classification via read reference when available (OA-08-02); stub acceptable until spec04 live
- Treat spec07 Allocation as **closed** — reference `VoucherIssuancePort` producer direction only; do not modify spec07 artifacts

Implementation **MUST NOT**:

- Skip waves or execute Wave 4–5 under this record
- Modify `spec.md`, `plan.md`, or `tasks.md` as part of execution
- Resolve **UD-03** or **UD-08** during Waves 1–3
- Reopen spec07 or implement Allocation / CheckIn behaviors
- Introduce capabilities not present in spec08 FR/US set

---

## Hard Stop Conditions

Execution **MUST HALT** immediately when any of the following occur:

| # | Condition | Action |
| - | --------- | ------ |
| 1 | Task outside **T001–T017** attempted | **HALT** — Waves 4–5 not authorized |
| 2 | Wave started before prior wave checkpoint **PASS** | **HALT** |
| 3 | Design ambiguity requires reinterpretation of spec or plan | **HALT** — change request required |
| 4 | Architecture drift — schema/API/service/workflow design required beyond authorized behavior | **HALT** — separate design authorization |
| 5 | **CD-016** violation — eligibility or issuance logic outside Voucher | **HALT** |
| 6 | **R8** violation — reverse dependency or upstream operational data ownership | **HALT** |
| 7 | spec07 reopening or modification attempted | **HALT** |
| 8 | **UD-03** or **UD-08** resolution attempted during execution | **HALT** |
| 9 | New capability required not in spec08 | **HALT** |
| 10 | Task expansion beyond FR-mapped behavior slice | **HALT** |
| 11 | `spec.md`, `plan.md`, or `tasks.md` modification required to proceed | **HALT** |

Stop conditions **override** wave progress. No implicit authorization to continue.

Per `.specify/governance/execution-policy.md` § Pre-Execution Requirements: if this record is missing, revoked, or superseded, report:

> `Missing or invalid implementation authorization record.`

---

## Execution Guardrails

| Rule | Enforcement |
| ---- | ----------- |
| No task expansion | Implement exactly the behavior described in each task |
| No design decisions during execution | Defer `data-model.md`, `contracts/`, UD-03, UD-08 to separate design gates |
| No schema/API/workflow definitions | Unless pre-authorized in a future design artifact — none exist for spec08 at authorization |
| Voucher-only ownership | CD-016 — all eligibility and issuance in Voucher |
| Facts-only upstream | R8 — Lottery and Allocation supply triggers only |
| No spec07 changes | Closed program; read-only upstream reference |
| No cross-module ownership changes | Audit, Notification, Reporting remain downstream only |
| UD-03 / UD-08 remain open | Document; do not resolve |
| Wave 4 parallelism | **Not applicable** — Wave 4 not authorized by this record |
| Sequential waves only | Wave 1 → 2 → 3 under this authorization |

---

## Mandatory Validation Checkpoints

Execution **must not** advance past a wave until its checkpoint **PASS**es.

| Checkpoint | After | Mandatory verifications |
| ---------- | ----- | ------------------------ |
| **CP-W1** | Wave 1 (EB-1; T001–T004) | Trigger facts accepted without upstream ownership; correlation recorded; duplicate correlation rejected; no CD-016/R8 drift |
| **CP-W2** | Wave 2 (EB-2; T005–T010) | Outcomes `Eligible` / `Ineligible` / `Deferred` producible; external-only rule enforced; immutable references recorded; US1 equivalent PASS |
| **CP-W3** | Wave 3 (EB-3/EB-4; T011–T017) | Issuance from `Eligible` only; global uniqueness; terminal re-issue rejected; archival enforced; transition records emitted; US2 equivalent PASS; no physical assignment or check-in scope |

**Per-checkpoint drift checks (all CP-*):**

- No spec/plan/tasks reinterpretation
- CD-016 and R8 intact
- No new capabilities introduced
- Dependency graph in `tasks.md` unchanged

**Wave 3 completion** is the terminal success state for **this** authorization record.

---

## Open Dependencies (carried — stub path acceptable)

| ID | Item | Waves 1–3 handling |
| -- | ---- | ------------------ |
| **UD-03** | Upstream trigger fact bundle shape | **Open** — synthetic/stub trigger facts permitted; do not resolve |
| **UD-08** | Voucher expiration and renewal policy | **Open** — implement expiration tracking per FR-009 with default behavior; do not close policy |
| **OA-08-02** | External dormitory classification from spec04 | Optional read; stub acceptable until spec04 supplier live |
| **OA-08-03** | Lottery facts after draw completion | Stub external-winner facts acceptable for Wave 1–3 tests |
| **OA-08-04** | Allocation trigger facts only | Reference closed spec07 producer direction; stub acceptable |
| **OA-08-07** | Unfulfilled-accommodation triggers | Stub acceptable; Voucher owns eligibility |

---

## Dependency Assumptions

| Dependency | Relationship | Waves 1–3 usage |
| ---------- | ------------ | --------------- |
| spec01 Foundation | Required | Platform conventions |
| spec05 Request | Upstream context in facts | Reference only — no Request lifecycle |
| spec06 Lottery | Upstream trigger supplier | Stub external-winner facts — no Lottery implementation |
| spec04 Accommodation Resource | Optional classification read | Stub acceptable (OA-08-02) |
| spec07 Allocation | Upstream trigger producer (**closed**) | Stub trigger facts only — no reopening |
| spec09 Notification | Downstream consumer | T017 transition records only — no delivery impl |
| spec10 Audit | Downstream consumer | T017 transition records only — no audit storage impl |
| spec11 Reporting | Downstream consumer | Not in Waves 1–3 scope |

---

## Authority Transition

| Item | State |
| ---- | ----- |
| spec07 | **CLOSED** — no active execution |
| spec08 (this record) | **ACTIVE** — Waves 1–3 — T001–T017 |
| spec08 Waves 4–5 | **NOT AUTHORIZED** |
| spec09–spec11 | **NOT AUTHORIZED** |

Upon **CP-W3 PASS** and completion of T001–T017:

- This record remains **`active`** until superseded or extended by governance
- **No automatic authorization** for T018–T031
- Follow-up authorization required before Wave 4 entry (T018)

---

## Final Execution Directive

Authorized execution under spec08 begins **only** as follows:

1. Verify preconditions **P-01** through **P-08**
2. Execute **Wave 1** — EB-1 — **T001–T004** → **CP-W1 PASS**
3. Execute **Wave 2** — EB-2 — **T005–T010** → **CP-W2 PASS**
4. Execute **Wave 3** — EB-3 + EB-4 — **T011–T017** → **CP-W3 PASS**
5. **STOP** — do not proceed to T018 without separate authorization

```text
Entry point: T001
Exit point: T017 + CP-W3 PASS
Authorized maximum: T001–T017
HALT on T018+ without follow-up Implementation Authorization.
```

---

## References

- [`spec08-nomination-record.md`](./spec08-nomination-record.md)
- [`spec07-implementation-authorization-wave1b.md`](./spec07-implementation-authorization-wave1b.md) — spec07 closure
- [`catalog-decisions.md`](../catalog-decisions.md) CD-016, CD-017
- [`context-map.md`](../context-map.md) R8
- [`spec-catalog.md`](../spec-catalog.md) — spec08
- `specs/008-external-accommodation/spec.md`
- `specs/008-external-accommodation/plan.md`
- `specs/008-external-accommodation/tasks.md`
- `.specify/governance/execution-policy.md`
- `.specify/governance/_meta/authority-model.md` §4–§5

---

**End of authorization record.**
