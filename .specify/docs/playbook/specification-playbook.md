
# Specification Engineering Playbook

| | |
|---|---|
| **Status** | 🟢 Final (v1.1.1) |
| **Version** | 1.1.1 |
| **Last Updated** | 1405/04/10 \| 2026/07/01 |
| **Governance Level** | Wave 1 Authority Document |
| **Supersedes** | v1.1.0 (2026-06-26) |
| **Alignment** | Mirrored from [`catalog-decisions.md`](../catalog-decisions.md) v2.8.1, [`context-map.md`](../context-map.md) v0.4.1, [`spec-catalog.md`](../spec-catalog.md) v1.0.8 |

> **Why RC and not Final:** The issues in version 1.0.0 were not execution-related; they were governance-related. Any governance error that enters this document contaminates all downstream Specs. Therefore, until the final approval of the Boundary Review, this document only guides the process and **is not permitted to produce architectural decisions**.

---

## I. Introduction and Scope

This Playbook guides the Specification production process from Discovery to the final technical document. Its goal is to prevent premature decisions, preserve evidence integrity, and manage structural conflicts.

**Fundamental principle:** The Playbook guides the process; it does not produce architectural decisions. Architectural decisions are made official only through Boundary Review and recording in `catalog-decisions.md` or a formal ADR.

### Execution Waves

| Wave | Content |
|------|---------|
| **Wave 1** | Discovery, Evidence Freeze, Boundary Review, Decision Catalog |
| **Wave 2** | Spec Writing, Implementation Blueprint (Plan) |
| **Wave 3** | Code Generation, Verification |

---

## II. Evidence Management

### Rule 1 — Evidence Sources (Layered)

Every finding must be extracted from one of these sources and with its level explicitly stated:

| Level | Source | Nature |
|------|--------|--------|
| **Governance** | `CONSTITUTION v1.3.0.md` | Architectural principles, immutable business rules, domain language, module boundaries, hierarchy of authority. **It does not define schema/tables.** |
| **Architecture** | `dormsys-architecture.md` | Operational translation of the Constitution into modules, versions, state machines, and distribution of responsibilities |
| **Discovery** | `DormSys Discovery Document.md` | Problem statement, pain points, actual scope |
| **History** | `hist02.md`, `hist03.md` | Evidence tables extracted from domain and code review |

> **Governance clarification:** Schema definition and table structures are a **Derived Artifact** (spec/plan), not a source of governance evidence. The Constitution is upstream of the schema, not the schema itself.

### Rule 2 — Mandatory Citation

Every claim must be cited in the format `[source: file, start line-end line]`. A claim without citation is not valid.

### Rule 3 — Separation of Concept from Implementation

- **Business Concept** (for example, “an employee has only one active allocation”) separately from
- **Implementation Field** (for example, column `allocation_id`) is recorded.

### Rule 4 — Evidence Freeze

Evidence Freeze is only the **final recording of citations** before Boundary Review, not decision-making. No Open Question is closed at this stage.

---

## III. Conflict Resolution

### Rule 5 — Tagging

Every conflict is recorded with identifier `CONF-xx` and remains visible in `catalog-decisions.md` until resolved (requirement of `spec-catalog.md` that Open Questions remain visible until resolution).

### Rule 6 — Resolution Process

Evidence Freeze
      ↓
Boundary Review
      ↓
Decision Brief
      ↓
ADR / Catalog Update


The Constitution is the superior document; every downstream artifact must conform to it, and conflicts must be flagged, not silently resolved.

---

## IV. Pipeline and Freeze Order (Revised)

This section replaces the incorrect sequence of the previous version (premature Hard Freeze of the Catalog).

Catalog v0.x  (Soft Freeze)
      ↓
Context Map
      ↓
Boundary Review  →  resolution of critical OQs (OQ-01 … OQ-05) + conflicts (CONF-DEP-01)
      ↓
Proposed Freeze spec02–spec06  (boundary-aligned; OQ-06/07/08 deferred at that wave — later closed CD-015..CD-017)
      ↓
spec02 … spec06 authoring  (with documented Open Questions where applicable)
      ↓
Review
      ↓
Catalog v1.0  (Hard Freeze)
      ↓
Plan (plan02 … plan11)
      ↓
Tasks (tasks02 … tasks11)
      ↓
Implement


