---
artifact: next_work_selection_revisit
wave: 02
status: DECISION_COMPLETE
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
decision_date: 2026-07-12
---

# WAVE_02 — Next Work Selection Revisit Gate

**Decision date:** 2026-07-12  
**Mission:** WAVE_02 — Next Work Selection Revisit

This artifact is a **work selection decision** only. It is **not** governance repair, regularization, authority recovery, or implementation authorization.

No repository mutation is performed by this decision beyond creating this artifact.

---

## 1. Revisit Context

| Input | Role |
| ----- | ---- |
| `.specify/docs/decision/next-authorized-work-selection.md` | Prior selection — `NO_SELECTION_DUE_TO_INSUFFICIENT_EVIDENCE`; next step named nomination |
| `.specify/docs/discovery/next-work-candidate-nomination.md` | Nomination complete — `READY_TO_RETRY`; prefers Spec04 Residual Ownership Decision |
| `.specify/docs/review/wave-02-governance-completion-review.md` | Wave exit stable; Spec06/Spec11 debt tracked, non-blocking for other selection |
| `.specify/docs/spec-catalog.md` | Spec04 `Backend CLOSED / Product PENDING_RESIDUAL`; Spec07 Fully Closed; Spec03 closed with EmployeeRead deferred |

Prior selection failed because the **original named candidate set** lacked selection-ready evidence. Nomination supplied a new inventory. This revisit selects from that inventory under normal feature/spec completion mode.

---

## 2. Candidate Evidence Review

| Candidate | Existing evidence | Current lifecycle state | Readiness |
| --------- | ----------------- | ----------------------- | --------- |
| Spec04 Residual Ownership Decision (human Decision Gate) | Residual ownership map; residual ownership Decision Gate (`READY FOR HUMAN OWNERSHIP DECISION`); Spec04 GDR Decision 4; catalog `PENDING_RESIDUAL` | Decision prep complete; ownership assignment pending | `READY` |
| Spec04 Allocation ↔ Dormitory integration residual | Closeout §6; Decision Gate §3.3; CD-014; Spec07 Fully Closed | Deferred residual; Spec packet owner undecided | `NEEDS_ANALYSIS` |
| Spec04 Check-in ↔ Dormitory wiring residual | Closeout §6; Decision Gate §3.4; CD-015 | Deferred residual; Spec/Wave packet missing | `NEEDS_ANALYSIS` |
| Spec04 Auth integration residual | Closeout §6; Decision Gate §3.1; Spec02 Frozen | Split ownership undecided | `NEEDS_ANALYSIS` |
| Spec04 Dormitory UI presentation residual | Closeout UI exclusion; UI triage blocks `dormitory-admin-ui` | Tracked residual; no product UI auth | `DEFERRED` |
| Post-Spec03 EmployeeRead (T049–T052) | Spec03 closure; Item B deferral; optional follow-on | Deferred at Spec03 close; no consumer mandate | `DEFERRED` |
| Request Dependent live integration | Owner D-01–D-03; IRG deferred | Explicitly deferred / blocked | `BLOCKED` |
| Live ActiveAllocation binding (replace Null) | Batch 1b Null adapter; IRG required; Spec07 closed | Residual path; no IRG/IA | `BLOCKED` |
| Notification mark-all-as-read | UI triage; backend batch absent | Blocked successor | `BLOCKED` |
| Identity OA-02-01 / Livewire admin (T035–T037) | Spec02 frozen deferrals | Spec-deferred | `DEFERRED` |
| Spec06 / Spec11 new implementation | Catalog holds; authority gaps tracked | Implementation held; gaps open | `BLOCKED` |

---

## 3. Selection Criteria

### C1 — Evidence Availability

