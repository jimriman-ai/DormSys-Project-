# Feature Specification: Accommodation Resource (spec04)

**Feature Branch**: `004-accommodation-resource`

**Created**: 2026-06-23

**Status**: Spec04-local composite (see GDR `spec04-governance-decision.md`)

| Layer (Spec04-local) | Value |
| -------------------- | ----- |
| Planning | Complete |
| Backend | CLOSED (`SPEC04_BACKEND_CLOSED` — Phases 1–4) |
| Product | PENDING_RESIDUAL |
| Documentation / mirrors | ALIGNED |

**Catalog**: spec04 — see `spec-catalog.md` (composite Status; not “Planning Authorized” alone)

**Governance decision**: [`.specify/docs/decision/spec04-governance-decision.md`](../../.specify/docs/decision/spec04-governance-decision.md)

**Residual implementation**: Not authorized by documentary alignment; see Product `PENDING_RESIDUAL` and Governance & Evolution Notes.

**Depends on**: spec01 Foundation (Approved)

**Input**: Establish the **Dormitory** bounded context: physical accommodation resources (dormitory sites, buildings, rooms, beds), capacity structure, and physical availability state — as upstream supplier for Allocation (spec07) and operational planning — while respecting **CD-014** (Dormitory owns physical state; Allocation owns assignment authority).

**Normative boundary**: [`../../.specify/docs/catalog-decisions.md`](../../.specify/docs/catalog-decisions.md) **CD-014**; [`../../.specify/docs/context-map.md`](../../.specify/docs/context-map.md) Dormitory row, **R7** (Allocation → Dormitory).

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Internal Dormitory Physical Catalog (Priority: P1)

As a dormitory administrator, I need to register internal dormitory sites with buildings, rooms, and beds so that the organization has an authoritative inventory of physical accommodation capacity.

**Why this priority**: All internal allocation, lottery capacity, and occupancy operations depend on a trustworthy physical resource catalog. Without this, Allocation cannot reference valid beds (system-flow INV-2).

**Independent Test**: Create an internal dormitory → add building → add rooms with floor labels → add beds → query capacity counts — entirely within Dormitory context, without Request or Allocation modules.

**Acceptance Scenarios**:

1. **Given** no dormitory exists, **When** an administrator registers an **internal** dormitory site, **Then** the site is persisted with a unique identifier and `internal` classification
2. **Given** an internal dormitory, **When** a building is added, **Then** the building is owned by that dormitory and queryable as part of its structure
3. **Given** a building, **When** rooms are added with optional floor labels, **Then** rooms are persisted under the building without requiring a separate Floor aggregate (OA-04-01)
4. **Given** a room, **When** one or more beds are added, **Then** each bed is a distinct capacity unit within the room and queryable by dormitory scope
5. **Given** a complete hierarchy, **When** capacity is summarized, **Then** total bed count per dormitory/building/room is accurate

---

### User Story 2 - External Dormitory Catalog (Priority: P1)

As a dormitory administrator, I need to register external dormitory sites without physical room/bed inventory so that lottery and voucher flows can reference third-party accommodation per BR-12.

**Why this priority**: Constitution distinguishes internal (physical tracking) from external (voucher-only) dormitories. Mixing them would violate BR-12 and CD-014 scope.

**Independent Test**: Register external dormitory → verify no building/room/bed children are required or permitted → query site metadata only.

**Acceptance Scenarios**:

1. **Given** an external dormitory registration, **When** saved, **Then** the site is classified as `external` and appears in the dormitory catalog
2. **Given** an external dormitory, **When** an administrator attempts to add beds or rooms, **Then** the operation is rejected (OA-04-03)
3. **Given** an external dormitory, **When** downstream consumers query physical capacity, **Then** the system reports no bed inventory for that site

---

### User Story 3 - Physical Bed & Room Status (Priority: P2)

As dormitory unit staff, I need to manage physical status of rooms and beds (in-service, out-of-service, maintenance) so that only operable capacity is offered for assignment.

**Why this priority**: system-flow INV-2 requires assignment only on beds with `In-Service` physical status. Physical operability is Dormitory-owned per CD-014.

**Independent Test**: Mark bed out-of-service → verify excluded from assignable capacity query; return to in-service → included again — without performing Allocation.

**Acceptance Scenarios**:

