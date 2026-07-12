---
artifact: spec02_auth_dormitory_surface_authorization_dependency_clarification
status: CLARIFICATION_RECORDED
mutation_permission: none
execution_authority: none
operating_mode: NON_AUTHORIZING_DISCOVERY
decision: DEPENDENCY_CLARIFIED_WITH_FOLLOWUP_DEFINITION_NEEDED
date: 2026-07-12
---

# Spec02 Auth ↔ Dormitory-Surface Authorization — Dependency Clarification

**Artifact type:** Dependency clarification (non-authorizing discovery)  
**Upstream readiness:** `.specify/docs/spec04/spec04-auth-integration-residual-readiness-review.md` (`READINESS_CONFIRMED` / `CROSSES_SPEC_BOUNDARY_BUT_CONTAINABLE`)  
**Status:** `CLARIFICATION_RECORDED`

This artifact clarifies ownership and dependency shape only. It does **not** authorize implementation, unfreeze Spec02, define a final RBAC catalog, start Dormitory UI, or reopen closed Spec04 Assignability / Check-in residuals.

---

## A. Current Context

Spec04 Auth residual readiness confirmed that the remaining residual is **Authorization / policies / roles / guards for Dormitory surfaces**, owned under Spec02 Identity (Ownership D3), containable as cross-spec integration without Spec04 domain extension.

This clarification answers what Spec02 must own so future Dormitory-facing surfaces can rely on Auth behavior **without** Spec04 becoming the cross-cutting authorization authority or reopening closed Spec04 residuals.

---

## B. Dependency Identification

### Precise dependency

**Name:** Spec02 Identity RBAC foundation → Dormitory-surface authorization binding  

**Meaning:** When Dormitory-facing protected surfaces/actions exist, they must **consume** Spec02-owned roles/permissions (and Spec02-supplied check APIs such as `IdentityUserReadContract`) for allow/deny decisions. Spec04 may name business actions on those surfaces; Spec02 owns authorization semantics and vocabulary expansion for dormitory-related permissions.

### Protected surfaces / actions (repository-evidenced; not invented)

| Surface / action class | Evidence | Status today |
| ---------------------- | -------- | ------------ |
| Livewire dormitory catalog admin (Phase H) | Spec04 `plan.md` Phase H deferred; UI triage `dormitory-admin-ui` blocked (no product auth) | **Not delivered** — primary deferred “Dormitory surface” |
| HTTP / API / controllers / FormRequests for Dormitory | Spec04 backend closeout §6 residual; Spec04 residual table | **Deferred** — no Spec04 HTTP auth layer |
| Dormitory Application Mutation/Read actions when exposed via UI/HTTP (catalog CRUD, structure writes, operability transitions) | Spec04 backend delivered under IA that **excluded** policies/gates; no Dormitory Policy classes | **Backend exists; authorization binding absent** |
| Constitution-level “Dormitory Manager manages dormitories…” administrative configuration | `.specify/memory/constitution.md` role matrix / admin configuration | **Product role intent** — not Spec04 Policy implementation |

**Explicitly out of this dependency (unless later separately evidenced):** OA-02-01 login/session redesign; Identity Livewire admin (T035–T037); CheckIn Operator gate (already Spec07/CheckIn consuming Identity roles — pattern reference only, not Spec04 reopen).

---

## C. Evidence Review

| Artifact | Signal |
| -------- | ------ |
| Spec04 Auth readiness review | Residual = Dormitory-surface auth binding; Spec02 owner; no Spec04 parallel auth authority |
| Ownership Decision **D3** | `SPEC02_IDENTITY` owns identity/roles/permissions foundation; surface policy binding must respect Spec02; no Spec02 unfreeze/coding authorized |
| Context-map **R12** | Identity supplies auth to all contexts; mechanism under Spec02 |
| Spec02 `spec.md` / `plan.md` | Wave 1A RBAC baseline in scope; “domain permissions added as modules land”; OA-02-01 out |
| `IdentityRoleSeeder` | Permissions today: `identity.*`, `audit.read`. Roles include `SystemAdministrator`, plus `Administrator` / `DormMgr` / `HRMgr` with **audit.read only** — **no `dormitory.*` permissions** |
| `PlatformRoles` / Spec02 data-model | SystemAdministrator primary; additional constitution roles noted as placeholders until later specs |
| Spec04 closeout / IA | Policies, gates, authorization code, UI, HTTP excluded from Spec04 backend |
| Spec04 `plan.md` Phase H | Livewire dormitory admin deferred (presentation surface not present) |
| CheckIn `OperatorRoleGate` | **Pattern example:** consumer gate uses `IdentityUserReadContract::userHasRole` — Spec02 remains owner of role truth |
| UI triage | `dormitory-admin-ui` not product-authorized |

**Missing / implied linkage:** Spec02 provides RBAC machinery and some role **names**, but **no dormitory permission vocabulary**, no Spec04 Policy/Gate binding, and no authorized Dormitory presentation surface to attach guards to. Authorization linkage is **implied by architecture**, not implemented for Dormitory.

---

## D. Ownership Clarification

| Concern | Owner | Boundary rule |
| ------- | ----- | ------------- |
| Role & permission **constructs**, Spatie tables, assign/revoke, `IdentityUserReadContract` checks | **Spec02** | Sole foundation owner (D3, R12) |
| Expanding platform permission/role **vocabulary** for dormitory-related capabilities (e.g. future `dormitory.*` or equivalent) | **Spec02** | “Domain permissions added as modules land” is Spec02 seed/catalog work — not Spec04 Domain ownership of RBAC |
| Naming which **business actions** a Dormitory surface exposes (create building, change operability, etc.) | **Spec04** (product/surface description) | Describes actions; does **not** own allow/deny authority |
| Placement of **guards/policies** that call Spec02 checks at Presentation/HTTP (or Application gate adapters) | **Shared integration surface** | Implementation may live near the surface module, but **must consume Spec02** — must not invent Spec04-owned role/permission authority |
| Login / session / OA-02-01 | **Spec02** (deferred) | Adjacent Auth topic; **not** required to clarify this dependency’s ownership shape |
| Dormitory UI product intake | **Independent UI feature (D4)** | Presentation owner ≠ Auth owner; UI still needs Spec02 auth binding when authorized |

