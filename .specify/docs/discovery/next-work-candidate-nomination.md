---
artifact: next_work_candidate_nomination
wave: 02
status: NOMINATION_COMPLETE
mutation_permission: none
execution_authority: none
selection_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
recorded: 2026-07-12
---

# Next Work Candidate Nomination

**Artifact type:** Candidate nomination only (non-selecting, non-authorizing)  
**Recorded:** 2026-07-12  
**Checkpoint:** `next-work-candidate-nomination`

This artifact nominates evidence-backed candidates for a future next-work **selection revisit**. It does **not** select a final work item, authorize implementation, create a feature contract or lock, modify specs/tasks/catalog/code/governance registers, or reopen Spec06/Spec11 regularization.

---

## 1. Context

Wave 02 governance repair is complete. Regularization mode is exited. Next-work selection is **allowed** by governance:

| Evidence | Finding |
| -------- | ------- |
| `.specify/docs/decision/next-work-selection-gate.md` | `NEXT_WORK_SELECTION_ALLOWED`; `FEATURE_AND_SPEC_COMPLETION_MODE` |
| `.specify/docs/decision/next-authorized-work-selection.md` | `SELECTION_ALLOWED` but `NO_SELECTION_DUE_TO_INSUFFICIENT_EVIDENCE` |
| Immediate next artifact named by that selection | `evidenced candidate nomination (product/governance)` |

Selection is therefore blocked by **insufficient selection-ready candidate evidence** in the previously assessed named set (closed / deferred / undefined slices), **not** by a governance-mode halt.

This nomination rebuilds a short inventory from repository evidence only.

---

## 2. Candidate Table

| Candidate | Evidence | Readiness | Risk | Reason |
| --------- | -------- | --------- | ---- | ------ |
| Spec04 Residual Ownership Decision (human Decision Gate) | `spec04-residual-ownership-map.md`; `spec04-residual-ownership-decision-gate.md` (`READY FOR HUMAN OWNERSHIP DECISION`); Spec04 GDR Decision 4; catalog `PENDING_RESIDUAL` / `DEFERRED_TO_FUTURE_WAVE` | `READY` | `MEDIUM` | Narrow, evidenced governance decision slice; map + gate prep exist; ownership assignment still missing; does **not** authorize residual implementation |
| Spec04 Allocation ↔ Dormitory integration residual | Spec04 backend closeout §6; residual map / Decision Gate §3.3; CD-014; Spec07 Fully Closed | `NEEDS_ANALYSIS` | `HIGH` | Real deferred residual, but Spec packet owner and reopen vs new extension packet undecided; Spec07 closed blocks silent reopen |
| Spec04 Check-in ↔ Dormitory wiring residual | Closeout §6; Decision Gate §3.4; CD-015; Spec07 program closed | `NEEDS_ANALYSIS` | `HIGH` | Domain ownership of CheckIn/CheckOut confirmed; Spec/Wave packet and IA missing |
| Spec04 Auth integration residual | Closeout §6; Decision Gate §3.1; Spec02 Frozen; Spec04 IA excluded policies | `NEEDS_ANALYSIS` | `HIGH` | Split Identity vs Dormitory-surface policy not assignable without Decision Gate (+ likely further discovery) |
| Spec04 Dormitory UI presentation residual | Closeout §6; Decision Gate §3.2; UI triage blocks `dormitory-admin-ui` without product auth | `DEFERRED` | `HIGH` | Tracked residual; UI intake not product-authorized; not selection-ready for implementation |
| Post-Spec03 EmployeeRead (T049–T052) | Spec03 closure handoff; Item B `SPEC03_ITEM_B_DEFERRED`; completion-wave optional follow-on; no in-app `EmployeeReadContract` consumer | `DEFERRED` | `MEDIUM` | Valid post-close work slice only after consumer-need evidence + separate IA; no current mandate |
| Request Dependent live integration | Owner D-01–D-03; IRG blocked / deferred; Completion Wave Batch 2 closed by deferral | `BLOCKED` | `HIGH` | Explicit deferral; Product `eligible` rule + Employee Dependent read surface still required before IRG can pass |
| Live ActiveAllocation binding (replace Null) | US4 Batch 1b completion (Null adapter delivered); residual requires separate IRG/IA; Spec07 closed | `BLOCKED` | `HIGH` | Evidenced residual path, but IRG/owner reopen not present; not selection-ready |
| Notification mark-all-as-read | UI triage / backlog discovery; P2–P9 closed; backend batch mutation absent; no product auth | `BLOCKED` | `MEDIUM` | Named residual successor; backend + product-auth blockers remain |
| Identity OA-02-01 / Livewire admin (T035–T037) | Spec02 frozen deferrals | `DEFERRED` | `MEDIUM` | Spec-deferred; no product authorization for UI intake |
| Spec06 / Spec11 new implementation | Catalog holds; authority gaps tracked; Wave 02 exit forbids treating gaps as closed | `BLOCKED` | `HIGH` | New implementation held; **must not** reopen regularization under this nomination |

**Excluded (not nominated):** previously assessed closed/satisfied UI items (Request List Detail Navigation core; Notification Inbox P2–P9; Request Create Discoverability without new evidence; Audit UI closed); invented labels such as “Dormitory Runtime” / “Employee Integration” without a narrower evidenced work-item definition.

---

## 3. Evidence Summaries

### 3.1 Spec04 Residual Ownership Decision — `READY`

