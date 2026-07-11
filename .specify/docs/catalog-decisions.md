# DormSys Catalog Decisions

**Version:** 2.8.3  
**Status:** ACTIVE  
**Last Updated:** 1405/04/20 | 2026/07/11
**Related Documents:** [`context-map.md`](context-map.md), [`spec-catalog.md`](spec-catalog.md), `CONSTITUTION v1.3.0.md`, `dormsys-architecture.md`

---

## Purpose

This document is the authoritative register of architectural boundary decisions for DormSys v1.0.  
It records:

- resolved conflicts between source documents,
- closures of open boundary questions (OQ-*),
- rationale, impact, and deferred implementation details.

Decisions here supersede provisional assumptions in discovery notes and informal history documents.

---

## Governance Decision Authority Map

This section is the **only** canonical authority ownership map for the operational governance decision classes listed below. Other governance documents MUST reference this section and MUST NOT redefine authority ownership.

Concept definitions and authorization-record lifecycle are defined in `.specify/governance/_meta/authority-model.md`. This document owns the canonical mapping of decision classes to authoritative source files.

### Authority Ownership Rules

- One decision class has one canonical owner.
- Multiple active authority owners for the same decision class are a governance conflict.
- Governance conflicts require HALT until resolved.

### Separation Rules

- Design Approval ≠ Implementation Authorization.
- Batch Execution Permission ≠ Implementation Authorization.
- Review Gate approval does not grant implementation authority.

| Decision | Canonical Authority Source | Owner | Boundary / Notes |
| --- | --- | --- | --- |
| Design Approval | `.specify/docs/handoff/<spec>-design-approved.md` | Governance Review | Confirms design readiness only; does not permit implementation |
| Implementation Authorization | `.specify/docs/handoff/<spec>-implementation-authorization.md` | Governance Review | Permits implementation execution under declared scope recorded in the authorization record |
| Batch Execution Permission | `.specify/docs/catalog-decisions.md` § Governance Decision Authority Map | Governance Review | Permits progression to the next eligible batch only. Review-gate approval satisfies the human-review governing input only; it does not grant Implementation Authorization. Governing inputs (not authority owners): `.specify/governance/execution-policy.md`, `.specify/governance/batches/<spec>.md`, and a recorded human review outcome. |

### Operational authority map scope (strict)

The table above is **strictly limited** to exactly **three** operational governance decision classes — and their corresponding operational authority types:

1. **Design Approval**
2. **Implementation Authorization**
3. **Batch Execution Permission**

No fourth operational authority type, decision class, or map row is introduced by this document or by tiered enforcement policy.

**Case C** (governance-precondition failure) and **Nomination Record** (evidence-only artifact) **do not** extend, modify, or participate in `## Governance Decision Authority Map`. They are documented below as **non-operational governance dependencies** only.

### Case C — governance precondition classification (non-operational)

**Case C is NOT an operational governance decision class.**

Case C is a **governance-precondition classification only** — used when a required Nomination Record is missing or invalid before certain next-spec governance flows. It:

- exists solely for HALT classification in `.specify/governance/execution-policy.md` v1.4.0 (§ HALT Classification — Case C; § Nomination and Execution Policy),
- is enforced procedurally by `.specify/governance/governance-enforcer.md` v1.3.0+ (HALT precedence: **Case C → Case A → Case B**),
- **does not** appear in `## Governance Decision Authority Map` as an authority type, decision class, or owner entry.

Case C **does not** grant Design Approval, Implementation Authorization, or Batch Execution Permission. It **does not** resolve or assign authority ownership.

*Informational cross-reference only (non-authority-defining):* Case C semantics and messages are normative in `execution-policy.md` v1.4.0; enforcement behavior is normative in `governance-enforcer.md` v1.3.0+.

### Nomination Record boundary (non-operational dependency)

A **Nomination Record** is an **evidence-only** artifact recording program-level spec selection after a governance transition boundary. It:

- is **not** part of the authorization record lifecycle (per `authority-model.md`),
- is **not** used to resolve or assign authority in `## Governance Decision Authority Map`,
- **must not** appear as a decision node or authority row in the authority map table above.

A Nomination Record **may** be referenced by execution policy as a **prerequisite** before initiating Design Approval workflows for a next specification. That prerequisite relationship is enforced as **Case C** when unmet; it does **not** make the Nomination Record an operational authority artifact.

Nomination Records **do not** grant Design Approval, Implementation Authorization, or Batch Execution Permission.

### Governance state / snapshot artifacts

This is a **descriptive category only**. It is **not** a decision class and is **not** listed in the authority map above.

