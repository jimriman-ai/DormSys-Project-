# DormSys Spec Catalog

**Version:** 1.0.31 (AUTH-013 disposition annotations mirrored)  
**Status:** Hard Freeze — Operational  
**Last Updated:** 1405/04/22 | 2026/07/13  
**Related Documents:** [`catalog-decisions.md`](catalog-decisions.md), [`context-map.md`](context-map.md), [`playbook/specification-playbook.md`](playbook/specification-playbook.md)

---
## Purpose

This catalog defines the controlled specification roadmap for DormSys v1.0.  
Each spec represents either:

- a bounded context confirmed or inferred from the architecture documents, or
- a cross-cutting capability required for platform completeness.

This file is the upstream reference for all future `spec.md`, `plan.md`, and `tasks.md` artifacts.

Boundary decisions recorded in [`catalog-decisions.md`](catalog-decisions.md) supersede provisional open questions listed here once closed.

---

## Catalog Rules

- Specs derive from this catalog, not vice versa.
- Explicitly documented boundaries must be distinguished from inferred ones.
- Open questions must remain visible until resolved in `catalog-decisions.md`.
- Deferred components must not be promoted into active implementation without activation criteria.
- Cross-cutting capabilities must not be mistaken for business bounded contexts.
- A spec may be planned even if some of its internal modeling decisions are still open, provided those questions are documented here or in `catalog-decisions.md`.

---

## Evidence Basis Legend

- `Explicit`: directly named or clearly defined in the reference documents.
- `Inferred`: strongly implied by the documents, but not explicitly formalized as a standalone spec.
- `Provisional`: currently useful for delivery planning, but subject to reshaping as implementation reveals actual boundaries.
- `Deferred`: intentionally postponed by architectural decision until activation criteria are met.
- `Decided`: boundary resolved in `catalog-decisions.md` — implementation detail may still be open.

---

## Resolved Boundary Questions (Catalog Level)

| OQ / Conflict | Decision | Affects |
| ------------- | -------- | ------- |
| CONF-DEP-01 | CD-009 — Dependent ∈ Employee | `spec03`, `spec05` |
| OQ-01 | CD-012 — Employee ↔ Identity via immutable UUID, no FK | `spec02`, `spec03` |
| OQ-02 | CD-013 — Employee computes eligibility; Request enforces (Recorded Assumption) | `spec03`, `spec05` |
| OQ-03 | CD-010 — Request owns approval state; Workflow owns transition rules (deferred) | `spec05`, Workflow |
| OQ-04 | CD-011 — Lottery owns all lottery rules and lifecycle | `spec06` |
| OQ-05 | CD-014 — Allocation and Occupancy split across Allocation, Dormitory, CheckIn/CheckOut | `spec04`, `spec07` |
| OQ-06 | CD-015 — CheckIn/CheckOut promoted to active operational boundary | `spec07` |
| OQ-07 | CD-016 — Voucher owns eligibility and issuance lifecycle | `spec08` |
| OQ-08 | CD-017 — Reporting remains read-only cross-domain projection consumer | `spec11` |

**All catalog-level boundary questions (OQ-01 through OQ-08) are closed.** See [`catalog-decisions.md`](catalog-decisions.md) Decision Index.

---

## Hard Freeze v1.0.0 — Acceptance Record

**Frozen:** 1405/04/05 | 2026/06/26  
**Authority:** [`specification-playbook.md`](playbook/specification-playbook.md) §IV (Catalog v1.0 Hard Freeze gate)

### Mandatory gate (playbook §IV)

| OQ | Requirement | Status |
| -- | ----------- | ------ |
| OQ-01 | Must be CLOSED before spec authoring | ✅ Closed (CD-012) |
| OQ-02 | Must be CLOSED before spec authoring | ✅ Closed — Current Scope (CD-013) |
| OQ-03 … OQ-08 | Not required closed at Hard Freeze gate; must remain documented | ✅ Closed (CD-010 … CD-017) |

> *Playbook criterion (verbatim):* Only OQs that are **critical for the Boundary** (OQ-01, OQ-02) must be closed before writing the spec. Other OQs may be carried inside the spec in documented form.

### Beyond minimum gate

OQ-03 through OQ-05 were closed before Hard Freeze (CD-010, CD-011, CD-014). CONF-DEP-01 closed (CD-009). This exceeds the mandatory gate.

### Post-Hard-Freeze boundary closures (CD-015 … CD-017)

| OQ | Topic | Closed by | Documented in |
| -- | ----- | --------- | ------------- |
| OQ-06 | CheckIn/CheckOut module boundary | CD-015 | `spec07`, catalog-decisions, context-map |
| OQ-07 | Voucher eligibility ownership | CD-016 | `spec08`, catalog-decisions, context-map |
| OQ-08 | Reporting projection scope | CD-017 | `spec11`, catalog-decisions, context-map |

### Cross-document consistency (verified at freeze)

| Document | Version |
| -------- | ------- |
| `catalog-decisions.md` | v2.8.1 |
| `context-map.md` | v0.4.1 |
| `spec-catalog.md` | v1.0.8 |
| `specification-playbook.md` | v1.1.1 |

### Wave 1A status snapshot

Status columns below are informational summaries only. They do not define governance decision authority ownership. For ownership, see `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`. Linked handoff files are instance records, not ownership definitions.

