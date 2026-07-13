---
artifact: deferred_portfolio_review_and_disposition
status: DEFERRED_PORTFOLIO_DISPOSITION_COMPLETED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
phase: CORE_COMPLETION_WAVE
primary_decision_surface: DEFERRED_PORTFOLIO
recommended_next_gate: PRODUCT_AUTHORIZATION_GAP_TRIAGE
date: 2026-07-13
---

# Deferred Portfolio Review and Disposition

**Artifact type:** Governance decision / deferred register (non-authorizing)  
**Status:** `DEFERRED_PORTFOLIO_DISPOSITION_COMPLETED`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Phase:** `CORE_COMPLETION_WAVE` — **ACTIVE**  
**Primary decision surface:** `DEFERRED_PORTFOLIO`

This gate classifies deferred, blocked, residual, and authority-held items **before** Core Completion Wave stream selection or UI-readiness work. It does **not** authorize implementation, UI, Lottery, Workflow, role mapping, HTTP/Policy, Feature Contracts, Spec reopen, or stream execution.

**Core principle preserved:** `DEFERRED` ≠ `INCOMPLETE`. Target is **100% Governance Decision Complete**, not 100% deferred-implementation complete.

---

## 1. Review Baseline

| Topic | Settled posture |
| ----- | --------------- |
| Current phase | Core Completion Wave — ACTIVE |
| Spec01 | Closed / near-closed — closure hygiene only if needed |
| Spec02 bounded packet | `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED` — closed |
| Spec02 overall | **Frozen — Wave 1A Complete** — full Spec02 auth / RBAC / role mapping / UI auth **not** complete |
| Spec03 | `SPEC03_CLOSED` |
| Spec04 Assignability / Check-in | CLOSED / RETIRED |
| Spec04 Auth residual | **OPEN** (role mapping, Presentation/UI, HTTP/Policy, product auth) — Application PEP closed only |
| Workflow | `WORKFLOW_REMAINS_DEFERRED` |
| Spec06 | `SPEC06_REMAINS_DEFERRED`; regularization complete; `AUTHORITY_NOT_AVAILABLE` |
| Spec07 | Closed |
| Spec11 | Scaffold/current scope closed for expansion; new Reporting held (authority gap) |
| UI Wave / Dormitory UI | **NOT_READY** / **BLOCKED** |
| Prior hygiene next-gate | Had named stream selection — **superseded** by this portfolio gate (stream selection premature while deferred portfolio unresolved) |

---

## 2. Deferred Portfolio Register