Documents in this category are:

- **evidence-only**
- **non-authoritative**
- usable only for status interpretation, transition interpretation, and audit/history context

Examples include governance state snapshots, transition-state records, checkpoint summaries, and audit/status documents.

Such artifacts:

- **DO NOT** grant Design Approval
- **DO NOT** grant Implementation Authorization
- **DO NOT** grant Batch Execution Permission
- **DO NOT** satisfy authorization checks

They are **outside** the authorization record lifecycle defined in `.specify/governance/_meta/authority-model.md`.

Formal authorization artifact classes — together with their existing authority mappings in the table above — are the **only** sources that may grant operational execution authority per those mappings. Governance state / snapshot artifacts, alone or in combination with naming similarity to authorization artifacts, **must not** be interpreted as authorization.

Naming, folder location, or similarity to authorization artifact names **alone** must not be interpreted as authorization.

### Governance Transition (state — not an authority owner)

**Governance Transition** is an operational state documented in `.specify/governance/execution-policy.md` § Governance Transition State. It is **not** a decision class in the map above and has **no** canonical authority owner here.

The state occurs when authorized implementation work is complete (or no target is nominated) and no valid Implementation Authorization exists for a next specification or batch because no governance decision has yet selected or authorized one.

**Selecting or authorizing the next specification or batch** is a governance decision that **requires** explicit authority ownership in this map. That decision class is **not** defined in `## Governance Decision Authority Map` at this time. This document does **not** assign it to any existing or new owner.

Until such ownership is formally added through a future governance change:

- enforcement documents must **HALT** with the Case B message from `.specify/governance/execution-policy.md`:

  > `No authorized implementation exists. Governance transition decision required.`

- enforcement must **not** infer the next specification or batch from catalog ordering, dependencies, or informational status mirrors.

---

## Authority Drift Resolution

Authority ownership is determined **only** by this document, section `## Governance Decision Authority Map`.

No other document may determine, duplicate, summarize, or replace that ownership map.

### Governance document roles

| Layer | Role | Ownership |
| --- | --- | --- |
| **Tiered governance** | Operational behavior: enforcement, precedence, orchestration, batch strategy, coding constraints, review controls | Non-owning. Must pointer-reference the canonical map. |
| **Meta governance** | Conceptual vocabulary, invariants, authorization-record lifecycle (`authority-model.md`); indexes and status mirrors (`decision-index.md`, `spec-catalog.md`) | Non-owning. Pointer-only or status-only. |

`.specify/governance/_meta/authority-model.md` (model-version **3.0.0**) is a conceptual and pointer anchor only. It is **not** an ownership map and must not be used to resolve authority ownership without the canonical map.

Meta-layer cleanup has removed parallel authority maps from tiered and meta documents. Ongoing governance changes **must** pass the **MANDATORY** `Authority Drift Prevention` section in `.specify/governance/review-checklist.md`.

### Final control question (verified)

> If `catalog-decisions.md` disappeared, could any other document determine who owns authority?

**Verified answer: NO.**

Without this document § `## Governance Decision Authority Map`, ownership cannot be resolved; enforcement and operational documents must HALT.

---

## Decision Index

| ID | Title | Status | Date | Closes |
| -- | ----- | ------ | ---- | ------ |
| CD-009 | Dependent Entity Ownership | ACCEPTED | 2026-06-25 | CONF-DEP-01 |
| CD-010 | Approval State vs Transition Rules | ACCEPTED | 2026-06-25 | OQ-03 |
| CD-011 | Lottery Domain Centralization | ACCEPTED | 2026-06-25 | OQ-04 |
| CD-012 | Employee ↔ Identity Attachment Mechanism | ACCEPTED | 2026-06-26 | OQ-01 |
| CD-013 | Eligibility Invariant Ownership | ACCEPTED (Recorded Assumption) | 2026-06-26 | OQ-02 (Current Scope) |
| CD-014 | Allocation ↔ Occupancy Ownership Split | ACCEPTED | 2026-06-26 | OQ-05 |
| CD-015 | CheckIn/CheckOut Module Boundary | ACCEPTED | 2026-07-01 | OQ-06 |
| CD-016 | Voucher Eligibility Ownership | ACCEPTED | 2026-07-01 | OQ-07 |
| CD-017 | Reporting Projection Boundary | ACCEPTED | 2026-07-01 | OQ-08 |

---

## Open Boundary Questions (Post-Closure Audit)