**Reason for this order:**
- According to `spec-catalog.md`, a spec **can be planned even if some internal decisions are still open**, provided that those questions have been documented in the catalog.
- Therefore, stopping the entire process until all OQs are resolved is overly strict and contrary to the Catalog philosophy.
- Premature Hard Freeze of the Catalog causes us, when writing spec03 and discovering the need to revise the Boundary, to be forced to write an ADR and reopen the Catalog. Soft Freeze removes this cost.

> **Criterion for moving from Soft to Hard Freeze (mandatory gate):** Only OQs that are **critical for the Boundary** (OQ-01, OQ-02) must be closed before writing the spec. Other OQs may be carried inside the spec in documented form.
>
> **Additional practice (v1.1.0):** Close OQs critical to a specific spec's boundary before that spec's plan is hard-frozen. OQs outside a spec's scope may remain open in `catalog-decisions.md`.

### Proposed Freeze — spec02 through spec06

**Registered:** 1405/04/05 \| 2026/06/26

| Spec | Freeze status | Boundary prerequisite | Out-of-scope OQs |
| ---- | ------------- | --------------------- | ---------------- |
| `spec02` Identity & Access | Proposed Freeze → **Hard Freeze / Wave 1A** | OQ-01 closed (CD-012) | OQ-06..08 deferred at this wave (later closed CD-015..CD-017) |
| `spec03` Employee Context | Proposed Freeze → **Hard Freeze / Wave 1A** | OQ-01, OQ-02 closed; CONF-DEP-01 closed (CD-009, CD-012, CD-013) | OQ-06..08 deferred at this wave (later closed CD-015..CD-017) |
| `spec04` Accommodation Resource | Proposed Freeze | CD-014 physical-state split acknowledged | OQ-06..08 deferred at this wave (later closed CD-015..CD-017) |
| `spec05` Request Management | Proposed Freeze | OQ-02, OQ-03 closed (CD-010, CD-013) | OQ-06..08 deferred at this wave (later closed CD-015..CD-017) |
| `spec06` Lottery Selection | Proposed Freeze | OQ-04 closed (CD-011) | OQ-06..08 deferred at this wave (later closed CD-015..CD-017) |

**Not frozen:** `spec07`, `spec08`, `spec11` — boundary closed (CD-015, CD-016, CD-017); implementation not authorized.

### Hard Freeze v1.0.0 — Catalog Acceptance

**Accepted:** 1405/04/05 \| 2026/06/26  
**Recorded in:** [`spec-catalog.md`](../spec-catalog.md) — Acceptance Record section

| Gate item | Result |
| --------- | ------ |
| OQ-01 closed (CD-012) | ✅ |
| OQ-02 closed (CD-013) | ✅ |
| OQ-06, OQ-07, OQ-08 closed (CD-015, CD-016, CD-017) | ✅ |
| Cross-document consistency (4 docs) | ✅ |
| Wave 1A (`spec02`, `spec03`) authorized | ✅ |
| `spec01` implementation debt | Out of freeze scope (not a gate) |

---

## V. Space and Boundary Management

### Rule 7 — Shared Database, Isolated Ownership

- A shared PostgreSQL database is **permitted**.
- **Direct Table Access** between Spaces is **prohibited**.
- Each Space has absolute ownership of its own tables.

### Rule 8 — Permitted Integration Patterns

1. **Application Service API** — calling the target service
2. **Domain Event** — publishing a domain event
3. **Saga** — multi-step coordination without distributed transaction
4. **Read-Only Projection** — only for Reporting (the only context allowed to perform cross-boundary read)

---

## VI. Boundary Review Matrix (Revised)

> **ID convention:** `CONF-*` = document conflict; `OQ-*` = open boundary question.  
> Dependent ownership is **CONF-DEP-01**, not OQ-08. OQ-08 = reporting projection scope (`spec11`).

### Resolved Conflicts

| ID | Topic | Type | Affected Specs | Status | Authority |
| ---- | ----- | ---- | -------------- | ------ | --------- |
| CONF-DEP-01 | Dependent entity ownership | Ownership | spec03, spec05 | ✅ Closed | CD-009 |

### Boundary Questions