| Spec | Status after freeze |
| ---- | ------------------- |
| `spec02` Identity & Access | **Frozen — Wave 1A Complete** (2026-06-26); bounded packet `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED` (2026-07-13) — does **not** unfreeze Spec02 | **HD-07 docs-only closeout COMPLETE** (1405/04/27 \| 2026-07-18, AUTH-013) |
| `spec03` Employee Context | **`SPEC03_CLOSED`** (2026-07-12) — US1–US4 Batch 1b + DOC-OPT + Phase 8; Phase 7 EmployeeRead **deferred** | — |
| `spec04` Accommodation Resource | **Backend CLOSED / Product PENDING_RESIDUAL** | — |
| `spec05` Request Management | **`SPEC05_CLOSED`** (HD-07 docs-only, 1405/04/27 \| 2026-07-18) — prior `DELIVERED-NEEDS-CLOSEOUT` *(AUTH-012)* | **HD-07 EXECUTED** — no new Request IA |
| `spec06` Lottery Selection | **`DECISION-BLOCKED`** — `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` *(AUTH-012 CONFIRMED; not NOT-STARTED)* | **AUTH-013 / HD-02A:** ACCEPTED-EXCEPTION; governance debt; new Lottery **FROZEN** |
| `spec07` Allocation & Occupancy | **Fully Closed** — T001–T074 (Wave 1A + Wave 1B) | — |

### Out of scope for this freeze (not blockers)

- `spec01` implementation alignment debt — **resolved (2026-06-26):** `/api/health` contract; `getId()` rejects unassigned UUID only (creation-time assignment compatible with CD-012).
- `spec08`–`spec10` — see Spec Inventory for closure status; boundary questions closed (CD-015 … CD-017).
- `spec11` — **`IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN`** / **`DECISION-BLOCKED`** *(AUTH-012 CONFIRMED)* — **OUT-OF-CURRENT-F3** per AUTH-013 HD-03; not Fully Closed; new Reporting implementation not authorized. Re-entry: F-next feature requiring reporting enters scope.
- `spec07` — **fully closed**; active execution scope **none**; see [`handoff/spec07-implementation-authorization-wave1b.md`](handoff/spec07-implementation-authorization-wave1b.md).

### Reopening policy

Any change to a closed OQ or bounded-context boundary requires a new entry in `catalog-decisions.md`, updates to `context-map.md`, and catalog version bump with a documented unfreeze rationale.

---

## Spec Inventory

