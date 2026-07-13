---
artifact: spec04_auth_residual_post_binding_status_refresh
status: AUTH_RESIDUAL_REFRESH_STATUS_COMPLETED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
decision: AUTH_RESIDUAL_REFRESH_STATUS_COMPLETED
upstream_workflow_decision: WORKFLOW_REMAINS_DEFERRED
upstream_gate: workflow-activate-vs-defer-decision.md
recommended_next_gate: SPEC06_CORE_WAVE_INCLUSION_DECISION
date: 2026-07-13
---

# Spec04 Auth Residual — Post-Binding Status Refresh

**Artifact type:** Governance / status / readiness clarification (non-authorizing)  
**Status:** `AUTH_RESIDUAL_REFRESH_STATUS_COMPLETED`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Upstream:**  
- Spec02 limited closeout — `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED`  
- Workflow decision — `WORKFLOW_REMAINS_DEFERRED`  
- Core Completion Wave Plan — Auth residual refresh as non-executing wave gate  

This artifact refreshes the Spec04 Auth residual picture after Application-layer structure binding closed and Workflow remained deferred. It does **not** authorize implementation, reopen closed packets, claim full Auth/RBAC/UI/role-mapping completion, or select an execution stream.

---

## 1. Residual Baseline

| Topic | Settled posture |
| ----- | --------------- |
| Spec02 bounded packet | `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED` — closed |
| Overall Spec02 | **Frozen — Wave 1A Complete** |
| Spec03 | `SPEC03_CLOSED` |
| Spec04 Backend | `SPEC04_BACKEND_CLOSED` |
| Spec04 Assignability | `CLOSED` / `FULLY_CLOSED` |
| Spec04 Check-in residual | `RETIRED_FROM_ACTIVE_SPEC04_TRACKING` / `CLOSED_NO_FURTHER_ACTION` |
| Spec04 Product | `PENDING_RESIDUAL` — Auth remainder + UI still open |
| Workflow | `WORKFLOW_REMAINS_DEFERRED` |
| Ownership (D3) | Spec02 Identity owns Auth foundation; Spec04 tracks Dormitory-surface auth deferral historically — does not own Identity |

**Workflow effect on Auth residual:** Deferral does **not** change Auth residual shape. Auth remainder is independent of Workflow module activation (CD-010 Request owns approval state; Workflow owns transition rules when activated). Auth surfaces below remain the open Spec04 Product Auth concern.

---

## 2. Completed Auth Surface

Treat the following as **closed** and **not reopenable** under this residual:

### Closed packet (Spec02-owned; consumed by Spec04 tracking)

| Element | Closed content |
| ------- | -------------- |
| Packet label | Dormitory Structure Authorization Binding |
| Closeout | `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED` |
| Permission keys | `dormitory.structure.view`, `dormitory.structure.manage` only |
| Covered actions | `#1–#8` → manage; `#12–#17` → view |
| Enforcement layer | **Application-layer PEP only** (`DormitoryStructureAuthorizationGate` on structure Mutation/Read services) |
| Seed | Keys registered; **no** dormitory role grants |
| Unresolved structure actions `#9–#11`, `#18–#21` | Deny-by-default relative to structure keys (not granted via this packet) |

**Explicit non-claims for this completed surface:**

```text
NOT: full Spec02 authorization
NOT: full Spec04 Auth residual closure
NOT: full RBAC
NOT: UI / Presentation auth
NOT: HTTP / middleware / Policy-class surface auth
NOT: role → dormitory.structure.* mapping
NOT: OA-02-01 / Livewire admin
```

**Answers — A. Completed Surface:** Application-layer dormitory **structure** PEP binding for the two approved keys on covered structure Mutation/Read actions only (Spec02 bounded packet). That packet is closed; it is **not** the whole Spec04 Auth residual.

---

## 3. Remaining Auth Residual Surface

Spec04 Auth residual remains **NOT closed**. Remaining open surfaces:

