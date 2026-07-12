# Feature Specification: Audit Trail & Traceability (spec10)

**Feature Branch**: `010-audit-trail`

**Created**: 2026-07-02

**Status**: **CLOSED / FROZEN** — T001–T040 program closed (`immutable_status: FROZEN`; reopen forbidden without new governance). OA-10-05 / Audit UI remains a **separate** work-item closeout (`AUDIT_UI_CLOSED`); not Spec10 baseline reopen or unfreeze.

**Catalog**: spec10 — Audit (`spec-catalog.md`)

**Depends on**: spec01 Foundation; spec02 Identity & Access (actor identity and authorization for audit review)

**Consumes audit entries from** (downstream only — **R10**): all implemented bounded contexts performing critical operations — including spec02 Identity; spec03 Employee; spec05 Request; spec06 Lottery; spec07 Allocation & CheckIn/CheckOut; spec08 Voucher; spec09 Notification (delivery actions may be auditable)

**Input**: Establish the **Audit** cross-cutting capability: a centralized, **immutable**, **append-only** audit trail for critical DormSys operations — enabling accountability, compliance review, and forensic reconstruction of lifecycle outcomes — without owning upstream domain lifecycle decisions.

**Normative boundaries**: [`../../.specify/docs/context-map.md`](../../.specify/docs/context-map.md) **R10**; constitution **AP-06** (Audit Everything); Definition of Done (all state transitions emit audit entries via **AuditService**).

**Governance**: Specification + planning baseline. **Not** Design Approval · **Not** Implementation Authorization. Nomination: [`spec10-nomination-record.md`](../../.specify/docs/handoff/spec10-nomination-record.md) (**active**). spec09 remains **CLOSED** ([`spec09-implementation-closure.md`](../../.specify/docs/handoff/spec09-implementation-closure.md)).

---

## Purpose

DormSys manages sensitive accommodation lifecycle operations — request approvals, lottery execution, allocations, check-in/out, voucher issuance, and permission changes. Operators, auditors, and governance stakeholders must be able to answer **who did what, to which entity, when, and with what before/after context** — without relying on mutable operational tables or scattered per-module logs.

Audit provides a **shared traceability layer** that persists immutable audit records for critical operations. Upstream bounded contexts remain authoritative for **business outcomes** and **state machines**. They supply **audit entry intents** (or invoke a central audit facade) when material transitions occur. Audit owns **audit record persistence**, **immutability guarantees**, **standardized audit vocabulary**, and **authorized read access** for compliance review — not request rules, allocation policy, or notification delivery.

This specification defines problem scope, users, constraints, functional requirements, and acceptance-oriented outcomes suitable for subsequent planning phases. It does **not** authorize implementation.

---

## Scope

| In scope | Out of scope (see § Out of Scope) |
| -------- | ----------------------------------- |
| Central **AuditService** facade for recording critical operations | Upstream domain lifecycle decisions and policy ownership |
| **Append-only** audit log persistence (no UPDATE/DELETE on audit store) | Notification delivery and inbox state (**spec09**) |
| Standard audit record fields per constitution AP-06 | Reporting projections and management dashboards (**spec11**) |
| Consumption of cross-boundary **audit entry** payloads via integration contract (**R10**) | Direct upstream repository reads to infer audit facts |
| Actor attribution (user or system identifier) | Workflow engine orchestration (**deferred**) |
| Entity subject reference (type + identifier) | Reopening or modifying closed programs (spec07, spec08, spec09) |
| Event/action vocabulary for critical operations | Presentation UI implementation detail (deferred follow-on) |
| Before/after state snapshots and contextual metadata where supplied | Email/SMS alerting on audit events |
| Authorized query of audit history by entity, actor, event category, and time range | Cross-domain analytics beyond audit log read (Reporting — spec11) |
| Migration path from interim **`RecordsActivity`** usage in implemented modules | Real-time external SIEM integration (deferred) |
| Persian (Farsi) labels for user-facing audit review where presentation is built | |

**Program note:** Audit is a **downstream consumer and central writer** for audit entries. Upstream modules must not write directly to audit tables. Audit must not mutate upstream domain state when recording or querying entries.

