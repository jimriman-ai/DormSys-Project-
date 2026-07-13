---
artifact: spec04_auth_residual_product_decision
status: COMPLETED
decision: SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
upstream: .specify/docs/planning/deferred-portfolio-review-and-disposition.md
recommended_next_gate: PRODUCT_SURFACE_AUTHORIZATION_DECISION
date: 2026-07-13
---

# Spec04 Auth Residual — Product Decision

**Artifact type:** Product / governance decision (non-authorizing)  
**Status:** `COMPLETED`  
**Decision:** `SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Upstream:** Deferred Portfolio Review — Spec04 Auth residual `REQUIRE_PRODUCT_DECISION`; recommended next gate was this artifact.

This gate decides whether Spec04 Auth residual can be promoted into a bounded pre-UI authorization-readiness packet. It does **not** implement authorization, role mapping, UI, HTTP/Policy, Feature Contracts, or stream execution.

---

## 1. Decision Baseline

| Topic | Settled posture |
| ----- | --------------- |
| Deferred portfolio | `DEFERRED_PORTFOLIO_DISPOSITION_COMPLETED` |
| Spec04 Auth residual (portfolio) | `REQUIRE_PRODUCT_DECISION` |
| Execution stream | `NO_EXECUTION_STREAM_ACTIVATED` |
| Spec02 structure binding | `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED` — closed |
| Spec02 overall | **Frozen — Wave 1A Complete** — full Spec02 auth **not** complete |
| Spec04 Assignability / Check-in | CLOSED / RETIRED |
| Spec04 Auth residual | **OPEN** (remainder after Application PEP) |
| Workflow | `WORKFLOW_REMAINS_DEFERRED` |
| Spec06 | `SPEC06_REMAINS_DEFERRED`; `AUTHORITY_NOT_AVAILABLE` |
| UI Wave / Dormitory UI | `NOT_READY` / `BLOCKED` |
| Role mapping / UI auth / HTTP | Merged into Auth residual (portfolio) — not standalone streams |

---

## 2. Auth Residual Scope Inventory

| Surface | Classification |
| ------- | -------------- |
| Application-layer PEP (`dormitory.structure.view` / `manage` on `#1–#8` / `#12–#17`) | **COMPLETED** (Spec02 bounded packet) |
| Role mapping (role → `dormitory.structure.*`) | **OPEN** — deferred; no grants shipped |
| Permission assignment / seeding beyond key registration | **OPEN** — keys registered only; grants deferred |
| Presentation authorization | **BLOCKED_PENDING_AUTHORITY** — no authorized Dormitory UI surface |
| Livewire authorization (Dormitory admin) | **BLOCKED_PENDING_AUTHORITY** |
| Blade authorization (Dormitory admin) | **BLOCKED_PENDING_AUTHORITY** |
| HTTP / route guards (Dormitory admin) | **OPEN** / deferred — no Spec04-authorized admin HTTP surface |
| Policy / middleware guards | **OPEN** / deferred — same |
| UI Anti-Leak readiness | **OPEN** — follow-up after product Auth/UI scope known |
| Full RBAC | **DEFERRED** / **OUT_OF_SCOPE** for this residual packet |
| OA-02-01 Livewire Admin | **DEFERRED** / **OUT_OF_SCOPE** (Spec02 Wave 1A) |
| Dormitory UI execution | **BLOCKED_PENDING_AUTHORITY** |

---

## 3. Product Authority Assessment

**Explicit product authority for a named Auth/UI surface:** **ABSENT**

| Candidate surface | Evidence | Authority? |
| ----------------- | -------- | ---------- |
| `dormitory-admin-ui` | UI triage / product discovery / Spec02 closeout / Auth refresh — blocked; not product-authorized | **No** |
| Dormitory Structure UI / Management UI (generic) | No product authorization artifact naming a Spec04 Auth or Dormitory admin feature slug for current intake | **No** |
| Main UI Feature Execution | Deferred; no authorized NEW_CANDIDATE | **No** |
| Employee Context UI | Closed; grant lifecycle-consumed; not Spec04 Auth | **N/A** |

**Authoritative product discovery:** `docs/product/next-ui-feature-authorization-discovery.md` — overall posture `BLOCKED_BY_MISSING_PRODUCT_DECISION`; next action `REQUEST_PRODUCT_AUTHORIZATION`. `dormitory-admin-ui` listed **Not authorized** / **Ineligible**.

**Do not invent authority.** Application PEP completion does **not** constitute product authorization for Presentation, role grants, HTTP admin surfaces, or Dormitory UI.

---

## 4. Pre-UI Requirement Assessment

### MUST_DECIDE_BEFORE_UI

| Decision | Why |
| -------- | --- |
| Named product surface / slug authorization (e.g. Dormitory admin UI) | Without it, Presentation/Livewire/Blade/HTTP targets are undefined |
| Auth residual packet scope after product names the surface | Which of role mapping / Presentation / HTTP are in the first pre-UI packet |
| Whether role→`dormitory.structure.*` grants are required for that surface | Practical access before admin UI |
| UI Anti-Leak posture for that surface (follow-up gate once scope known) | Prevent UI-invented authority |

### CAN_DEFER_BEFORE_UI

| Item | Why |
| ---- | --- |
| Full RBAC / Spec02 unfreeze | Not required for a bounded pre-UI Auth packet |
| OA-02-01 Livewire Admin | Spec02 deferred; not Dormitory UI pre-req |
| Workflow activation | Deferred; not Auth residual |
| Spec06 / Spec11 new work | Authority-blocked; not Dormitory UI pre-req |
| HTTP/Policy if product chooses Presentation-only first packet | May be sequenced after role mapping + capability delivery — **only after** product names the surface |

### MUST_NOT_ACTIVATE_NOW

