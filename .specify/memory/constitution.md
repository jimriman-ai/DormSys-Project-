# CONSTITUTION v1.3.0 — DormSys Project

## Document Control

| Field | Value |
|---|---|
| **Version** | 1.3.0 |
| **Date** | 1405/03/31 (2026-06-21) |
| **Status** | Baseline |
| **Approval Status** | Approved |
| **Approved By** | Product Owner & Tech Lead |
| **Previous Version** | 1.2.0 |
| **Classification** | Internal – Project Governance |

### Change Summary (1.2.0 → 1.3.0)

- Aligned technology stack with ADR-001 (Laravel 13, PHP 8.4, Livewire 3, Tailwind CSS 4, Alpine.js 3)
- Added MinIO (S3-compatible) to the infrastructure stack
- Added Laravel Horizon, Laravel Pulse, and Sentry to the platform layer
- Expanded module boundary table to include CheckIn, Report, and Voucher modules
- Revised domain language to include Voucher, AllocationMethod, and CheckIn/Out terms from discovery
- Added Storage Layer to architecture description
- Updated permission matrix to include Operator and Lottery Operator roles
- Expanded business rules to align with discovery document (external dormitory, voucher, auto-allocation)
- Updated architecture constraints to explicitly prohibit SPA frameworks

---

# 1. Purpose of This Document

This Constitution is the **supreme governance document** for the DormSys project.

It establishes the **non-negotiable principles, invariants, and architectural rules** that govern the entire system. Every technical artifact produced within the project must align with the constraints defined here.

The Constitution defines:

- Architectural principles and technology choices
- Immutable business rules
- Domain language authority
- System constraints and governance policies
- Decision authority hierarchy

This document intentionally **does not describe implementation details** such as API contracts, schema definitions, UI flows, or infrastructure setup scripts. Those artifacts are derived from and constrained by this Constitution.

### Document Role in the Specification Hierarchy

The Constitution sits at the **top of the specification hierarchy**. All other documents must conform to it.

Derived artifacts include:

- Product Requirement Documents (PRDs)
- Domain Specifications
- Database Schema Specifications
- API Contracts
- Architecture Decision Records (ADRs)
- Implementation Code

If any lower-level artifact conflicts with this Constitution, **the Constitution prevails**.

### Governance Intent

This document exists to ensure that:

- architectural decisions remain consistent over time
- domain terminology remains stable and unambiguous
- critical business invariants cannot be accidentally violated
- architectural complexity remains controlled
- long-term maintainability is preserved

Because this document governs both **human developers and AI agents**, it is written to eliminate ambiguity wherever possible.

---

# 2. Project Identity

**Name:** DormSys (Organizational Dormitory Management System)

**Type:** Enterprise Internal System (Greenfield Development)

**Domain:**
Centralized management of employee dormitory accommodations including request submission, multi-stage approvals, lottery-based allocation, direct mission-based allocation, room and bed assignment, occupancy lifecycle, check-in/check-out operations, and operational reporting.

**Target Users**

| Role | Description |
|---|---|
| Employee | Regular employees submitting accommodation requests |
| Department Manager | Approves requests at Stage 1 |
| HR Manager | Approves requests at Stage 2 |
| Dormitory Manager | Manages dormitories, executes lotteries, approves at Stage 3 |
| Dormitory Unit Staff | Manages rooms, beds, allocations, approves at Stage 4 |
| Lottery Operator | Operates lottery programs on behalf of Dormitory Manager |
| Operator | Registers check-in and check-out events |
| System Administrator | Full system access and configuration |

**Primary Operational Context**

DormSys manages the full lifecycle of employee dormitory accommodation:

- Accommodation request submission (Personal, Family, Group/Mission, Lottery Registration)
- Multi-stage approval workflow
- Lottery-based selection with deterministic scoring
- Direct mission-based allocation
- Room and bed assignment (internal dormitories)
- Voucher generation (external dormitories)
- Occupancy tracking and check-in/check-out
- Departure and extension management
- Operational reporting and audit