| ID | Question | Priority | Next Action |
| -- | -------- | -------- | ----------- |
| OQ-06 | Is check-in / check-out inside Allocation, or a separate context? | Medium | CLOSED by CD-015 |
| OQ-07 | Where does voucher eligibility ownership live? | Medium | CLOSED by CD-016 |
| OQ-08 | What is the reporting projection boundary / read-model scope? | Medium | CLOSED by CD-017 |

---

## Risk Register (Cross-Boundary)

| Risk ID | Description | Likelihood | Impact | Mitigation |
| ------- | ----------- | ---------- | ------ | ---------- |
| R-012-01 | Identity deletion leaves orphaned Employee records | Low | Medium | Defer to `spec03`: soft delete, event notification, or validation on Employee creation |
| R-013-01 | Employee service downtime blocks Request submission | Medium | High | Defer to `spec05`: circuit breaker, cached eligibility, or async validation |
| R-013-02 | Future eligibility reuse invalidates CD-013 | Low | High | Recorded assumption: reopen OQ-02 if evidence appears |
| R-014-01 | Allocation unavailability blocks occupancy transitions | Medium | Medium | Defer to `spec07` / `spec04`: event-driven reconciliation and idempotent handlers |

---

## Evidence Summary

| ID | Type | Primary Evidence For | Primary Evidence Against | Confidence | Status |
| -- | ---- | ------------------ | ------------------------ | ---------- | ------ |
| CONF-DEP-01 | Conflict | `dormsys-architecture.md:78` → Employee | `CONSTITUTION v1.3.0.md:642` → Request table | High | RESOLVED (CD-009) |
| OQ-01 | Open Question | `dormsys-architecture.md:324` → immutable ID, no FK | Exact sync mechanism unspecified | Medium | CLOSED (CD-012) |
| OQ-02 | Open Question | `hist03.md:2431` → logic in Employee; `CONSTITUTION:521` → BR-01 under Request | No explicit invariant owner named | Medium | CLOSED — Current Scope (CD-013) |
| OQ-03 | Open Question | `dormsys-architecture.md:80` → RequestApproval in Request; `:81,377` → Workflow engine | — | High | CLOSED (CD-010) |
| OQ-04 | Open Question | `dormsys-architecture.md:83`; `CONSTITUTION:356` → Lottery lifecycle | — | High | CLOSED (CD-011) |
| OQ-05 | Open Question | `dormsys-architecture.md:81,86` → Allocation owns assignment; `:80-83` → Dormitory + CheckIn own physical/operational | — | High | CLOSED (CD-014) |
| OQ-06 | Open Question | `dormsys-architecture.md:79-84`; `system-flow.md:180-182` → CheckIn/CheckOut operational transitions separated from assignment authority | CD-014 did not originally promote CheckIn to active inventory | Medium | CLOSED (CD-015) |
| OQ-07 | Open Question | `spec08` planning evidence; Voucher issuance lifecycle belongs to Voucher domain rules | Eligibility trigger may originate upstream, but ownership was unresolved | Medium | CLOSED (CD-016) |
| OQ-08 | Open Question | Constitution constraints + context map reporting rule → Reporting is read-only cross-boundary projection consumer | Projection/read-model boundary was unspecified | Medium | CLOSED (CD-017) |

---

## CD-009 — Dependent Entity Ownership

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-25 |
| **Type** | Conflict Resolution |
| **Status** | ACCEPTED |
| **Closes** | CONF-DEP-01 |
| **Related** | `CONSTITUTION v1.3.0.md:435,642`, `dormsys-architecture.md:78` |

### Context

Internal conflict in source documents:

- **Constitution §I.2 / line 435:** defines `Dependent ∈ Employee`
- **Constitution line 642:** states "Request owns Dependents table"
- **Architecture:** consistently places Dependent within Employee boundary

This affects aggregate root design, service boundaries, and data lifecycle.

### Evidence

| Supports Employee ownership | Supports Request ownership |
| --------------------------- | -------------------------- |
| `CONSTITUTION:435` — entity definition | `CONSTITUTION:642` — table assignment |
| `dormsys-architecture.md:78` — Employee aggregate includes Dependent | `Discovery:379` — `request_id` on Dependent (linkage only) |
| `system-flow.md:78,351` — Dependent in Employee tree | Weak: linkage ≠ aggregate ownership |
| Lifecycle: Dependent created/modified with Employee context | |

### Decision

**`Dependent ∈ Employee` (Option A).**

Request retains **snapshots or references** to Dependent data at submission time; it does not own the Dependent aggregate.

### Rationale

1. Architectural documents consistently place Dependent in Employee.
2. Dependent lifecycle is tied to Employee, not Request.
3. `request_id` on Dependent is a reference link, not ownership.
4. Conflict originated from inconsistent Constitution wording, not genuine ambiguity.