| OQ ID | Topic | Type | Affected Specs | Status | Authority |
| ----- | ----- | ---- | -------------- | ------ | --------- |
| OQ-01 | Employee ↔ Identity attachment | Attachment | spec02, spec03 | ✅ Closed | CD-012 |
| OQ-02 | Eligibility ownership | Service vs enforcement | spec03, spec05 | ✅ Closed — Current Scope | CD-013 |
| OQ-03 | Approval state vs transition rules | Module split | spec05, Workflow (deferred) | ✅ Closed | CD-010 |
| OQ-04 | Lottery rules ownership | Business logic | spec06 | ✅ Closed | CD-011 |
| OQ-05 | Allocation ↔ Occupancy split | Context split | spec04, spec07 | ✅ Closed | CD-014 |
| OQ-06 | CheckIn/CheckOut module boundary | Context split | spec07 | ✅ Closed | CD-015 |
| OQ-07 | Voucher eligibility ownership | Business logic | spec08 | ✅ Closed | CD-016 |
| OQ-08 | Reporting projection / read-model scope | Read model | spec11 | ✅ Closed | CD-017 |

---

## VII. Context Inventory

**Active Bounded Contexts:**
Identity · Employee · Dormitory (Accommodation Resource) · Request · Lottery · Allocation · CheckIn/CheckOut · Voucher

**Cross-Cutting Capabilities:**
Notification · Audit · Reporting (the only cross-boundary read)

**Deferred Capability:**
`Workflow Engine` — **capability, not an active module** (CD-010). When activated: owns approval transition rules and orchestration. Request retains `RequestApproval` state ownership regardless.

> Identity and Employee are two **separate** bounded contexts; coupling is through immutable-ID, and there is no shared table or “Employee extends User” in the documents.

---

## VIII. Deferred Decisions Framework

A new Space is not created unless all three conditions are met:
1. At least **2 real cases** with similar behavior
2. Proven **behavioral duplication**
3. Existence of a **natural Boundary** in the domain

The activation criterion for Workflow Engine is also subject to this same framework.

---

## IX. Boundary Questions — Status

> Governance note: Statuses are **mirrored** from [`catalog-decisions.md`](../catalog-decisions.md) and [`context-map.md`](../context-map.md).  
> Decisions are owned by the Decision Catalog via Boundary Review — **not decided here**.  
> **Do not reuse OQ IDs for conflicts:** Dependent ownership = `CONF-DEP-01` → CD-009, not OQ-08.

### Resolved Conflicts

| ID | Topic | Status | Authority |
| ---- | ----- | ------ | --------- |
| CONF-DEP-01 | Dependent entity ownership | ✅ Closed | CD-009 |

### Boundary Questions (OQ-01 … OQ-08)

| OQ | Topic | Status | Authority |
| ---- | ----- | ------ | --------- |
| OQ-01 | Identity ↔ Employee attachment | ✅ Closed | CD-012 |
| OQ-02 | Eligibility ownership | ✅ Closed — Current Scope | CD-013 |
| OQ-03 | Approval state vs transition rules | ✅ Closed | CD-010 |
| OQ-04 | Lottery rules ownership | ✅ Closed | CD-011 |
| OQ-05 | Allocation ↔ Occupancy split | ✅ Closed | CD-014 |
| OQ-06 | CheckIn/CheckOut inside Allocation vs separate context | ✅ Closed | CD-015 |
| OQ-07 | Voucher eligibility ownership | ✅ Closed | CD-016 |
| OQ-08 | Reporting projection / read-model scope | ✅ Closed | CD-017 |

---

## X. Naming Conventions

- The terms `registry` and `module` in design discourse are changed to `context` (aligned with bounded context).
- The context name = domain name, not technical implementation name.

---

## XI. Technical Practices

- **Workspace:** Laravel Sail.
- **Namespace Strategy:** `App\Contexts\{Context}\Domain\Models\{Model}` (for example `App\Contexts\Employee\Domain\Models\Employee`).
- Direct access to the domain model of one context from another context is **prohibited**.
- Long-running tasks via Queue + Redis + Horizon.
- Lottery execution must be **idempotent and transactional** with a repeatable seed.
- Configurable values in `settings` or env, not hardcoded.

---

## XII. Integration Examples

| Scenario | Pattern |
|----------|---------|
| Request → Lottery | Domain Event |
| Allocation → Dormitory | Saga |
| Employee Eligibility check | Application Service |
| Reporting | Read-Only Projection (the only cross-boundary read) |