**Version:** 1.3.0
**Status:** Baseline Governance Document

DormSys is implemented as a **server-rendered enterprise web application** with a modular monolith backend and Livewire-powered UI.

---

# 3. Roles When Working With This Constitution

When interacting with this document, contributors must assume the responsibilities of multiple architectural roles.

### Enterprise Software Architect
Ensures the system architecture remains coherent, avoids unnecessary complexity, and supports long-term evolution.

### Domain Analyst
Preserves the integrity of domain concepts and ensures that terminology and business rules remain consistent.

### Backend Systems Designer
Maps domain concepts into maintainable backend structures while respecting architectural constraints.

### Constitution Author
Maintains governance integrity and prevents accidental erosion of architectural discipline.

### Responsibility Boundaries

This document defines **principles and invariants**, not implementation details.

When uncertainty arises:
- Architectural decisions escalate to the **Tech Lead**
- Business rule changes escalate to the **Product Owner**

No contributor should infer or invent rules not explicitly defined here.

---

# 4. Architecture Principles

DormSys architecture follows **enterprise modular monolith principles combined with Clean Architecture and DDD-Lite**.

The goal is to maintain **clarity of boundaries without distributed system complexity**.

---

## AP-01: Technology Stack

### Principle

DormSys uses **stable, well-supported open-source technologies** with large developer ecosystems. The project intentionally favors **proven technology** over experimental frameworks.

This stack was selected based on the Why → Problem → Need → Solution → Stack chain (see ADR-001). DormSys is an enterprise workflow application for 10–50 concurrent users with forms, approvals, reporting, and audit requirements — not a SaaS platform, not a real-time system, not an AI product.

### Approved Technology Stack

#### Core Infrastructure

| Layer | Technology | Version |
|---|---|---|
| Language | PHP | 8.4 |
| Framework | Laravel | 13.x |
| Database | PostgreSQL | 17 |
| Cache / Queue / Session | Redis | 7.x |
| Web Server | Nginx | 1.26 |
| Container Runtime | Docker + Docker Compose | 27.x |

#### Presentation Layer

| Technology | Version | Role |
|---|---|---|
| Laravel Blade | Built-in | Server-side templates, layouts, partials |
| Livewire | 3.x | Reactive server-driven UI components |
| Alpine.js | 3.x | Minimal client-side interactivity |
| Tailwind CSS | 4.x | Utility-first styling with RTL support |

**Layer Usage Rule:**
- Blade → page structure, layouts, static partials
- Livewire → any component requiring server interaction or reactive state
- Alpine.js → purely client-side UI (open/close, tab switch, dropdown)
- Tailwind → all visual styling

**SPA frameworks (React, Vue, Next.js, Nuxt) are explicitly prohibited.**

#### Platform Packages

| Package | Version | Purpose |
|---|---|---|
| laravel/fortify | 1.x | Authentication engine |
| laravel/sanctum | 4.x | Token-based API authentication (future use) |
| laravel/horizon | 5.x | Queue monitoring and management |
| laravel/pulse | 1.x | Application performance monitoring |
| livewire/livewire | 3.x | Reactive UI layer |
| spatie/laravel-permission | 6.x | RBAC — Roles and permissions |
| spatie/laravel-model-states | 2.x | Explicit state machines |
| spatie/laravel-activitylog | 4.x | Audit logging |
| maatwebsite/excel | 3.x | Excel report export |
| barryvdh/laravel-dompdf | 3.x | PDF report generation |
| sentry/sentry-laravel | 4.x | Error tracking and observability |

#### Storage Layer

| Technology | Version | Purpose |
|---|---|---|
| MinIO | Latest stable | S3-compatible object storage for documents and attachments |

#### Development and Quality Tools

