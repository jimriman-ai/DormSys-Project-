# DormSys Spec Catalog

**Version:** 1.0.11 (spec08 nomination)  
**Status:** Hard Freeze — Operational  
**Last Updated:** 1405/04/10 | 2026/07/01  
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
| `spec02` Identity & Access | **Frozen — Wave 1A Complete** (2026-06-26) |
| `spec03` Employee Context | **MVP Implemented — Wave 1A** (US1 / T001–T026a) · **Wave 1B Completed (US2)** (T027–T034) |
| `spec04` Accommodation Resource | **Planning Authorized** (implementation not authorized) |
| `spec05` Request Management | **Implementation Authorized** (T001–T052; tag `spec05-design-approved`) |
| `spec07` Allocation & Occupancy | **Fully Closed** — T001–T074 (Wave 1A + Wave 1B) |

### Out of scope for this freeze (not blockers)

- `spec01` implementation alignment debt — **resolved (2026-06-26):** `/api/health` contract; `getId()` rejects unassigned UUID only (creation-time assignment compatible with CD-012).
- `spec08`–`spec11` — remain Planned; boundary questions closed (CD-015 … CD-017); implementation not authorized.
- `spec07` — **fully closed**; active execution scope **none**; see [`handoff/spec07-implementation-authorization-wave1b.md`](handoff/spec07-implementation-authorization-wave1b.md).

### Reopening policy

Any change to a closed OQ or bounded-context boundary requires a new entry in `catalog-decisions.md`, updates to `context-map.md`, and catalog version bump with a documented unfreeze rationale.

---

## Spec Inventory

| Spec ID | Name | Purpose | Bounded Context / Capability | Dependencies | Status | Evidence Basis | Open Questions |
| ------- | ---- | ------- | ---------------------------- | ------------ | ------ | -------------- | -------------- |
| `spec01` | **Foundation** | Bootstrap Laravel 13 application, modular monolith structure, shared kernel, database baseline, testing, quality gates, local environment, and CI foundation | Platform Foundation | None | Approved | Explicit | None |
| `spec02` | **Identity & Access** | Identity accounts, roles, permissions, and access-control baseline for the platform | `Identity` | `spec01` | **Frozen — Wave 1A Complete** | Explicit | OA-02-01 auth UX deferred. **Delivered:** User lifecycle, RBAC, `IdentityUserReadContract`, boundary tests. Livewire admin deferred. |
| `spec03` | **Employee Context** | Employee profiles, departments, and dependent records as an organizational domain context | `Employee` | `spec01`, `spec02` | **MVP Implemented — Wave 1A**; **Wave 1B Completed (US2)** (T027–T034; US3+ hold) | Explicit | **Delivered (US1):** Employee create, `identity_id` CD-012 boundary, `IdentityUserReadContract` consumer, BT-01–BT-05. **Delivered (US2):** Department create/deactivate, employee assignment, R-17 inactive guard. **Hold:** US3, US4, T035+. |
| `spec04` | **Accommodation Resource** | Dormitories, buildings, rooms, beds, and physical accommodation capacity/availability structures | `Dormitory` | `spec01` | **Planning Authorized** | Explicit | **Open (planning):** building/floor hierarchy — resolve during spec04 authoring. **Decided (CD-014):** owns physical room/bed occupancy state; Allocation drives updates via events. **Hold:** implementation until separate authorization. |
| `spec05` | **Request Management** | Accommodation request submission, request lifecycle, and request-level approval state/history | `Request` | `spec01`, `spec02`, `spec03` | **Implementation Authorized** | Explicit + Inferred | **Authorized:** T001–T052 per [`handoff/spec05-implementation-authorization.md`](handoff/spec05-implementation-authorization.md). Tag `spec05-design-approved` @ `6ce0e94`; tasks @ `61e2a48`. **CD-009/010/013** frozen. **Hold:** spec03 US3 for Wave 1B; spec04 impl unchanged. |
| `spec06` | **Lottery Selection** | Lottery programs, registrations, draw execution, scoring, and result production | `Lottery` | `spec01`, `spec05` | Planned | Explicit + Inferred | **Decided (CD-011):** Lottery owns all lottery rules, lifecycle, and results; emits proposed allocations to Allocation. |
| `spec07` | **Allocation & Occupancy** | Assign rooms/beds (Allocation BC); coordinate physical occupancy (Dormitory) and operational stay transitions (CheckIn/CheckOut) | `Allocation`, `Dormitory`, `CheckIn/CheckOut` (spec07 program) | `spec01`, `spec04`, `spec05`, `spec06` | **Fully Closed — Implementation Complete** | Explicit + Inferred | **Delivered:** T001–T074 per [`handoff/spec07-implementation-authorization-wave1b.md`](handoff/spec07-implementation-authorization-wave1b.md). **CD-014/CD-015** frozen. **Active execution scope:** none. **Hold:** spec04 impl; Voucher/Reporting/Notification. |
| `spec08` | **External Accommodation** | Voucher/external-stay handling when internal capacity cannot satisfy accommodation demand | `Voucher` | `spec01`, `spec05`, `spec06` | **Nominated for Authorization** | Inferred | **Nominated:** [`handoff/spec08-nomination-record.md`](handoff/spec08-nomination-record.md). **Decided (CD-016):** Voucher owns eligibility evaluation and issuance lifecycle. **Execution:** NOT AUTHORIZED. |
| `spec09` | **Notification** | Shared delivery capability for system notifications such as email, SMS, and in-app alerts | Cross-cutting Capability | `spec01` | Planned | Provisional | Is Notification only an infrastructure delivery mechanism, or does DormSys need a domain-aware notification policy layer later? |
| `spec10` | **Audit** | Immutable audit trail, activity logging, and compliance-oriented traceability across critical actions | Cross-cutting Capability | `spec01` | Planned | Explicit + Inferred | Should audit remain purely technical logging, or evolve to include domain audit semantics per module? |
| `spec11` | **Reporting** | Read models, operational reports, and management-facing projections for analysis and decision support | Cross-cutting / Provisional | `spec01`, implemented business specs as needed | Planned | Explicit + Provisional | **Decided (CD-017):** Reporting remains a read-only cross-domain projection consumer; no write authority over upstream domain state. |

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