### Impact

- **Constitution §11** aligned — Employee owns `employee_dependents`; Request holds snapshots/references only (governance pass PR #2, 2026-06-26).
- Employee BC owns Dependent CRUD.
- Request BC consumes Dependent data via Application Service or Event (read-only or snapshot).

### What Was NOT Decided

- Snapshot format on Request submission.
- Handling when Dependent is modified after request submission.

---

## CD-010 — Approval State vs Transition Rules

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-25 |
| **Type** | Domain Boundary |
| **Status** | ACCEPTED |
| **Closes** | OQ-03 |
| **Related** | `dormsys-architecture.md:80-81,377`, `CONSTITUTION v1.3.0.md:81,101,356` |

### Context

Approval spans Request data and a reusable workflow engine. Source documents split entity storage from process orchestration.

### Evidence

- `dormsys-architecture.md:80` — `Request (Request, RequestApproval)`
- `dormsys-architecture.md:81,377` — `Workflow (Approval Engine)` orchestrates multi-stage chains
- `Discovery:66` — Dept Mgr → HR → Dorm Mgr → Dorm Unit Mgr chain
- `CONSTITUTION:356` — approval as state transition mechanism

### Decision

**Split ownership:**

| Context | Owns |
| ------- | ---- |
| **Request** | `RequestApproval` entity, approval **state**, history |
| **Workflow** (deferred module) | Approval **transition rules**, chain definition, routing, orchestration |

Integration pattern:

- Request emits approval state changes as Domain Events.
- Workflow subscribes and triggers next approval steps when activated.
- Final approval is delivered back to Request via Domain Event.

### Rationale

- Approval records stay with the request aggregate (data locality).
- Workflow engine can serve multiple domains when activated.
- Single responsibility: Request manages state; Workflow manages process rules.

### Impact

- `RequestApproval` table remains in Request schema.
- Workflow module stays **deferred** per spec catalog until activation criteria are met.
- Boundary applies immediately to modeling; implementation follows module activation.

### What Was NOT Decided

- Auto-approval configuration storage location.
- Workflow module activation timeline.

---

## CD-011 — Lottery Domain Centralization

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-25 |
| **Type** | Domain Boundary |
| **Status** | ACCEPTED |
| **Closes** | OQ-04 |
| **Related** | `dormsys-architecture.md:83,393`, `system-flow.md:253`, `CONSTITUTION v1.3.0.md:356,370` |

### Context

Lottery rules, programs, registrations, and results could have been split across Request, Lottery, and Allocation.

### Evidence

- `dormsys-architecture.md:83` — `Lottery (LotteryProgram, Registration, Result)`
- `system-flow.md:253,365-367` — Lottery state machine with full lifecycle
- `Discovery:11,29,38` — lottery as core allocation mechanism
- `CONSTITUTION:356,370` — lottery rules and auditable execution in dedicated module

### Decision

**Lottery BC owns all lottery-related concerns:**

- `LotteryProgram` (rules, criteria, schedules)
- `LotteryRegistration` (participant enrollment)
- `LotteryResult` (outcome records)
- Scoring, eligibility for draw, and program lifecycle

Lottery emits **proposed allocations** to Allocation; Allocation owns assignment execution.

### Rationale

- Single source of truth for lottery logic.
- Clean separation from allocation execution.
- Audit trail integrity for draw operations.

### Impact

- Lottery has full write authority over lottery lifecycle.
- Allocation consumes lottery results as read-only input.
- Request references lottery registration status via Application Service or Event.

### What Was NOT Decided

- Exact event contract between Lottery and Allocation.
- Whether Request-level eligibility gates lottery registration or Lottery re-validates.

---

## CD-012 — Employee ↔ Identity Attachment Mechanism

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-26 |
| **Type** | Boundary Decision |
| **Status** | ACCEPTED |
| **Closes** | OQ-01 |
| **Related** | `dormsys-architecture.md:324`, `CONSTITUTION v1.3.0.md:640-641`, `context-map.md` R1 |

### Context

Identity and Employee are separate bounded contexts. The attachment mechanism between them was unspecified.

### Evidence

| Source | Location | Statement |
| ------ | -------- | --------- |
| `dormsys-architecture.md` | line 324 | FK forbidden across modules; use Immutable Identifier |
| `CONSTITUTION v1.3.0.md` | §I.2 | Identity owns Users/Roles/Permissions; Employee owns Employees/Departments |
| `Discovery` | lines 151-152 | `/identity` and `/employee` are separate paths |

### Evidence NOT Found

- No permission for FK or shared table between contexts.
- No mandated event/sync attachment pattern.
- No shared lifecycle between Employee and Identity.

### Options Considered

| Option | Verdict |
| ------ | ------- |
| A) Immutable UUID linkage, no FK | **CHOSEN** |
| B) Domain Event attachment | Not chosen — no evidence requiring eventual consistency at this stage |
| C) Shared table | Rejected — violates Constitution §I.2 |

