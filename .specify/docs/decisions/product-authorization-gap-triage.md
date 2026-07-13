---
artifact: product_authorization_gap_triage
status: PRODUCT_AUTHORIZATION_REQUIRES_HUMAN_DECISION
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
phase: CORE_COMPLETION_WAVE
named_product_surface: NO_NAMED_PRODUCT_SURFACE_AUTHORIZED
spec04_auth_disposition: REQUIRES_PRODUCT_AUTHORITY
recommended_next_gate: PRODUCT_SURFACE_AUTHORIZATION_DECISION
upstream_auth_decision: SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY
date: 2026-07-13
---

# Product Authorization Gap Triage

**Artifact type:** Governance discovery / authority classification (non-authorizing)  
**Status:** `PRODUCT_AUTHORIZATION_REQUIRES_HUMAN_DECISION`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Upstream:** Spec04 Auth residual product decision — `SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY`  
**Phase:** `CORE_COMPLETION_WAVE` — gate `PRODUCT_AUTHORIZATION_GAP_TRIAGE`

This triage classifies product-authorization gaps that block Auth-readiness packet preparation. It does **not** invent product surfaces, authorize Auth/UI/Lottery/Workflow work, draft Feature Contracts, or merge Spec11 into Spec04.

---

## 1. Authority Gap Baseline

| Topic | Posture |
| ----- | ------- |
| Spec04 Auth residual | `OPEN` — `SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY` |
| Authorization readiness for packet prep | **Not ready** — named product surface absent |
| Named product surface for Auth/UI path | **`NO_NAMED_PRODUCT_SURFACE_AUTHORIZED`** |
| Spec02 structure PEP | Completed (bounded); Spec02 **Frozen — Wave 1A Complete** |
| Deferred portfolio | Disposition completed; no execution stream activated |
| This gate activates execution stream? | **No** |

**Evidence anchors:**  
`.specify/docs/spec04/spec04-auth-residual-product-decision.md`;  
`.specify/docs/planning/deferred-portfolio-review-and-disposition.md`;  
`docs/product/next-ui-feature-authorization-discovery.md` (`BLOCKED_BY_MISSING_PRODUCT_DECISION`);  
`docs/ui/review/governance-next-candidate-triage.md` (`dormitory-admin-ui` blocked).

---

## 2. Product Authorization Gap Register

| Gap | Current State | Why It Exists | Required Authority Decision | Blocking Impact | Recommended Status |
| --- | ------------- | ------------- | --------------------------- | --------------- | ------------------ |
| Named product surface | Absent | Only consumed grant is closed `employee-context-ui`; no successor auth | Explicit product authorization naming a surface/slug | Blocks Auth packet + UI readiness | **BLOCKING** |
| Product surface owner / authority | Not identified for next Auth/UI surface | Catalog Authority Map / product discovery do not name an active owner for successor intake | Identify who may authorize next surface | Ambiguous decision path | **BLOCKING** |
| Target role / audience boundary | Undefined | No authorized surface → no audience | Product states intended roles/users for first surface | Role mapping cannot be scoped | **BLOCKING** |
| Role mapping dependency | Deferred until surface defined | Structure keys registered without grants; no target surface | Whether first surface requires role→`dormitory.structure.*` | Cannot seed grants safely | **DEFERRED_UNTIL_PRODUCT_SURFACE_DEFINED** |
| UI authorization scope | Blocked | No product-authorized UI slug | Authorize (or refuse) Presentation/Livewire/Blade path for named slug | UI auth undefined | **BLOCKED_PENDING_PRODUCT_SURFACE** |
| HTTP / Policy / middleware scope | Blocked pending surface | No admin HTTP surface authorized under Spec04 Auth path | Include/exclude HTTP guards for named surface | Policy work premature | **BLOCKED_PENDING_SURFACE_DECISION** |
| UI Anti-Leak boundary | Open / follow-up | Anti-Leak applies once UI surface exists | Confirm Anti-Leak mandatory before UI-readiness after surface auth | Later gate only | **FOLLOW_UP_AFTER_SURFACE** |
| Reporting separation (Spec11) | Separate track | Spec11 authority gap / Reporting UI excluded from Spec11 IA | Keep Spec11 out of Spec04 Auth residual | Prevents false merge | **SEPARATE_AUTHORITY_TRACK** / **NOT_IN_CURRENT_AUTH_TRIAGE_SCOPE** |

---

## 3. First Product Surface Assessment

```text
NO_NAMED_PRODUCT_SURFACE_AUTHORIZED
```

**Evidence (no inference):**

| Candidate | Finding |
| --------- | ------- |
| `dormitory-admin-ui` | Triage / discovery: **not product-authorized**; blocked |
| `employee-context-ui` | Product auth **consumed**; feature **closed** — not Spec04 Auth path |
| Reporting / Operator Explorer UI | Spec11 IA excludes Operator Explorer / KPI dashboards |
| Request / Main UI successor | No open product-authorized NEW_CANDIDATE for Auth residual path |
| Auth-readiness-only slug | **Not** present as an authorized product decision |