| Spec ID | Name | Purpose | Bounded Context / Capability | Dependencies | Status | Evidence Basis | Open Questions |
| ------- | ---- | ------- | ---------------------------- | ------------ | ------ | -------------- | -------------- |
| `spec01` | **Foundation** | Bootstrap Laravel 13 application, modular monolith structure, shared kernel, database baseline, testing, quality gates, local environment, and CI foundation | Platform Foundation | None | Approved | Explicit | None |
| `spec02` | **Identity & Access** | Identity accounts, roles, permissions, and access-control baseline for the platform | `Identity` | `spec01` | **Frozen — Wave 1A Complete** (+ **HD-07 docs closeout COMPLETE** 2026-07-18) | Explicit | OA-02-01 auth UX deferred. Livewire admin deferred. Role mapping for dormitory structure permissions deferred. **Delivered:** User lifecycle, RBAC baseline, `IdentityUserReadContract`, boundary tests. **Bounded packet closed (not Spec02 unfreeze):** Dormitory Structure Authorization Binding — `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED` (2026-07-13); Application-layer PEP for `dormitory.structure.view` / `dormitory.structure.manage` on covered structure actions `#1–#8` / `#12–#17`; keys registered without role grants; unresolved `#9–#11` / `#18–#21` remain deny-by-default relative to structure keys. Evidence: [`closeout/spec02-dormitory-structure-authorization-binding-closeout.md`](closeout/spec02-dormitory-structure-authorization-binding-closeout.md); reconciliation [`reconciliation/spec02-dormitory-structure-authorization-binding-catalog-reconciliation.md`](reconciliation/spec02-dormitory-structure-authorization-binding-catalog-reconciliation.md). Does **not** mean full Spec02 authorization, full RBAC, UI auth, role mapping, or OA-02-01 completed. **HD-07:** docs-only status sync COMPLETE (AUTH-013); Spec02 remains frozen. |
| `spec03` | **Employee Context** | Employee profiles, departments, and dependent records as an organizational domain context | `Employee` | `spec01`, `spec02` | **`SPEC03_CLOSED`** (2026-07-12) | Explicit | **Delivered:** US1 (T001–T026a), US2 (T027–T034), US3 (T035–T040), US4 Batch 1b eligibility, Item A DOC-OPT, Phase 8 (T053–T058). **Deferred at Spec03 close:** Phase 7 EmployeeRead (T049–T052); Scenario 9 N/A. **Not required for close:** Request Dependent live, live Allocation, Main UI. Evidence: [`handoff/spec03-closure-handoff.md`](handoff/spec03-closure-handoff.md). |
| `spec04` | **Accommodation Resource** | Dormitories, buildings, rooms, beds, and physical accommodation capacity/availability structures | `Dormitory` | `spec01` | **Backend CLOSED / Product PENDING_RESIDUAL** | Explicit | **Composite (GDR):** Planning Complete; Backend CLOSED (`SPEC04_BACKEND_CLOSED`); Product PENDING_RESIDUAL; Documentation aligned per alignment plan. **Domain:** OA-04-01 identified as superseded by Floor Aggregate — see `specs/004-accommodation-resource/spec.md` Governance & Evolution Notes (body retained). **Closed residual:** Allocation Assignability (`SPEC04_RESIDUAL_CLOSED` / `FULLY_CLOSED`) — live VACANT/RESERVED/OCCUPIED assignability + Integration Null→live path; evidence [`closeout/spec04-allocation-assignability-residual-closeout.md`](closeout/spec04-allocation-assignability-residual-closeout.md); review `IMPLEMENTATION_ACCEPTED`. **Retired residual (not Spec04 execution):** Check-in ↔ Dormitory wiring — `RETIRED_FROM_ACTIVE_SPEC04_TRACKING` / `CLOSED_NO_FURTHER_ACTION`; evidence [`discovery/spec04-checkin-dormitory-residual-readiness-review.md`](discovery/spec04-checkin-dormitory-residual-readiness-review.md); [`reconciliation/spec04-checkin-residual-closeout-reconciliation.md`](reconciliation/spec04-checkin-residual-closeout-reconciliation.md) — does **not** reopen Spec07. **Open residuals:** Auth (UI/Presentation / role-mapping / HTTP surface auth still deferred; Spec02 Application-layer structure PEP binding already closed as bounded Spec02 packet — see `spec02` Notes), UI, and other deferred items remain `DEFERRED_TO_FUTURE_WAVE` — see `spec.md` Governance & Evolution Notes. **Non-blocking note:** lottery `dormitory_id`→allocation `bedId` test fixture debt (does not reopen residual). **Hold:** remaining residual implementation until separate authorization. Catalog hierarchy “Open (planning)” note retired as stale. |
| `spec05` | **Request Management** | Accommodation request submission, request lifecycle, and request-level approval state/history | `Request` | `spec01`, `spec02`, `spec03` | **`SPEC05_CLOSED`** (HD-07 docs-only, 2026-07-18) | Explicit + Inferred | **Delivered** T001–T052 per [`handoff/spec05-implementation-authorization.md`](handoff/spec05-implementation-authorization.md). **Docs closeout (HD-07 / AUTH-013):** status synced from `DELIVERED-NEEDS-CLOSEOUT` → `SPEC05_CLOSED`. Tag `spec05-design-approved` @ `6ce0e94`; tasks @ `61e2a48`. **CD-009/010/013** frozen. **Hold:** no new Request implementation without authorization. |
| `spec06` | **Lottery Selection** | Lottery programs, registrations, draw execution, scoring, and result production | `Lottery` | `spec01`, `spec05` | **`DECISION-BLOCKED`** — `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` *(AUTH-012 CONFIRMED)* | Explicit + Inferred | **Documented Exception - Authority Gap (Lottery).** **Disposition (AUTH-013 / HD-02A):** exception ACCEPTED; governance debt recorded; **new Lottery work FROZEN.** Treating delivered code as NOT-STARTED is falsification. **Authority:** `AUTHORITY_NOT_AVAILABLE`. **CD-011:** Lottery owns all lottery rules. Evidence: [`docs/decision/spec06-regularization-decision.md`](decision/spec06-regularization-decision.md). |
| `spec07` | **Allocation & Occupancy** | Assign rooms/beds (Allocation BC); coordinate physical occupancy (Dormitory) and operational stay transitions (CheckIn/CheckOut) | `Allocation`, `Dormitory`, `CheckIn/CheckOut` (spec07 program) | `spec01`, `spec04`, `spec05`, `spec06` | **Fully Closed — Implementation Complete** | Explicit + Inferred | **Delivered:** T001–T074 per [`handoff/spec07-implementation-authorization-wave1b.md`](handoff/spec07-implementation-authorization-wave1b.md). **CD-014/CD-015** frozen. **Active execution scope:** none. **Spec04 note:** Allocation Assignability residual closed (live Spec04 provider path); Check-in Spec04 tracking retired (`CLOSED_NO_FURTHER_ACTION` — Spec07 not reopened); remaining Spec04 Product residuals (Auth, UI, etc.) remain open. **Hold:** remaining Spec04 residuals; Voucher/Reporting/Notification. |
| `spec08` | **External Accommodation** | Voucher/external-stay handling when internal capacity cannot satisfy accommodation demand | `Voucher` | `spec01`, `spec05`, `spec06` | **Fully Closed — Implementation Complete** | Inferred | **Delivered:** T001–T031 per [`handoff/spec08-implementation-closure.md`](handoff/spec08-implementation-closure.md). **Decided (CD-016):** Voucher owns eligibility evaluation and issuance lifecycle. **Active execution scope:** none. **Open (carried):** UD-03 / UD-08. |
| `spec09` | **Notification** | Shared delivery capability for system notifications such as email, SMS, and in-app alerts | Cross-cutting Capability | `spec01` | **Fully Closed — Implementation Complete** | Provisional | **Delivered:** T001–T032 per [`handoff/spec09-implementation-closure.md`](handoff/spec09-implementation-closure.md). **Active execution scope:** none. **Deferred:** Presentation UI (OA-09-05). Open (policy): domain-aware notification policy layer vs delivery-only? |
| `spec10` | **Audit** | Immutable audit trail, activity logging, and compliance-oriented traceability across critical actions | Cross-cutting Capability | `spec01` | **Fully Closed — Implementation Complete** (`CLOSED` / `FROZEN`) | Explicit + Inferred | **Delivered:** T001–T040 per [`handoff/spec10-final-closure.md`](handoff/spec10-final-closure.md). **R10/AP-06** frozen. **M1 producers:** Identity, Voucher. **Retention:** soft-archive (84mo default). **Bridge:** present, disabled by default. **Active execution scope:** none. **Deferred:** M4 producers, notification audit (R-08). **OA-10-05 / Audit UI:** separate work-item closeout (`AUDIT_UI_CLOSED`) — not Spec10 T001–T040 unfreeze. |
| `spec11` | **Reporting** | Read models, operational reports, and management-facing projections for analysis and decision support | Cross-cutting / Provisional | `spec01`, spec10 frozen baseline | **`DECISION-BLOCKED`** — `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` *(AUTH-012 CONFIRMED)* | Explicit + Provisional | **Documented Exception - Authority Gap (Reporting).** **Disposition (AUTH-013 / HD-03A):** same exception as spec06; **OUT-OF-CURRENT-F3** parked. Re-entry: explicit F-next feature requiring reporting. **No L9 blocker.** **Authority:** `AUTHORITY_CLAIMED_EVIDENCE_MISSING`. **CD-017:** read-only cross-domain projection consumer. Evidence: [`decision/spec11-authority-resolution-decision.md`](decision/spec11-authority-resolution-decision.md). |