| Item | Current Posture | Why Deferred / Blocked | Product Authority Needed? | UI Dependency? | Risk If Left Unowned | Risk If Activated Early | Recommended Disposition | Activation / Revisit Criteria |
| ---- | --------------- | ---------------------- | ------------------------- | -------------- | -------------------- | ----------------------- | ----------------------- | ----------------------------- |
| Workflow | `WORKFLOW_REMAINS_DEFERRED` | Catalog activation criteria unmet; CD-010; not Core Wave blocker | No (for keep-deferred) | No | Orchestration stays deferred — acceptable | Premature engine/contract scope | **KEEP_DEFERRED** | ≥2 multi-stage workflows + proven shared transition duplication; explicit activate decision |
| Spec06 Lottery | Wave deferred; `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN`; regularization complete | Domain authority gap; new work held | Yes — before any new Lottery work | No (not Dormitory UI pre-req) | Governance debt remains open but catalog-tracked | Invented IA / false Full Closure | **BLOCKED_PENDING_AUTHORITY** | Map-backed Spec06 authority + product “Lottery priority” decision |
| Spec04 Auth residual (aggregate) | OPEN after post-binding refresh | Remainder: role mapping, Presentation/UI auth, HTTP/Policy, product auth | **Yes** — packet scope & priority | **Yes** — blocks safe UI path | UI blocked indefinitely; Auth residual unmanaged | Coding without product packet definition | **REQUIRE_PRODUCT_DECISION** | Product decides Auth packet scope (role map vs Presentation vs HTTP) + separate Spec02 IA path |
| Role mapping (`dormitory.structure.*`) | Deferred (keys registered, no grants) | Explicit exclusion from structure-binding packet; Spec02 Frozen | Yes | Yes (practical admin access) | Permissions exist but unusable in roles | Grants without product role model | **MERGE_INTO_OTHER_STREAM** | Merge into Spec04 Auth residual / Spec02-owned role-mapping packet after product decision |
| UI authorization (Presentation) | Deferred / blocked | No authorized Dormitory UI surface; Anti-Leak | Yes | **Yes** | UI cannot bind capabilities safely | UI-invented auth authority | **MERGE_INTO_OTHER_STREAM** | Merge into Spec04 Auth residual product packet + UI Feature Contract after product auth |
| HTTP / Policy auth | Deferred | No Spec04-authorized HTTP admin surface | Yes (with Auth packet) | Yes (if HTTP surfaces planned) | Surface gap when UI/HTTP appear | Policy classes without targets | **MERGE_INTO_OTHER_STREAM** | Same Auth residual product decision; only after surfaces exist |
| Dormitory UI (`dormitory-admin-ui`) | BLOCKED | No product UI authorization; Auth remainder unfinished | **Yes** | Self | Admin discoverability stuck | Feature Contract without product auth | **BLOCKED_PENDING_AUTHORITY** | Explicit product auth for named slug + Auth residual disposition + UI triage PASS |
| OA-02-01 Livewire Admin | Spec02 deferred (Wave 1A) | Spec02 freeze; not structure-binding packet | Yes to reopen Spec02 UX | Indirect | Identity admin UX remains deferred | Spec02 unfreeze without product mandate | **KEEP_DEFERRED** | Separate Spec02 product reopen / OA-02-01 priority decision |
| Full RBAC / Full Spec02 authorization | Not complete | Only bounded structure PEP closed | Yes for any expansion | Partial | Over-claim risk if unmanaged | Spec02 unfreeze / parallel auth authority | **KEEP_DEFERRED** | Explicit Spec02 unfreeze or successive bounded packets with human IA |
| Spec01 closure hygiene | Approved / foundation delivered | Possible doc/status polish only | No | No | Minor catalog noise | Spec01 implementation reopen | **CLOSURE_HYGIENE_ONLY** | Editorial closeout if gaps found; no impl reopen |
| Spec05 terminal closeout readiness | Implementation Authorized | Not Fully Closed as terminal governance | Optional product/governance | No | Status ambiguity | Treating IA as reopen | **CLOSURE_HYGIENE_ONLY** | Documentation/status closeout gate when selected; not Core Wave execution |
| Spec11 Reporting new work | `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` | Authority gap (Reporting) | Yes | No | Debt remains catalog-tracked | New Reporting without authority | **BLOCKED_PENDING_AUTHORITY** | Separate Reporting authority resolution |
| Request Dependent live path | IRG-blocked; Spec03 closed | Live stub replacement needs IRG; not needed unless Family-live asserted | Yes if asserted | Conditional | Only if Family UI/path needs live Dependent | Spec03 reopen / forced live path | **KEEP_DEFERRED** | Product asserts Family-live need for chosen stream + IRG PASS |
| EmployeeRead (T049–T052) | Deferred at Spec03 close | Post-Spec03; Spec03 closed | Yes to reopen | Conditional | Cross-module read remains deferred | Spec03 reopen | **KEEP_DEFERRED** | Separate Post-Spec03 product/architecture selection |
| Main UI Feature Execution | Deferred | No authorized NEW_CANDIDATE | Yes | Self | Main UI wave stays deferred | Contracts without product auth | **BLOCKED_PENDING_AUTHORITY** | Product auth + UI candidate triage PASS |
| Spec03 reopen / Dependent “completion” | Closed | Spec03 already closed | Yes (reopen) | No | None if left closed | False incompleteness reopen | **KEEP_CLOSED** | Only explicit Spec03 reopen authority |
| Spec04 Assignability / Check-in | CLOSED / RETIRED | Settled residuals | No | No | None | Residual reopen | **KEEP_CLOSED** / **RETIRE** (Check-in already retired) | Do not revisit for Core Wave execution |
| Spec07 | Closed | Fully closed | No | No | None | Spec07 reopen | **KEEP_CLOSED** | Do not reopen |

---

## 3. Disposition Analysis

### Workflow — `KEEP_DEFERRED`
Intentional deferral; wave membership already decided. Not incomplete work. No UI dependency. Reactivate only on catalog activation criteria + explicit decision.