---

## XIII. Decision Brief Sample — DB-001 (Revised)

**DB-001 — Dependent Entity Ownership**
Status: 🟢 Decided → CD-009 (catalog-decisions.md)

Resolution: Resolved through Boundary Review. Decision: `Dependent ∈ Employee`
(aggregate root pattern; `request_id` is a reference link only).
Constitution v1.3.0.md:642 to be corrected accordingly.
Evidence below is retained as historical record of the blocked state.

### Evidence (Layered)

**From Governance (Constitution):**
- Identity owns User/Role/Permission and Employee owns Employee/Department.
- `Dependent` is not explicitly assigned to any context (silence at the governance level, not schema).
- ⛔ There is no `CREATE TABLE` or DDL in the Constitution; any DDL reference to the Constitution is **fabricated**.
`[source: CONSTITUTION v1.3.0.md, ownership domains and module boundaries section]`

**From Architecture:**
- Employee Context owns `Dependent` (entity) — explicit ownership.
`[source: dormsys-architecture.md, Domain Modules section]`

**From Discovery / History:**
- `Dependent` has an independent UUID and is dependent on Employee; ownership is still conflicting.
`[source: hist03.md, Employee evidence table]`

### Trade-off Analysis

| Option | Benefit | Cost |
|--------|---------|------|
| **A: Dependent ∈ Employee Context** | aligned with Architecture; high cohesion with employee lifecycle | more load on Employee context |
| **B: Dependent as an independent context** | explicit separation | violates the framework of section VIII (only 1 case, natural Boundary not proven) |

### Resolution (Historical — now decided)

Option A (Dependent ∈ Employee Context) was accepted in Boundary Review as **CD-009**, closing **CONF-DEP-01**.  
This conflict used the working label `DB-001` in early playbook drafts; it is **not** OQ-08.

### Next Steps / Catalog Update Process (completed)

1. ~~Record in `catalog-decisions.md` with status `Provisional`.~~ → **CD-009 ACCEPTED**
2. ~~Resolve in Boundary Review.~~ → **Done (2026-06-25)**
3. ~~Promote to `Decided`.~~ → **Done**; Constitution v1.3.0.md:642 correction still pending

---

## XIV. Change History

| Version | Date | Change |
|---------|------|--------|
| 1.0.0 | 1405/04/04 | Initial version (withdrawn — governance conflict) |
| 1.0.1 | 1405/04/05 | Revised OQ status (IX), revised Constitution description (II), layered Evidence in DB-001 (XIII), revised Boundary matrix (VI), revised Freeze order to Soft→Hard (IV), downgraded status to RC |
| 1.0 Final | 1405/04/05 | Promoted RC → Final after Boundary Review round (CD-012, CD-013, CD-014); mirrored OQ statuses (IX); DB-001 → CD-009 (XIII) |
| 1.1.1 | 1405/04/10 | Governance drift sync: OQ-06..08 closed (CD-015..CD-017); CheckIn/CheckOut in Context Inventory; alignment mirrors updated |
| 1.1.0 | 1405/04/05 | Fixed OQ-08 ID collision; added OQ-06/OQ-07; registered Proposed Freeze spec02–spec06; **Hard Freeze v1.0.0 accepted** (spec-catalog Acceptance Record); Wave 1A authorized |

---

# Boundary Review Session — Checklist for OQ-01 & OQ-02 (Historical)

**Session Goal:** Resolve critical ownership & boundary questions blocking Playbook v1.0.1 → v1.0 Final promotion and spec02/spec03 authoring.

**Date:** `______` | **Participants:** Product Owner, Tech Lead, Domain Expert(s)

---

## Pre-Session Preparation

- [ ] All participants have read: `context-map.md`, `catalog-decisions.md`, `CONSTITUTION v1.3.0.md`
- [ ] Evidence artifacts ready: `hist03.md`, `dormsys-architecture.md`
- [ ] Whiteboard/collaborative tool prepared for boundary sketching

---

## OQ-01: Employee ↔ Identity Attachment

**Current State:**
- Identity and Employee are **separate bounded contexts** (aggregate roots)
- Coupling via **immutable ID only** (no shared FK, no cross-module table access)
- **Attachment mechanism undefined**

**Decision Required:**
Which context owns the **Employee ↔ Identity binding**?