---

## Deferred Components

| Component | Current Decision | Reason for Deferral | Activation Criteria | Status |
| --------- | ---------------- | ------------------- | ------------------- | ------ |
| **Workflow Engine** | Do not implement as an active standalone spec yet | Reusable orchestration is deferred until concrete duplication is proven; **CD-010** already defines the boundary: Workflow owns approval transition rules when activated | 1. At least 2 implemented multi-stage workflows exist. 2. Shared transition/state behavior appears across modules. 3. Reusable approval/state engine is justified by concrete duplication. | Deferred |

When activated, Workflow will:

- own approval chain definition, routing, and transition rules (**CD-010**),
- subscribe to Request approval state events,
- deliver final approval outcomes back via Domain Event.

Request retains `RequestApproval` entity ownership regardless of Workflow activation.

---

## Ordering Guidance

Implementation planning should generally proceed in this order:

1. `spec01` Foundation
2. `spec02` Identity & Access
3. `spec03` Employee Context
4. `spec04` Accommodation Resource
5. `spec05` Request Management
6. `spec06` Lottery Selection
7. `spec07` Allocation & Occupancy
8. `spec08` External Accommodation
9. `spec10` Audit
10. `spec09` Notification
11. `spec11` Reporting

Notes:

- `Audit` may be implemented earlier at technical-foundation level if required by constitutional traceability rules.
- `Notification` should not drive core domain modeling; it should follow domain events and operational outcomes.
- `Reporting` should be shaped by actual implemented read-model needs, not speculative analytics abstractions.
- `Workflow Engine` remains outside the active implementation sequence until activation criteria are met (**CD-010** boundary applies at activation).
- `spec07` delivery spans Allocation assignment plus coordination with `spec04` (Dormitory) and `CheckIn/CheckOut` per **CD-014** and **CD-015**.

---

## Governance Rules For Changes

Any later change must follow this rule set:

- If a bounded-context boundary changes, update [`catalog-decisions.md`](catalog-decisions.md) first, then this catalog.
- If a technical design changes inside one approved spec, update that spec's `plan.md`.
- If only implementation sequencing changes, update `tasks.md`, not the catalog.
- No new numbered spec may be introduced unless it is first added here with evidence basis and dependency mapping.
- Reopening a closed OQ requires a new catalog decision entry and updates to [`context-map.md`](context-map.md).

---

## Freeze Decision

**Status: HARD FREEZE v1.0.0 — ACCEPTED** (1405/04/05 | 2026/06/26)

The mandatory Hard Freeze gate is satisfied:

- OQ-01 and OQ-02 are closed.
- OQ-01 through OQ-08 are closed (CD-009 through CD-017).
- Cross-document consistency verified (see Acceptance Record).
- Wave 1A (`spec02`, `spec03`) authorized.

This catalog is the controlling operational reference for downstream `spec.md`, `plan.md`, and `tasks.md` generation until unfreeze.

**Pre-freeze alignment release:** v1.1.0 (2026-06-26) — boundary alignment work merged into this Hard Freeze acceptance.

---
## Change Log

### 1.0.30 — 2026-07-13 (human authority response resolution mirrored)

- **HDAC response resolution:** PARTIAL — HDAC-04/06 resolved from prior surface decision; HDAC-01/02/03/05 UNRESOLVED; Business Owner not designated; Spec04 Auth **BLOCKED**.
- `business-owner-designation.md` **not** created (no formal designation).
- Evidence: [`decisions/human-authority-response-resolution.md`](decisions/human-authority-response-resolution.md).
- Next flow: `HUMAN_RESPONSE_REQUIRED`.

### 1.0.29 — 2026-07-13 (human domain authority clarification packet mirrored)

- **Gate B1 packet:** Six OPEN human questions (HDAC-01–06) packaged; Spec04 Auth remains BLOCKED; no owner designated; no implementation/UI authorized.
- Evidence: [`clarifications/human-domain-authority-clarification.md`](clarifications/human-domain-authority-clarification.md).
- Next flow: `HUMAN_RESPONSE_REQUIRED` (then B2 Business Owner designation when HDAC-05 answered).