### Decision

Employee attaches to Identity via an **immutable UUID reference** (`identity_id`), without FK or shared table.

**Domain invariant:** `identity_id` is assigned exactly once at Employee creation. Subsequent modification is prohibited by the domain model.

### Coupling Analysis

| Dimension | Assessment |
| --------- | ---------- |
| Coupling type | Data reference (unidirectional) |
| Direction | Employee → Identity |
| Autonomy impact | Low |
| Circular risk | None |
| DDD pattern | Customer-Supplier (Employee downstream of Identity) |

### Impact

- No referential-integrity coupling at the database layer.
- Cross-context Identity data resolved by identifier, not join.
- Implementation deferred to `spec02` and `spec03`.

### What Was NOT Decided

- Validation when referenced Identity is deleted or deactivated.
- Projection/query pattern for Identity lookups.
- Caching strategy.

---

## CD-013 — Eligibility Invariant Ownership

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-26 |
| **Type** | Boundary Decision |
| **Status** | ACCEPTED (Recorded Assumption) |
| **Closes** | OQ-02 (Current Scope) |
| **Related** | `CONSTITUTION v1.3.0.md:521-524`, `hist03.md:2431`, `context-map.md` R2 |

### Context

BR-01 ("Request Eligibility") requires active employee and no active allocation. Data lives in Employee; the rule is titled under Request.

### Evidence

| Source | Statement |
| ------ | --------- |
| `CONSTITUTION:521-524` | BR-01: active employee + no active allocation |
| `hist03:2431` | Eligibility logic lives in Employee; Request consumes it |
| `hist03:1724` | Final ownership was open |

### Options Considered

| Option | Verdict |
| ------ | ------- |
| A) Request enforces; Employee answers queries | **CHOSEN** |
| B) Standalone Eligibility context | Not chosen — no second consumer; premature per Playbook §VIII |
| C) Eligibility fully inside Employee | Not chosen — BR-01 defined under Request in Constitution |

### Decision

| Context | Responsibility |
| ------- | -------------- |
| **Request** | Owns **enforcement** of eligibility invariant at submission |
| **Employee** | Owns **computation** of eligibility (data + logic) |

### Recorded Assumption

If eligibility is consumed elsewhere or gains an independent lifecycle, **OQ-02 must be reopened**.

This decision governs ownership only — not whether validation uses Application Service, Domain Service, Policy, or Specification.

### Coupling Analysis

| Dimension | Assessment |
| --------- | ---------- |
| Coupling type | Query/computation dependency (unidirectional) |
| Direction | Request → Employee |
| Autonomy impact | Medium |
| Circular risk | Low |
| DDD pattern | Customer-Supplier (Request downstream of Employee) |
| Failure mode | Employee unavailable → Request submission blocked |

### Impact

- Coupling: medium. Duplication: low. Testability: high.
- Implementation deferred to `spec03` and `spec05`.

### What Was NOT Decided

- Exact query mechanism (sync API vs specification).
- Error handling on eligibility-query failure.
- Caching strategy.

---

## CD-014 — Allocation ↔ Occupancy Ownership Split

| Field | Value |
| ----- | ----- |
| **Date** | 2026-06-26 |
| **Type** | Domain Boundary |
| **Status** | ACCEPTED |
| **Closes** | OQ-05 |
| **Related** | `dormsys-architecture.md:79-84`, `system-flow.md:180-182`, `context-map.md` R7 |
| **Follow-up** | OQ-06 later resolved by CD-015 (CheckIn/CheckOut Module Boundary) |


### Context

"Occupancy" spans assignment authority, physical bed state, and operational check-in/out transitions. Source documents split these across modules.

### Evidence

- `dormsys-architecture.md:79-84` — `Dormitory`, `Allocation`, `CheckIn` as separate modules
- `Discovery:55` — occupancy updates after lottery allocation (Allocation drives)
- `system-flow.md:180-182` — `WaitingForAllocation → Allocated → CheckedIn → CheckedOut`
- `Discovery:21` — external dormitories: no physical occupancy monitoring

### Options Considered

