---
artifact: post_spec04_ownership_next_work_selection
wave: 02
status: DECISION_COMPLETE
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
decision_date: 2026-07-12
---

# Post–Spec04 Ownership — Next Work Selection

**Decision date:** 2026-07-12  
**Mission:** Select the next authorized work item after Spec04 residual ownership Decision Record closure

This artifact is a **work selection decision only**. It does **not** reopen governance regularization, revisit Spec04 ownership decisions, authorize implementation, modify code/specs/tasks/catalog, invent backlog items, or grant execution beyond naming the next required artifact.

---

## 1. Decision Context

- Wave 02 governance completion review is complete.
- Spec04 residual ownership ambiguity is **closed** by `.specify/docs/decision/spec04-residual-ownership-decision.md`.
- The project remains in **`FEATURE_AND_SPEC_COMPLETION_MODE`**.
- Prior selection revisit chose the ownership Decision Gate; that gate’s Decision Record is now recorded.
- This step selects the **next** work item only under clarified ownership lines.

---

## 2. Ownership Consequence Summary

From the recorded ownership Decision Record:

| Owner | Responsibility |
| ----- | -------------- |
| Spec02 Identity | Identity / role / permission / access-control foundation |
| Spec04 | Allocation assignment responsibility (“who got which room/bed”) |
| Spec07 | Occupancy / check-in / resident-presence state |
| Dormitory UI Feature | Presentation/interaction only — not business-logic owner |

Ownership clarity removes the prior selection blocker; it does **not** by itself authorize residual coding, Spec07 reopen, Spec02 unfreeze, or UI intake.

---

## 3. Candidate Assessment Table

| Candidate | Ownership Basis | Evidence Basis | Readiness | Risk | Next Artifact Type | Reason |
| --------- | --------------- | -------------- | --------- | ---- | ------------------ | ------ |
| Spec04 Allocation ↔ Dormitory residual follow-on | D1 → `SPEC04` | Spec04 closeout §6 residual; residual map/gate; catalog `PENDING_RESIDUAL`; ownership Decision Record | `READY` (for discovery/readiness only) | `MEDIUM` | residual scope discovery / readiness review | Ownership home matches Spec04 Product residual posture; exact packet/port scope still needs analysis before IA |
| Spec07 occupancy / check-in follow-on | D2 → `SPEC07` | Catalog Spec07 Fully Closed; active execution **none**; ownership forbids auto-reopen | `NOT_READY` | `HIGH` | Spec07 reopen / activation decision (not selected) | Ownership assigned, but Spec07 remains closed; silent reopen unsafe |
| Spec02 auth / identity follow-on | D3 → `SPEC02_IDENTITY` | Spec02 Frozen Wave 1A; OA-02-01 / Livewire admin deferred | `NOT_READY` | `HIGH` | product/Spec02 reopen decision (not selected) | Ownership assigned; freeze + deferred auth UX block progression |
| Dormitory UI feature preparation | D4 → `INDEPENDENT_UI_FEATURE` | UI triage blocks `dormitory-admin-ui` without product auth; ownership says presentation-only | `NOT_READY` | `HIGH` | product authorization for UI intake (not selected) | No product UI authorization; must not redefine domain ownership |
| No selection | n/a | n/a | n/a | n/a | n/a | Rejected — Spec04 Allocation residual is safely selectable for **analysis**, not implementation |

---

## 4. Comparative Reasoning (C1–C5)

### Spec04 Allocation ↔ Dormitory residual follow-on

| Criterion | Assessment |
| --------- | ---------- |
| C1 Ownership Clarity | **Pass** — D1 explicitly assigns Spec04 |
| C2 Evidence Readiness | **Pass for analysis** — closeout residual wording + map/gate + ownership record exist; exact implementable packet still incomplete |
| C3 Scope Safety | **Pass** — assignment residual under Spec04; does not claim Spec07 occupancy ownership |
| C4 Dependency Safety | **Pass** — avoids Spec06/Spec11 regularization reopen; avoids Spec07 silent reopen; stays inside Spec04 Product `PENDING_RESIDUAL` |
| C5 Execution Preparedness | **Ready for readiness review / residual scope discovery** — **not** ready for Implementation Authorization |

### Spec07 occupancy / check-in follow-on

Fails C4/C5: Spec07 Fully Closed with no active execution; ownership Decision Record forbids automatic reopen. Selecting Spec07 implementation follow-on would invent reopen authority.

### Spec02 auth / identity follow-on

Fails C4/C5: Spec02 Frozen; OA-02-01 and Livewire admin remain deferred; ownership alone does not authorize auth expansion.

### Dormitory UI feature preparation

Fails C2/C4/C5: presentation-only ownership is clear, but product authorization for UI intake is absent; UI must not own domain logic.

**Conclusion:** The safest justified next move is Spec04 Allocation ↔ Dormitory residual **follow-on analysis** (readiness / scope discovery), not coding and not Spec07/Spec02/UI progression.

---

## 5. Selection Decision

### Result

`SELECTED_WORK_ITEM`

### Selected item

**Spec04 Allocation ↔ Dormitory residual readiness review**

Canonical short name: `spec04-allocation-dormitory-residual-readiness`

### Why best now

1. Ownership for Allocation ↔ Dormitory is fixed to Spec04.
2. Spec04 Product remains `PENDING_RESIDUAL` — the natural residual home.
3. Repository evidence already names the residual (closeout §6 / residual map) without a complete implementable packet.
4. Other ownership-home candidates (Spec07, Spec02, Dormitory UI) remain blocked by closed/frozen/unauthorized postures.
5. Selecting readiness review advances product progression without pretending Implementation Authorization exists.

### Next required artifact

**Residual scope discovery / readiness review** for Spec04 Allocation ↔ Dormitory

Expected path pattern (not created here):

`.specify/docs/discovery/spec04-allocation-dormitory-residual-readiness.md`

(or repository-equivalent under `.specify/docs/discovery/` / `.specify/docs/review/`)

That artifact must inventory existing ports/contracts/gaps, propose a bounded residual packet under Spec04 ownership, and explicitly **not** authorize implementation or Spec07 reopen.

---

## 6. Decision Block

```text
POST_SPEC04_OWNERSHIP_NEXT_WORK_SELECTION

Selection Result:
SELECTED_WORK_ITEM

Selected Item:
Spec04 Allocation ↔ Dormitory residual readiness review

Next Required Artifact:
residual scope discovery / readiness review
```

---

## 7. Guardrails

- This decision does **not** authorize implementation.
- This decision does **not** reopen Spec04 ownership decisions (D1–D4 remain fixed).
- This decision does **not** reopen Spec06 or Spec11 regularization.
- This decision does **not** change specs, tasks, catalog, or code.
- Downstream work still requires a proper artifact and authority chain (readiness → later selection/IA as applicable).
- Spec07 check-in ownership does **not** imply Spec07 active execution.

---

## Document Control

- Artifact: `post_spec04_ownership_next_work_selection`
- Path: `.specify/docs/decision/post-spec04-ownership-next-work-selection.md`
- Wave: 02
- Status: `DECISION_COMPLETE`
- Mutation permission: none
- Execution authority: none
- Operating mode: `FEATURE_AND_SPEC_COMPLETION_MODE`
- Owner: Governance / Work Selection
- Last Updated: 2026-07-12