`docs/product/next-ui-feature-authorization-discovery.md`: status `BLOCKED_BY_MISSING_PRODUCT_DECISION`; feature authorized for next intake: **none**; next action `REQUEST_PRODUCT_AUTHORIZATION`.

---

## 4. Spec04 Auth Residual Disposition

```text
REQUIRES_PRODUCT_AUTHORITY
```

Consistent with §3: no Auth packet may be prepared until a named product surface is explicitly authorized. Classification answers:

| Dimension | Classification |
| --------- | -------------- |
| Role mapping | `DEFERRED_UNTIL_PRODUCT_SURFACE_DEFINED` (not `REQUIRED_FOR_FIRST_AUTH_PACKET` until surface exists) |
| UI authorization | `BLOCKED_PENDING_PRODUCT_SURFACE` (not `READY_FOR_AUTH_PACKET`) |
| HTTP / Policy / middleware | `BLOCKED_PENDING_SURFACE_DECISION` |
| Spec11 Reporting | `SEPARATE_AUTHORITY_TRACK` / `NOT_IN_CURRENT_AUTH_TRIAGE_SCOPE` |

**Reporting separation note:** Shared themes (permissions, access) do **not** imply Spec11 membership in Spec04 Auth residual. Report-access policy, reporting UI ACL, or reporting permission surfaces require **separate** product authority unless a future explicit governance decision says otherwise.

---

## 5. Items That Remain Deferred or Blocked

| Item | Posture |
| ---- | ------- |
| Workflow | Remains deferred — do not activate |
| Spec06 Lottery | Remains deferred; `AUTHORITY_NOT_AVAILABLE` |
| Full RBAC / OA-02-01 | Keep deferred |
| Dormitory UI / Main UI / Reporting UI | Blocked pending product auth (Reporting separate track) |
| Spec03 / Spec04 Assignability / Check-in reopen / Spec07 | Keep closed / retired |
| Role-mapping / policy / middleware / UI implementation | Not authorized |
| Spec02 structure-binding packet | Closed — do not reopen |
| Spec02 overall | Frozen — Wave 1A Complete |

---

## 6. Recommended Next Governance Gate

```text
PRODUCT_SURFACE_AUTHORIZATION_DECISION
```

**Why:** Missing step is an explicit human/product decision that **names and authorizes** (or explicitly refuses) a product surface for the blocked Auth/UI path. Auth packet preparation is **not** valid yet (`NO_AUTH_PACKET_POSSIBLE` until that decision). Prefer the actionable decision gate over a pure waiting label.

**Not selected:**

| Gate | Why not |
| ---- | ------- |
| `SPEC04_AUTH_RESIDUAL_AUTH_PACKET_PREPARATION` | No named authorized surface |
| `NO_AUTH_PACKET_POSSIBLE_PENDING_PRODUCT_AUTHORITY` | True as state, but next action should be the surface authorization **decision**, not a no-op hold label |

---

## 7. Decision

```text
PRODUCT_AUTHORIZATION_REQUIRES_HUMAN_DECISION
```

Artifacts do not contain enough authority to name a product surface for Spec04 Auth residual / Dormitory Auth-UI path. Triage classification is complete; **human product decision** is required next.

---

## 8. Boundary Preservation

This gate does **not** authorize:

- implementation or Auth packet execution  
- UI work or Feature Contract creation  
- Reporting merge into Spec04  
- Workflow or Lottery activation  
- Full RBAC  
- closed-spec reopening  
- role-mapping, policy, middleware, Livewire, Blade, seeder changes  

---

## Catalog / Status Reconciliation

| Artifact | Action |
| -------- | ------ |
| This decision | **Created** |
| Core Completion Wave Plan | Update next gate → `PRODUCT_SURFACE_AUTHORIZATION_DECISION` |
| Spec catalog | Informational changelog mirror only |

---

## Required Final Decision Block

```text
PRODUCT_AUTHORIZATION_GAP_TRIAGE

Named Product Surface:
NO_NAMED_PRODUCT_SURFACE_AUTHORIZED

Spec04 Auth Residual:
REQUIRES_PRODUCT_AUTHORITY

Spec11 Reporting:
SEPARATE_AUTHORITY_TRACK — NOT_IN_CURRENT_AUTH_TRIAGE_SCOPE

Execution Stream:
NONE ACTIVATED

Decision:
PRODUCT_AUTHORIZATION_REQUIRES_HUMAN_DECISION

Recommended Next Gate:
PRODUCT_SURFACE_AUTHORIZATION_DECISION
```

---

## Explicit Non-Authorization

`No application, test, contract, UI, lottery, workflow, role-mapping, policy, middleware, Livewire, Blade, or authorization implementation files were modified.`

---

## Document Control

- Version: 1.0.0  
- Status: **`PRODUCT_AUTHORIZATION_REQUIRES_HUMAN_DECISION`**  
- Recommended next gate: **`PRODUCT_SURFACE_AUTHORIZATION_DECISION`**  
- Last Updated: 2026-07-13  
- Checkpoint: `product-authorization-gap-triage`