| Option | Verdict |
| ------ | ------- |
| A) Unified Allocation + Occupancy context | Rejected — conflates assignment with physical/operational state |
| B) Split with Allocation as driver | **CHOSEN** |
| C) Standalone Occupancy context | Deferred — OQ-06 (CheckIn/CheckOut module boundary) unresolved at decision time; resolved later by CD-015 |


### Decision

Ownership is **split**; Allocation is the upstream driver:

| Context | Owns |
| ------- | ---- |
| **Allocation** | Assignment authority (`Allocation`, `AllocationItem`) — who is assigned to what |
| **Dormitory** | Physical occupancy state (`Room`, `Bed` capacity/availability) |
| **CheckIn/CheckOut** | Operational transitions (`CheckedIn`, `CheckedOut`) |

**Invariant:** Effective bed occupancy is derived from active Allocation + CheckIn/CheckOut state — not stored authoritatively in one place.

### Impact

- Allocation publishes assignment events.
- Dormitory updates physical availability.
- CheckIn/CheckOut consumes assignment to enable transitions.
- Coupling: `Allocation → Dormitory` (R7), unidirectional.
- CheckIn/CheckOut module promotion resolved in CD-015.


### Recorded Assumption

If occupancy later requires a unified, independently-owned lifecycle (e.g., bed blocking independent of allocation), **OQ-05 should be reopened**.

### What Was NOT Decided

- Independent occupancy lifecycle beyond current CheckIn/CheckOut operational boundary.

- Reconciliation when Allocation and Dormitory state diverge.

---

## Boundary Review Session — CD-012 & CD-013

**Date:** 1405/04/05 | 2026/06/26  
**Scope:** Cross-boundary coupling for Identity-Employee attachment and Request-Employee eligibility

### Cross-Boundary Dependency Chain

```
Identity
   ↓ immutable UUID (CD-012)
Employee
   ↓ eligibility query (CD-013)
Request
```

- No circular dependencies detected.
- Each context owns its aggregates and invariants.
- Coupling direction follows standard DDD Customer-Supplier pattern.

### Session Outcomes

| Decision | Verdict |
| -------- | ------- |
| CD-012 | ACCEPTED — low risk |
| CD-013 | ACCEPTED — medium risk, mitigated by Recorded Assumption |
| Implementation details | Deferred to `spec02`, `spec03`, `spec05` |

**Next review trigger:** Evidence of eligibility reuse outside Request, or independent eligibility lifecycle.

---

## Evidence Traceability Index

### By Source Document

**CONSTITUTION v1.3.0.md**

- Lines 435, 641-642 → CONF-DEP-01, CD-009
- Lines 640-641 → OQ-01, CD-012
- Lines 521-524 → OQ-02, CD-013
- Lines 81, 101, 356, 370, 422 → OQ-03, OQ-04, OQ-05, CD-010, CD-011

**dormsys-architecture.md**

- Line 78 → CONF-DEP-01, CD-009
- Lines 77-78, 324 → OQ-01, CD-012
- Lines 80-81, 377, 379 → OQ-03, CD-010
- Lines 83, 86, 393 → OQ-04, CD-011
- Lines 79-84 → OQ-05, OQ-06, CD-014, CD-015

**hist03.md**

- Lines 3362, 2456 → CONF-DEP-01, CD-009
- Lines 2431, 1724 → OQ-02, CD-013

**DormSys Discovery Document.md**

- Lines 379-380 → CONF-DEP-01, CD-009
- Lines 151-152 → OQ-01, CD-012
- Lines 37, 188 → OQ-02, CD-013
- Line 66 → OQ-03, CD-010
- Lines 11, 29, 38, 50 → OQ-04, CD-011
- Lines 21, 29, 36, 38, 55, 134 → OQ-05, CD-014

**system-flow.md**

- Lines 78, 351 → CONF-DEP-01, CD-009
- Lines 253, 365-367 → OQ-04, CD-011
- Lines 180-182, 204, 205, 297, 463 → OQ-05, OQ-06, CD-014, CD-015

**context-map.md**

- R1 → OQ-01, CD-012
- R2 → OQ-02, CD-013
- R3 → OQ-03, CD-010
- R4 → OQ-04, CD-011
- R7 → OQ-05, OQ-06, CD-014, CD-015
- R8 → OQ-07, CD-016
- R11 → OQ-08, CD-017

---

## Change Log

### 2.8.3 — 2026-07-11 (spec03 US3 Implementation Authorization create)

