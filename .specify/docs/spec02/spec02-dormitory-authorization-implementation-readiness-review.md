---
artifact: spec02_dormitory_authorization_implementation_readiness_review
status: READINESS_RECORDED
mutation_permission: none
execution_authority: none
operating_mode: NON_IMPLEMENTING_READINESS_REVIEW
current_authorization_state: AUTHORIZATION_PENDING
readiness_decision: NEEDS_HUMAN_DECISION_BEFORE_AUTHORIZATION
date: 2026-07-13
---

# Spec02 Dormitory Authorization — Implementation Readiness Review

**Artifact type:** Readiness / dependency / scope-containment review (non-implementing)  
**Upstream contract:** `.specify/docs/spec02/spec02-dormitory-formal-permission-vocabulary-definition.md` (`FORMAL_VOCABULARY_DEFINED`)  
**Upstream review:** `.specify/docs/spec02/spec02-dormitory-permission-contract-review-record.md` (`CONTRACT_ACCEPTED`)  
**Status:** `READINESS_RECORDED`

This review determines whether the accepted coarse vocabulary contract is ready to enter a **Human Authorization Approval** gate. It does **not** implement, seed, authorize coding, redefine vocabulary, resolve the seven unresolved actions, or reopen Spec04 residuals.

---

## A. Executive Summary