| ID | Remaining surface | Owner class (planning) |
| -- | ----------------- | ---------------------- |
| R1 | **Role → permission mapping** for `dormitory.structure.view` / `dormitory.structure.manage` (no grants shipped with binding packet) | Spec02 Identity (D3) — separate packet / IA required |
| R2 | **Presentation / Livewire / Blade** authorization for Dormitory admin or structure UI surfaces | Spec02 foundation + future UI feature path (D4 UI separate) |
| R3 | **HTTP / route / middleware / Policy-class** guards for Dormitory presentation or HTTP entrypoints when those surfaces exist | Spec02-owned binding consuming Identity; not Spec04 domain ownership |
| R4 | **Broader dormitory authorization** beyond the two structure keys (Allocation/Check-in auth, additional permission vocabulary, unresolved `#9–#11` / `#18–#21` affirmative grants) | Separate Spec02 vocabulary/IA decisions — out of closed structure packet |
| R5 | **OA-02-01 / Identity Livewire admin / Spec02 auth UX** | Spec02 deferred Wave 1A items — not Spec04 Auth execution |
| R6 | **Dormitory UI product authorization** (`dormitory-admin-ui` intake) | Product authority (independent of Auth coding) — blocks UI readiness |

**Answers — B. Remaining Residual Surface:** R1–R6 above. Spec04 continues to track Auth as Product `PENDING_RESIDUAL`; the closed Application PEP packet does not retire the residual as a whole.

---

## 4. Residual Status Classification

| ID | Current status | Why not executable now | Authority / decision needed later |
| -- | -------------- | ---------------------- | --------------------------------- |
| R1 Role mapping | **Deferred** + **pending separate Spec02 IA** | Binding packet explicitly excluded role grants; Spec02 Frozen | Human Spec02-owned IA for role→`dormitory.structure.*` only |
| R2 Presentation auth | **Blocked** + **pending product UI path** | No authorized Dormitory UI surface; UI Anti-Leak requires backend capability/auth authority, not UI-invented checks | Product auth for UI slug + Feature Contract / IA; Auth binding packet scoped to Presentation |
| R3 HTTP / Policy surface | **Deferred** + **pending surface targets** | No Spec04-authorized HTTP admin surface; no Dormitory Policy classes as Spec04 backend deliverable | Spec02-owned surface-binding packet after surfaces exist; not Spec04 domain IA |
| R4 Broader dormitory auth | **Out of current wave execution scope** (and out of closed packet) | Would reopen/expand vocabulary beyond approved keys | Separate Spec02 vocabulary + IA; must not reopen closed structure packet |
| R5 OA-02-01 / Livewire admin | **Deferred** (Spec02 Wave 1A) | Catalog freeze; not Auth residual “next coding” | Spec02 unfreeze / product reopen decision — **not** this refresh |
| R6 Dormitory UI product auth | **Blocked** / **pending product authority** | UI triage fails without product authorization | Explicit product authorization for named UI slug |

**Answers — C. Residual Classification:** As in the table. None of R1–R6 are authorized for implementation by this refresh.

---

## 5. Dormitory UI Readiness Effect

**Dormitory UI readiness remains blocked.**

Exact blockers (both still active after structure binding + Workflow deferral):

1. **Missing product authority** for `dormitory-admin-ui` (or any named Dormitory UI slug) — Ownership D4 / UI triage  
2. **Missing Auth remainder disposition for Presentation/HTTP** — Application PEP alone does not constitute product-authorized UI auth readiness; role mapping (R1) and Presentation/HTTP binding (R2–R3) are still deferred  

Structure Application PEP **helps** a future UI path (backend enforcement exists for covered structure services) but does **not** unblock UI intake or authorize Feature Contract / implementation.

**Answers — D. Dormitory UI Effect:** Still blocked — primarily by missing product UI authorization; secondarily by unfinished Auth remainder (role mapping + Presentation/HTTP) for any executable admin surface.

---

## 6. Hard Boundaries Preserved