| Tool | Version | Purpose |
|---|---|---|
| pestphp/pest | 3.x | Testing framework |
| pestphp/pest-plugin-laravel | 3.x | Laravel integration for Pest |
| phpstan/phpstan | 2.x | Static analysis |
| nunomaduro/larastan | 3.x | Laravel-aware PHPStan extension |
| laravel/pint | 1.x | Code style enforcement (PSR-12 + Laravel conventions) |

### Constraint

All technology additions or changes require:
- An Architecture Decision Record (ADR)
- Approval from the Tech Lead

Technology changes without ADR approval are **prohibited**.

---

## AP-02: Modular Monolith Architecture

DormSys is implemented as a **Modular Monolith** — a single deployable application with logically isolated bounded-context modules.

### Key Properties

- Single deployment artifact
- Single application runtime (Laravel)
- Single relational database (PostgreSQL)
- Logical module boundaries enforced in code structure under `app/Modules/`
- Strict module ownership of domain entities and database tables

### Rationale

Compared to microservices, a modular monolith provides:
- simpler deployment
- easier debugging and tracing
- simpler transactions
- lower infrastructure complexity
- faster development velocity

### Future Evolution

The architecture preserves the ability to evolve toward service extraction if required. However, **microservices are explicitly prohibited for version 1.0**. Any decomposition into services requires ADR approval, architectural review, and operational readiness analysis.

---

## AP-03: Clean Architecture with DDD-Lite

DormSys follows a **Clean Architecture layered structure** combined with **lightweight Domain-Driven Design practices** within each module.

### Architectural Layers

#### Domain Layer
Contains pure business logic. Components include Entities, Value Objects, Aggregates, Domain Services, Domain Events, State Machines, and Business Invariants.

Constraints:
- Must not depend on framework components
- Must not access external systems or I/O
- Must remain pure business logic

#### Application Layer
Orchestrates use cases. Components include Application Services, Command Handlers, DTO Mapping, and Transaction Boundaries.

#### Infrastructure Layer
Implements technical concerns: Eloquent Models, Repository Implementations, Queue Workers, Cache Adapters, External Integrations, Storage Adapters.

#### Presentation Layer
Handles external interaction: Livewire Components, Blade Views, REST Controllers, CLI Commands, Scheduled Jobs, Form Requests.

### Dependency Rule

```
Presentation → Application → Domain
Infrastructure → Application → Domain
```

The Domain layer must **never depend on outer layers**.

**Business state transitions must not be implemented in controllers, SQL triggers, UI logic, or background jobs.** All transitions belong in the Domain layer via explicit State Machines.

---

## AP-04: Shared Database with Bounded Module Ownership

DormSys operates on a **single PostgreSQL database**. Logical modularity is preserved through **strict table ownership rules**.

### Module Ownership Rules

Each module:
- owns its database tables exclusively
- is the only component permitted to write to those tables

Other modules may interact only through **Application Services** — never through direct SQL access to another module's tables.

### Cross-Module Data Access

Allowed:
```
Module A → Application Service Interface → Module B
```

Prohibited:
```
Module A → direct SQL query → Module B tables
```

### Foreign Key Policy

Within module boundaries: foreign keys are allowed and encouraged.

Across module boundaries: foreign keys are **prohibited by default**. Cross-module references must use immutable identifier values (e.g., `employee_id` stored in Request without a FK constraint to the Employee table).

**Exception:** Cross-module joins are allowed for **read-only reporting projections** only, and must not modify source data.

---

## AP-05: Explicit State Machines

All domain entities with lifecycle transitions must implement **explicit state machines** using `spatie/laravel-model-states`.

Entities requiring state machines include:
- Request lifecycle
- Lottery Program lifecycle
- Allocation lifecycle
- Check-in/Check-out process

State transitions must be defined in **Domain State Classes**. State transitions must never appear in controllers, SQL, background jobs, or UI components.

### Defined State Machines