---

## Governing Decisions

### R10 — Audit ← all critical operations

| Field | Value |
| ----- | ----- |
| Direction | Downstream (records critical operations from many contexts) |
| Integration | Domain Event, application hook, or **AuditService** contract — **direct upstream repository access forbidden** for audit fact discovery |
| Ownership | Audit owns **audit log persistence** and **immutability**; upstream contexts own **when** a material transition occurred and supply transition facts |
| Implementation | Deferred to spec10 (this specification) |

### Technical vs domain audit semantics (spec-catalog provisional question)

| Layer | Owner | Responsibility |
| ----- | ----- | -------------- |
| **Domain transition** | Source bounded context | Commit lifecycle outcome; emit audit entry with domain-meaningful event name and snapshots |
| **Audit persistence** | Audit (spec10) | Validate, normalize, persist immutable record; enforce append-only store |
| **Presentation** | Deferred follow-on | Livewire audit explorer, export triggers — not fixed at specification level |

**Recorded assumption (OA-10-01):** DormSys requires **domain-aware audit semantics** (typed events, entity references, state snapshots for critical transitions) centralized through **AuditService**, not scattered technical-only CRUD logs. Upstream modules may continue using interim `RecordsActivity` during migration, but the target state is uniform emission through AuditService for all critical operations listed in AP-06.

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Critical Operation Recorded (Priority: P1)

As a compliance stakeholder, I need every critical accommodation lifecycle transition to produce an immutable audit record so that accountability can be verified after the fact.

**Why this priority**: Constitution AP-06 and Definition of Done require audit on all state transitions; without centralized recording, traceability is fragmented across interim model activity logs.

**Independent Test**: Simulate a material state transition (e.g., request approval) by supplying an audit entry payload and confirm a persisted audit record exists with actor, entity reference, event type, timestamps, and supplied snapshots — without Audit mutating upstream state.

**Acceptance Scenarios**:

1. **Given** a request submission outcome, **When** the Request context records an audit entry, **Then** an immutable audit record is stored with entity reference, actor, and event indicating submission
2. **Given** an approval decision with reason, **When** recorded, **Then** the audit entry includes the reason in metadata and before/after state snapshots when supplied
3. **Given** a lottery execution outcome, **When** recorded, **Then** the audit entry includes execution context metadata (e.g., program reference, seed reference) as supplied by upstream
4. **Given** an allocation creation or cancellation, **When** recorded, **Then** the audit entry identifies the affected employee/allocation subject and actor
5. **Given** a check-in or check-out transition, **When** recorded, **Then** the audit entry captures operational transition facts without Allocation assignment authority leakage
6. **Given** a voucher lifecycle transition, **When** recorded, **Then** the audit entry references voucher subject and transition type
7. **Given** a role or permission change, **When** recorded, **Then** the audit entry identifies affected user/role and actor performing the change

---

### User Story 2 - Authorized Audit History Review (Priority: P1)

As an authorized auditor or dormitory manager, I need to query audit history for a specific entity or time period so that I can investigate incidents and demonstrate compliance.

**Why this priority**: Immutable storage alone does not deliver value without governed read access for investigation and oversight.

**Independent Test**: Persist multiple audit entries for different entities and actors, query by entity identifier and date range as an authorized role, and confirm only permitted entries are returned in reverse chronological order.

**Acceptance Scenarios**:

1. **Given** audit records for a request entity, **When** an authorized user queries by entity type and identifier, **Then** all matching records for that subject are returned
2. **Given** audit records across entities, **When** an authorized user queries by actor and date range, **Then** only records matching filters are returned
3. **Given** an unauthorized user, **When** they attempt audit history access, **Then** access is denied
4. **Given** an empty result set, **When** queried, **Then** an empty list is returned without error
5. **Given** a persisted audit record, **When** any party attempts to modify or delete it through application interfaces, **Then** the operation is rejected

---

### User Story 3 - Uniform Upstream Emission Contract (Priority: P2)

As a module maintainer, I need a single, stable way to emit audit entries for critical operations so that all bounded contexts record traceability consistently without writing to audit tables directly.