| Item | Value |
| ---- | ----- |
| Current authorization state | `AUTHORIZATION_PENDING` |
| Vocabulary keys | **2** (`COARSE`): `dormitory.structure.view`, `dormitory.structure.manage` |
| Covered actions | **14** (#1–#8 manage; #12–#17 view) |
| Unresolved actions | **7** (`KEEP_UNRESOLVED`) |
| Contract review | `CONTRACT_ACCEPTED` — boundaries `SPECIFIC`; ADR update not required |
| Implementation authority | **NONE** |
| Scope integrity | `CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED` — **reaffirmed** |

**Readiness snapshot:** Vocabulary and contract quality are sufficient to *describe* a bounded enforcement packet. Proceeding to Implementation Authorization is **blocked pending human decisions** on (1) Spec02 freeze/seed authority boundary, (2) enforcement placement for Application-only surfaces, (3) role→permission grants, and (4) stance toward already-implemented unresolved Application/Integration call paths.

---

## B. Enforcement Surfaces Inventory (Non-Implementing)

Evidence: Spec04 action enumeration + dependency clarification; Dormitory Presentation directories are **empty placeholders** (`.gitkeep` only); no Dormitory Policy/Gate/`authorize` in `app/Modules/Dormitory`.

### B.1 Read surfaces (covered → `dormitory.structure.view`)

| Conceptual surface | Action refs | Permission | Covered-only? |
| ------------------ | ----------- | ---------- | ------------- |
| Application `DormitoryStructureReadContract` — list/detail hierarchy | #12–#17 | `dormitory.structure.view` | **Yes** — covered only |
| Future Phase H Livewire catalog browse (deferred; not present) | #12–#17 | `dormitory.structure.view` | Would be covered-only when authorized |
| Future HTTP/API read controllers (deferred; not present) | #12–#17 | `dormitory.structure.view` | Same |

### B.2 Mutation surfaces (covered → `dormitory.structure.manage`)

| Conceptual surface | Action refs | Permission | Covered-only? |
| ------------------ | ----------- | ---------- | ------------- |
| Application `DormitoryStructureMutationContract` — create hierarchy + status | #1–#8 | `dormitory.structure.manage` | **Yes** — covered only |
| Future Phase H Livewire catalog mutations (deferred) | #1–#8 | `dormitory.structure.manage` | Would be covered-only when authorized |
| Future HTTP/API mutation controllers (deferred) | #1–#8 | `dormitory.structure.manage` | Same |

### B.3 Cross-cutting / unresolved-touching surfaces (flagged)

| Conceptual surface | Action refs | Risk if coarse keys misapplied |
| ------------------ | ----------- | ------------------------------ |
| Application occupancy-marker APIs | #9–#10 | Must **not** inherit `structure.manage` |
| Application/Integration Allocation physical-state `apply` | #11 | Must **not** inherit `structure.manage` |
| Allocation assignability Integration reads | #18–#20 | Must **not** inherit `structure.view` |
| Request `siteExists` Integration read | #21 | Must **not** inherit `structure.view` |

### B.4 Cross-cutting entry patterns (conceptual only)

| Layer | Role if later authorized |
| ----- | ------------------------ |
| Routing / controllers / Livewire | Deferred Presentation — not present today |
| Application mutation/read handlers | **Primary current binding target** for covered #1–#8 / #12–#17 |
| Query/command orchestration | Same Application contracts — enforce on covered methods only |

---

## C. Dependency Readiness Check

| Foundation | Evidence | Assessment |
| ---------- | -------- | ---------- |
| Identity/RBAC base (Spec02 Wave 1A) | Spatie tables; `UserModel` `HasRoles`; `IdentityRoleSeeder`; Spec02 Frozen Wave 1A Complete | **READY** as platform foundation |
| Permission storage | Spatie permission catalog already used for `identity.*`, `audit.read` | **READY** (mechanism exists; dormitory keys **not** seeded) |
| Permission check API | `IdentityUserReadContract::userHasPermission` | **READY** |
| Principal resolution | Mutation/audit principal patterns exist elsewhere; OA-02-01 login UX deferred | **PARTIAL** — sufficient for Application PEP with principal id; interactive session UX still deferred (not required to bind Application checks) |
| Guard/policy framework for Dormitory | Dependency clarification gap #3; Audit Application PEP + CheckIn role gate are **pattern references only**; no Dormitory placement decision recorded | **NOT DECIDED** |
| Role→permission mapping for Manage Rooms | `DormMgr` seeded with `audit.read` only; constitution Manage Rooms not mapped to `dormitory.structure.*` | **NOT DECIDED** |
| Spec02 freeze / domain-permission landing | Spec02 Frozen; plan: “domain permissions added as modules land”; D3 does not authorize Spec02 unfreeze | **HUMAN GATE REQUIRED** |

**Dependency output:** **BLOCKED** for immediate Implementation Authorization until the human decisions in §G are recorded.

**Missing prerequisite artifacts / decisions (not inventable here):**

1. Human decision: Spec02 reopen / seed-authorization boundary for `dormitory.structure.view` / `dormitory.structure.manage` only.  
2. Human decision: Enforcement placement for first packet (Application PEP on covered contracts vs wait for Presentation).  
3. Human decision: Role→permission grant set (which seeded/constitution roles receive which key).  
4. Human decision: Unresolved Application/Integration call-path stance (§F).

---

## D. Boundary Enforcement Feasibility (Critical)

**Can enforcement be implemented strictly using the accepted include/exclude lists?**

| Check | Result |
| ----- | ------ |
| `dormitory.structure.view` ↔ #12–#17 only | **Feasible** — maps 1:1 to `DormitoryStructureReadContract` hierarchy reads |
| `dormitory.structure.manage` ↔ #1–#8 only | **Feasible** — maps 1:1 to structure create + status methods on Mutation contract |
| Forbid treating `manage` as “anything dormitory-related” | **Feasible if** enforcement is method-scoped to covered actions and unresolved methods are explicitly out of those checks |
| Covered action cannot be cleanly enforced? | **None proven** — no `NEEDS_ADJUSTMENT` on vocabulary boundaries |

**Hard rule reaffirmed:** `dormitory.structure.manage` must never authorize #9–#11, #18–#21, Assign Bed, or Check-in.

Contract clarification is **not** required for boundary ambiguity. Blockers are **authorization-packet decisions**, not vocabulary defects.

---

## E. Spec Boundary Safety Re-Validation

| Risk | Finding |
| ----- | ------- |
| Allocation permission coupling | **Not required** for covered #1–#8 / #12–#17. Assignability reads #18–#20 remain unresolved/excluded. |
| Check-in / Check-out permission coupling | **Not required**. Spec07 Operator pattern is reference only; Check-in residual stays closed. |
| Spec04 Assignability residual reopen | **Not required** for structure view/manage enforcement. |
| Spec04 Check-in residual reopen | **Not required**. |

**No Spec-boundary coupling blocker** for a packet limited to covered structure actions. Coupling would only appear if an authorization mistakenly bundled unresolved Integration actions into the same packet — **forbidden**.

---

## F. Unresolved Actions Handling Plan (Non-Implementing)

| Rule | Statement |
| ---- | --------- |
| Vocabulary | Keys for #9–#11, #18–#21 remain **undefined** (`KEEP_UNRESOLVED`) |
| Coarse inheritance | Implementation must **not** silently grant unresolved actions via `dormitory.structure.view` / `manage` |
| Evidence of exposure | Enumeration records these seven as **Implemented** Application/Integration call paths (not Presentation UI) |

**Stance selection:** Evidence alone does **not** authorize choosing among:

| Option | Meaning |
| ------ | ------- |
| `DENY_BY_DEFAULT_UNTIL_RESOLVED` | When any enforcement wave lands, unresolved methods deny without a defined permission |
| `HARD_BLOCK_SURFACE_EXPOSED` | Treat callable Application/Integration paths as exposed; hard-block until Integration Auth discovery |
| `DEFER_ENFORCEMENT_NOT_EXPOSED` | **Not supported by evidence** — methods exist in Application/Integration today |

**Required human decision:** Choose `DENY_BY_DEFAULT_UNTIL_RESOLVED` **or** `HARD_BLOCK_SURFACE_EXPOSED` for the seven unresolved actions relative to the first enforcement packet. Do **not** select `DEFER_ENFORCEMENT_NOT_EXPOSED` without new evidence that those surfaces are unreachable.

---

## G. Readiness Decision

`NEEDS_HUMAN_DECISION_BEFORE_AUTHORIZATION`

### Decision rationale

1. **Contract quality is accepted** — vocabulary keys, COARSE grain, and finite boundaries are fit for a later bounded implementation packet.  
2. **Platform RBAC machinery is ready** — Spatie + `userHasPermission` exist.  
3. **Authorization cannot safely proceed** without recorded human answers on Spec02 freeze/seed scope, enforcement placement, role grants, and unresolved-call-path stance — otherwise a future IA risks Spec02 freeze violation, super-user-style coarse over-grant, or silent allow on Integration paths.  
4. These are **not** vocabulary redefinitions and **not** Spec04 residual reopens.

### Blockers (decision blockers, not contract defects)

| ID | Blocker |
| -- | ------- |
| B1 | Spec02 Frozen Wave 1A — no recorded authority to land `dormitory.structure.*` in seed/catalog |
| B2 | Guard/policy placement for first Dormitory enforcement packet undecided |
| B3 | Role→permission mapping for Manage Rooms roles undecided |
| B4 | Unresolved Application/Integration enforcement stance undecided |

### Minimal authorization scope proposal (still no code; for after human decisions)

If B1–B4 are answered, a future Human Authorization Approval **may** consider a packet limited to:

1. Spec02-owned seed/catalog of **only** `dormitory.structure.view` and `dormitory.structure.manage`.  
2. Explicit role grants per human decision (no implied SystemAdministrator/dormitory-all).  
3. Application-layer checks **only** on covered methods (#1–#8 / #12–#17).  
4. Explicit non-coverage of #9–#11, #18–#21 per chosen unresolved stance.  
5. **No** Presentation/UI, **no** Assignability reopen, **no** Check-in reopen, **no** new permission keys.

This proposal is **not** authorization.

---

## H. Next Step Recommendation

**Next step:** `RECORD_HUMAN_DECISION`

Capture answers in a dedicated Spec02 human-decision artifact (suggested path: `.specify/docs/decisions/spec02-dormitory-authorization-pre-ia-decisions.md` or equivalent), answering:

1. **Spec02 seed authority:** May the next IA authorize seeding only `dormitory.structure.view` and `dormitory.structure.manage` under Spec02 ownership despite Wave 1A freeze? (`YES_BOUNDED_SEED` / `NO_DEFER` / other)  
2. **Enforcement placement for first packet:** `APPLICATION_PEP_COVERED_CONTRACTS_ONLY` / `WAIT_FOR_PRESENTATION_SURFACE` / other  
3. **Role→permission grants:** Which roles receive `view`, which receive `manage`? (Must not invent roles; map from evidenced `DormMgr` / constitution Manage Rooms set)  
4. **Unresolved stance:** `DENY_BY_DEFAULT_UNTIL_RESOLVED` **or** `HARD_BLOCK_SURFACE_EXPOSED` (not `DEFER_ENFORCEMENT_NOT_EXPOSED`)

After that artifact exists with binding answers → re-run readiness or proceed to **Human Authorization Approval** gate (still not implementation).

---

## Required Final Block

```text
SPEC02_DORMITORY_AUTHORIZATION_IMPLEMENTATION_READINESS_REVIEW

Current Authorization State:
AUTHORIZATION_PENDING

Contract Status:
CONTRACT_ACCEPTED

Naming Strategy:
ACCEPT_MODULE_RESOURCE_VERB

Granularity:
COARSE

Approved Keys:
dormitory.structure.view
dormitory.structure.manage

Covered Actions:
14

Unresolved Actions:
7 (KEEP_UNRESOLVED)

Readiness Decision:
NEEDS_HUMAN_DECISION_BEFORE_AUTHORIZATION

Implementation Authority:
NONE

Next Step:
RECORD_HUMAN_DECISION

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED

Confirmation:
Only the readiness review artifact was created; no other files changed.
```

---

## Explicit Non-Authorization

This readiness review does **not** authorize implementation, seeding, Spatie sync, policies, middleware, UI gating, role assignment, vocabulary expansion, unresolved-action definition, or Spec04 residual reopen.

---

## No-Change Confirmation

`Only the readiness review artifact was created; no other files changed.`

- `.specify/docs/spec02/spec02-dormitory-authorization-implementation-readiness-review.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`READINESS_RECORDED`** / **`NEEDS_HUMAN_DECISION_BEFORE_AUTHORIZATION`**  
- Last Updated: 2026-07-13