- Lifecycle operation only — no boundary or CD-* changes.
- Created / activated Spec03 US3 Implementation Authorization: `.specify/docs/handoff/spec03-implementation-authorization-us3.md` (`authorization-status: active`; `authorized-scope` T035–T040 verbatim).
- Actor: Governance Review. Checkpoint: `spec03-us3-implementation-authorization`.
- Supersedes US3 hold in `spec03-post-mvp-authorization.md` for T035–T040 only; US4 / Request stub replacement / Spec04–Spec07 reopen remain unauthorized.
- CD-009 ownership unchanged (`Dependent ∈ Employee`).

### 2.8.2 — 2026-07-01 (spec07 implementation program closure)

- Lifecycle alignment only — no boundary or decision changes.
- spec07 program closed (T001–T074); Wave 1B authorization record `revoked` (program closure); Wave 1A `superseded`.
- Active execution scope for spec07: **none**. Checkpoint: `spec07-implementation-closure`.

### 2.8.1 — 2026-07-01 (documentation synchronization remediation)

- Updated Evidence Traceability Index for CD-015, CD-016, CD-017 and OQ-06, OQ-07, OQ-08.
- Normalized date metadata format (`2026/07/01`).
- Editorial only — no decision or boundary changes.

### 2.8.0 — 2026-07-01

- Added CD-015 to close OQ-06 and promote CheckIn/CheckOut to an active boundary.
- Added CD-016 to close OQ-07 and assign Voucher ownership for eligibility and issuance lifecycle.
- Added CD-017 to close OQ-08 and confirm Reporting as a read-only cross-domain projection consumer.
- Updated Open Boundary Questions and Evidence Summary accordingly.
- Updated CD-014 follow-up references after OQ-06 closure.

### 2.7.0 — 2026/06/24

- Under `## Governance Decision Authority Map`: added **Operational authority map scope (strict)**; **Case C — governance precondition classification (non-operational)**; **Nomination Record boundary (non-operational dependency)**. No new map rows; three operational authority types unchanged. Informational cross-references to `execution-policy.md` v1.4.0 and `governance-enforcer.md` v1.3.0+ only.

### 2.6.0 — 2026/06/23

- Added **Governance state / snapshot artifacts** descriptive subsection under `## Governance Decision Authority Map`; evidence-only, non-authoritative; not a decision class.

### 2.5.0 — 2026/06/23

- Added **Governance Transition (state — not an authority owner)** descriptive note under `## Governance Decision Authority Map`; no new decision class or owner row.

### 2.4.0 — 2026-06-26

- Added **Authority Drift Resolution** closure section; recorded verified final control question (NO).

### 2.3.0 — 2026-06-26

- Added **Governance Decision Authority Map** as the single canonical authority ownership register for Design Approval, Implementation Authorization, and Batch Execution Permission.

### 2.2.0 — 2026-06-26

- Full document rewrite for consistent structure and formatting.
- Fixed contradictory "Open Items" listing OQ-05 after CD-014 closed it.
- Removed broken markdown fences and duplicate sections.
- Moved CD-014 into canonical decision order.
- Aligned `context-map.md` cross-references.

### 2.1.0 — 2026-06-26

- Added CD-012, CD-013, CD-014.
- Conducted boundary review session for CD-012 and CD-013.
- Closed OQ-01, OQ-02, OQ-05.

### 2.0.0 — 2026-06-25

- Resolved CONF-DEP-01 via CD-009.
- Resolved OQ-03 via CD-010.
- Resolved OQ-04 via CD-011.

### 1.0.0 — 2026-06-24

- Initial evidence mapping and conflict documentation.


## CD-015 — CheckIn/CheckOut Module Boundary

| Field | Value |
| ----- | ----- |
| **Date** | 2026-07-01 |
| **Type** | Domain Boundary |
| **Status** | ACCEPTED |
| **Closes** | OQ-06 |
| **Related** | `spec07`, `context-map.md` R7 |

### Context

The Allocation ↔ Occupancy split in CD-014 separated assignment authority from physical and operational occupancy concerns, but it did not formally decide whether CheckIn/CheckOut should remain implicit or be promoted to an active bounded context.

### Evidence

- `dormsys-architecture.md:79-84` distinguishes `Dormitory`, `Allocation`, and `CheckIn` as separate modules.
- `system-flow.md:180-182` shows operational transitions `Allocated → CheckedIn → CheckedOut`.
- `catalog-decisions.md` CD-014 already separated assignment authority from operational transitions.

### Options Considered

| Option | Verdict |
| ------ | ------- |
| A) Keep CheckIn/CheckOut inside Allocation | Rejected — assignment authority and operational transitions are different responsibilities |
| B) Keep CheckIn/CheckOut inside Dormitory | Rejected — Dormitory owns physical state, not operational lifecycle transitions |
| C) Promote CheckIn/CheckOut to an active bounded context | **CHOSEN** |