### Spec06 Lottery — `BLOCKED_PENDING_AUTHORITY`
Regularization complete; new work blocked by `AUTHORITY_NOT_AVAILABLE`. Wave inclusion already deferred. Product Lottery priority alone is insufficient without authority resolution — primary label is authority-blocked.

### Spec04 Auth residual — `REQUIRE_PRODUCT_DECISION`
Dominant **pre-UI** open residual. Status is clarified (refresh completed) but **packet composition and priority** (role mapping vs Presentation vs HTTP vs product auth sequencing) still need human/product decision. Not `ACTIVATE_NEXT_CANDIDATE` for execution — product must define the next Auth packet first.

### Role mapping / UI auth / HTTP-Policy — `MERGE_INTO_OTHER_STREAM`
Not standalone Core Wave streams. Belong inside Spec04 Auth residual (Spec02-owned foundation D3) after product packet decision. Prevents fragmented parallel auth streams.

### Dormitory UI / Main UI — `BLOCKED_PENDING_AUTHORITY`
Blocked on explicit product authorization (and Auth disposition). Must not be treated as deferred-but-ready.

### OA-02-01 / Full RBAC / Full Spec02 — `KEEP_DEFERRED`
Outside bounded closed packet; Spec02 remains Frozen. Do not merge Full RBAC into a single “do everything” stream — keep deferred until successive bounded packets are authorized.

### Spec01 / Spec05 — `CLOSURE_HYGIENE_ONLY`
Documentation/status only. Not implementation reopen.

### Spec11 — `BLOCKED_PENDING_AUTHORITY`
Parallel to Spec06 authority-gap pattern; out of Core Wave execution path.

### Request Dependent / EmployeeRead — `KEEP_DEFERRED`
Conditional; Spec03 closed; not Core Wave blockers unless product asserts need.

### Spec03 / Spec04 closed residuals / Spec07 — `KEEP_CLOSED` (Check-in **RETIRE** already recorded)
Must not reopen.

---

## 4. UI-Readiness Implications

**Minimum governance set before any UI-readiness / UI Feature Contract path:**

1. **Product authorization** for a named UI slug (e.g. `dormitory-admin-ui`) — currently absent  
2. **Spec04 Auth residual product decision** — which Auth packet(s) must exist before Presentation surfaces (role mapping and/or Presentation/HTTP binding scope)  
3. **UI Anti-Leak compliance path** — consume backend capabilities; no UI-invented authority  
4. Then (only after 1–3): `UI_CANDIDATE_READINESS_TRIAGE` → Feature Contract / IA (separate authorities)

**Not required before Dormitory UI readiness:** Workflow activation; Spec06 new Lottery work; Spec11 expansion; EmployeeRead; Dependent live (unless that UI path asserts Family-live).

---

## 5. Items That Must Not Be Activated Now

| Item | Disposition |
| ---- | ----------- |
| Workflow | KEEP_DEFERRED |
| Spec06 new Lottery work | BLOCKED_PENDING_AUTHORITY |
| Spec11 new Reporting work | BLOCKED_PENDING_AUTHORITY |
| OA-02-01 / Full RBAC / Spec02 unfreeze | KEEP_DEFERRED |
| Dormitory UI / Main UI execution | BLOCKED_PENDING_AUTHORITY |
| Request Dependent live / EmployeeRead | KEEP_DEFERRED |
| Spec03 / Spec04 Assignability / Spec07 | KEEP_CLOSED |
| Spec04 Check-in | RETIRE (already) |
| Role mapping / UI auth / HTTP as standalone streams | MERGE — do not activate alone |
| Core Completion Wave **stream execution** | Not selected in this gate |

---

## 6. Candidate for Next Stream

```text
NO_EXECUTION_STREAM_ACTIVATED
```

No item receives `ACTIVATE_NEXT_CANDIDATE` for implementation or IA-prep execution.

**Governance next focus (non-executing):** Spec04 Auth residual **product decision** — the dominant pre-UI blocker cluster (role mapping + Presentation/UI auth + HTTP/Policy framing + product auth sequencing).

Stream selection remains **premature** until that product decision narrows an eligible, authority-safe candidate.

---

## 7. Risks and Boundary Preservation

### Unmanaged deferred portfolio risks
- Deferred items treated as silent defects → forced premature completion  
- Auth remainder left ambiguous → UI kickoff without product auth  
- Multiple parallel “auth streams” without merge → scope chaos  