### 1.0.28 — 2026-07-13 (relationship evidence consolidation mirrored)

- **Relationship evidence map:** Nine scoped relationships classified (8 EXPLICIT, 1 GAP Department↔Dormitory). Consolidation COMPLETE; does **not** unblock Spec04 Auth or designate Business Owner.
- Evidence: [`discovery/domain-entity-relationship-map.md`](discovery/domain-entity-relationship-map.md).
- Next recommended gate (routing): `B1_HUMAN_DOMAIN_AUTHORITY_CLARIFICATION` (owner designation path remains `HUMAN_OWNER_DESIGNATION_REQUIRED` per prior clarification).

### 1.0.27 — 2026-07-13 (product surface owner authority clarification mirrored)

- **Owner authority clarification:** Surface `employee-request-self-service` remains authorized; formal Business Owner still not designated → `OWNER_DECISION_REQUIRED_TO_PROCEED`; Spec04 Auth readiness **BLOCKED**.
- Informational/status only; does **not** designate an owner, authorize Auth packet prep, UI, or implementation.
- Evidence: [`decisions/product-surface-owner-authority-clarification.md`](decisions/product-surface-owner-authority-clarification.md).
- Next flow: `HUMAN_OWNER_DESIGNATION_REQUIRED` (non-executing).

### 1.0.26 — 2026-07-13 (product surface authorization decision mirrored)

- **Product surface named:** `employee-request-self-service` (authenticated employee; own-request create/list/detail/status). Business Owner remains `UNRESOLVED` (`BLOCK_PENDING_HUMAN_AUTHORITY` preserved).
- Spec04 Auth residual: `REQUIRES_MORE_PRODUCT_AUTHORITY` — Auth packet prep **not** ready while owner unresolved.
- Informational/status only; does **not** authorize UI, RBAC, Workflow, Lottery, Reporting, dormitory admin, or manager approval.
- Evidence: [`decisions/product-surface-authorization-decision.md`](decisions/product-surface-authorization-decision.md); skeleton [`contracts/employee-request-self-service.feature-contract.skeleton.yaml`](contracts/employee-request-self-service.feature-contract.skeleton.yaml).
- Next flow: `PRODUCT_SURFACE_REFINEMENT_REQUIRED` (resolve Business Owner before owner-bound Auth packet handoff).

### 1.0.25 — 2026-07-13 (business owner formalization review mirrored)

- **Business Owner wording review:** Proposed label «واحد اداری / منابع انسانی» classified `CONFLICTING_TERM`; owner field for next auth prompt must remain `UNRESOLVED` (`BLOCK_PENDING_HUMAN_AUTHORITY`).
- Informational/status only; does **not** assign a Business Owner, authorize Auth/UI, invent departments, or map `hr_manager` / `department_manager` to owner.
- Evidence: [`decisions/business-owner-formalization-review.md`](decisions/business-owner-formalization-review.md).
- Next flow: `HUMAN_DOMAIN_AUTHORITY_CLARIFICATION` (non-executing); product-surface authorization remains blocked until human names an evidenced owner.

### 1.0.24 — 2026-07-13 (domain entity/relationship/authority map consolidation mirrored)

- **Evidence consolidation completed:** Canonical map recorded — entity inventory COMPLETED; relationship/actor maps PARTIAL; business owner NOT_DEFINED; org model still REQUIRES_HUMAN_CLARIFICATION; Auth basis NOT_READY.
- Informational/status only; does **not** authorize Auth packet prep, UI, role mapping, product-surface selection, Lottery, Workflow, or Spec02 unfreeze.
- Evidence: [`discovery/domain-entity-relationship-authority-map.md`](discovery/domain-entity-relationship-authority-map.md); gate decision [`decisions/domain-structure-evidence-consolidation-gate.md`](decisions/domain-structure-evidence-consolidation-gate.md).
- Next flow: `HUMAN_DOMAIN_AUTHORITY_CLARIFICATION` (non-executing).

### 1.0.23 — 2026-07-13 (domain structure evidence consolidation gate mirrored)

- **Intermediate gate accepted:** `DOMAIN_STRUCTURE_AND_RELATIONSHIP_EVIDENCE_CONSOLIDATION` inserted after domain/org discovery and before human clarification / product-surface authorization.
- Informational/status only; does **not** authorize Auth packet prep, UI, role mapping, human clarification completion, Lottery, Workflow, or Spec02 unfreeze.
- Evidence: [`decisions/domain-structure-evidence-consolidation-gate.md`](decisions/domain-structure-evidence-consolidation-gate.md); upstream discovery [`discovery/domain-authority-and-organization-model-discovery.md`](discovery/domain-authority-and-organization-model-discovery.md).
- Next flow: `DOMAIN_STRUCTURE_AND_RELATIONSHIP_EVIDENCE_CONSOLIDATION` (discovery consolidation; non-executing).

### 1.0.22 — 2026-07-13 (product authorization gap triage mirrored)

- **Product authorization:** Gap triage recorded — `NO_NAMED_PRODUCT_SURFACE_AUTHORIZED`; Spec04 Auth residual remains `REQUIRES_PRODUCT_AUTHORITY`; Spec11 Reporting remains **separate authority track** (not merged into Spec04 Auth residual).
- Informational/status only; does **not** authorize Auth packet prep, UI, role mapping, HTTP/Policy, Feature Contracts, Lottery, Workflow, or Spec02 unfreeze.
- Evidence: [`decisions/product-authorization-gap-triage.md`](decisions/product-authorization-gap-triage.md); [`spec04/spec04-auth-residual-product-decision.md`](spec04/spec04-auth-residual-product-decision.md).
- Next flow: `PRODUCT_SURFACE_AUTHORIZATION_DECISION` (human/product; non-executing).