| Boundary | Posture |
| -------- | ------- |
| Spec02 structure-binding packet | **Closed** — do not reopen |
| Overall Spec02 | **Frozen — Wave 1A Complete** |
| Workflow | **Remains deferred** (`WORKFLOW_REMAINS_DEFERRED`) |
| Spec03 | **Closed** |
| Spec04 Assignability | **Closed** |
| Spec04 Check-in residual | **Retired** |
| Spec04 Backend Phases 1–4 | **Closed** — not reopenable |
| Spec07 | Fully Closed — not reopened by Auth residual |
| Full RBAC / UI auth / role mapping / OA-02-01 | **Not completed** — must not be claimed |
| Implementation / UI / role-mapping / HTTP / Spec06 coding | **Unauthorized** by this artifact |
| Stream selection | **Not performed** — refresh ≠ selection |

**Answers — E. Non-Reopen Boundaries:** Spec02 bounded packet + Spec02 freeze; Spec03; Spec04 Assignability; Spec04 Check-in retirement; Spec04 Backend; Spec07; closed structure PEP packet.

---

## 7. Recommended Next Governance Gate

```text
SPEC06_CORE_WAVE_INCLUSION_DECISION
```

**Why:** Auth residual status is now refreshed and authoritative for later wave gating. Workflow membership is already decided (deferred). Per Core Completion Wave Plan, Spec06 inclusion/continuation remains the outstanding membership decision before hygiene and stream selection. This gate stays **non-executing** (inclusion decision only — no Spec06 implementation).

**Answers — F. Next Governance Use:** Use this refresh as the **authoritative residual-state picture** when later gates assess Auth-related eligibility (e.g., whether a role-mapping readiness gate, Presentation auth packet, or UI readiness triage can even be considered). Do **not** treat this refresh as Implementation Authorization, stream selection, contract drafting, or product UI approval.

---

## Required Questions Summary

| Q | Answer |
| - | ------ |
| A. Completed | Application-layer structure PEP binding for `dormitory.structure.view` / `manage` on `#1–#8` / `#12–#17` (Spec02 packet closed) |
| B. Remaining | Role mapping; Presentation/Livewire auth; HTTP/Policy guards; broader auth beyond structure keys; OA-02-01; Dormitory UI product auth |
| C. Classification | Deferred / blocked / pending product authority / out of wave execution — see §4 |
| D. UI effect | Still blocked (product auth + Auth remainder for Presentation/HTTP) |
| E. Non-reopen | Spec02 packet + freeze; Spec03; Spec04 Assignability/Check-in/Backend; Spec07 |
| F. Use | Status input to later wave gates — not execution authority |

---

## Required Final Decision Block

```text
SPEC04_AUTH_RESIDUAL_POST_BINDING_STATUS_REFRESH

Refresh Status:
COMPLETED

Completed Auth Surface:
Application-layer structure PEP binding only
(SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED)

Spec04 Auth Residual:
NOT CLOSED — remainder open at UI / Presentation / HTTP / role-mapping / broader auth

Workflow:
REMAINS DEFERRED — no Auth-shape change

Dormitory UI:
STILL BLOCKED — product auth + Auth remainder

Execution Authority:
NONE

Recommended Next Gate:
SPEC06_CORE_WAVE_INCLUSION_DECISION
```

---

## Explicit Non-Authorization

This refresh does **not** authorize:

- application, test, contract, or authorization implementation changes  
- role mapping, HTTP/Policy, Presentation, or UI coding  
- OA-02-01 / Spec02 unfreeze  
- Spec06 implementation  
- Spec02 / Spec03 / closed Spec04 residual reopen  
- Core Completion Wave stream selection or Implementation Authorization  

---

## No-Change Confirmation

`No application, test, contract, or authorization implementation files were modified.`

Only this status-refresh artifact was created:

- `.specify/docs/spec04/spec04-auth-residual-post-binding-status-refresh.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`AUTH_RESIDUAL_REFRESH_STATUS_COMPLETED`**  
- Spec04 Auth residual: **NOT closed** (remainder open)  
- Recommended next gate: **`SPEC06_CORE_WAVE_INCLUSION_DECISION`**  
- Last Updated: 2026-07-13  
- Checkpoint: `spec04-auth-residual-post-binding-status-refresh`
