# DormSys Spec Catalog

**Version:** 1.0.0  
**Status:** Proposed Freeze  
**Last Updated:** 1405/04/02

---

## Purpose

This catalog defines the controlled specification roadmap for DormSys v1.0.  
Each spec represents either:

- a bounded context confirmed or inferred from the architecture documents, or
- a cross-cutting capability required for platform completeness.

This file is the upstream reference for all future `spec.md`, `plan.md`, and `tasks.md` artifacts.

---

## Catalog Rules

- Specs derive from this catalog, not vice versa.
- Explicitly documented boundaries must be distinguished from inferred ones.
- Open questions must remain visible until resolved.
- Deferred components must not be promoted into active implementation without activation criteria.
- Cross-cutting capabilities must not be mistaken for business bounded contexts.
- A spec may be planned even if some of its internal modeling decisions are still open, provided those questions are documented here.

---

## Evidence Basis Legend

- `Explicit`: directly named or clearly defined in the reference documents.
- `Inferred`: strongly implied by the documents, but not explicitly formalized as a standalone spec.
- `Provisional`: currently useful for delivery planning, but subject to reshaping as implementation reveals actual boundaries.
- `Deferred`: intentionally postponed by architectural decision until activation criteria are met.

---

## Spec Inventory

| Spec ID | Name | Purpose | Bounded Context / Capability | Dependencies | Status | Evidence Basis | Open Questions |
|---------|------|---------|------------------------------|--------------|--------|----------------|----------------|
| `spec01` | **Foundation** | Bootstrap Laravel 13 application, modular monolith structure, shared kernel, database baseline, testing, quality gates, local environment, and CI foundation | Platform Foundation | None | Approved | Explicit | None |
| `spec02` | **Identity & Access** | Identity accounts, roles, permissions, and access-control baseline for the platform | `Identity` | `spec01` | Planned | Explicit | Should authentication behavior be specified in this spec directly, or deferred until real entry flows are defined? |
| `spec03` | **Employee Context** | Employee profiles, departments, and dependent-related records as an organizational domain context | `Employee` | `spec01`, `spec02` | Planned | Explicit | Is Employee a profile attached to Identity users, or a standalone context with its own lifecycle and linkage rules? |
| `spec04` | **Accommodation Resource** | Dormitories, buildings, rooms, beds, and physical accommodation capacity/availability structures | `Dormitory` | `spec01` | Planned | Explicit | Should building/floor hierarchy be modeled inside the same context from the start, or introduced only when needed by operations? |
| `spec05` | **Request Management** | Accommodation request submission, request lifecycle, and request-level approval capability | `Request` | `spec01`, `spec02`, `spec03` | Planned | Explicit + Inferred | Approval capability is currently cataloged under Request Management. Future extraction remains possible only if reusable workflow behavior becomes concrete. |
| `spec06` | **Lottery Selection** | Lottery programs, candidate selection, draw execution, and result production for capacity-constrained scenarios | `Lottery` | `spec01`, `spec05` | Planned | Explicit + Inferred | Are lottery eligibility rules owned entirely by Lottery, or partially derived from Request policy and workflow outcomes? |
| `spec07` | **Allocation & Occupancy** | Assign rooms/beds, track occupancy, and manage check-in/check-out/stay usage lifecycle | `Allocation` | `spec01`, `spec04`, `spec05`, `spec06` | Planned | Explicit + Inferred | Are Allocation and Occupancy one aggregate boundary, or two related subdomains that should remain within one delivery spec for v1? |
| `spec08` | **External Accommodation** | Voucher/external-stay handling when internal capacity cannot satisfy accommodation demand | `Voucher` | `spec01`, `spec05`, `spec06` | Planned | Inferred | What is the exact boundary between failed internal allocation, lottery outcome, and external accommodation eligibility? |
| `spec09` | **Notification** | Shared delivery capability for system notifications such as email, SMS, and in-app alerts | Cross-cutting Capability | `spec01` | Planned | Provisional | Is Notification only an infrastructure delivery mechanism, or does DormSys need a domain-aware notification policy layer later? |
| `spec10` | **Audit** | Immutable audit trail, activity logging, and compliance-oriented traceability across critical actions | Cross-cutting Capability | `spec01` | Planned | Explicit + Inferred | Should audit remain purely technical logging, or evolve to include domain audit semantics per module? |
| `spec11` | **Reporting** | Read models, operational reports, and management-facing projections for analysis and decision support | Cross-cutting / Provisional | `spec01`, implemented business specs as needed | Planned | Explicit + Provisional | Does Reporting remain projection-based inside the monolith, or later evolve toward a richer analytics/KPI boundary? |

---

## Deferred Components

| Component | Current Decision | Reason for Deferral | Activation Criteria | Status |
|-----------|------------------|---------------------|---------------------|--------|
| **Workflow Engine** | Do not implement as an active standalone spec yet | The architecture permits workflow-centric design, but explicit extraction of a reusable Workflow module is deferred until real duplication and reusable orchestration patterns are proven | 1. At least 2 implemented multi-stage workflows exist. 2. Shared transition/state behavior appears across modules. 3. Reusable approval/state engine is justified by concrete duplication. | Deferred |

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
- `Workflow Engine` remains outside the active implementation sequence until activation criteria are met.

---

## Governance Rules For Changes

Any later change must follow this rule set:

- If a bounded-context boundary changes, update this catalog first.
- If a technical design changes inside one approved spec, update that spec's `plan.md`.
- If only implementation sequencing changes, update `tasks.md`, not the catalog.
- No new numbered spec may be introduced unless it is first added here with evidence basis and dependency mapping.

---

## Freeze Decision

This catalog may be considered ready for operational use when:

- all `Open Questions` are accepted as visible unresolved items,
- no spec claims stronger documentary certainty than the evidence supports,
- deferred components remain deferred,
- downstream specs are generated from this file without inventing new top-level contexts.

Until then, this document remains the controlling draft for roadmap alignment.