**Spec04 must not:** define cross-cutting permission catalogs, own Identity roles, or reopen Assignability/Check-in under an “auth” label.

---

## E. Dependency Gap Assessment

Ranked by importance for this dependency:

| Rank | Gap type | Assessment |
| ---- | -------- | ---------- |
| **1** | **Permission vocabulary** | **Primary gap** — no `dormitory.*` (or equivalent) permissions seeded; Spec02 plan explicitly deferred domain permissions until modules land |
| **2** | **Sequencing / dependency ordering** | Spec02 Frozen; Dormitory UI/HTTP deferred and not product-authorized — auth binding cannot ship before surface + Spec02 vocabulary definition + product/IA gates |
| **3** | **Policy ownership / guard placement** | Unresolved **where** surface policies/gates will live (Presentation vs Application gate), but ownership of **semantics** is Spec02 — placement is integration detail for a later definition step |
| **4** | **Role vocabulary / role→permission mapping** | Partial — `DormMgr` etc. exist as names with only `audit.read`; constitution implies dormitory admin capability not mapped to permissions |
| **5** | **Actor-context mapping** | Secondary — authenticated actor identity (OA-02-01) needed for interactive surfaces later; not the Spec04 Auth residual’s core missing piece vs permission/policy binding |
| — | Guard semantics (allow/deny mechanics) | Largely **already defined** by Spec02 Spatie + `IdentityUserReadContract` pattern (see CheckIn gate) |

---

## F. Clarification Outcome

`DEPENDENCY_CLARIFIED_WITH_FOLLOWUP_DEFINITION_NEEDED`

**Clarified now:**

- Dependency name and direction (Spec02 → Dormitory surfaces).  
- Primary owner (**Spec02**).  
- Spec04 role (name protected business actions / surfaces only).  
- That Spec04 domain boundary need not expand.  
- That the largest missing Spec02 definition point is **dormitory-related permission vocabulary** (+ later guard placement when surfaces are authorized).

**Still needed in a later definition step (not this artifact; not final RBAC design):**

- Explicit minimum permission names for evidenced Dormitory surface actions.  
- Role→permission mapping for constitution-aligned dormitory admin roles.  
- Guard/policy placement convention for the first authorized Dormitory surface packet.  
- Product sequencing relative to Dormitory UI intake and Spec02 freeze/reopen.

**Not** `DEPENDENCY_BLOCKED_BY_OWNERSHIP_AMBIGUITY` — Ownership D3 already assigns Auth to Spec02.  
**Not** full `DEPENDENCY_CLARIFIED` without follow-up — permission catalog and surface-guard definition remain incomplete by design of Spec02 Wave 1A.

---

## G. Immediate Next Step

**Create a Spec02-owned Dormitory-surface permission vocabulary definition discovery** (non-authorizing, non-final RBAC design).

That discovery must:

- propose only the **minimum** Spec02 permission/role mapping points for evidenced Dormitory surface actions (Phase H / deferred HTTP / Application actions when exposed)  
- not unfreeze Spec02, not authorize seeding/coding, not authorize Dormitory UI  
- not reopen Spec04 Assignability or Check-in  
- not invent a full enterprise permission matrix rewrite  

Optional later governance (not authorized here): when vocabulary is recorded, revisit whether Spec04 Auth residual tracking can be retired as status-only pending UI product auth — similar to Check-in residual retirement after readiness found no Spec04-executable gap.

---

## Required Final Decision Block

```text
AUTH_DORMITORY_AUTHORIZATION_DEPENDENCY

Decision:
DEPENDENCY_CLARIFIED_WITH_FOLLOWUP_DEFINITION_NEEDED

Dependency:
Spec02 Identity RBAC foundation → Dormitory-surface authorization binding

Primary Owner:
SPEC02

Spec04 Boundary Status:
NOT_REOPENED

Selection Basis:
Ownership D3 and R12 place Auth under Spec02; Spec04 only deferred surface policies; Wave 1A has identity/audit permissions but no dormitory permission vocabulary; Phase H/UI/HTTP surfaces are deferred — follow-up is Spec02 permission vocabulary definition, not Spec04 domain expansion.

Immediate Next Step:
Create Spec02-owned Dormitory-surface permission vocabulary definition discovery (non-authorizing)

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED
```

---

## Scope Integrity Confirmation

| Check | Result |
| ----- | ------ |
| No code changes | **Confirmed** |
| No implementation authorization | **Confirmed** |
| No final RBAC/ABAC design | **Confirmed** |
| Spec04 Assignability / Check-in not reopened | **Confirmed** |
| Spec04 boundary not rewritten | **Confirmed** |
| Only this clarification artifact written | **Confirmed** |

---

## No-Change Confirmation

`No application, test, catalog, Spec04, or Spec02 source-spec files were modified.`

Only this artifact was created:

- `.specify/docs/spec02/spec02-auth-dormitory-surface-authorization-dependency-clarification.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`CLARIFICATION_RECORDED`** / **`DEPENDENCY_CLARIFIED_WITH_FOLLOWUP_DEFINITION_NEEDED`**  
- Primary owner: Spec02  
- Last Updated: 2026-07-12