**Request States:**
```
Draft → Submitted → PendingDepartmentManager
     → PendingHR → PendingDormitoryManager
     → PendingDormitoryUnit → Approved
     → WaitingForAllocation → Allocated
     → CheckedIn → CheckedOut
     → Rejected | Cancelled | AllocationFailed
```

**Lottery Program States:**
```
Draft → WaitingApproval → Approved
     → RegistrationOpen → RegistrationClosed
     → Locked → Drawn → Completed
     → Cancelled
```

---

## AP-06: Audit Everything

DormSys implements **full operational audit logging** using `spatie/laravel-activitylog`.

All critical events must generate immutable audit records including:
- Request submissions and state changes
- Approval and rejection decisions (with reasons)
- Lottery program creation, execution, and results
- Allocation creation, modification, and cancellation
- Check-in and check-out events
- Room and bed status changes
- Role and permission changes
- Reserve promotions

### Audit Record Fields

| Field | Description |
|---|---|
| `entity_type` | Model class (Request, Allocation, Room, …) |
| `entity_id` | UUID of the subject entity |
| `event` | Action type (created, updated, state_changed, …) |
| `actor_id` | UserID or system identifier |
| `old_values` | JSON snapshot of previous state |
| `new_values` | JSON snapshot of new state |
| `metadata` | Additional context (reason, seed, etc.) |
| `created_at` | UTC timestamp |

### Audit Storage