**Why this priority**: Multiple implemented modules currently use interim `RecordsActivity`; spec10 must converge on AuditService without breaking existing modules.

**Independent Test**: Invoke the audit emission contract from a test double representing an upstream module and confirm the entry is persisted with required fields; repeat invocation with identical correlation identifier and confirm idempotent or duplicate-safe behavior per planning decision.

**Acceptance Scenarios**:

1. **Given** a valid audit entry payload from an upstream module, **When** emitted through the central audit contract, **Then** a record is persisted satisfying AP-06 field requirements
2. **Given** a payload missing required fields (entity reference, event, actor), **When** emission is attempted, **Then** the entry is rejected with a diagnosable error without partial persistence
3. **Given** interim `RecordsActivity` on a module model, **When** migration adapter is active (per planning), **Then** critical transitions still produce queryable audit history without duplicate contradictory records for the same correlation identifier

---

### User Story 4 - System and Background Actor Attribution (Priority: P2)

As an auditor, I need background jobs and scheduled processes to record audit entries with a clear system actor identity so that automated actions remain traceable.

**Why this priority**: Lottery execution, reserve promotion, and archival jobs perform material outcomes without a human click.

**Independent Test**: Record an audit entry with system actor identifier for a job-triggered transition and confirm it is distinguishable from human-initiated entries in query results.

**Acceptance Scenarios**:

1. **Given** a lottery draw executed by background processing, **When** recorded, **Then** actor is identified as system/job context while entity references remain accurate
2. **Given** a human-initiated action, **When** recorded, **Then** actor references the authenticated user identity

---

### Edge Cases

- What happens when upstream emits an audit entry before the domain transaction commits? **Expected:** Audit persistence must not create orphan records that contradict rolled-back domain state (planning must define transactional boundary).
- How does the system handle duplicate audit emission for the same correlation identifier? **Expected:** Duplicate-safe behavior defined in planning (reject or idempotent accept) — no contradictory twin records.
- What happens when snapshot payloads exceed reasonable size? **Expected:** Reject or truncate per documented policy with metadata flag — never silent loss of actor/entity/event fields.
- How are audit queries handled under high volume? **Expected:** Paginated results; performance targets defined in success criteria.
- What happens when actor identity is unavailable (legacy job)? **Expected:** System actor fallback with explicit metadata — never null actor for critical operations.

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a central **AuditService** (application facade) as the **only** supported write path for audit records
- **FR-002**: System MUST persist audit records in an **append-only** store; UPDATE and DELETE of audit records MUST be prohibited at application and migration policy level
- **FR-003**: System MUST capture, at minimum, for each critical audit entry: entity type, entity identifier, event/action type, actor identifier, UTC timestamp
- **FR-004**: System MUST support optional **old_values** and **new_values** state snapshots when supplied by upstream contexts
- **FR-005**: System MUST support optional **metadata** for contextual fields (e.g., rejection reason, lottery seed reference, override justification)
- **FR-006**: System MUST record audit entries for all **AP-06** critical categories: request submissions and state changes; approval/rejection decisions; lottery program lifecycle and execution; allocation create/modify/cancel; check-in and check-out; room/bed status changes; role and permission changes; reserve promotions
- **FR-007**: System MUST accept audit entries from upstream bounded contexts via a **cross-boundary contract** without Audit reading upstream operational repositories (**R10**)
- **FR-008**: System MUST attribute each entry to an **actor** — authenticated user identifier or documented system/job identifier
- **FR-009**: System MUST enforce **authorization** on audit history queries — only roles permitted by policy may read audit data
- **FR-010**: System MUST support querying audit history by **entity reference**, **actor**, **event category**, and **time range** with pagination
- **FR-011**: System MUST ensure audit recording does **not mutate** upstream domain lifecycle state
- **FR-012**: System MUST define a **stable event vocabulary** for critical operations sufficient to support compliance review (exact identifier set deferred to planning)
- **FR-013**: System MUST provide a **migration strategy** from interim `RecordsActivity` usage to AuditService without loss of traceability for new critical operations post-cutover
- **FR-014**: System MUST reject audit entry payloads that omit required identity fields (entity reference, event, actor)
- **FR-015**: System MUST retain audit records according to a documented retention policy (**UD-10-03**) — retention enforcement mechanism deferred to planning

