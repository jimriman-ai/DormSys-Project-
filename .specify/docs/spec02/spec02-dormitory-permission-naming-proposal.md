---
artifact: spec02_dormitory_permission_naming_proposal
status: PROPOSAL_RECORDED
mutation_permission: none
execution_authority: none
operating_mode: NON_AUTHORIZING_DISCOVERY
decision: PROPOSAL_READY_FOR_HUMAN_DECISION
date: 2026-07-12
---

# Spec02 Dormitory Permission Naming and Granularity — Proposal Discovery

**Artifact type:** Naming/granularity proposal (non-authorizing)  
**Upstream vocabulary discovery:** `.specify/docs/spec02/spec02-dormitory-surface-permission-vocabulary-discovery.md` (`VOCABULARY_PARTIALLY_DEFINED`)  
**Status:** `PROPOSAL_RECORDED`

This artifact proposes a **vocabulary naming convention and granularity** for the **14** human-admin catalog actions marked `REQUIRES_UNDEFINED_PERMISSION`. It does **not** seed permissions, authorize implementation, design policies/PDP placement, name Integration-consumer auth (#9–#11, #18–#21), or reopen closed Spec04 Assignability / Check-in residuals.

---

## A. Current Context

Vocabulary discovery established:

| Finding | Value |
| ------- | ----- |
| Decision | `VOCABULARY_PARTIALLY_DEFINED` |
| Actions needing permission definitions | **14** (`REQUIRES_UNDEFINED_PERMISSION`) |
| Mutations | #1–#8 (create site/building/floor/room/bed; change dormitory/room/bed status) |
| Reads | #12–#17 (list/get hierarchy: dormitories → beds) |
| Existing Spatie Dormitory permissions | **None** |
| Seed naming precedent | `identity.users.manage`, `identity.users.view`, `identity.roles.manage`, `audit.read` |
| Product access grouping | Constitution §12 **Manage Rooms** — same ✅ for Admin / Dormitory Manager / Dormitory Unit Staff |

Without an agreed naming and granularity rule, formal vocabulary definition risks either:

- **over-permissioning** (one permission per Application method → naming explosion), or  
- **under-specified catch-alls** that obscure read vs mutation and Spec02/Spec04 ownership.

This proposal is required **before** Formal Permission Vocabulary Definition so human review can lock convention and grain.

**In scope:** only the 14 actions above.  
**Out of scope:** Integration/dual-path actions (#9–#11, #18–#21), Assign Bed, Check-in, Phase H UI policy design.

---

## B. Proposed Naming Convention

### Strategy name

**`module.resource.verb` (Identity-aligned dotted namespace)**

### Evidence basis

| Precedent | Pattern |
| --------- | ------- |
| `identity.users.manage` / `identity.users.view` | `{module}.{resource}.{verb}` |
| `identity.roles.manage` | same |
| `audit.read` | `{module}.{verb}` when the module is a single capability |
| Spec02 plan | `identity.*` for Identity admin; **domain permissions added as modules land** |
| Spec04 backend foundation plan (candidates only) | `dormitory.view`, `dormitory.manage_structure`, … — **planning candidates, not approved Spec02 vocabulary** |

### Convention rules (proposal)

1. **Module prefix:** `dormitory.` — Spec04 owns the domain surface; Spec02 owns the permission string catalog.  
2. **Resource segment:** `structure` — the 14 actions are all catalog hierarchy create/status/browse (site → bed), not Allocation assignability, not Check-in occupancy.  
3. **Verb segment:** reuse Identity’s established pair — `view` (read) and `manage` (mutation). Do **not** invent `create_building`, `change_room_status`, etc., as separate permission keys under this proposal.  
4. **Do not** use role names (`dormmgr.*`) as permission keys.  
5. **Do not** introduce a single catch-all `dormitory.manage` that collapses read and mutation (Identity separates `view` / `manage`).

### Proposed names for the 14 actions

| Permission key (proposed) | Covers actions | Read / mutation |
| ------------------------- | -------------- | --------------- |
| `dormitory.structure.view` | #12 List dormitories; #13 Get dormitory detail; #14 List buildings; #15 List floors; #16 List rooms; #17 List beds | Read |
| `dormitory.structure.manage` | #1 Create dormitory site; #2 Create building; #3 Create floor; #4 Create room; #5 Create bed; #6 Change dormitory status; #7 Change room status; #8 Change bed operability/status | Mutation |

### Illustrative action → key mapping (not policy logic)

| # | Action | Proposed permission |
| - | ------ | ------------------- |
| 1 | Create dormitory site | `dormitory.structure.manage` |
| 2 | Create building | `dormitory.structure.manage` |
| 3 | Create floor | `dormitory.structure.manage` |
| 4 | Create room | `dormitory.structure.manage` |
| 5 | Create bed | `dormitory.structure.manage` |
| 6 | Change dormitory status | `dormitory.structure.manage` |
| 7 | Change room status | `dormitory.structure.manage` |
| 8 | Change bed operability/status | `dormitory.structure.manage` |
| 12 | List dormitories | `dormitory.structure.view` |
| 13 | Get dormitory detail | `dormitory.structure.view` |
| 14 | List buildings | `dormitory.structure.view` |
| 15 | List floors | `dormitory.structure.view` |
| 16 | List rooms | `dormitory.structure.view` |
| 17 | List beds | `dormitory.structure.view` |

### Non-preferred alternatives (recorded for human contrast)

| Alternative | Why not preferred here |
| ----------- | ---------------------- |
| Spec04 plan candidates `dormitory.view` + `dormitory.manage_structure` | Same coarse intent, but verb/resource order differs from seeded `identity.users.*`; risks dual conventions |
| Fine keys per method (`dormitory.bed.create`, …) | 14 keys for one constitution capability → naming explosion; no evidence of split access among Manage Rooms roles |
| Single `dormitory.manage` | Collapses read/mutation contrary to Identity `view`/`manage` split |

---

## C. Granularity Recommendation

### Recommendation

**`COARSE`** — **two** permission keys for **fourteen** Application actions.

### Trade-offs considered

| Option | Keys for these 14 | Readability | Maintenance | Fit to evidence |
| ------ | ----------------- | ----------- | ----------- | --------------- |
| **Fine** (1:1 action→permission) | 14 | High local clarity | High seed/role-matrix churn; Phase H would wire 14 checks | **Weak** — constitution Manage Rooms does not split by hierarchy level |
| **Medium** (per entity: site/building/floor/room/bed × view/manage) | ~10 | Medium | Still fragments one admin job | **Weak** — no role matrix evidence for “can create rooms but not buildings” |
| **Coarse** (structure.view + structure.manage) | **2** | Clear browse vs mutate | Matches Identity users view/manage; stable under Phase H UI growth | **Strong** — same ✅ Manage Rooms audience |

### Justification

1. Constitution §12 treats **Manage Rooms** as one capability across Admin / Dormitory Manager / Dormitory Unit Staff — not per-entity create rights.  
2. Spec02 seed already uses **resource-level** `manage`/`view`, not per-field or per-method keys.  
3. Status changes (#6–#8) are catalog operability of the same structure tree as creates (#1–#5); splitting “create” vs “status” without product evidence would invent ABAC-like grain.  
4. Reads (#12–#17) are the browse surface of that same tree; a single `view` key prevents read-side naming explosion when Phase H Livewire appears.

**Out of this COARSE proposal:** Spec04 plan candidates for capacity / availability / physical_state / consume_* — those target actions **outside** the 14 (or Integration consumers) and must not be smuggled in here.

---

## D. Architectural Drift Prevention

| Drift risk | How this proposal mitigates |
| ---------- | --------------------------- |
| Spec04 inventing permission strings in Domain/Application | Naming stays Spec02-owned; Spec04 continues to **describe actions** only |
| Spec02 seeding Integration permissions for Allocation/Request bridges | Explicitly **excluded**; Integration Auth remains deferred (vocabulary discovery `INSUFFICIENT_EVIDENCE`) |
| Permission-per-Livewire-button explosion at Phase H | UI binds to the same two keys; new screens do not mint new permission names |
| Dual conventions (`dormitory.view` vs `dormitory.structure.view`) | Prefer Identity-aligned `module.resource.verb`; treat Spec04 plan list as superseded candidates for these 14 |
| Collapsing Assignability / Check-in into Manage Rooms | Resource segment is `structure` only — not `assign`, not `occupancy`, not Check-in |
| Catch-all `dormitory.*` wildcards in policies | Proposal defines **exact** two strings; no wildcard authority implied |

**Boundary alignment:** Spec04 enumerates protected actions; Spec02 names and catalogs permissions for human-admin catalog binding. Closed Assignability and Check-in residuals stay **not reopened**.

---

## E. Proposal Outcome

`PROPOSAL_READY_FOR_HUMAN_DECISION`

Granularity is not ambiguous for the 14 actions given constitution Manage Rooms + Identity view/manage precedent. Remaining human choice is formal acceptance (or deliberate override) of the two proposed keys and convention name.

---

## Required Final Decision Block

```text
PERMISSION_NAMING_AND_GRANULARITY_PROPOSAL

Decision:
PROPOSAL_READY_FOR_HUMAN_DECISION

Actions Covered:
14

Naming Strategy Proposed:
module.resource.verb (Identity-aligned dotted namespace)

Granularity Level:
COARSE

Primary Owner:
SPEC02

Selection Basis:
14 REQUIRES_UNDEFINED_PERMISSION catalog actions share Manage Rooms access; seed precedent is identity.users.view/manage; proposing dormitory.structure.view + dormitory.structure.manage avoids 1:1 naming explosion and excludes Integration/Assignability/Check-in.

Immediate Next Step:
Human Decision Checkpoint: Formal Permission Vocabulary Definition

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED
```

---

## Explicit Non-Authorization

This proposal does **not** authorize:

- seeding or renaming permissions in code  
- Spec02 unfreeze / Wave implementation  
- policy, middleware, Gate, or Livewire enforcement  
- role→permission attachment changes  
- Integration-consumer permission design  

---

## No-Change Confirmation

`No application, test, catalog, Spec04, vocabulary-discovery, or other Spec02 files were modified.`

Only this artifact was created:

- `.specify/docs/spec02/spec02-dormitory-permission-naming-proposal.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`PROPOSAL_RECORDED`** / **`PROPOSAL_READY_FOR_HUMAN_DECISION`**  
- Owner: Spec02 (Identity RBAC vocabulary)  
- Last Updated: 2026-07-12
