---
artifact: spec02_dormitory_surface_permission_vocabulary_discovery
status: DISCOVERY_RECORDED
mutation_permission: none
execution_authority: none
operating_mode: NON_AUTHORIZING_DISCOVERY
decision: VOCABULARY_PARTIALLY_DEFINED
date: 2026-07-12
---

# Spec02 Dormitory-Surface Permission Vocabulary — Discovery (Resumed)

**Artifact type:** Vocabulary discovery (non-authorizing)  
**Upstream dependency clarification:** `.specify/docs/spec02/spec02-auth-dormitory-surface-authorization-dependency-clarification.md`  
**Upstream action enumeration:** `.specify/docs/spec04/spec04-dormitory-protected-action-enumeration-discovery.md` (`ACTIONS_ENUMERATED`, 21 actions)  
**Status:** `DISCOVERY_RECORDED`

This discovery maps authorization vocabulary to the **enumerated Dormitory protected actions only**. It does **not** invent final permission string names, authorize seeding/coding, unfreeze Spec02, design PDP/PEP placement, or reopen closed Spec04 Assignability / Check-in residuals.

---

## A. Current Context

1. Spec02 owns Identity RBAC → Dormitory-surface authorization binding (dependency clarification; Ownership D3).  
2. Spec04 enumerated **21** Application/Integration actions as domain input (`ACTIONS_ENUMERATED`).  
3. Prior vocabulary pass was blocked for missing action enumeration; that block is lifted. This pass discovers what vocabulary **exists** vs what is **required but undefined** for those 21 actions.

---

## B. Discovery Basis