### Key Entities

- **AuditLog** — immutable audit record; subject entity type and identifier; event/action type; actor reference; optional old/new snapshots; metadata; correlation identifier for deduplication; created timestamp (UTC)
- **AuditEntry** — inbound cross-boundary payload from upstream context: subject reference, event type, actor, snapshots, metadata, correlation identifier, optional occurred-at timestamp
- **AuditEventType** — stable vocabulary entry for critical operation categories (e.g., request submitted, state changed, approved, rejected, lottery executed, allocation created, check-in recorded, voucher issued, permission changed)
- **ActorReference** — user identity UUID or system actor identifier with optional display context

### Critical Event Categories (specification vocabulary — AP-06 alignment)

| Category (conceptual) | Typical subject entity | Upstream source (facts only) |
| --------------------- | ---------------------- | ---------------------------- |
| Request lifecycle | Request | Request |
| Approval decision | Request / Approval step | Request |
| Lottery lifecycle | Lottery program / registration | Lottery |
| Lottery execution | Lottery program / result | Lottery |
| Allocation change | Allocation | Allocation |
| Check-in / Check-out | Occupancy record | CheckIn |
| Physical status change | Room / Bed | Dormitory |
| Voucher transition | Voucher | Voucher |
| Permission change | User / Role | Identity |
| Reserve promotion | Lottery result | Lottery |
| Notification delivery (optional) | Notification | Notification |

*Exact event identifiers and payload shape are deferred to planning (**UD-10-01**).*

---

## Success Criteria *(mandatory)*

- **SC-001**: 100% of critical operations listed in FR-006 produce an audit record when upstream contexts invoke the audit contract in acceptance tests
- **SC-002**: 100% of audit records persisted through the system remain immutable — no successful application-level update or delete path exists
- **SC-003**: Authorized auditors can retrieve complete history for a given entity reference within 10 seconds for up to 10,000 records in acceptance test datasets
- **SC-004**: 100% of unauthorized audit query attempts are denied without leaking record contents
- **SC-005**: 100% of audit entries include actor attribution (human or system) and UTC timestamp
- **SC-006**: After migration cutover, 100% of newly occurring critical transitions in covered modules emit through AuditService (zero new direct audit table writes from upstream modules)

---

## Assumptions

- **OA-10-01**: Audit owns **persistence and immutability**; upstream contexts own **domain transition authority** and supply audit facts after material outcomes
- **OA-10-02**: Interim **`RecordsActivity`** on Eloquent models in implemented modules is a **temporary pattern**; spec10 introduces AuditService as the constitutional target without requiring immediate removal of all activity logs in Wave 1
- **OA-10-03**: Actor identity resolves through **Identity** (spec02); subject employee/user references use immutable UUIDs per CD-012
- **OA-10-04**: Audit read access is restricted to **authorized operational and compliance roles** (e.g., Dormitory Manager, system administrator) — exact role matrix deferred to planning
- **OA-10-05**: Livewire audit explorer UI, export buttons, and PDF/Excel generation are **deferred** presentation follow-on; query contract and authorization are in scope
- **OA-10-06**: Notification delivery audit (whether each notification send is auditable) is **optional** in v1 — BR-09.1 delivery traceability may rely on notification log; explicit notification audit events may be added if planning confirms
- **OA-10-07**: Reporting module (spec11) may consume audit data read-only for compliance reports (CD-017) — Audit does not own report projections
- **OA-10-08**: spec09, spec07, and spec08 remain **closed** — integration uses ports/contracts only; no modification of closed program code except minimal adapter wiring authorized under future implementation records

### Open Planning Items