### Decision

CheckIn/CheckOut is promoted to a valid active boundary.

Ownership is defined as follows:

| Context | Responsibility |
| ------- | -------------- |
| **Allocation** | Assignment authority |
| **Dormitory** | Physical room/bed capacity and availability |
| **CheckIn/CheckOut** | Operational occupancy transitions (`CheckedIn`, `CheckedOut`) |

### Rationale

- Operational transition lifecycle is distinct from assignment authority.
- Physical capacity ownership must remain in Dormitory.
- Existing source architecture already distinguishes CheckIn as a separate module.

### Impact

- `spec07` must treat CheckIn/CheckOut as an active boundary dependency, not an implicit sub-flow.
- `context-map.md` active inventory must include CheckIn/CheckOut.
- Cross-boundary interactions must respect Application Service / Domain Event constraints.

### What Was NOT Decided

- Detailed event contract between Allocation, Dormitory, and CheckIn/CheckOut.
- Reconciliation strategy for state divergence.

---

## CD-016 — Voucher Eligibility Ownership

| Field | Value |
| ----- | ----- |
| **Date** | 2026-07-01 |
| **Type** | Domain Boundary |
| **Status** | ACCEPTED |
| **Closes** | OQ-07 |
| **Related** | `spec08`, `context-map.md` R8 |

### Context

Voucher issuance may be triggered by upstream processes such as Lottery or Allocation outcomes, but ownership of voucher eligibility and issuance lifecycle required explicit boundary assignment.

### Evidence

- `context-map.md` identifies Voucher as a distinct context under `spec08`.
- Upstream processes may trigger Voucher flows, but no other context is defined as voucher lifecycle owner.
- Constitution constraints forbid cross-context ownership ambiguity.

### Options Considered

| Option | Verdict |
| ------ | ------- |
| A) Lottery owns voucher eligibility and issuance | Rejected — Lottery may trigger downstream action but does not own Voucher lifecycle |
| B) Allocation owns voucher eligibility and issuance | Rejected — Allocation owns assignment authority, not external accommodation issuance |
| C) Voucher owns voucher eligibility and issuance lifecycle | **CHOSEN** |

### Decision

Voucher owns voucher eligibility evaluation within its domain rules and owns the issuance lifecycle.

Upstream contexts may provide triggering facts or eligibility inputs, but Voucher is the final authority for voucher issuance decisions.

### Rationale

- Issuance lifecycle must have a single owner.
- Triggering a voucher is not the same as owning voucher policy.
- This keeps Voucher autonomous and prevents ownership leakage from Lottery or Allocation.

### Impact

- `spec08` must model voucher issuance rules inside Voucher.
- Upstream modules provide facts/events only.
- `context-map.md` must reflect Voucher ownership explicitly.

### What Was NOT Decided

- Exact input contract from Lottery / Allocation into Voucher.
- Whether Voucher eligibility logic will later be shared elsewhere.

---

## CD-017 — Reporting Projection Boundary

| Field | Value |
| ----- | ----- |
| **Date** | 2026-07-01 |
| **Type** | Domain Boundary |
| **Status** | ACCEPTED |
| **Closes** | OQ-08 |
| **Related** | `spec11`, `context-map.md` R11 |

### Context

Reporting requires cross-context read access, but the projection boundary and read-model scope were not explicitly closed.

### Evidence

- `context-map.md` states Reporting is a downstream read-only context.
- Constitution constraints explicitly allow Reporting as the only cross-boundary read consumer and forbid writes.
- No evidence supports Reporting owning domain write authority.

### Options Considered

| Option | Verdict |
| ------ | ------- |
| A) Reporting owns derived business state | Rejected — violates read-only reporting constraint |
| B) Reporting acts as read-only projection consumer across contexts | **CHOSEN** |
| C) Reporting shares ownership with source contexts | Rejected — creates ownership ambiguity |

### Decision

Reporting remains a cross-domain read-only consumer.

Its scope is limited to projections and read models derived from source contexts. Reporting has no write authority and no ownership over upstream domain lifecycle rules.

### Rationale

- Reporting is explicitly the only allowed cross-boundary read context.
- Projection ownership is not domain ownership.
- Read-model scope must remain downstream and non-authoritative.

### Impact

- `spec11` must be implemented strictly as read-only projection/reporting behavior.
- No reporting workflow may mutate source domain state.
- `context-map.md` must reflect Reporting as read-only cross-domain consumer.

### What Was NOT Decided

- Exact reporting model structure.
- Refresh/update mechanism for projections.
