---
artifact: product_surface_authorization_decision
status: PRODUCT_SURFACE_AUTHORIZED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
phase: CORE_COMPLETION_WAVE
authorized_surface: employee-request-self-service
business_owner: UNRESOLVED
spec04_auth_residual_status: REQUIRES_MORE_PRODUCT_AUTHORITY
recommended_next_gate: PRODUCT_SURFACE_REFINEMENT_REQUIRED
upstream_triage: .specify/docs/decisions/product-authorization-gap-triage.md
upstream_owner_review: .specify/docs/decisions/business-owner-formalization-review.md
date: 2026-07-13
---

# Product Surface Authorization Decision

**Artifact type:** Governance product-surface decision (non-executing; docs-only)  
**Status:** `PRODUCT_SURFACE_AUTHORIZED`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Purpose:** Record the human-authorized product surface and validated scope boundaries; preserve unresolved Business Owner as a blocking authority gap for owner-finalized Auth packet handoff. Does **not** authorize implementation, UI coding, role mapping, permissions, Workflow, Lottery, Reporting, dormitory admin, or manager approval.

---

## 1. Decision Baseline

| Field | Value |
| ----- | ----- |
| Gate | `PRODUCT_SURFACE_AUTHORIZATION_DECISION` |
| Phase | `CORE_COMPLETION_WAVE` |
| Upstream triage | `.specify/docs/decisions/product-authorization-gap-triage.md` (`NO_NAMED_PRODUCT_SURFACE_AUTHORIZED` at triage time) |
| Upstream owner review | `.specify/docs/decisions/business-owner-formalization-review.md` |
| Operating mode | Conflict/boundary validation only — no product redesign, no application changes |
| Execution | **Not activated** |

This decision **names** a product surface and records approved scope. It does **not** complete owner-bound authorization wording or Auth packet preparation.

---

## 2. Human Product Surface Decision

**Authoritative human inputs** (not re-questioned):

| Field | Value |
| ----- | ----- |
| Surface Name | `employee-request-self-service` |
| Audience | authenticated employee |
| Business Owner | `UNRESOLVED` |
| Approved Scope | create own request; list own requests; view own request detail; track own request status |
| Explicitly Out of Scope | manager approval; dormitory allocation; reporting; lottery; workflow activation; full RBAC; OA admin UI |

---

## 3. Prior Business Owner Formalization Result

**Preserved verbatim — not overwritten or reinterpreted:**

| Field | Value |
| ----- | ----- |
| `BUSINESS_OWNER_FORMALIZATION_STATUS` | `CONFLICTING_TERM` |
| `OWNER_HANDLING_RECOMMENDATION` | `BLOCK_PENDING_HUMAN_AUTHORITY` |
| `NEXT_PROMPT_OWNER_FIELD` | `UNRESOLVED` |
| Source | `.specify/docs/decisions/business-owner-formalization-review.md` |

**Consequence for this gate:** Business Owner remains `UNRESOLVED`. Owner-finalized authorization wording is **blocked** pending human authority. Proposed term «واحد اداری / منابع انسانی» must **not** be substituted.

---

## 4. Governance Validation

### 4.1 Conflict check (surface vs existing governance)

| Boundary | Separation preserved? | Notes |
| -------- | --------------------- | ----- |
| Workflow | **Yes** | Workflow activation explicitly out of scope; CD-010 / wave disposition: Workflow remains deferred |
| Spec06 Lottery | **Yes** | Lottery explicitly out of scope; Spec06 remains deferred |
| Spec11 Reporting | **Yes** | Reporting explicitly out of scope; separate authority track (triage) |
| Dormitory Admin | **Yes** | Not in approved scope; distinct from self-service |
| Manager Approval | **Yes** | Explicitly out of scope |
| Full RBAC | **Yes** | Explicitly out of scope; role mapping deferred |
| OA admin UI | **Yes** | Explicitly out of scope |

**Result:** No governance contradiction found between the named surface + self-service scope and catalog/architecture separations. Surface does **not** reopen closed specs.

### 4.2 Deferred origin alignment (triage categories only)

| Item | Category | Rationale |
| ---- | -------- | --------- |
| Workflow activation | `KEEP_DEFERRED` | Out of surface; wave disposition unchanged |
| Spec06 Lottery | `KEEP_DEFERRED` | Out of surface |
| Spec11 Reporting ACL / reporting UI | `KEEP_DEFERRED` | Separate track; out of surface |
| Manager approval / approver inbox | `KEEP_DEFERRED` | Out of surface |
| Full RBAC / role→permission grants expansion | `KEEP_DEFERRED` | Out of surface; mapping deferred |
| Dormitory / OA admin UI | `KEEP_DEFERRED` | Out of surface |
| Formal Business Owner identity | `BLOCKED_PENDING_AUTHORITY` | Prior formalization: `BLOCK_PENDING_HUMAN_AUTHORITY` |
| Spec04 Auth residual → Auth packet prep | `BLOCKED_PENDING_AUTHORITY` | Unresolved owner prevents governance-safe packet handoff |
| Named surface + self-service scope (this decision) | `REQUIRE_PRODUCT_DECISION` → **satisfied for naming/scope** | Human surface decision recorded; owner decision still open |

Origin/status remains **governance-only**. No new product design triggered beyond recording the human inputs.

### 4.3 Business Owner conflict status (explicit)

| Statement | Status |
| --------- | ------ |
| Business Owner field | `UNRESOLVED` |
| Owner-finalized authorization wording | **Blocked** |
| Formalization assessment (prior) | `CONFLICTING_TERM` (preserved) |
| Handling | `BLOCK_PENDING_HUMAN_AUTHORITY` (preserved) |