| Field | Detail |
| ----- | ------ |
| Source artifacts | `.specify/docs/planning/spec04-residual-ownership-map.md`; `.specify/docs/review/spec04-residual-ownership-decision-gate.md`; `.specify/docs/decision/spec04-governance-decision.md` (Decision 4); `.specify/docs/spec-catalog.md` |
| What exists | Residual inventory; ownership-map rows; Decision Gate prep stating readiness for human ownership decision on evidenced domain candidates |
| What is missing | Final ownership Decision Record; Spec retention vs transfer choices; Wave/IA nomination for any residual packet |
| Selection-ready? | **Yes — as a governance decision work item only** (not as residual implementation) |

### 3.2 Spec04 Allocation / Check-in / Auth residual packets — `NEEDS_ANALYSIS`

| Field | Detail |
| ----- | ------ |
| Source artifacts | Spec04 backend closeout; residual map; residual ownership Decision Gate §§3.1–3.4; CD-014 / CD-015; Spec07 Fully Closed |
| What exists | Explicit deferred residual wording; confirmed domain splits for Allocation and Check-in |
| What is missing | Spec packet owner; new IA / reopen authority; exact port surfaces for live binding |
| Selection-ready? | **No** until ownership Decision Gate outcomes (and further discovery where Auth/UI transfer targets remain UNKNOWN) |

### 3.3 Spec04 Dormitory UI residual — `DEFERRED`

| Field | Detail |
| ----- | ------ |
| Source artifacts | Closeout UI exclusion; Decision Gate §3.2; `docs/ui/review/governance-next-candidate-triage.md` (`dormitory-admin-ui` blocked) |
| What exists | Tracked product residual under Spec04 until reassigned |
| What is missing | Product authorization for UI intake; ownership transfer decision if leaving Spec04 id |
| Selection-ready? | **No** |

### 3.4 Post-Spec03 EmployeeRead — `DEFERRED`

| Field | Detail |
| ----- | ------ |
| Source artifacts | `.specify/docs/handoff/spec03-closure-handoff.md`; `.specify/governance/batch-b.spec03-item-b-resolution.md`; completion-wave plan Phase 7 optional |
| What exists | Formal deferral text; T049–T052 unfinished by design; reopen path = new selection + IA |
| What is missing | Evidenced downstream consumer mandate; Implementation Authorization |
| Selection-ready? | **No** (optional follow-on only) |

### 3.5 Request Dependent live / Live ActiveAllocation — `BLOCKED`

| Field | Detail |
| ----- | ------ |
| Source artifacts | Request Dependent owner decision (D-01–D-03); IRG/Completion Wave notes; Batch 1b Null ActiveAllocation delivery |
| What exists | Explicit deferral / Null-default design; future IRG requirements documented |
| What is missing | Product reopen inputs; IRG PASS; Integration IA; Spec07 reopen authority for live Allocation |
| Selection-ready? | **No** |

### 3.6 Notification mark-all / Identity deferred UI — `BLOCKED` / `DEFERRED`

| Field | Detail |
| ----- | ------ |
| Source artifacts | UI triage ledger; Spec09 OA-09-05 history vs closed inbox chain; Spec02 OA-02-01 / T035–T037 |
| What exists | Named deferrals / blocked successors |
| What is missing | Backend capability and/or product UI authorization |
| Selection-ready? | **No** |

### 3.7 Spec06 / Spec11 new implementation — `BLOCKED`

| Field | Detail |
| ----- | ------ |
| Source artifacts | Catalog holds; Wave 02 completion review / selection gate debt baseline |
| What exists | Implementation present with open governance authority gaps (tracked) |
| What is missing | Separate authority resolution / new IA (outside this nomination) |
| Selection-ready? | **No** — do not reopen regularization here |

---

## 4. Nomination Decision

**One candidate is ready for selection revisit:**

- **Spec04 Residual Ownership Decision (human Decision Gate)** — readiness `READY` for a **decision-only** next work item.

All other inventoried items remain `NEEDS_ANALYSIS`, `DEFERRED`, or `BLOCKED` and must **not** be treated as selection-ready product/implementation picks without further evidence or ownership outcomes.

This nomination does **not** claim residual implementation readiness for Spec04 Auth/UI/Allocation/Check-in.

---

## 5. Recommended Next Action

| Field | Value |
| ----- | ----- |
| Immediate next artifact type | **selection revisit** |
| Scope of revisit | Prefer the Spec04 Residual Ownership Decision candidate; do not auto-select blocked/deferred UI or closed Spec reopen slices |
| Not next | Feature contract; implementation lock; Spec06/Spec11 regularization reopen; residual coding |

After a successful selection of the ownership Decision Gate work item, the expected subsequent artifact type would be an **ownership Decision Record** (not implementation authorization).

---

## 6. Decision Block

```text
NEXT_WORK_CANDIDATE_NOMINATION

Nomination Result:
CANDIDATE_NOMINATION_COMPLETE

Selection Revisit Status:
READY_TO_RETRY

Recommended Next Artifact Type:
selection revisit
```

---

## Explicit Non-Actions

This nomination does **not**:

- Select the final next work item
- Authorize implementation or Integration IA
- Create feature contracts or locks
- Modify specs, tasks, catalog, code, or conflict registers
- Reopen Spec06 or Spec11 regularization
- Invent backlog items beyond repository-evidenced slices

---

## Document Control

- Artifact: `next_work_candidate_nomination`
- Path: `.specify/docs/discovery/next-work-candidate-nomination.md`
- Status: `NOMINATION_COMPLETE`
- Mutation permission: none
- Execution authority: none
- Owner: Governance / Discovery
- Last Updated: 2026-07-12