### 1.0.32 — 2026-07-18 (HD-07 spec02/spec05 docs-only closeout)

- **HD-07 EXECUTED** (AUTH-013 Option A; Lead F3 Sprint A auth 1405/04/27).
- **spec02:** remains **Frozen — Wave 1A Complete**; disposition **HD-07 docs-only closeout COMPLETE** (does not unfreeze).
- **spec05:** status **`SPEC05_CLOSED`** (docs-only); prior `DELIVERED-NEEDS-CLOSEOUT` satisfied by status sync. No new Request implementation authorization.
- Evidence: `specs/002-identity-access/spec.md`, `specs/005-request-management/spec.md`, `docs/governance/open-decisions.md` § HD-07; modules `app/Modules/Identity`, `app/Modules/Request` + existing handoffs.
- Does **not** authorize UI, Spec04 Auth packet, or Spec02 unfreeze.

### 1.0.31 — 2026-07-16 (AUTH-013 disposition annotations — Lead CONFIRMED)

- **AUTH-012:** All 18 disposition audit rows CONFIRMED (recorded under AUTH-013).
- **spec05 Status:** `DELIVERED-NEEDS-CLOSEOUT` (AUTH-012 corrected mismatch).
- **spec06 Status:** `DECISION-BLOCKED` / `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` — not NOT-STARTED (AUTH-012; HD-02A).
- **spec11 Status:** `DECISION-BLOCKED` / OUT-OF-CURRENT-F3 parked (HD-03A).
- **spec02 / spec05:** docs-only closeout scheduled Wave 2 post-merge (HD-07A).
- Disposition column annotations only in Wave 1A snapshot; inventory Status column updated for spec05/06/11. No spec content changes.
- Evidence: `docs/governance/open-decisions.md` (AUTH-012, HD-01…07); `docs/governance/roadmap.md` § F3 Execution Waves.

### 1.0.21 — 2026-07-13 (spec04 auth residual product decision mirrored)

- **spec04 Auth residual:** Product decision recorded — `SPEC04_AUTH_RESIDUAL_REQUIRES_PRODUCT_AUTHORITY`. Application PEP remains completed under Spec02; Auth residual remains **OPEN**; pre-UI packet promotion **not viable** without named product-authorized surface (`dormitory-admin-ui` and peers remain unauthorized).
- Informational/status only; does **not** authorize role mapping, UI, HTTP/Policy, Feature Contracts, stream selection, Lottery, Workflow, Full RBAC, or Spec02 unfreeze.
- Evidence: [`spec04/spec04-auth-residual-product-decision.md`](spec04/spec04-auth-residual-product-decision.md); portfolio [`planning/deferred-portfolio-review-and-disposition.md`](planning/deferred-portfolio-review-and-disposition.md).
- Next flow: `PRODUCT_AUTHORIZATION_GAP_TRIAGE` (non-executing).

### 1.0.20 — 2026-07-13 (core completion wave membership / hygiene mirrored)

- **Core Completion Wave (informational):** Workflow `WORKFLOW_REMAINS_DEFERRED`; Spec06 `SPEC06_REMAINS_DEFERRED` (out of Core Wave path); Spec04 Auth residual post-binding status refresh completed (Application structure PEP closed under Spec02; Auth residual **not** closed — role mapping / Presentation / HTTP remainder deferred; Dormitory UI product auth still blocked).
- Spec06 inventory status unchanged: `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN`; regularization complete; `AUTHORITY_NOT_AVAILABLE` continues to hold **new** Lottery implementation.
- Spec02 / Spec03 / Spec04 Assignability / Check-in postures unchanged (closed / frozen / retired as previously recorded).
- Informational/status reconciliation only; does **not** authorize implementation, stream selection, UI intake, role mapping, OA-02-01, Lottery work, or Workflow activation.
- Evidence: [`planning/core-completion-wave-plan.md`](planning/core-completion-wave-plan.md); [`decisions/workflow-activate-vs-defer-decision.md`](decisions/workflow-activate-vs-defer-decision.md); [`spec04/spec04-auth-residual-post-binding-status-refresh.md`](spec04/spec04-auth-residual-post-binding-status-refresh.md); [`decisions/spec06-core-wave-inclusion-decision.md`](decisions/spec06-core-wave-inclusion-decision.md); [`planning/core-completion-wave-hygiene-pass.md`](planning/core-completion-wave-hygiene-pass.md).
- Next flow: `CORE_COMPLETION_WAVE_STREAM_SELECTION` (non-executing).

### 1.0.19 — 2026-07-13 (spec02 dormitory structure authorization binding closeout mirrored)