1. **Given** a bed in `InService` status, **When** staff marks it `OutOfService` or `Maintenance`, **Then** the bed is excluded from assignable physical capacity
2. **Given** a bed in non-operational status, **When** staff restores `InService`, **Then** the bed re-enters assignable capacity (subject to occupancy state from Allocation — OA-04-04)
3. **Given** a private room (OA-04-05), **When** configured, **Then** room kind is recorded for downstream family-direct rules (BR-03 enforced in Allocation, not Dormitory)

---

### User Story 4 - Allocation-Driven Physical State Updates (Priority: P2)

As the Dormitory bounded context, I need to apply physical occupancy state changes when Allocation publishes assignment outcomes so that bed availability reflects assignment decisions without Dormitory owning who is assigned.

**Why this priority**: CD-014 / R7 — Allocation drives; Dormitory owns physical state. Dormitory must consume assignment signals, not make assignment decisions.

**Independent Test**: Simulate allocation-assigned and allocation-released signals (contract stub) → verify bed occupancy flags update → verify Dormitory does not store employee/person assignment authority.

**Acceptance Scenarios**:

1. **Given** an in-service bed with no occupancy, **When** a valid **allocation-assigned** signal is received, **Then** physical occupancy state reflects occupied/reserved per documented model (OA-04-04)
2. **Given** an occupied bed, **When** a valid **allocation-released** signal is received, **Then** physical occupancy state clears assignable reservation/occupancy markers
3. **Given** any state update, **When** inspected, **Then** Dormitory holds **no authoritative assignment decision** — only physical state mirroring Allocation outcomes

---

### User Story 5 - Capacity Supplier Queries (Priority: P3)

As a downstream bounded context (Allocation, Reporting), I need read-only queries of dormitory structure and physical capacity so that planning and assignment can reference current inventory without cross-module persistence coupling.

**Why this priority**: Modular monolith requires supplier read contracts; Allocation (spec07) must not query Dormitory tables directly.

**Independent Test**: Query dormitory list, bed availability summary, and bed detail by identifier via documented supplier contract — using stand-in consumer stubs only.

**Acceptance Scenarios**:

1. **Given** registered dormitories, **When** a consumer lists internal sites, **Then** structure and capacity summaries are returned
2. **Given** a bed identifier, **When** a consumer queries physical status, **Then** operability and occupancy markers are returned without person assignment details owned by Allocation
3. **Given** an unknown bed identifier, **When** queried, **Then** a clear not-found outcome is returned

---

### Edge Cases

- External dormitory with building/room/bed children attempted? (Rejected — OA-04-03.)
- Bed marked out-of-service while Allocation still holds assignment? (Coordination rule deferred to spec07; Dormitory documents physical state; reconciliation is cross-context.)
- Duplicate bed code within same dormitory? (Must prevent or enforce documented uniqueness rule.)
- Room with zero beds? (Allowed as structural placeholder; zero assignable capacity.)
- Deactivate dormitory/building with active physical occupancy? (Policy documented in plan — default: block deactivation if occupied beds exist.)
- Cross-module direct query of `dormitory_*` tables from Allocation? (Forbidden — architecture boundary.)
- Check-in/check-out transitions? (**Out of scope** — OQ-06 / spec07; CD-014 assigns operational transitions to CheckIn/CheckOut candidate context.)

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST maintain **Dormitory** as the root catalog of accommodation sites within the Dormitory bounded context
- **FR-002**: System MUST classify each dormitory as **internal** (physical inventory) or **external** (catalog-only per BR-12)
- **FR-003**: System MUST model **Building → Room → Bed** hierarchy for **internal** dormitories (OA-04-01)
- **FR-004**: System MUST treat **floor** as a **Room attribute** (label or number), not a separate aggregate root (OA-04-01)
- **FR-005**: System MUST own **Bed** as the atomic physical capacity unit for bed-based accommodation
- **FR-006**: System MUST own **Room** grouping (private vs shared kind per OA-04-05) and **Building** grouping under Dormitory
- **FR-007**: System MUST maintain **physical operability status** on beds (at minimum: `InService`, `OutOfService`, `Maintenance`) — Dormitory-owned
- **FR-008**: System MUST maintain **physical occupancy markers** on beds updated by **Allocation domain signals** — Dormitory applies, does not decide (CD-014, OA-04-04)
- **FR-009**: System MUST **reject** room/bed structure under **external** dormitories (OA-04-03, BR-12)
- **FR-010**: System MUST expose **read-only supplier queries** for dormitory structure and physical capacity to downstream contexts (no cross-module Eloquent)
- **FR-011**: System MUST record audit-relevant Dormitory lifecycle actions (constitution AP-06)
- **FR-012**: System MUST use **UUID** identifiers for all Dormitory entities (constitution / spec01 kernel)
- **FR-013**: System MUST store cross-context references (e.g., to Allocation) as **immutable UUID values without FK** to other modules' tables

