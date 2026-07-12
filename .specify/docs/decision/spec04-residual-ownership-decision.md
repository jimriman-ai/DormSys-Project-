---
artifact: spec04_residual_ownership_decision
spec: Spec04
wave: 02
status: DECISION_COMPLETE
decision_type: human_ownership_decision
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
decision_date: 2026-07-12
---

# Spec04 Residual Ownership Decision Record

**Artifact type:** Human ownership Decision Record (non-authorizing)  
**Recorded:** 2026-07-12  
**Checkpoint:** `spec04-residual-ownership-decision`

Upstream selection: `.specify/docs/decision/next-work-selection-revisit.md` — `SELECTED_WORK_ITEM` = Spec04 Residual Ownership Decision (human Decision Gate).

---

## 1. Decision Purpose

This artifact **records already-made human ownership decisions** for Spec04 residual topics identified under Wave 02 residual ownership gate preparation.

It:

- records **ownership boundaries** for the four locked residual topics
- does **not** authorize implementation
- does **not** reopen governance repair or Spec06/Spec11 regularization
- does **not** create feature/spec contracts
- does **not** revisit candidate nomination or work selection
- does **not** invent new residuals or new decision branches

Supporting context (citation only): Spec04 Product `PENDING_RESIDUAL` / `DEFERRED_TO_FUTURE_WAVE` in `.specify/docs/spec-catalog.md`; residual Decision Gate prep in `.specify/docs/review/spec04-residual-ownership-decision-gate.md`; nomination/revisit selection of this decision work item.

---

## 2. Decision Summary Table

| Residual Topic | Decision | Plain Meaning | Ownership Effect | Follow-on Implication |
| -------------- | -------- | ------------- | ---------------- | --------------------- |
| Allocation ↔ Dormitory | `SPEC04` | Spec04 owns who got which room/bed allocation | Allocation assignment responsibility is Spec04’s residual ownership home | Later Allocation↔Dormitory residual packets are selected under Spec04 ownership lines; still need separate IA for coding |
| Check-in ↔ Dormitory | `SPEC07` | Spec07 owns whether the resident actually entered, occupied, or left | Real occupancy / check-in / leave state is Spec07’s residual ownership home | Later check-in wiring residuals are selected under Spec07 ownership lines; Spec07 is not auto-reopened |
| Auth Integration | `SPEC02_IDENTITY` | Spec02 owns user identity, roles, permissions, access-control foundation | Identity/access residual concerns are Spec02 Identity’s ownership home | Later auth residual work is selected under Spec02; not auto-authorized |
| Dormitory UI | `INDEPENDENT_UI_FEATURE` | UI pages display/interact; they do not own domain behavior | Presentation/interaction feature — not business-logic owner | Future UI feature (if selected) must consume backend-owned contracts; no immediate UI IA |

---

## 3. Detailed Decision Entries

### D1 — Allocation ↔ Dormitory

| Field | Value |
| ----- | ----- |
| Decision id | `D1` |
| Topic | Allocation ↔ Dormitory |
| Chosen owner | `SPEC04` |
| Simple rationale | Allocation assignment responsibility (“who got which room/bed”) remains with Spec04 residual ownership as decided. |
| Boundary clarification | Spec04 owns **allocation assignment responsibility**. Physical-state collaboration and closed Spec07 program constraints remain as previously tracked; this decision assigns Spec ownership of the residual **assignment** question to Spec04. |
| Does not authorize | Dormitory integration implementation; Spec07 reopen; live port coding; Feature Contracts; Wave invention |

### D2 — Check-in ↔ Dormitory

| Field | Value |
| ----- | ----- |
| Decision id | `D2` |
| Topic | Check-in ↔ Dormitory |
| Chosen owner | `SPEC07` |
| Simple rationale | Real occupancy / check-in / leave state (“did the resident actually enter, occupy, or leave”) belongs to Spec07 as decided. |
| Boundary clarification | Spec07 owns **real occupancy / check-in / resident-presence state**. Dormitory remains a physical-state collaborator under existing CD boundaries; process ownership of operational transitions is Spec07. |
| Does not authorize | Automatic Spec07 reopen or mutation; Check-in wiring implementation; Integration IA; Feature Contracts |

### D3 — Auth Integration

| Field | Value |
| ----- | ----- |
| Decision id | `D3` |
| Topic | Auth Integration |
| Chosen owner | `SPEC02_IDENTITY` |
| Simple rationale | Identity, user, role, and permission concerns belong to Spec02 Identity as decided. |
| Boundary clarification | Spec02 Identity owns **identity / roles / permissions / access-control foundation**. Dormitory-surface policy binding, if later scoped, must respect Spec02 as the foundation owner rather than inventing a parallel auth authority under Spec04. |
| Does not authorize | Auth expansion work; Identity Livewire admin; OA-02-01 delivery; Spec02 unfreeze; coding |

### D4 — Dormitory UI

| Field | Value |
| ----- | ----- |
| Decision id | `D4` |
| Topic | Dormitory UI |
| Chosen owner | `INDEPENDENT_UI_FEATURE` |
| Simple rationale | Dormitory UI is presentation/interaction scope, not the owner of business logic, as decided. |
| Boundary clarification | A Dormitory UI feature may **display and interact** with backend-owned logic/contracts. It does **not** define ownership of domain behavior, allocation assignment, occupancy meaning, or auth rules. |
| Does not authorize | Immediate UI implementation; UI product intake without separate authorization; Feature Contracts/locks; backend expansion under a UI grant |

---

## 4. Ownership Boundary Statement

Resulting architectural split (ownership only):

- **Spec02 Identity** owns identity / role / permission foundation.
- **Spec04** owns allocation assignment responsibility.
- **Spec07** owns occupancy / check-in / resident-presence state.
- **Dormitory UI Feature** is presentation-only and depends on backend-owned logic/contracts.

---

## 5. Follow-On Consequences

This Decision Record enables next steps **without authorizing execution**:

- Residual ownership ambiguity for the four locked topics is **closed**.
- Later feature/spec selection may proceed on clearer ownership lines.
- Implementation still requires proper downstream authorization/artifacts (IA, locks, product auth as applicable).
- UI work, if selected later, must **consume** backend-owned boundaries rather than redefine them.
- Spec07 ownership of check-in state does **not** itself reopen Spec07 active execution scope.

---

## 6. Decision Block

```text
SPEC04_RESIDUAL_OWNERSHIP_DECISION

Allocation ↔ Dormitory:
SPEC04

Check-in ↔ Dormitory:
SPEC07

Auth Integration:
SPEC02_IDENTITY

Dormitory UI:
INDEPENDENT_UI_FEATURE
```

---

## 7. Guardrails

- No implementation is authorized.
- No feature contract is created.
- No spec catalog entry is changed.
- No task list is changed.
- No code is changed.
- No regularization is reopened.
- No new residuals are introduced.

---

## Document Control

- Artifact: `spec04_residual_ownership_decision`
- Path: `.specify/docs/decision/spec04-residual-ownership-decision.md`
- Spec: Spec04
- Wave: 02
- Status: `DECISION_COMPLETE`
- Decision type: `human_ownership_decision`
- Mutation permission: none
- Execution authority: none
- Operating mode: `FEATURE_AND_SPEC_COMPLETION_MODE`
- Owner: Governance / Human Ownership Decision
- Last Updated: 2026-07-12