- **spec02:** Bounded packet **Dormitory Structure Authorization Binding** mirrored as `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED` (implementation completed; review `IMPLEMENTATION_ACCEPTED`; limited closeout recorded).
- Spec02 overall status remains **Frozen — Wave 1A Complete** — this is **not** Spec02 unfreeze, full authorization closure, full RBAC, UI auth, role mapping, or OA-02-01 completion.
- **spec04:** Open Auth residual wording clarified — Application-layer structure PEP binding closed under Spec02; UI/Presentation / role-mapping Auth residual remains deferred.
- Informational/status reconciliation only; does **not** authorize new Spec02/Spec04 implementation, UI intake, role mapping, or Spec07 reopen.
- Evidence: [`closeout/spec02-dormitory-structure-authorization-binding-closeout.md`](closeout/spec02-dormitory-structure-authorization-binding-closeout.md); [`reconciliation/spec02-dormitory-structure-authorization-binding-catalog-reconciliation.md`](reconciliation/spec02-dormitory-structure-authorization-binding-catalog-reconciliation.md).
- Next flow: `NEXT_WORK_SELECTION_GATE` (not a new Spec02 implementation/review/closeout action).

### 1.0.18 — 2026-07-12 (spec04 Check-in residual closeout reconciliation)

- **spec04:** Check-in ↔ Dormitory residual retired from active Spec04 tracking (`RETIRED_FROM_ACTIVE_SPEC04_TRACKING` / `CLOSED_NO_FURTHER_ACTION`) after readiness `NO_FURTHER_ACTION_RECOMMENDED`.
- Product status remains `PENDING_RESIDUAL` for Auth / UI and other deferred open items; CheckIn wiring no longer listed as open Spec04 residual.
- Informational/status reconciliation only; does **not** authorize Spec04/Spec07 implementation, Spec07 reopen, or Assignability reopen.
- Evidence: [`discovery/spec04-checkin-dormitory-residual-readiness-review.md`](discovery/spec04-checkin-dormitory-residual-readiness-review.md); [`reconciliation/spec04-checkin-residual-closeout-reconciliation.md`](reconciliation/spec04-checkin-residual-closeout-reconciliation.md).

### 1.0.17 — 2026-07-12 (spec04 Allocation Assignability residual closeout)

- **spec04 Open Questions:** Allocation Assignability residual recorded `CLOSED` / `FULLY_CLOSED` after accepted implementation review; Product status remains `PENDING_RESIDUAL` for Auth / UI / CheckIn and other deferred items.
- Informational mirror only; does **not** authorize new Spec04 residuals, Spec02/Spec07 ownership changes, UI, or lottery redesign.
- Evidence: [`closeout/spec04-allocation-assignability-residual-closeout.md`](closeout/spec04-allocation-assignability-residual-closeout.md); [`reviews/spec04-allocation-assignability-impl-review.md`](reviews/spec04-allocation-assignability-impl-review.md).
- Next flow: `NEXT_WORK_SELECTION`.

### 1.0.16 — 2026-07-12 (spec11 controlled alignment execution)

- **spec11 Status** set to `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` under Wave 02 execution grant `EXECUTION_AUTHORIZATION_GRANTED`.
- **Notes:** Documented Exception - Authority Gap (Reporting); `AUTHORITY_CLAIMED_EVIDENCE_MISSING`; governance debt active; not Fully Closed.
- Informational mirror only; does **not** invent Design Approval, re-authenticate IA via recovered DA, or authorize new Reporting implementation.
- Evidence: [`decision/spec11-regularization-execution-authorization-grant.md`](decision/spec11-regularization-execution-authorization-grant.md); [`decision/spec11-authority-resolution-decision.md`](decision/spec11-authority-resolution-decision.md); [`validation/spec11-alignment-verification.md`](validation/spec11-alignment-verification.md).

### 1.0.15 — 2026-07-12 (spec06 controlled alignment execution)

- **spec06 Status** set to `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` under execution authorization `GRANTED`.
- **Notes:** Documented Exception - Authority Gap (Lottery).
- Informational mirror only; does **not** grant IA, Full Closure, or new Lottery execution.
- Evidence: [`handoff/spec06-regularization-execution-authorization.md`](handoff/spec06-regularization-execution-authorization.md); [`docs/validation/spec06-alignment-verification.md`](validation/spec06-alignment-verification.md).

### 1.0.14 — 2026-07-12 (spec06 documentary regularization)

- **spec06 Status synchronized** to GDR composite labels: Implementation Complete / Governance Open (informational mirror only; does not grant IA, Full Closure, or new Lottery execution).
- Open Questions / Notes: Documented exception (Option B); Authority `AUTHORITY_NOT_AVAILABLE` (Documented Exception); SPEC06-C06 not claimed as confirmed.
- Evidence: [`docs/decision/spec06-regularization-decision.md`](decision/spec06-regularization-decision.md); [`docs/plans/spec06-regularization-plan.md`](plans/spec06-regularization-plan.md).

### 1.0.13 — 2026-07-12 (spec04 documentary alignment)

- **spec04 Status synchronized** to GDR composite labels: Backend CLOSED / Product PENDING_RESIDUAL (informational mirror only; does not grant IA or residual implementation).
- Open Questions: Floor hierarchy “Open (planning)” retired as stale; residuals marked `DEFERRED_TO_FUTURE_WAVE` per GDR Decision 4.
- Evidence: [`docs/decision/spec04-governance-decision.md`](decision/spec04-governance-decision.md); [`docs/plans/spec04-alignment-plan.md`](plans/spec04-alignment-plan.md); [`handoff/spec04-backend-closeout.md`](handoff/spec04-backend-closeout.md).

### 1.0.12 — 2026-07-12 (spec03 Batch B closure)