### Option A: Identity Context Owns Attachment
- Identity stores `employee_id` (nullable UUID) in `users` table
- Employee queries Identity via Application Service to verify user existence
- **Pro:** Identity = single source of truth for authentication & user lifecycle
- **Con:** Identity becomes aware of Employee domain

### Option B: Employee Context Owns Attachment
- Employee stores `identity_id` (immutable UUID) in `employees` table
- Identity publishes `UserCreated` event → Employee subscribes
- **Pro:** Identity remains generic, zero coupling to Employee
- **Con:** Employee must handle orphan detection (user deleted but employee exists)

### Option C: Explicit Mapping Table (Shared or Third Context)
- Dedicated `user_employee_mapping` table with `(user_id, employee_id)`
- Owned by new Integration context or by one of the two
- **Pro:** Clean separation, auditable binding
- **Con:** Adds complexity, violates "no new context without 2+ similar cases" rule (Deferred Framework, Playbook §VIII)

### Decision:
- [ ] **Selected Option:** `______`
- [ ] **Rationale:** `______`
- [ ] **Owner for implementation:** `______`
- [ ] **Catalog status update:** Open → Decided

**Action Items:**
- [ ] Update `catalog-decisions.md` with selected option
- [ ] Write ADR-00X if architectural deviation from current assumptions
- [ ] Update `context-map.md` relationship definition for Identity↔Employee

---

## OQ-02: Eligibility Ownership

**Current State:**
- **Business rule:** `active employee + no active allocation` = eligible for request
- `catalog-decisions.md`: rule attributed to **Request context**
- `hist03.md`: eligibility data/logic lives in **Employee context**
- **Conflict:** Rule location vs Data location

**Decision Required:**
Which context **owns** and **enforces** the eligibility rule?

### Option A: Employee Context Owns Eligibility
- Employee exposes `EmployeeService::isEligible(employee_id): bool`
- Request invokes this service before accepting request
- **Pro:** Data & logic co-located, single source of truth
- **Con:** Employee becomes aware of allocation state (cross-context coupling)

### Option B: Request Context Owns Eligibility
- Request queries Employee (for status) + Allocation (for active check) directly via services
- Eligibility logic lives in Request's domain layer
- **Pro:** Request owns its pre-conditions, no extra service surface on Employee
- **Con:** Request must coordinate two contexts; eligibility logic separated from employee data

### Option C: Shared Eligibility Service (Cross-Cutting Capability)
- Dedicated `EligibilityService` reads Employee + Allocation via projections
- Invoked by Request before submission
- **Pro:** Clean separation, reusable for reporting/admin tools
- **Con:** Adds new capability without proven duplication (violates Deferred Framework)

### Decision:
- [ ] **Selected Option:** `______`
- [ ] **Rationale:** `______`
- [ ] **Contract definition:** API signature if service-based, event schema if event-based
- [ ] **Catalog status update:** Open → Decided

**Action Items:**
- [ ] Update `catalog-decisions.md` with selected option
- [ ] Define Application Service contract or Domain Event schema
- [ ] Update affected specs: spec03 (Employee), spec05 (Request)
- [ ] Tag related conflicts: CONF-DEP-01 (Dependent, CD-009) may be affected if eligibility includes dependent check

---

## Post-Decision Steps

- [ ] Update `spec-catalog.md` status for OQ-01, OQ-02: Open → Decided
- [ ] Promote Playbook v1.0.1 (RC) → v1.0 (Final) after catalog freeze
- [ ] Authorize spec02/spec03 authoring with resolved boundaries
- [x] OQ-06, OQ-07, OQ-08 closed (CD-015, CD-016, CD-017) — governance drift sync 2026-07-01

---

## Session Output Template

```markdown
## Boundary Review — Session Notes
**Date:** ______
**Duration:** ______
**Participants:** ______

### OQ-01 Resolution
**Decision:** Option ___ selected
**Reasoning:** ______
**Next owner:** ______

### OQ-02 Resolution
**Decision:** Option ___ selected
**Reasoning:** ______
**Contract:** ______

### Open Items
- [ ] ______
- [ ] ______

**Catalog update committed:** Yes / No
**Playbook promotion authorized:** Yes / No
```

---

**Reminder:** Constitution v1.3.0 is the supreme governance document. Any decision contradicting it requires explicit amendment via ADR and Product Owner approval.