| Artifact / evidence | Use |
| ------------------- | --- |
| `spec04-dormitory-protected-action-enumeration-discovery.md` | Bounded action list (#1–#21) |
| `spec02-auth-dormitory-surface-authorization-dependency-clarification.md` | Spec02 ownership; human vs Integration split |
| `spec04-auth-integration-residual-readiness-review.md` | Auth residual = surface binding |
| `IdentityRoleSeeder.php` | Existing Spatie roles/permissions |
| `PlatformRoles.php` | `SystemAdministrator` constant |
| Spec02 `spec.md` / `plan.md` / `data-model.md` | Wave 1A RBAC baseline; domain permissions deferred |
| Constitution §12 Permission Matrix | Manage Rooms role intent (plain-text) |
| `app/Modules/Dormitory/**` | **No** Policy/Gate/`authorize`/Spatie checks |

**Bound:** Only the 21 enumerated Dormitory protected actions. No hypothetical workflows.

---

## C. Enumerated Action Input

| Field | Value |
| ----- | ----- |
| Source | `.specify/docs/spec04/spec04-dormitory-protected-action-enumeration-discovery.md` |
| Total actions | **21** |
| Mutations / Reads | **11** / **10** |
| In code / Spec-only | **21** / **0** |
| Presentation auth surfaces | None (Application/Integration only; Phase H deferred) |

---

## D. Roles

Roles evidenced and relevant to enumerated actions (Manage Rooms / catalog admin or seeded names). No new roles invented.

| Name | Source | Description | Status |
| ---- | ------ | ----------- | ------ |
| `DormMgr` | `IdentityRoleSeeder::ROLE_DORM_MGR` | Seeded role name; currently holds only `audit.read` — **no** dormitory permission attachment | **EXISTING** |
| `Dormitory Manager` | Constitution §12 (Manage Rooms ✅) | Product matrix display name aligned with dormitory catalog administration | **EXISTING** (spec matrix; maps conceptually to `DormMgr` seed name) |
| `Dormitory Unit Staff` | Constitution §12 (Manage Rooms ✅) | Product matrix role for manage rooms; **no** matching seed role name found | **EXISTING** (spec matrix only) |
| `Admin` | Constitution §12 (Manage Rooms ✅) | Product matrix role for manage rooms / system config; distinct from seeded `Administrator` | **EXISTING** (spec matrix only) |

**Not counted as Dormitory-surface roles for this mapping:** `SystemAdministrator`, `Administrator`, `HRMgr` (Identity/audit seed only); `Operator` (Check-in — out of Spec04 residual).

**Roles discovered (decision count):** **4**

**New vocabulary items (roles):** **0**

---

## E. Permissions

### Existing platform permissions (not Dormitory-action vocabulary)

| Name | Related action(s) | Source | Read/mutation | Status |
| ---- | ----------------- | ------ | ------------- | ------ |
| `identity.users.manage` | None of #1–#21 | `IdentityRoleSeeder` | Mutation (Identity) | **EXISTING** (out of Dormitory action scope) |
| `identity.users.view` | None of #1–#21 | `IdentityRoleSeeder` | Read (Identity) | **EXISTING** (out of scope) |
| `identity.roles.manage` | None of #1–#21 | `IdentityRoleSeeder` | Mutation (Identity) | **EXISTING** (out of scope) |
| `audit.read` | None of #1–#21 as Dormitory surface auth | `IdentityRoleSeeder` (also on `DormMgr`) | Read (Audit) | **EXISTING** (out of Dormitory catalog scope) |

**No EXISTING Spatie permission strings map to any of the 21 Dormitory actions.**

### Required but undefined (gaps — no invented keys)

| Name | Related action(s) | Source | Read/mutation | Status |
| ---- | ----------------- | ------ | ------------- | ------ |
| `REQUIRED_BUT_UNDEFINED` — human admin catalog **mutations** (Manage Rooms) | #1–#8 | Constitution Manage Rooms + Spec04 enumeration plain-text rules; no seed permission | Mutation | **REQUIRED_BUT_UNDEFINED** |
| `REQUIRED_BUT_UNDEFINED` — human admin catalog **reads** (Manage Rooms browse) | #12–#17 | Same; Phase H would expose these | Read | **REQUIRED_BUT_UNDEFINED** |

Integration/system actions (#9–#11, #18–#21) do **not** get invented Integration permission names here; they are recorded as mapping **INSUFFICIENT_EVIDENCE** in §F (no Spec02 vocabulary evidenced for machine consumers).

**Permissions discovered (decision count):** **2** Dormitory-relevant gap records + **0** EXISTING dormitory permissions. (Platform `identity.*` / `audit.read` listed for completeness but not counted as Dormitory vocabulary.)

**For decision block “Permissions Discovered”:** **2** (the REQUIRED_BUT_UNDEFINED gap records that apply to enumerated human-admin actions).

**New vocabulary items (permissions):** **0** (no final names proposed)

---

## F. Action-to-Permission Mapping

| # | Action | Surface | R/M | Existing mapped role/permission | Mapping status |
| - | ------ | ------- | --- | ------------------------------- | -------------- |
| 1 | Create dormitory site | Application | M | None — constitution Manage Rooms roles only (plain-text) | **REQUIRES_UNDEFINED_PERMISSION** |
| 2 | Create building | Application | M | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 3 | Create floor | Application | M | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 4 | Create room | Application | M | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 5 | Create bed | Application | M | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 6 | Change dormitory status | Application | M | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 7 | Change room status | Application | M | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 8 | Change bed operability/status | Application | M | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 9 | Record bed occupancy start | Application | M | Dual path vs Allocation signal; no permission | **INSUFFICIENT_EVIDENCE** |
| 10 | Record bed occupancy end | Application | M | Dual path vs Allocation signal; no permission | **INSUFFICIENT_EVIDENCE** |
| 11 | Apply Allocation physical-state signal | Application + Integration | M | System/Integration consumer; no Spec02 Integration permission vocabulary evidenced | **INSUFFICIENT_EVIDENCE** |
| 12 | List dormitories | Application | R | None — Manage Rooms browse (plain-text) | **REQUIRES_UNDEFINED_PERMISSION** |
| 13 | Get dormitory detail | Application | R | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 14 | List buildings | Application | R | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 15 | List floors | Application | R | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 16 | List rooms | Application | R | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 17 | List beds | Application | R | None | **REQUIRES_UNDEFINED_PERMISSION** |
| 18 | Bed exists | Application + Integration | R | Allocation Integration; no permission vocabulary | **INSUFFICIENT_EVIDENCE** |
| 19 | Is bed assignable | Application + Integration | R | Allocation Integration; no permission vocabulary | **INSUFFICIENT_EVIDENCE** |
| 20 | Get physical occupancy state | Application + Integration | R | Allocation Integration; no permission vocabulary | **INSUFFICIENT_EVIDENCE** |
| 21 | Site exists (Request) | Integration | R | Request Integration; no permission vocabulary | **INSUFFICIENT_EVIDENCE** |

**Action mapping status totals:**

| Status | Count |
| ------ | ----- |
| `MAPPED_TO_EXISTING_VOCABULARY` | **0** |
| `REQUIRES_UNDEFINED_PERMISSION` | **14** (#1–#8, #12–#17) |
| `INSUFFICIENT_EVIDENCE` | **7** (#9–#11, #18–#21) |

---

## G. Current Enforcement Pattern

| Pattern | Evidence |
| ------- | -------- |
| Spatie RBAC seed (`identity.*`, `audit.read`, roles including `DormMgr`) | `IdentityRoleSeeder` — **does not** enforce Dormitory actions |
| Dormitory Policy / Gate / middleware / Livewire `authorize` | **Absent** — no matches under `app/Modules/Dormitory` |
| Application mutation/read services | Execute **without** Identity permission checks |
| Integration bridges (Allocation/Request) | Composition-root wiring — **not** Spec02 permission vocabulary enforcement on Dormitory surfaces |
| CheckIn `OperatorRoleGate` → `IdentityUserReadContract` | **Pattern reference only** — not applied to Spec04 Dormitory actions |

**Current enforcement for the 21 actions:** effectively **none** at Auth/RBAC layer.

---

## H. Vocabulary Completeness Assessment

**Partially defined with explicit gaps:**

- Roles for human **Manage Rooms** intent are evidenced (constitution + `DormMgr` seed name).  
- **No** EXISTING permission strings bind to any of the 21 actions.  
- **14** human-admin catalog actions clearly need Spec02 permission vocabulary (`REQUIRED_BUT_UNDEFINED`).  
- **7** Integration/dual-path actions lack enough Auth evidence to map without inventing Integration permission model.  
- Enforcement patterns for Dormitory actions are absent in code.

Not fully defined; not blocked on missing Spec04 action list (that input is complete).

---

## I. Discovery Outcome

`VOCABULARY_PARTIALLY_DEFINED`

---

## J. Immediate Next Step

**Create a Spec02-owned non-authorizing permission-naming proposal** limited to the **14** `REQUIRES_UNDEFINED_PERMISSION` human-admin catalog actions (#1–#8 mutations, #12–#17 reads), using Spec02 naming conventions (`dormitory.*` or project-standard namespaces) — **proposal only**, no seeder/IA/code.

Separately defer (do not invent here) Integration-consumer Auth for actions #9–#11 and #18–#21 until a Spec02 Integration-auth clarification exists.

Do **not** unfreeze Spec02 or authorize implementation in that proposal step.

---

## Required Final Decision Block

```text
DORMITORY_PERMISSION_VOCABULARY_DISCOVERY

Decision:
VOCABULARY_PARTIALLY_DEFINED

Protected Actions Input:
21

Roles Discovered:
4

Permissions Discovered:
2

Action Mapping Status:
0 mapped to existing vocabulary, 14 requires undefined permission, 7 insufficient evidence

New Vocabulary Items:
0

Primary Owner:
SPEC02

Spec04 Boundary Status:
NOT_REOPENED

Selection Basis:
21 Spec04-enumerated actions are available; constitution/seed roles cover Manage Rooms intent; no Spatie permission binds any Dormitory action; 14 human-admin actions need undefined permissions; 7 Integration/dual-path actions lack Auth mapping evidence; zero Dormitory enforcement in code.

Immediate Next Step:
Create Spec02 non-authorizing permission-naming proposal for the 14 human-admin catalog actions only

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED
```

---

## Scope Integrity Confirmation

| Check | Result |
| ----- | ------ |
| No final permission names invented | **Confirmed** (`REQUIRED_BUT_UNDEFINED` only) |
| No code / catalog / Spec mutations | **Confirmed** |
| Bounded to 21 enumerated actions | **Confirmed** |
| Assignability / Check-in not reopened | **Confirmed** |
| Only this discovery artifact updated | **Confirmed** |

---

## No-Change Confirmation

`No application, test, catalog, Spec04, or other Spec02 files were modified.`

Only this artifact was updated:

- `.specify/docs/spec02/spec02-dormitory-surface-permission-vocabulary-discovery.md`

---

## Document Control

- Version: 2.0.0 (resumed after Spec04 action enumeration)  
- Status: **`DISCOVERY_RECORDED`** / **`VOCABULARY_PARTIALLY_DEFINED`**  
- Supersedes prior `VOCABULARY_BLOCKED_BY_MISSING_DOMAIN_INPUT` body in this path  
- Last Updated: 2026-07-12