- **spec03 closed** — `SPEC03_CLOSED`; see [`handoff/spec03-closure-handoff.md`](handoff/spec03-closure-handoff.md).
- Live status rows: remove false US3+/US4 hold; record US1–US4 Batch 1b + DOC-OPT + Phase 8 delivered.
- Phase 7 EmployeeRead (T049–T052) **deferred at Spec03 close** (not delivered).
- Batch B governance: Items A+C completed; Item B deferred; Item D status sync.

### 1.0.11 — 2026-07-01 (spec08 nomination)

- **spec08 nominated** for authorization initiation — see [`handoff/spec08-nomination-record.md`](handoff/spec08-nomination-record.md).
- Informational status: **Nominated for Authorization**; execution **NOT AUTHORIZED**.
- spec07 remains **Fully Closed**; active execution scope **none**.

### 1.0.10 — 2026-07-01 (spec07 implementation closure)

- **spec07 fully closed** — T001–T074 complete; Wave 1A + Wave 1B closed.
- Authorization lifecycle: Wave 1B record `revoked` (program closure); Wave 1A `superseded`.
- Active execution scope for spec07: **none**. Next implementation requires new explicit authorization.
- spec08–spec11 implementation not authorized.

### 1.0.9 — 2026-07-01 (spec07 implementation authorization)

- **spec07 implementation authorized** — Wave 1A T006–T052; see [`handoff/spec07-implementation-authorization.md`](handoff/spec07-implementation-authorization.md).
- Baseline: design handoff [`spec07-design-approved.md`](handoff/spec07-design-approved.md); tasks @ `a12e32cc`.
- Excluded: T053–T074, spec04 Dormitory implementation, Voucher, Reporting, Notification, reconciliation.
- spec04 implementation hold unchanged; spec08–spec11 not authorized.

### 1.0.8 — 2026-07-01 (governance drift sync)

- Closed OQ-06 (CD-015), OQ-07 (CD-016), OQ-08 (CD-017) in catalog mirrors.
- Updated Spec Inventory bounded-context and Open Questions columns for spec07..spec11.
- Editorial only — no architecture decision changes.

### 1.0.7 — 2026-06-23 (spec05 implementation authorization)

- **spec05 implementation authorized** — T001–T052; see [`handoff/spec05-implementation-authorization.md`](handoff/spec05-implementation-authorization.md).
- Baseline: tag `spec05-design-approved` (`6ce0e94`), tasks (`61e2a48`).
- spec04 frozen / implementation hold unchanged; spec03 US3+ hold preserved.

### 1.0.6 — 2026-06-23 (spec05 planning authorization)

- **spec05 planning authorized** — Phase 1 design authorized; see [`handoff/spec05-planning-authorization.md`](handoff/spec05-planning-authorization.md).
- Checkpoint: `spec05-planning-review` PASS (OA-05-09).
- Tasks, implementation, and code **not** authorized.
- spec04 unchanged.

### 1.0.5 — 2026-06-23 (spec04 planning authorization)

- **spec04 planning authorized** — specify/plan/tasks only; see [`handoff/spec04-planning-authorization.md`](handoff/spec04-planning-authorization.md).
- spec03 Wave 1B completion unchanged; US3/US4 remain on hold; spec05 implementation not authorized.

### 1.0.4 — 2026-06-23 (Wave 1B completion)

- **spec03 Wave 1B completed** — US2 / T027–T034 implemented and verified; see [`handoff/spec03-post-mvp-authorization.md`](handoff/spec03-post-mvp-authorization.md).
- US3, US4, spec04, and spec05 remain unauthorized.

### 1.0.3 — 2026-06-23 (Wave 1B authorization)

- **spec03 Wave 1B authorized** — US2 / T027–T034 only; see [`handoff/spec03-post-mvp-authorization.md`](handoff/spec03-post-mvp-authorization.md).
- US3, US4, spec04, and spec05 remain unauthorized.

### 1.0.2 — 2026-06-26 (Post-MVP checkpoint)

- **spec03 MVP implemented** — PR #4 + PR #2 merged into `spec02-baseline`; US1 (T001–T026a) delivered; US2 (T030+) remains on hold pending explicit authorization.
- Wave 1A table and Spec Inventory updated from authoring → MVP Implemented.

### 1.0.1 — 2026-06-26

- **spec02 frozen** — Wave 1A implementation complete; status → Frozen — Wave 1A Complete.
- **spec03 authoring** — Wave 1A partner context; depends on frozen spec02 supplier surface.

### 1.0.0 (Hard Freeze) — 2026-06-26

- **Hard Freeze v1.0.0 accepted** — Acceptance Record registered.
- Mandatory gate: OQ-01 (CD-012), OQ-02 (CD-013) closed.
- Non-blocking: OQ-06, OQ-07, OQ-08 documented.
- Cross-document consistency verified across four governance documents.
- Wave 1A authorized: `spec02`, `spec03`.
- `spec01` implementation debt explicitly out of freeze scope.

### 1.1.0 — 2026-06-26 (pre-freeze alignment)

- Aligned with `catalog-decisions.md` v2.2.0 and `context-map.md` v0.3.
- Added Resolved Boundary Questions section (CD-009 through CD-014).
- Proposed Freeze spec02–spec06 registered in playbook.
- Superseded by 1.0.0 Hard Freeze acceptance.

### 1.0.0-draft — 1405/04/02

- Initial catalog draft (Soft Freeze / Proposed Freeze).