### Explicitly Out of Scope (spec04)

- **FR-EX-001**: Assignment decisions, allocation overlap rules, person-to-bed authority (**Allocation** — spec07; CD-014)
- **FR-EX-002**: Accommodation request submission and approval (**Request** — spec05)
- **FR-EX-003**: Lottery programs, scoring, draw execution (**Lottery** — spec06)
- **FR-EX-004**: Check-in / check-out operational transitions (**OQ-06** — spec07 candidate; not Dormitory)
- **FR-EX-005**: Voucher issuance for external winners (**Voucher** — spec08)
- **FR-EX-006**: Employee, Identity, or organizational data (**spec02**, **spec03**)
- **FR-EX-007**: Workflow engine activation (**deferred**)
- **FR-EX-008**: Reporting projections (**spec11**)

### Key Entities

- **Dormitory**: Accommodation site (internal or external); root catalog entry; owns buildings (internal only).
- **Building**: Physical structure within an internal dormitory; groups rooms.
- **Room**: Space within a building; optional floor attribute; `private` or `shared` kind; groups beds.
- **Bed**: Atomic capacity unit; physical operability status; physical occupancy markers driven by Allocation signals.

---

## Success Criteria *(mandatory)*

- **SC-001**: Internal dormitory hierarchy (dormitory → building → room → bed) can be created and queried independently of Request/Allocation modules
- **SC-002**: External dormitories exist in catalog without bed inventory; structural child creation is rejected
- **SC-003**: Physical operability status changes affect assignable capacity queries without Allocation module present
- **SC-004**: Allocation signal handling updates physical occupancy markers without Dormitory storing assignment authority (CD-014)
- **SC-005**: Architecture boundary test passes — no cross-module persistence imports between Dormitory and Allocation/Request/Employee
- **SC-006**: PHPStan L8 zero errors for Dormitory module paths when implemented (DoD)

---

## Assumptions & Recorded Decisions

### OA-04-01 — Building / floor hierarchy (DECIDED)

**Decision:** For **internal** dormitories, model **Dormitory → Building → Room → Bed** from the start. **Floor** is a **Room attribute** (e.g., floor number or label), not a separate aggregate.

**Rationale:** Resolves catalog open question; supports multi-floor buildings without an extra aggregate lifecycle; aligns with constitution "buildings, rooms, beds."

**Alternatives rejected:** Deferred floor (insufficient for operations planning); Floor as separate aggregate (premature complexity for Wave 1).

### OA-04-02 — Room / bed ownership (DECIDED)

**Decision:** **Dormitory** bounded context owns **Room** and **Bed** entities and all `dormitory_*` persistence. No other module owns physical room/bed records.

**Rationale:** CD-014; `context-map.md` Dormitory row; constitution module table.

### OA-04-03 — External dormitory inventory (DECIDED)

**Decision:** **External** dormitories are catalog entries only. **No** Building, Room, or Bed children. BR-12 scope.

**Rationale:** External sites do not expose room/bed identifiers; voucher flow only.

### OA-04-04 — Availability vs assignment boundary (DECIDED — CD-014 refinement)

**Decision:**

| Concern | Owner | Dormitory responsibility |
| ------- | ----- | ------------------------ |
| Physical operability (`InService`, etc.) | **Dormitory** | Authoritative |
| Who is assigned to which bed | **Allocation** | Not stored as authority in Dormitory |
| Physical occupancy markers (occupied/reserved) | **Dormitory** | Updated by consuming **Allocation** domain signals (R7) |
| Effective occupancy lifecycle (check-in/out) | **CheckIn/CheckOut** (OQ-06) | **Out of scope** for spec04 |

**Invariant (CD-014):** Effective bed occupancy is derived from Allocation + CheckIn/CheckOut — not a single authoritative field in Dormitory alone.

**Recorded deferral:** Reconciliation when Allocation and Dormitory state diverge → spec07 planning.

### OA-04-05 — Room kind (DECIDED)

**Decision:** Room carries a **kind**: `private` (exclusive / family-suitable) or `shared` (multi-bed). Dormitory records kind; **BR-03 enforcement** (family must get private room) remains **Allocation** responsibility.

### OA-04-06 — Downstream integration style (RECORDED ASSUMPTION)

**Assumption:** Allocation → Dormitory integration uses **versioned domain events** and/or **application service contracts** (R7). Direct repository access across modules is prohibited. Exact contract names deferred to `plan.md` / `contracts/`.