| Candidate class | Pass? |
| --------------- | ----- |
| Spec04 Residual Ownership Decision | **Yes** — map + Decision Gate prep + GDR Decision 4 + catalog residual posture |
| Spec04 residual implementation packets | **No** — ownership / IA / port surfaces incomplete |
| EmployeeRead / Dependent / ActiveAllocation / UI successors / Spec06–11 new impl | **No** — mandate, IRG, product auth, or authority resolution missing |

### C2 — Governance Readiness

| Candidate class | Pass? |
| --------------- | ----- |
| Spec04 Residual Ownership Decision | **Yes** — Wave 02 Spec04 alignment/GDR chain + residual Decision Gate prep form an authority chain for a **decision** artifact (not implementation) |
| Residual implementation / reopen / UI intake | **No** — no IA / product auth / IRG PASS |

### C3 — Scope Clarity

| Candidate class | Pass? |
| --------------- | ----- |
| Spec04 Residual Ownership Decision | **Yes** — decision-only: record domain candidates; Spec04 retention vs transfer; forbid Wave invention; **explicitly exclude** residual coding |
| Other nominated residuals | **No** — boundaries depend on ownership outcomes first |

### C4 — Dependency Safety

| Candidate class | Pass? |
| --------------- | ----- |
| Spec04 Residual Ownership Decision | **Yes** — does not reopen Spec06/Spec11 regularization; does not reopen Spec07 implementation; does not mutate Spec04 backend closeout |
| Spec07 reopen / live Allocation / Spec06–11 new impl | **No** — would reopen closed or held governance surfaces |

**Only** Spec04 Residual Ownership Decision passes C1–C4 as a selection-ready next work item.

---

## 4. Selection Decision

### Result

`SELECTED_WORK_ITEM`

### Work item

**Spec04 Residual Ownership Decision (human Decision Gate)**

Canonical short name: `spec04-residual-ownership-decision`

### Reason

1. Nomination classified this item `READY` and recommended selection revisit preferring it.
2. Repository evidence already supports starting a human ownership Decision Record (map + Decision Gate prep + GDR residual deferral).
3. Scope is decision-only and contained; it unblocks later residual packet analysis without authorizing implementation.
4. Safer than selecting residual implementation, EmployeeRead, Dependent live, ActiveAllocation live binding, UI successors, or Spec06/Spec11 new work — all of which fail readiness and/or dependency safety.

### Next artifact required

**Ownership Decision Record** for Spec04 residuals

Expected path pattern (not created here):

`.specify/docs/decision/spec04-residual-ownership-decision.md`

(or repository-equivalent Decision Record under `.specify/docs/decision/`)

That next artifact must:

- assign or explicitly retain Spec ownership per residual where evidence allows
- record Spec04 retention vs transfer choices
- record whether Spec07 reopen / new Spec / Spec04 Application extension packets are required (decision only)
- forbid Wave invention without nomination/IA artifacts
- **not** grant Implementation Authorization, Feature Contracts, locks, or coding authority

---

## 5. Decision Block

```text
WAVE_02_NEXT_WORK_SELECTION_REVISIT

Selection Result:
SELECTED_WORK_ITEM

Selected Work Item:
Spec04 Residual Ownership Decision (human Decision Gate)

Next Required Artifact:
ownership Decision Record (Spec04 residuals)
```

---

## 6. Explicit Non-Authorization

This revisit does **not**:

- Authorize residual implementation (Auth, UI, Allocation integration, Check-in wiring)
- Authorize Spec07 reopen or live ActiveAllocation binding
- Authorize EmployeeRead, Request Dependent live, or UI intake
- Authorize Spec06/Spec11 new implementation or reopen regularization
- Modify specs, tasks, catalog, code, or conflict registers
- Create closure artifacts

---

## Document Control

- Artifact: `next_work_selection_revisit`
- Wave: 02
- Status: `DECISION_COMPLETE`
- Mutation permission: none
- Execution authority: none
- Operating mode: `FEATURE_AND_SPEC_COMPLETION_MODE`
- Owner: Governance / Wave 02
- Last Updated: 2026-07-12