### Premature activation risks
- Workflow/Lottery/Reporting coding without criteria/authority  
- Role grants / HTTP policies without product role model or surfaces  
- Dormitory UI Feature Contracts without product authorization  
- Spec02/Spec03/closed Spec04 residual reopen  

### Boundaries preserved
- Spec02 structure-binding packet **closed**; Spec02 **Frozen**  
- Full Spec02 auth / Full RBAC **not** complete  
- Spec03 closed; Spec04 Assignability closed; Check-in retired  
- Spec04 Auth residual **remains OPEN** (disposition = require product decision — not closed)  
- Workflow deferred; Spec06 deferred; Lottery new work held  
- Dormitory UI blocked; UI Wave not ready  
- **No** implementation, Feature Contract, stream execution, or invented product authority  

---

## 8. Decision

```text
DEFERRED_PORTFOLIO_DISPOSITION_COMPLETED
```

**Portfolio-level meaning:** Every listed deferred/blocked/residual item now has a primary disposition, owner-class implication, and revisit criteria. This is **governance decision completeness** for the register — **not** product authorization completeness and **not** implementation completeness.

**Answers — required questions:**

| Q | Answer |
| - | ------ |
| A. Portfolio | See §2 register (Workflow through Spec07 + related) |
| B. True deferred | Workflow; OA-02-01; Full RBAC; Dependent; EmployeeRead |
| C. Authority-blocked | Spec06; Spec11; Dormitory UI; Main UI |
| D. Before UI | Product UI auth + Spec04 Auth residual product decision (+ Anti-Leak path) |
| E. Out of wave now | Workflow; Spec06; Spec11; OA-02-01; Full RBAC; Dependent; EmployeeRead; closed specs |
| F. Next candidate stream | **None** for execution; Auth residual → product decision gate |
| G. Next gate | `PRODUCT_AUTHORIZATION_GAP_TRIAGE` (after Spec04 Auth residual product decision: `SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY`) |

---

## 9. Recommended Next Gate

```text
PRODUCT_AUTHORIZATION_GAP_TRIAGE
```

**Progression:** This portfolio recommended `SPEC04_AUTH_RESIDUAL_PRODUCT_DECISION` next; that gate completed with `SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY`. Current wave next gate is product authorization gap triage.

**Why Spec04 Auth was the prior focus:** Spec04 Auth residual is the dominant **pre-UI** open residual; role mapping / Presentation / HTTP are merged into it. Product must authorize a named surface before any Core Completion Wave stream selection or UI-readiness path.

---

## Required Final Decision Block

```text
DEFERRED_PORTFOLIO_REVIEW_AND_DISPOSITION

Decision:
DEFERRED_PORTFOLIO_DISPOSITION_COMPLETED

Principle:
DEFERRED ≠ INCOMPLETE
Target = 100% Governance Decision Complete

Next Stream Execution:
NONE SELECTED

UI Wave:
NOT READY — Dormitory UI BLOCKED

Workflow / Spec06:
REMAIN DEFERRED / AUTHORITY-BLOCKED

Spec04 Auth Residual:
REMAINS OPEN — REQUIRE_PRODUCT_DECISION

Execution Authority:
NONE

Recommended Next Gate:
PRODUCT_AUTHORIZATION_GAP_TRIAGE
```

---

## Explicit Non-Authorization

This review does **not** authorize:

- application, test, contract, UI, lottery, workflow, or authorization implementation  
- Feature Contracts; stream execution selection  
- Spec02 unfreeze; role mapping coding; OA-02-01; HTTP/Policy coding  
- Spec03 / Spec04 Assignability / Spec07 reopen; Check-in un-retirement  
- Spec06/Spec11 new work; Workflow activation  

---

## No-Change Confirmation

`No application, test, contract, UI, lottery, workflow, or authorization implementation files were modified.`

Only this governance artifact was created (plus optional pointer updates in planning docs if applied in the same pass):

- `.specify/docs/planning/deferred-portfolio-review-and-disposition.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`DEFERRED_PORTFOLIO_DISPOSITION_COMPLETED`**  
- Recommended next gate at portfolio creation: **`SPEC04_AUTH_RESIDUAL_PRODUCT_DECISION`** (executed)  
- Current wave next gate: **`PRODUCT_AUTHORIZATION_GAP_TRIAGE`**  
- Last Updated: 2026-07-13  
- Checkpoint: `deferred-portfolio-review-and-disposition`