| ID | Item | Status |
| -- | ---- | ------ |
| **UD-10-01** | Cross-boundary **audit entry** payload contract and event vocabulary | **Resolved** — [plan.md](./plan.md) §3; [contracts/audit-entry-dto.md](./contracts/audit-entry-dto.md) |
| **UD-10-02** | `RecordsActivity` → AuditService **migration strategy** | **Resolved** — [plan.md](./plan.md) §2 M0–M4; [research.md](./research.md) R-05 |
| **UD-10-03** | Audit log **retention period** and archival policy | **Resolved** — 84-month soft-archive; [data-model.md](./data-model.md) § Retention |
| **UD-10-04** | Transactional boundary: audit write relative to domain commit | **Resolved** — after-commit default; [research.md](./research.md) R-02 |
| **UD-10-05** | Correlation / idempotency rules for duplicate emission | **Resolved** — [contracts/audit-entry-dto.md](./contracts/audit-entry-dto.md) |
| **UD-10-06** | Role matrix for audit query authorization | **Resolved** — `audit.read`; [contracts/audit-history-read-contract.md](./contracts/audit-history-read-contract.md) |

---

## Dependencies

| Dependency | Relationship | Notes |
| ---------- | ------------ | ----- |
| **spec01** Foundation | Required | Module scaffold, shared kernel, queue infrastructure |
| **spec02** Identity & Access | Required | Actor identity; permission model for audit read access |
| **spec03** Employee | Reference | Employee entities appear as audit subjects |
| **spec05** Request | Upstream supplier | Request lifecycle and approval audit entries |
| **spec06** Lottery | Upstream supplier | Lottery lifecycle and execution audit entries |
| **spec07** Allocation / CheckIn | Upstream supplier | Allocation and operational transition audit entries (**closed** — facts only) |
| **spec08** Voucher | Upstream supplier | Voucher transition audit entries (**closed** — facts only) |
| **spec09** Notification | Adjacent | Notification log separate; optional delivery audit entries |
| **spec11** Reporting | Downstream optional | May project audit metrics read-only (CD-017) |

---

## Out of Scope (spec10)

- Notification delivery and inbox management (**Notification** — spec09)
- Reporting dashboards, cross-domain projections, and export report definitions (**Reporting** — spec11)
- Upstream domain lifecycle rules (Request approval, Allocation assignment, Voucher eligibility, Lottery scoring)
- Workflow engine orchestration (**deferred**)
- Direct repository access to Request, Lottery, Allocation, Voucher, or other upstream stores to **discover** audit facts (**R10** violation)
- Mutable audit records, audit log correction workflows, or administrative DELETE
- External SIEM/streaming integration (deferred)
- Presentation UI implementation detail (deferred follow-on)
- Reopening or modifying closed programs (spec07, spec08, spec09)
- Implementation authorization, task decomposition, or code changes under this specification artifact alone

---

## Governance Traceability

| Reference | Relevance |
| --------- | --------- |
| `spec-catalog.md` spec10 | Audit — Planned; cross-cutting traceability capability |
| `context-map.md` R10 | Downstream consumer; hook/event integration |
| `handoff/spec09-implementation-closure.md` | spec09 closed; no active execution carryover |
| Constitution AP-06 | Critical events, audit fields, append-only, AuditService |
| Definition of Done | All state transitions emit audit entries via AuditService |
| `program-alignment-spec07-spec11.md` | C-12 AuditService facade; interim RecordsActivity pattern |
| `dormsys-architecture.md` §4-5 | Sensitive operations and AuditLog structure (planning reference) |

**Planning authority:** Specification-definition only. Nomination Record, Design Approval, architecture freeze, `plan.md`, `tasks.md`, and Implementation Authorization require separate governance records.

---

## Readiness for Planning

| Gate | Status |
| ---- | ------ |
| Problem statement | ✅ Defined |
| Scope / non-scope | ✅ Bounded |
| User stories | ✅ Defined (P1–P2) |
| R10 boundary | ✅ Aligned |
| AP-06 requirements | ✅ Mapped to FR-006 / vocabulary |
| Open ambiguities | ✅ Resolved at planning (UD-10-01–UD-10-06) |
| Implementation authorization | ❌ Not granted |
| Nomination record | ✅ Active — [`spec10-nomination-record.md`](../../.specify/docs/handoff/spec10-nomination-record.md) |
| Plan baseline | ✅ [plan.md](./plan.md) |

**Recommended next steps (governance only):** `/speckit-tasks` → Design Approval → Implementation Authorization.