Audit records are stored in `tbl_AuditLog` (or the activitylog package's `activity_log` table). Records are **append-only and immutable**. UPDATE and DELETE operations on audit tables are prohibited.

All audit writes must occur through the designated **AuditService**. Direct insertion into audit tables is prohibited.

---

## AP-07: Background Processing

Long-running and deferred operations must execute via **Laravel Queue** backed by Redis, monitored by **Laravel Horizon**.

### Primary Background Jobs

| Job | Purpose |
|---|---|
| `ExecuteLotteryDrawJob` | Execute lottery algorithm and auto-allocation |
| `AutoLockLotteryJob` | Automatic lottery lock after deadline |
| `SendNotificationJob` | Dispatch in-app notifications |
| `GenerateReportJob` | Generate Excel/PDF reports |
| `LateCheckOutWarningJob` | Notify of late check-out |
| `PromoteReserveWinnerJob` | Promote next reserve to winner |

The lottery execution job must be **idempotent** and wrapped in a database transaction.

---

## AP-08: Configuration Over Hardcoding

All changeable parameters must be stored in a `settings` table or environment configuration — not hardcoded in application logic.

Examples include: lottery scoring coefficients, group size limits, stay duration limits, approval stage routing policies.

---

# 5. Domain Language Authority

DormSys maintains a **canonical domain vocabulary**. These terms are authoritative and must be used consistently across code, documentation, APIs, UI labels, and database schemas.

| Term | Definition | Owner Module |
|---|---|---|
| Request | Employee accommodation application | Request |
| RequestType | Personal, FamilyDirect, Mission, LotteryRegistration | Request |
| RequestMember | Member of a Mission/Group request | Request |
| Dependent | Family member included in a FamilyDirect request | Employee |
| Approval Workflow | Four-stage approval process | Workflow |
| Allocation | Assignment of a room or bed to an employee | Allocation |
| AllocationMethod | Manual or LotteryAutomatic | Allocation |
| AllocationItem | Individual room/bed record within an Allocation | Allocation |
| AllocationFailed | State when approved request cannot be fulfilled | Allocation |
| Lottery Run | Batch process selecting accommodation winners | Lottery |
| Lottery Program | Defined lottery event with dates, capacity, and scoring config | Lottery |
| Eligible Snapshot | Immutable snapshot of eligible registrations at draw time | Lottery |
| Weighted Score | Lottery ranking score per registration | Lottery |
| Lottery Win Counter | Historical count of lottery wins for penalty calculation | Lottery |
| Penalty | Score reduction applied based on previous wins | Lottery |
| RandomSeed | Deterministic seed used to ensure lottery reproducibility | Lottery |
| Winner | Selected lottery participant | Lottery |
| Reserve | Ranked candidate for winner promotion | Lottery |
| Voucher | Allocation credential issued for external dormitory winners | Voucher |
| Check-In | Occupancy start event (internal dormitories only) | CheckIn |
| Check-Out | Occupancy end event (internal dormitories only) | CheckIn |
| External Dormitory | Third-party accommodation managed via lottery and vouchers only | Allocation |
| AuditLog | Immutable record of all system events | Audit |
| Mission | Organizational assignment justifying a group or direct allocation | Request |
| ScoringConfig | JSON-based configurable formula for lottery score calculation | Lottery |

These terms are **authoritative and must not be reinterpreted**.

---

# 6. Problem Statement

The organization operates multiple dormitories across different cities. Accommodation management currently relies on **manual processes and fragmented coordination**, causing:

- Low transparency in allocation decisions and bed availability
- Frequent conflicts in bed assignment due to manual tracking
- Employee perception of unfair lottery outcomes
- Limited real-time visibility into occupancy levels
- Slow approval processes with manual coordination between departments
- No unified system for tracking requests, approvals, and allocations

DormSys exists to provide **a transparent, auditable, and automated dormitory management system** covering the full lifecycle from request to check-out.

---

# 7. Goals

1. **Process Automation** — Automate the full accommodation lifecycle: request, approval, lottery, allocation, occupancy.
2. **Transparent Lottery** — Provide a deterministic, auditable, and reproducible lottery mechanism.
3. **Allocation Conflict Elimination** — Prevent double booking and overlapping allocations at the system level.
4. **Operational Visibility** — Real-time insights into bed availability, occupancy rates, and allocation status.
5. **Reduced Approval Cycle Time** — Accelerate approvals through automated workflow routing.
6. **Traceability and Accountability** — Immutable audit records for all critical operations.

---

# 8. Scope Definition

## 8.1 In Scope (Version 1)

- **Employee Request Management** — Personal, FamilyDirect, Mission, and LotteryRegistration request types
- **Multi-Stage Approval Workflow** — Four stages: Department Manager → HR Manager → Dormitory Manager → Dormitory Unit Staff
- **Lottery Allocation** — Deterministic scoring, winner selection, reserve list, auto-allocation for internal dormitories, voucher generation for external dormitories
- **Direct Allocation (Mission-Based)** — Bypasses lottery with mandatory justification; subject to approval workflow
- **Room and Bed Allocation** — Bed-based (Personal/Mission) and private-room (FamilyDirect) assignment
- **External Dormitory** — Lottery-only; no room/bed monitoring; winners receive vouchers
- **Occupancy Lifecycle** — Check-in, active occupancy, check-out (internal dormitories only)
- **Audit Logging** — Immutable records for all critical system events
- **Administrative Configuration** — Dormitory buildings, rooms, beds, lottery parameters, approval policies, settings
- **Notifications** — In-app (database-backed) notifications for key lifecycle events
- **Reporting** — Occupancy rates, request distributions, approval metrics, lottery results, usage history

## 8.2 Out of Scope (Version 1)

- Financial management, rent payments, billing
- Facility maintenance and repair ticketing
- IoT integrations (smart locks, sensors)
- Native mobile applications
- Email or SMS notifications
- AI-based allocation recommendations
- Multi-tenant architecture
- Integration with financial or payroll systems

---

# 9. Immutable Business Rules

These rules are **non-negotiable** and must always be enforced at the domain layer. Violating these rules compromises system integrity.

## BR-01 — Request Eligibility
An employee may submit a request only if:
- the employee is currently active
- the employee does not hold an active dormitory allocation
- the employee has no active pending request
- the requested check-in date is not in the past
- the requested check-out date is after the check-in date

## BR-02 — One Person One Allocation
A person may not hold more than **one active allocation** at any time. This constraint applies across all allocation methods: lottery, direct, and external.

## BR-03 — FamilyDirect Room Exclusivity
FamilyDirect requests must be allocated to **private rooms only**. The room must remain exclusive to the family unit for the entire allocation period. Shared beds are not permitted.

## BR-04 — Group Request Size
Mission/Group requests must contain **at least 2 and at most 20 members**. Requests outside this range are invalid. Each group request must designate a **group leader**.

## BR-05 — Allocation Overlap Prevention
Two allocations for the same person must never overlap in time. The domain layer must validate date ranges before confirming any allocation.

## BR-06 — Direct Allocation Justification
Direct allocations must include a **mandatory reason code**: mission assignment, emergency housing, or organizational directive. Direct allocations without a valid reason are prohibited.

## BR-07 — Lottery Penalty
Employees who previously won dormitory lotteries receive a **score penalty** in future lotteries. Penalty magnitude is determined by the `LotteryWinCounter` and the active `ScoringConfig`.

## BR-08 — Lottery Execution Integrity
Before a lottery draw:
- An **Eligible Snapshot** must be captured and frozen
- The snapshot must remain immutable throughout execution
- Results must use a **deterministic RandomSeed** to be reproducible
- Winners are selected in descending score order up to available capacity
- A **Reserve List** must be generated after winner selection
- Overlapping wins (same person winning multiple lotteries) must be resolved before finalization
- Every step must generate audit records
- Once completed, lottery results are **immutable**; corrections require a new Lottery Run

## BR-09 — Lottery Auto-Allocation
After a successful draw:
- **Internal dormitories:** Allocation records and AllocationItems are created automatically (`AllocationMethod = LotteryAutomatic`). Bed and room occupancy is updated atomically.
- **External dormitories:** Voucher codes are generated and archived. No physical allocation or occupancy tracking applies.

## BR-10 — Reserve Promotion
If a winner declines or becomes ineligible, the **next Reserve** is automatically promoted. For internal dormitories, an automatic allocation is created for the promoted reserve. For external dormitories, a voucher is generated.

## BR-11 — Lottery-Based Allocation Immutability
Allocations created by the lottery engine (`AllocationMethod = LotteryAutomatic`) are **read-only**. Manual modification requires explicit override with Dormitory Manager permission and audit record.

## BR-12 — External Dormitory Scope
External dormitory allocations must respect:
- One allocation per person rule (BR-02)
- Occupancy lifecycle tracking via voucher only
- Full audit logging
External dormitories do not expose room or bed identifiers.

## BR-13 — Check-In/Check-Out Scope
Check-In and Check-Out registration applies **only to internal dormitory allocations**. External dormitory lottery winners interact with the system through vouchers only.

## BR-14 — Idempotency
Lottery execution, allocation creation, and approval operations must be **idempotent** or must explicitly prevent duplicate processing.

---

# 10. Non-Functional Requirements

## 10.1 Performance
- API response time < 500ms (95th percentile) for typical requests
- Lottery draw execution < 5 seconds for 1,000 registrations
- Post-lottery automatic allocation < 60 seconds for 100 winners
- Dashboard real-time updates < 10 seconds delay

## 10.2 Security
- Authenticated access required for all operations
- Role-based authorization using Spatie Laravel Permission
- HTTPS enforcement
- Protection against SQL injection, XSS, and privilege escalation
- Sensitive data access must be logged

## 10.3 Availability
- 99.5% uptime during operational hours
- Reliable deployment processes, database backup strategy, monitoring and alerting

## 10.4 Maintainability
- Modular design with clear separation of concerns
- Consistent naming conventions following PSR-12 and Laravel standards
- 100% automated test coverage for Domain layer logic

## 10.5 Observability
- Structured application logs
- Audit logs for compliance
- Laravel Pulse for performance monitoring
- Sentry for error tracking

## 10.6 Data Integrity
- All timestamps stored in UTC
- All database transactions must maintain ACID guarantees
- Lottery execution and allocation creation must be wrapped in transactional boundaries
- Automatic rollback on partial failures

## 10.7 Localization
- Primary UI language: **Persian (Farsi)**
- RTL layout required
- Unicode data handling throughout
- Timezone: Asia/Tehran for display; UTC for storage

## 10.8 Scalability
- Support 1,000 concurrent employees
- Support up to 50 dormitories
- Support up to 5,000 beds across all dormitories
- Handle up to 10,000 lottery registrations per program

---

# 11. Module Boundaries

DormSys is logically partitioned into the following bounded contexts. Each module has exclusive ownership of its domain logic and database tables.

| Module | Responsibility | Owned Tables (prefix: `tbl_`) |
|---|---|---|
| **Identity** | User accounts, authentication, roles, permissions | `Users`, `Roles`, `Permissions`, `RoleUser`, `PermissionRole` |
| **Employee** | Employee profiles, departments, dependents | `Employees`, `Departments` |
| **Request** | Accommodation request lifecycle and members | `Requests`, `RequestMembers`, `Dependents` |
| **Workflow** | Four-stage approval process and decision records | `WorkflowInstances`, `ApprovalLogs` |
| **Dormitory** | Dormitory buildings, rooms, beds, physical status | `Dormitories`, `Rooms`, `Beds` |
| **Allocation** | Bed/room assignments and occupancy records | `Allocations`, `AllocationItems` |
| **Lottery** | Lottery programs, registrations, draws, results | `LotteryPrograms`, `LotteryRegistrations`, `LotteryResults` |
| **Voucher** | Voucher generation and tracking for external dormitories | `Vouchers` |
| **CheckIn** | Check-in and check-out event records | `CheckInOutEvents` |
| **Notification** | In-app notification records and delivery status | `Notifications` |
| **Audit** | Centralized, immutable, append-only event log | `AuditLog` (activity_log) |
| **Report** | Read-only reporting projections (cross-module reads only) | none (projections only) |

### Module Ownership Invariant
No module may write to another module's tables. The Report module is the only module permitted to read across module boundaries, and must never write.

---

# 12. Permission Matrix

Access control is governed by the **Principle of Least Privilege**.

| Role | Submit Request | Approve (Stage) | Execute Lottery | Allocate | Manage Rooms | Check-In/Out | System Config |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Employee | ✅ | — | — | — | — | — | — |
| Dept. Manager | ✅ | Stage 1 | — | — | — | — | — |
| HR Manager | ✅ | Stage 2 | — | — | — | — | — |
| Dormitory Manager | ✅ | Stage 3 | ✅ | ✅ | ✅ | — | ✅ |
| Dormitory Unit Staff | — | Stage 4 | — | ✅ | ✅ | — | — |
| Lottery Operator | — | — | ✅ | — | — | — | — |
| Operator | — | — | — | — | — | ✅ | — |
| Admin | — | — | ✅ | ✅ | ✅ | ✅ | ✅ |

---

# 13. Architecture Constraints

These constraints are **absolute**. No exception may be made without an approved ADR.

1. **No Distributed Transactions.** Cross-module state changes must use Domain Events or Saga patterns within the application layer.
2. **No External Calls in Domain Layer.** Domain objects must be free of I/O, framework dependencies, and infrastructure concerns.
3. **No Business Logic in SQL.** PostgreSQL stored procedures and triggers are prohibited for business logic. The database is restricted to storage and referential integrity constraints.
4. **No Hardcoding.** All environment-specific and configurable values must be injected via configuration or the settings table.
5. **No SPA Frameworks.** React, Vue, Next.js, Nuxt, and equivalent SPA frameworks are prohibited. The UI layer uses Blade + Livewire 3 + Alpine.js only.
6. **No Microservices in v1.** All modules deploy as a single Laravel application.
7. **Idempotency Required.** All state-changing operations must be idempotent or must explicitly prevent duplicate execution.
8. **State Transitions in Domain Only.** All entity state changes must occur through defined State Machine classes.
9. **Audit on All Critical Events.** No critical operation may bypass the AuditService.

---

# 14. Development Standards

- **Coding Standard:** PSR-12 enforced by Laravel Pint
- **Static Analysis:** PHPStan Level 8 via Larastan
- **Documentation:** PHPDoc on all Service classes and business rule methods
- **Testing:** 100% coverage for Domain layer logic using Pest PHP 3
- **Migrations:** All schema changes must be versioned migrations; no direct schema modification
- **Git:** Feature-branch workflow; no direct commits to `main`
- **CI:** GitHub Actions — must pass Pint, PHPStan, and Pest before merge

---

# 15. Governance and Decision Authority

- **Tech Lead:** Final authority on architectural decisions and technology stack choices.
- **Product Owner:** Final authority on business rules and feature prioritization.
- **ADR Process:** Any significant architectural change requires a written Architecture Decision Record presented to and approved by the Tech Lead before implementation.

---

# 16. Agent Operating Protocol

All AI agents contributing to DormSys must:

1. **Read this Constitution** before suggesting any code, architecture, or specification.
2. **Prioritize Domain Language** from Section 5 in all outputs.
3. **Flag Conflicts.** If an instruction contradicts this Constitution, the agent must flag the conflict immediately and halt until resolved.
4. **Adhere to Laravel Standards.** Use Eloquent best practices, FormRequests for validation, Service classes for business logic, and Spatie packages per their defined roles.
5. **Never Invent Rules.** Do not infer or invent constraints not explicitly stated in this document.

---

# 17. Constitution Amendment Policy

This Constitution is **versioned and controlled**. Amendments require:

1. A formal proposal document
2. Review by the Tech Lead and Product Owner
3. Updating the Change Log in this document
4. Incrementing the version number

---

# 18. Risks and Mitigation

| Risk | Mitigation |
|---|---|
| Lottery auto-allocation failure due to concurrent modifications | Database transactions with pessimistic locking; atomic rollback on failure; clear error messaging |
| Data corruption | Strict ACID transactions; audit logging; no direct table writes across modules |
| Module coupling creep | Interface-based cross-module communication; architectural tests enforcing boundaries |
| Workflow stuck state | Explicit state machines; approval history logging; admin override capability |
| Capacity inconsistency | Reconciliation jobs; manual correction interface; audit trail |
| Voucher code collision | Cryptographically secure generation; uniqueness validation; expiration tracking |
| Performance bottleneck | Query optimization; Redis caching; Laravel Horizon for queue management |

---

# 19. Specification Hierarchy

1. **Constitution** (Supreme — this document)
2. **ADR Repository**
3. **Domain Specifications**
4. **API Contracts**
5. **Codebase**

---

# 20. Quality Checklist

Before any implementation artifact is considered complete:

- [ ] Does this conform to all applicable Business Rules (Section 9)?
- [ ] Is the audit log updated for this action?
- [ ] Are module boundaries respected (no cross-module table writes)?
- [ ] Is the state transition handled in the Domain layer via a State Machine?
- [ ] Is the feature covered by Domain layer unit tests?
- [ ] Does the implementation pass PHPStan Level 8?
- [ ] Does the implementation pass Laravel Pint formatting?

---

# 21. Change Log

| Version | Date | Summary |
|---|---|---|
| 1.1.0 | 1405/03/15 | Initial Baseline (FastAPI stack) |
| 1.2.0 | 1405/03/19 | Migration to Laravel Modular Monolith |
| 1.3.0 | 1405/03/31 | Full alignment with ADR-001 stack; discovery document integration; Voucher, CheckIn, Report modules added; extended business rules; SPA prohibition explicit; Operator and Lottery Operator roles added; MinIO and Horizon added to stack |

---

**CONSTITUTION ENDS**