---

## Governance Traceability

| Reference | Relevance |
| --------- | --------- |
| `docs/decision/spec04-governance-decision.md` | Spec04 GDR — `DECISION_FINALIZED` (composition / labels / Floor / residuals) |
| `docs/plans/spec04-alignment-plan.md` | Alignment plan — documentary sync applied |
| `handoff/spec04-backend-closeout.md` | `SPEC04_BACKEND_CLOSED` — Phases 1–4 |
| `handoff/spec04-implementation-authorization.md` | Historical `IMPLEMENTATION_AUTHORIZED` (backend foundation) |
| `spec-catalog.md` spec04 | Accommodation Resource; composite Status (Backend CLOSED / Product PENDING_RESIDUAL) |
| `handoff/spec04-planning-authorization.md` | Historical planning scope (specify/plan/tasks); superseded for execution status by IA + backend closeout |
| `handoff/spec03-post-mvp-authorization.md` | spec03 complete; US3+ hold — unchanged |
| CD-014 | Dormitory physical state vs Allocation assignment |
| `context-map.md` R7 | Allocation → Dormitory (event/service) |
| BR-12 | External dormitory scope |
| system-flow INV-2 | Assignment only on in-service beds |
| OQ-06 | CheckIn/CheckOut — deferred; documented only |

---

## Governance & Evolution Notes

**Authority:** Recorded in [`.specify/docs/decision/spec04-governance-decision.md`](../../.specify/docs/decision/spec04-governance-decision.md) (Decision 3). This note does not rewrite the historical OA-04-01 decision body below.

### Floor hierarchy — OA-04-01 and accepted backend evolution

OA-04-01 is identified as superseded by the accepted backend domain evolution (Floor Aggregate: Dormitory → Building → Floor → Room → Bed), as recorded in Spec04 Implementation Authorization / backend design and `SPEC04_BACKEND_CLOSED`.

The original OA-04-01 decision text (Floor as a Room attribute; no separate Floor aggregate) is retained for decision-chain history. Normative understanding for Spec04 alignment purposes follows the accepted Floor Aggregate evolution. Consumers and future amendments should treat the Floor Aggregate hierarchy as the current domain disposition pending any later Design Approval that reopens this topic.

**Evidence pointers:** `.specify/docs/handoff/spec04-implementation-authorization.md`; `.specify/docs/handoff/spec04-backend-closeout.md`.

### Residual scope (deferred — not cancelled)

Per GDR Decision 4 and `spec04-backend-closeout.md` §6, the following are identified as deferred scope. Ownership is transferred to future waves/specs; items are **not** considered cancelled.

| Residual item | Disposition |
| ------------- | ----------- |
| Authorization / policies / roles / guards for Dormitory surfaces | `DEFERRED_TO_FUTURE_WAVE` |
| Livewire / Blade / UI | `DEFERRED_TO_FUTURE_WAVE` |
| HTTP / API / controllers / FormRequests | `DEFERRED_TO_FUTURE_WAVE` |
| Allocation ↔ Dormitory integration (`bedExists` / `isBedAssignable`, related Application Read extensions) | `DEFERRED_TO_FUTURE_WAVE` |
| CheckIn/CheckOut ↔ Dormitory occupancy request wiring | `DEFERRED_TO_FUTURE_WAVE` |
| Workflow ownership / orchestration inside Dormitory | `DEFERRED_TO_FUTURE_WAVE` |
| Events / listeners / jobs (unless separately approved) | `DEFERRED_TO_FUTURE_WAVE` |
| External system adapters | `DEFERRED_TO_FUTURE_WAVE` |
| Voucher / billing / payment / notification behavior | `DEFERRED_TO_FUTURE_WAVE` |
| Broad Request Feature-suite dormitory fixture remediation | `DEFERRED_TO_FUTURE_WAVE` |

Successor Spec / Wave ids are **not** assigned by this alignment; nomination remains a separate governance act.

---

## Consumer Language Guard (authoring)

- ✅ Dormitory **owns** physical buildings, rooms, beds, and operability status
- ✅ Dormitory **applies** occupancy markers from Allocation signals
- ✅ Allocation **owns** who is assigned to what (spec07)
- ❌ Dormitory deciding or storing authoritative person-to-bed assignment
- ❌ FK from Dormitory tables to `allocation_*`, `employee_*`, or `identity_*`
- ❌ Cross-module Eloquent queries (Allocation reading `BedModel` directly)