| Item | Why |
| ---- | --- |
| Role mapping coding / seeding grants | No product packet / Spec02 IA |
| Policy / middleware / Livewire / Blade / route auth implementation | No product surface |
| Dormitory UI / Main UI / Feature Contracts | Blocked pending product auth |
| Full RBAC; OA-02-01; Workflow; Lottery | Deferred or authority-blocked |
| Spec02 / Spec03 / Spec04 Assignability / Spec07 reopen | Closed / frozen |

---

## 5. Packet Viability

```text
BOUNDED_PRE_UI_AUTH_PACKET: NOT_VIABLE_NOW
```

**Why not viable:** A bounded packet requires (a) a **named product-authorized surface**, (b) explicit include/exclude Auth layers for that surface, and (c) Spec02-owned IA path for any role mapping / Presentation binding. Repository evidence shows **(a) is missing**. Promoting without (a) would invent product authority and create ambiguous scope (role mapping vs Presentation vs HTTP without a target).

**What product decision is missing (exact):**

1. Authorize (or explicitly refuse) a named pre-UI / UI surface related to Dormitory structure admin — e.g. product auth for `dormitory-admin-ui` or an Auth-readiness-only slug if product chooses that pattern  
2. State whether the first Auth packet must include **role mapping only**, **role mapping + Presentation binding**, and/or **HTTP/Policy**  
3. Confirm Spec02 remains Frozen except for any separately authorized bounded packet (structure binding already closed — do not reopen that packet)

**If authority later appears, candidate packet shape (non-activating preview only):**

| Field | Preview (not authorized) |
| ----- | ------------------------ |
| Packet name | e.g. Spec04 Auth Residual Pre-UI Readiness (name TBD by product) |
| Included | Likely role mapping for structure keys ± Presentation capability/auth for named surface |
| Excluded | Full RBAC; OA-02-01; Workflow; Lottery; Dormitory UI execution; Spec02 reopen beyond packet |
| Authority source | Future product authorization + Spec02 human IA |
| Exit criteria | Packet IA + review/closeout — still not UI Feature Contract unless separately authorized |
| Next gate then | `SPEC04_AUTH_RESIDUAL_PACKET_DEFINITION` |

---

## 6. Decision

```text
SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY
```

**Rationale:** Spec04 Auth residual remains the dominant **pre-UI** governance cluster, but it **cannot** be promoted to a pre-UI packet or implementation path without explicit product authority for a named surface. Status clarification (post-binding refresh) and portfolio disposition are complete; **product authorization is the missing gate**.

**Not selected:**

| Option | Why rejected |
| ------ | ------------ |
| `PROMOTE_TO_PRE_UI_PACKET` | No named product-authorized surface |
| `KEEP_DEFERRED` | Would understate pre-UI criticality; residual must stay visible as authority-blocked, not casually deferred |
| `SPLIT_REQUIRED` | Portfolio already merged role/UI/HTTP into Auth residual; split is premature until product names surface and first packet scope |

---

## 7. Boundary Preservation

This decision does **not** authorize:

- implementation of role mapping, seeding grants, policies, middleware, Livewire/Blade/route auth  
- UI, Feature Contracts, Dormitory UI, Main UI  
- Workflow activation; Lottery / Spec06 new work; Full RBAC; OA-02-01  
- Spec02 reopen / unfreeze; Spec03 reopen; Spec04 Assignability reopen; Check-in un-retirement; Spec07 reopen  
- Core Completion Wave stream execution selection  

Spec04 Auth residual remains **OPEN** and **blocked pending product authority** — not closed, not implemented.

**UI relationship:** Dormitory UI and UI Wave remain **BLOCKED** / **NOT_READY**. Before UI-readiness: product auth for named slug + Auth residual packet definition (after product decision) + UI Anti-Leak path.

---

## 8. Recommended Next Gate

```text
PRODUCT_AUTHORIZATION_GAP_TRIAGE
```

**Why:** Product authority is missing across Auth/UI (and remains absent for Lottery). Triage must inventory authority gaps for named surfaces without inventing grants, then route product decisions. Do **not** skip to packet definition, UI Anti-Leak, or stream selection.

---

## Catalog / Status Reconciliation

| Artifact | Action |
| -------- | ------ |
| This decision document | **Created** |
| Core Completion Wave Plan | **Update** recommended next gate → `PRODUCT_AUTHORIZATION_GAP_TRIAGE` |
| Spec catalog | **Optional informational changelog** — decision mirror only; no inventory status change required for Spec04 Product `PENDING_RESIDUAL` |

Spec04 Product remains `PENDING_RESIDUAL`. Spec02 remains Frozen. No implementation files modified.

---

## Required Final Decision Block

```text
SPEC04_AUTH_RESIDUAL_PRODUCT_DECISION

Decision:
SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY

Product Authority (named Auth/UI surface):
ABSENT

Packet Promotion:
NOT VIABLE NOW

Spec04 Auth Residual:
REMAINS OPEN — BLOCKED PENDING PRODUCT AUTHORITY

UI / Dormitory UI:
REMAIN BLOCKED

Execution Authority:
NONE

Recommended Next Gate:
PRODUCT_AUTHORIZATION_GAP_TRIAGE
```

---

## Explicit Non-Authorization

`No application, test, contract, UI, lottery, workflow, role-mapping, policy, middleware, Livewire, Blade, or authorization implementation files were modified.`

---

## Document Control

- Version: 1.0.0  
- Status: **`COMPLETED`**  
- Decision: **`SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY`**  
- Recommended next gate: **`PRODUCT_AUTHORIZATION_GAP_TRIAGE`**  
- Last Updated: 2026-07-13  
- Checkpoint: `spec04-auth-residual-product-decision`