---

## 5. Surface Boundary

| Dimension | Recorded value |
| --------- | -------------- |
| Surface Name | `employee-request-self-service` |
| Primary Audience | authenticated employee |
| Allowed Capabilities | create own request; list own requests; view own request detail; track own request status |
| Excluded Capabilities | manager approval; dormitory allocation; reporting; lottery; workflow activation; full RBAC; OA admin UI |
| Data Ownership Boundary | employee-owned requests only (`employee_id` / self-ownership path) |
| Auth Boundary | self-access to own requests only |
| UI Boundary | **no UI implementation authorized** by this decision |
| Role Mapping Dependency | **deferred** |
| Reporting Boundary | **excluded** |
| Lottery Boundary | **excluded** |
| Owner Field Status | **unresolved pending human authority** |

---

## 6. Explicit Exclusions

This decision does **not** authorize:

1. Manager approval surfaces, stages, or inbox visibility  
2. Dormitory allocation or dormitory admin UI  
3. Reporting / Spec11 presentation or ACL  
4. Lottery / Spec06 work  
5. Workflow module activation or orchestration UI  
6. Full RBAC design, permission seeding, or role grants for this surface  
7. OA admin UI  
8. Application code, tests, migrations, routes, controllers, policies, middleware, Livewire, Blade  
9. Replacement of `UNRESOLVED` with any guessed or conflicting owner term  

---

## 7. Business Owner Conflict Status

- Prior review remains authoritative: `CONFLICTING_TERM` / `BLOCK_PENDING_HUMAN_AUTHORITY`.  
- This decision records Business Owner as **`UNRESOLVED`** only.  
- Auth packet preparation that requires a named Business Owner **must not** proceed until human authority resolves the owner field.  
- Surface naming/scope acceptance ≠ owner-finalized handoff readiness.

---

## 8. Impact on Spec04 Auth Residual

| Classification (exactly one) | Selected |
| ---------------------------- | -------- |
| `READY_FOR_AUTH_PACKET_PREPARATION` | **No** — unresolved Business Owner blocks governance-safe handoff |
| `REQUIRES_MORE_PRODUCT_AUTHORITY` | **Yes** |
| `SURFACE_AUTHORIZATION_CONFLICT` | **No** — surface/scope validated; owner gap is authority refinement, not surface contradiction |

**Spec04 Auth Residual status recorded:** `REQUIRES_MORE_PRODUCT_AUTHORITY`

**Note:** Naming `employee-request-self-service` clears the prior triage gap of *no named surface* for **this** self-service path. It does **not** by itself make Spec04 residual packet-ready while Business Owner is unresolved. Spec04 dormitory/structure Auth residual remain a **related but distinct** residual track; this surface does not merge Reporting or Dormitory Admin into Spec04 Auth residual.

---

## 9. Downstream Auth Packet Questions

List only (not answered here):

1. Is employee self-access sufficient for first packet?  
2. Is an explicit role required or is authenticated employee enough?  
3. Exact request ownership rule  
4. Visible request states to employee  
5. Capability bundling (create/list/detail/status)  
6. Exclusion of manager/admin capabilities  
7. Anti-leak constraints for request data  
8. What formal human authority must resolve Business Owner before owner-bound authorization can proceed?  

---

## 10. Separate Track Preservation

| Track | Posture |
| ----- | ------- |
| Workflow | Remains deferred — separate |
| Spec06 Lottery | Remains deferred — separate |
| Spec11 Reporting | Separate authority — not merged |
| Dormitory Admin / Spec04 structure Auth residual | Separate residual — not expanded by this surface |
| Manager approval | Out of surface — separate |
| Full RBAC | Deferred — separate |
| Existing Request UI closeouts / contracts | Not reopened; this decision does not authorize new UI implementation |
| `audit-ui` product grant | Unchanged; Request UI remains excluded under that grant |

---

## 11. Feature Contract Skeleton (structure only)

Skeleton created (no business rules, permissions, or authority decisions filled):

`.specify/docs/contracts/employee-request-self-service.feature-contract.skeleton.yaml`

Filling rules, PEP keys, or UI authority into that skeleton is **out of scope** for this gate.

---

## 12. Final Decision

| Decision element | Outcome |
| ---------------- | ------- |
| Named surface | **Accepted** — `employee-request-self-service` |
| Audience / scope / exclusions | **Accepted** as human authoritative input after governance validation |
| Business Owner | **`UNRESOLVED`** — blocks owner-finalized wording and Auth packet prep |
| Spec04 Auth residual | `REQUIRES_MORE_PRODUCT_AUTHORITY` |
| Next gate | `PRODUCT_SURFACE_REFINEMENT_REQUIRED` (resolve Business Owner / owner-bound handoff prerequisites; respect `BLOCK_PENDING_HUMAN_AUTHORITY`) |
| Implementation / UI / RBAC | **Not authorized** |

---

## Required Final Lines

```text
PRODUCT_SURFACE_AUTHORIZATION_STATUS: PRODUCT_SURFACE_AUTHORIZED

AUTHORIZED_SURFACE: employee-request-self-service

SPEC04_AUTH_RESIDUAL_STATUS: REQUIRES_MORE_PRODUCT_AUTHORITY

RECOMMENDED_NEXT_GATE: PRODUCT_SURFACE_REFINEMENT_REQUIRED

BUSINESS_OWNER_STATUS: UNRESOLVED

APPLICATION_FILES_MODIFIED: NO
```
