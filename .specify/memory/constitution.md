# CONSTITUTION v2.0.0 – DormSys Project

## Document Control
- **Version:** 2.0.0
- **Date:** 1405/03/30 (2026-06-20)
- **Status:** Baseline
- **Approval Status:** Approved
- **Approved By:** Product Owner & Tech Lead
- **Previous Version:** 1.1.0
- **Classification:** Internal - Project Governance
- **Architecture:** Laravel 12 + Livewire 3 + PostgreSQL 17 + Redis + Tailwind CSS + Alpine.js

---

## 1. Purpose of This Document
This Constitution is the **supreme governance document** for the DormSys project. It defines immutable business rules, architecture principles, decision authority, and constraints that all agents, developers, and stakeholders must follow. It is **not** a PRD, schema doc, API spec, or implementation guide—those are derivatives governed by this Constitution.

This document serves as the foundation for generating modular specifications and breaking down implementation into logical, atomic tasks.

---

## 2. Project Identity
- **Name:** DormSys (Organizational Dormitory Management System)
- **Type:** Greenfield enterprise application
- **Domain:** Employee accommodation request, allocation, lottery, and lifecycle management
- **Target Users:** Employees, HR managers, department managers, dormitory managers, dormitory unit staff, operators
- **Version:** 2.0.0
- **Status:** Production Baseline
- **Architecture Pattern:** Modular Monolith with Clean Architecture + DDD Lite

---

## 3. You Are Acting As
- **Enterprise Software Architect**
- **Domain Analyst**
- **Laravel Systems Designer**
- **Spec Kit Constitution Author**

Your role is to **document principles and invariants**, not to write PRDs or implementation details. When uncertain, escalate to Decision Authority.

---

## 4. Architecture Principles

### AP-01: Technology Stack (REVISED)
- **Stack:** Laravel 12 + Livewire 3 + PostgreSQL 17 + Redis + Tailwind CSS + Alpine.js
- **Rationale:**
  - **Laravel 12:** Mature PHP framework with strong enterprise features, workflow support, and long-term maintainability
  - **Livewire 3:** Server-first architecture reduces frontend complexity while maintaining interactivity
  - **PostgreSQL 17:** ACID-compliant RDBMS with excellent JSON support, CTEs, and concurrency control
  - **Redis:** High-performance cache and queue backend
  - **Tailwind CSS:** Utility-first styling for rapid UI development
  - **Alpine.js:** Lightweight JavaScript for progressive enhancement
- **Constraint:** All technology choices require an ADR approved by Tech Lead
- **No SPA complexity:** Avoids Vue/React state management overhead; suitable for form-heavy enterprise workflows

### AP-02: Modular Monolith
- **Principle:** Single deployable Laravel application, logically partitioned into bounded modules
- **Module Structure:**
    app/
    Modules/
      Identity/
      Employee/
      Dormitory/
      Request/
      Workflow/
      Allocation/
      Lottery/
      CheckIn/
      Report/
      Notification/
      Audit/
  ```
- **Rationale:** Simplify deployment, avoid distributed system complexity for v1.0
- **Constraint:** No microservices for v1.0; future decomposition requires ADR

### AP-03: Clean Architecture + DDD Lite (Laravel-Adapted)
- **Layers:**
  - **Domain Layer** (`app/Modules/{Module}/Domain/`):
    - Entities, Value Objects, Aggregates
    - Domain Services
    - Domain Events
  - **Application Layer** (`app/Modules/{Module}/Application/`):
    - Use Cases (Actions/Commands)
    - DTOs (Data Transfer Objects)
    - Application Services
  - **Infrastructure Layer** (`app/Modules/{Module}/Infrastructure/`):
    - Eloquent Models (as persistence adapters)
    - Repositories (implementing domain contracts)
    - External integrations
    - Queue Jobs
  - **Presentation Layer** (`app/Modules/{Module}/Presentation/`):
    - Livewire Components
    - HTTP Controllers (for API endpoints if needed)
    - Blade Views
    - Form Requests (validation)
- **Constraint:**
  - Domain layer owns business invariants and domain rules
  - Application layer coordinates use cases and orchestration
  - Infrastructure and Presentation layers depend on inner layers, never the reverse
  - Eloquent models live in Infrastructure layer, not Domain

### AP-04: Shared Database with Bounded Module Ownership (REVISED)
- **Principle:** Single PostgreSQL database, strict module ownership of tables
- **Rules:**
  - Each module owns its tables and is the **sole writer**
  - Cross-module reads via **application services only** (no direct Eloquent queries across module boundaries)
  - **Exception:** Read-only reporting projections may combine data from multiple modules via database views or dedicated read models
  - Cross-module foreign keys are **prohibited by default** and require ADR approval
  - Foreign keys within module boundaries are permitted and encouraged
  - Shared reference data (e.g., settings) exposed via read-only service
  - Cross-module identifiers stored as **immutable value references** (UUID strings) without FK constraints
- **Laravel Implementation:**
  - Use Eloquent relationships cautiously across modules
  - Prefer service layer calls over direct model relationships
  - Database migrations organized per module
- **Rationale:** Preserve logical boundaries while avoiding distributed transaction complexity

### AP-05: Explicit State Machines
- **Principle:** All entity lifecycle transitions modeled as explicit state machines in domain layer
- **Implementation:** Use Laravel state machine packages (e.g., `spatie/laravel-model-states`) or custom state handlers
- **Constraint:** State transition logic must not be scattered in Livewire components or controllers

### AP-06: Audit Everything (REVISED)
- **Principle:** All state transitions, approvals, rejections, allocations, lottery runs recorded in audit log
- **Implementation:**
  - Use `spatie/laravel-activitylog` or custom audit service
  - Audit entries are **append-only** and **immutable**
  - No UPDATE or DELETE operations permitted on audit records
  - Schema enforced via database constraints and Eloquent observers
- **Constraint:** Audit log must capture:
  - `Timestamp` (UTC, stored as PostgreSQL `timestamp with time zone`)
  - `Actor` (UserID or system identifier)
  - `Action` (e.g., APPROVED, REJECTED, ALLOCATED, LOTTERY_RUN)
  - `EntityType` and `EntityID`
  - `PreviousState` and `NewState` (JSON columns)
  - `Metadata` (JSON payload with context-specific details)
- **Laravel Integration:** Use Eloquent events and observers for automatic audit logging

### AP-07: Server-First Architecture (NEW)
- **Principle:** Livewire components handle primary interactivity; Alpine.js for progressive enhancement only
- **Rationale:** Reduce frontend complexity, maintain PHP-centric development, simplify state management
- **Constraint:**
  - No separate API layer for frontend consumption (internal APIs only if needed)
  - Real-time updates via Livewire `wire:poll` or Laravel Echo for critical features
  - Alpine.js reserved for dropdown toggles, modals, and micro-interactions

### AP-08: Queue-Based Processing (NEW)
- **Principle:** Long-running operations (lottery execution, bulk notifications) executed via Redis queues
- **Implementation:** Laravel Queue with Redis driver
- **Constraint:**
  - All queued jobs must be idempotent
  - Failed jobs logged and retried with exponential backoff
  - Critical jobs (lottery execution) monitored via Horizon

### AP-09: Caching Strategy (NEW)
- **Principle:** Redis cache for read-heavy data (dormitory availability, user permissions)
- **Rules:**
  - Cache invalidation via domain events
  - TTL-based expiration as fallback
  - No cache for financial or audit-critical data
- **Laravel Integration:** Use `Cache` facade with Redis driver

---

## 5. Domain Language Authority

| **Term**                  | **Definition**                                                                 | **Owner Module** |
|---------------------------|--------------------------------------------------------------------------------|------------------|
| Request                   | Employee application for dormitory accommodation                               | `request`        |
| Allocation                | Assignment of specific room/bed to approved request                            | `allocation`     |
| Lottery Program           | Scheduled lottery event with configurable scoring and capacity                 | `lottery`        |
| Lottery Registration      | Employee enrollment in specific lottery program                                | `lottery`        |
| Lottery Result            | Outcome record (Winner/Reserve) with allocation or voucher reference           | `lottery`        |
| Eligible Snapshot         | Immutable record of request state at lottery execution time                    | `lottery`        |
| Approval Workflow         | Four-stage manual or automated approval process                                | `workflow`       |
| Weighted Score            | Calculated priority value for lottery ranking                                  | `lottery`        |
| Lottery Win Counter       | Number of lottery wins per employee (configurable reset policy)                | `lottery`        |
| Check-In                  | Physical arrival and occupancy start (internal dormitories only)               | `checkin`        |
| Check-Out                 | Physical departure and occupancy end (internal dormitories only)               | `checkin`        |
| Penalty                   | Score reduction applied for previous wins                                      | `lottery`        |
| RandomSeed                | Deterministic seed value for lottery random component                          | `lottery`        |
| RequestType               | Category: Personal, FamilyDirect, Mission, LotteryRegistration)(```
LotteryRegistration = separate lottery flow
```)                 | `request`        |
| Mission                   | Group request with mission document and member list                            | `request`        |
| MissionMember             | Dependent belongs to request module
                                    | `request`        |
| Dependent                 | Family member included in family request                                       | `request`        |
| Winner                    | Employee whose request was selected in lottery                                 | `lottery`        |
| Reserve                   | Lottery participant eligible for promotion if winner withdraws                 | `lottery`        |
| External Dormitory        | Third-party accommodation not managed in system inventory                      | `dormitory`      |
| Internal Dormitory        | Organization-owned facility with full room/bed tracking                        | `dormitory`      |
| Allocation Method         | Distinguishes Manual vs LotteryAutomatic allocation origin                     | `allocation`     |
| Voucher                   | Confirmation code for external dormitory lottery winners                       | `lottery`        |
| Person (Base)             | Abstract base for Employee and ExternalPerson                                  | `employee`       |
| ExternalPerson            | Non-employee individual participating in mission requests                      | `employee`       |
| AllocationItem            | Specific bed or room assignment within an allocation                           | `allocation`     |
| Out-of-Service            | Physical status indicating bed/room temporarily unavailable                    | `dormitory`      |

All domain terms must be used consistently across code, documentation, APIs, and UI.

---

## 6. Problem Statement & Goals

### Problem
Manual, error-prone dormitory request and allocation process leads to:
- Low transparency in occupancy status
- Allocation conflicts and double-booking
- Perceived unfairness in lottery
- Poor resource utilization
- Long approval cycles
- Lack of auditability

### Goals
1. Automate request submission, approval workflow, and allocation
2. Implement transparent, auditable lottery system with automatic allocation
3. Eliminate double-booking via real-time occupancy tracking
4. Provide real-time visibility into bed availability
5. Reduce average approval time through configurable auto-approval
6. Ensure fairness and traceability via comprehensive audit logging
7. Support both internal (full tracking) and external (voucher-only) dormitories

---

## 7. Scope Definition

### In Scope (Initial Release)
- Employee request submission (same-day allowed, no advance timing constraint)
- Four-stage approval workflow (default manual, configurable auto-approval per stage)
- Lottery system with weighted scoring and configurable formula
- **Automatic allocation for internal dormitory lottery winners**
- **Voucher generation for external dormitory lottery winners**
- Direct mission-based allocation (bypass lottery)
- Room and bed allocation with overlap detection
- Check-in/check-out tracking (internal dormitories only)
- External dormitory handling (lottery-only, no physical monitoring)
- Audit logging with immutable records
- Role-based access control (RBAC)
- In-app notification system
- Basic reporting and occupancy monitoring
- Out-of-service room/bed management

### Out of Scope (v1.0)
- Payment processing
- Meal management
- Guest pass issuance
- Native mobile application
- Third-party integrations (LDAP, SSO)
- Advanced analytics / BI dashboards
- Email/SMS notifications (in-app only for MVP)
- Multi-tenant architecture
- AI-based allocation recommendations

---

## 8. Immutable Business Rules

### BR-01: Request Eligibility
- **Rule:** Any employee can submit a request for any future date range
- **Constraint:** No minimum advance notice required (same-day requests allowed)
- **Implementation:** Validation in `Request` domain entity and `SubmitRequestAction`
- **Owner:** `request` module

### BR-02: Allocation Constraints
- **Rule:** One person may be assigned one allocation (bed or room) at any given time
- **Allocation modes:**
  - **Bed-based:** Individual bed assignment within shared room
  - **Room-based:** Exclusive room assignment (entire room allocated)
- **Constraint:** System must enforce no overlapping allocations for the same person across date ranges
- **Penalty:** Violation = incident; must be zero in production
- **Implementation:**
  - Overlap detection in `AllocationService`
  - Database unique constraint on `(person_id, date_range)` using PostgreSQL exclusion constraint with `daterange`
- **Owner:** `allocation` module

### BR-03: Allocation Rules

#### BR-03.1: Family Request Allocation
- **Rule:** Family requests must always receive a private room (room-based allocation)
- **Constraint:** System must not assign family requests to shared beds
- **Implementation:** Validation in `AllocateFamilyRequestAction`
- **Owner:** `allocation` module

#### BR-03.2: Private Room Exclusivity
- **Rule:** A room allocated as "private" cannot be assigned to any other request during the allocation period
- **Constraint:** Enforced via allocation conflict detection
- **Implementation:** Room availability query excludes rooms with active room-based allocations
- **Owner:** `allocation` module

#### BR-03.3: Group Request Size Limit
- **Rule:** Maximum 20 people in a mission request
- **Constraint:** Validation enforced at request submission
- **Implementation:** `MissionRequest` value object validation
- **Owner:** `request` module

#### BR-03.4: Overlap Detection in Direct Allocation
- **Rule:** Overlap must be checked when dormitory manager performs direct allocation
- **Constraint:** Direct allocation cannot create overlapping assignments for any person
- **Implementation:** `CheckAllocationOverlapService` called before allocation creation
- **Owner:** `allocation` module

#### BR-03.5: Direct Allocation Requires Reason
- **Rule:** Direct allocation by dormitory manager must include a documented reason
- **Constraint:** `AllocationReason` field is mandatory for allocations not originating from lottery (`allocation_method = 'Manual'`)
- **Implementation:** Validation in `CreateManualAllocationAction`
- **Owner:** `allocation` module

### BR-04: Lottery Penalty Rule
- **Rule:** Each lottery win reduces the employee's score for subsequent lotteries
- **Formula (conceptual):** `Score = BaseScore - (WinCount × PenaltyWeight) + RandomBoost`
- **Rationale:** Promote fairness by rotating access
- **Implementation:**
  - Exact formula parameters stored in `settings` table
  - Formula logic in `LotteryScoringEngine` application service
  - Formula must not be hardcoded
- **Owner:** `lottery` module
- **Constraint:** Formula modification requires ADR and Product Owner approval

### BR-05: Mission Allocation Priority
- **Rule:** Approved mission-based requests bypass lottery and receive direct allocation
- **Process:**
  1. Mission request approved through workflow
  2. `DormitoryManager` performs direct allocation via `allocation` module
  3. Allocation reason: "Mission: [mission_document_reference]"
- **Constraint:** Mission allocation must be audited with justification
- **Owner:** `allocation` module (receives approved mission request from `request` module)

### BR-06: Lottery Execution Rules

#### BR-06.1: Eligibility Criteria
- **Rule:** Only lottery registrations with status `Approved` and within the lottery program schedule are eligible
- **Immutable Snapshot:** At lottery execution, system creates `LotteryResult` records capturing:
  - `lottery_program_id`
  - `registration_id`
  - `employee_id`
  - `raw_weight` (calculated base score)
  - `normalized_weight`
  - `random_noise` (derived from `LotteryProgram.random_seed`)
  - `weighted_score` (final ranking value)
  - `result_type` (Winner/Reserve)
  - `draw_order`
- **Implementation:** `ExecuteLotteryAction` creates immutable snapshot entities
- **Owner:** `lottery` module

#### BR-06.2: Deterministic Scoring
- **Rule:** Lottery scoring must be reproducible given the same inputs and `random_seed`
- **Implementation:**
  - Each `LotteryProgram` entity stores a `random_seed` value (UUID or integer)
  - `random_noise` component for each registration derived deterministically:
    - `random_noise = PRNG(random_seed, registration_id)`
  - PRNG algorithm: PHP's `mt_rand()` seeded with `hash('sha256', random_seed . registration_id)`
  - Formula: `weighted_score = normalized_weight + random_noise`
- **Constraint:** PRNG algorithm documented in ADR and remains stable across lottery runs
- **Rationale:** Enable forensic analysis and dispute resolution by re-running lottery with same seed
- **Owner:** `lottery` module

#### BR-06.3: Winner Selection
- **Rule:** Winners chosen in descending order of `weighted_score`
- **Tie Breaking:** If two registrations have identical `weighted_score` after rounding:
  1. Higher `raw_weight` wins
  2. If still tied, earlier `registration_created_at` timestamp wins
  3. If still tied (edge case), lower `registration_id` (UUID lexicographic order) wins
- **Persistence:** Each winner recorded in `lottery_results` table with `result_type = 'Winner'`
- **Automatic Allocation (Internal Dormitories):**
  - If `lottery_program.dormitory_type = 'Internal'` and `auto_allocate = true`:
    1. Create `Allocation` record with `allocation_method = 'LotteryAutomatic'`
    2. Create `AllocationItem` records for assigned beds/rooms
    3. Create AllocationItem records; occupancy is derived from active allocations over a date range.
    4. Link `lottery_result.allocation_id` to created allocation
  - Transaction must be atomic; rollback on failure
- **Voucher Generation (External Dormitories):**
  - If `lottery_program.dormitory_type = 'External'`:
    1. Generate unique `voucher_code` (e.g., `EXT-{lottery_id}-{winner_order}`)
    2. Store in `lottery_result.voucher_code`
    3. No allocation or physical resource tracking
- **Owner:** `lottery` module

#### BR-06.4: Lottery Lock
- **Rule:** After lottery program status changes to `RegistrationClosed`, new registrations are forbidden
- **Implementation:** `LotteryProgram` state machine transition guards
- **Owner:** `lottery` module

#### BR-06.5: Reserve Winner Replacement
- **Rule:** If a main winner withdraws or cancels, first reserve automatically promoted
- **Process:**
  1. Winner cancellation triggers `PromoteReserveAction`
  2. Next reserve (by `draw_order`) promoted to winner
  3. If internal dormitory: automatic allocation created
  4. If external dormitory: voucher code generated
  5. Notification sent to promoted reserve
- **Constraint:** Replacement must be audited with reason
- **Owner:** `lottery` module

#### BR-06.6: Cross-Dormitory Overlap
- **Rule:** Overlap between different dormitories in lottery is not checked during registration
- **Rationale:** Each dormitory operates independently for lottery purposes
- **Note:** Final allocation overlap detection still enforced by `allocation` module
- **Owner:** `lottery` module

#### BR-06.7: Audit Requirement
- **Rule:** Every lottery run logs:
  - Snapshot of eligible registrations (`lottery_results` records)
  - Selected winners and reserves
  - `random_seed` used
  - Execution timestamp
  - Actor (user or system)
  - Allocation creation (for internal dormitories)
  - Voucher generation (for external dormitories)
- **Implementation:** Automatic via `AuditService` and Eloquent observers
- **Owner:** `lottery` module + `audit` module

### BR-07: External Dormitory Rules
- **Rule:** External dormitory allocations do not consume internal bed inventory
- **Constraints:**
  - **Lottery-only** functionality
  - **No monitoring:** Rooms/Beds not tracked in system
  - **No Check-In/Out** capability
- **Winner Process:**
  - Winners receive voucher codes via notification
  - Voucher code stored in `lottery_result.voucher_code`
  - Accessible via employee dashboard
  - Valid for lottery program check-in/check-out period only
- **Owner:** `dormitory` module (dormitory type), `lottery` module (voucher generation)

### BR-08: Four-Stage Approval Workflow
- **State Machine:**
  ```
  Draft → Submitted →
    → [DeptMgr approval] → PendingHR →
    → [HRMgr approval] → PendingDormitoryManager →
    → [DormMgr approval] → PendingDormitoryUnit →
    → [DormUnit approval] → Approved → WaitingForAllocation
  
  Side transitions from any pending state:
    → Rejected (with reason, terminal state)
    → Cancelled (by employee in Draft/Submitted only)
  ```
- **Rules:**
  - **Default mode:** Manual approval at each stage
  - **Configurable auto-approval:** System supports conditional auto-approval per stage
    - Auto-approval rules based on business logic (e.g., request duration < 3 days, employee base score > threshold)
    - Auto-approval toggle per stage stored in `settings` table
    - Auto-approval events logged in `audit_logs` with `actor_type = 'System'`
  - All transitions (manual or auto) audited
  - Rejection requires non-null `rejection_reason`
  - Approved requests transition to `WaitingForAllocation` status
  - **Post-APPROVED modification:**
    - `HRManager` can modify or cancel requests in `Approved` or `WaitingForAllocation` status
    - Employee must be notified of any modification
    - Modified/cancelled requests **do not decrement lottery win counter**
    - Modification events must be audited
- **Implementation:**
  - `RequestApproval` state machine using `spatie/laravel-model-states`
  - `ApprovalWorkflowService` coordinates stage transitions
  - Auto-approval rules evaluated in `ProcessApprovalAction`
- **Owner:** `workflow` module

### BR-09: Lottery Win Counter Policy
- **Default policy:** Employee lottery win counter **does NOT reset** automatically at fiscal year boundary
- **Rationale:** Preserve historical win data for fairness analysis and long-term scoring
- **Configurable reset:** `settings` table may include `lottery_win_counter_reset_enabled` flag
  - If enabled, reset happens at fiscal year boundary via scheduled job
  - Reset event must be audited
- **Implementation:** `ResetLotteryWinCountersJob` scheduled annually
- **Owner:** `lottery` module

### BR-10: Group Request Authority
- **Rule:** Mission requests can only be registered by the authorized mission lead
- **Constraint:** Mission lead must be an employee with valid `employee_id`
- **Validation:** `CreateMissionRequestAction` verifies lead authorization
- **Owner:** `request` module

### BR-11: Stay Duration Flexibility
- **Rule:** Employee stay may end early or be extended beyond originally allocated period
- **Process:**
  - Early departure: `Operator` performs early check-out via `checkin` module
  - Extension: Requires new request or allocation modification (subject to availability)
- **Constraint:** All modifications must be audited
- **Owner:** `checkin` module (early departure), `allocation` module (extension)

### BR-12: Out-of-Service Management
- **Rule:** Rooms and beds can be marked `OutOfService` by `DormitoryUnitManager`
- **Constraints:**
  - `out_of_service_reason` and `out_of_service_at` timestamp required
  - `OutOfService` rooms/beds excluded from allocation queries
  - If active allocation exists on resource being marked `OutOfService`:
    - System shows warning
    - Requires re-allocation or cancellation approval
- **Return to Active:**
  - `DormitoryUnitManager` can return status to `Active`
  - Reason and timestamp recorded
- **Implementation:** `MarkOutOfServiceAction`, `ReturnToActiveAction`
- **Owner:** `dormitory` module

### BR-13: Check-In/Out Rules (Internal Dormitories Only)
- **Check-In Registration:**
  - Only `Operator` role can register check-in
  - Check-in allowed on or after `allocation.check_in_date`
  - Early check-in requires approval
  - Allocation status transitions: `Allocated` → `CheckedIn`
- **Check-Out Registration:**
  - Only `Operator` role can register check-out
  - Check-out allowed anytime after check-in
  - Late check-out shows warning but is allowed
  - Allocation status transitions: `CheckedIn` → `CheckedOut`
  - Bed/room inventory freed upon check-out
- **Restrictions for External Dormitories:**
  - Check-in/check-out **not applicable**
  - External lottery winners receive vouchers only
- **Implementation:** `RegisterCheckInAction`, `RegisterCheckOutAction`
- **Owner:** `checkin` module

---

## 9. Non-Functional Requirements

### NFR-01: Performance (NFR-P01)
- **Targets:**
  - API response time (p95): < 500ms
  - Livewire component render time: < 200ms
  - Dashboard occupancy update: < 10 seconds (via polling or Echo)
  - Lottery execution for 1000 registrations: < 30 seconds
  - Automatic allocation creation post-lottery: < 1 minute for 100 winners
- **Implementation:**
  - Database query optimization with proper indexing
  - Eager loading for Eloquent relationships
  - Redis caching for read-heavy data
  - Queue jobs for long-running operations

### NFR-02: Security (NFR-S01)
- **Requirements:**
  - Authentication via organizational username/password (Laravel Sanctum or Fortify)
  - Role-based authorization enforced at controller and Livewire component level
  - CSRF protection enabled for all forms
  - SQL injection prevention via Eloquent query builder
  - XSS prevention via Blade escaping
  - No hardcoded credentials
  - Audit log access restricted to `Admin` and `SystemAuditor` roles
  - HTTPS enforcement in production
- **Implementation:**
  - Laravel middleware: `auth`, custom RBAC middleware
  - Form Request validation for all inputs
  - Spatie Permission package for RBAC

### NFR-03: Availability (NFR-A01)
- **Target uptime:** 99% during business hours (8 AM - 8 PM local time)
- **Graceful degradation:**
  - Non-critical features (reports) degraded before core workflow
  - Queue worker failures logged and alerted
- **Monitoring:**
  - Laravel Telescope for local development
  - Sentry for production error tracking
  - Laravel Horizon for queue monitoring
- **Backup:**
  - Daily PostgreSQL backups with 30-day retention
  - Transaction logs for point-in-time recovery

### NFR-04: Maintainability (NFR-M01)
- **Test coverage:** > 80% for domain and application layers
- **Testing strategy:**
  - PHPUnit for unit tests (domain entities, value objects)
  - Pest for feature tests (use cases, workflows)
  - Livewire testing for component behavior
  - Laravel Dusk for critical E2E flows (optional)
- **Code review:** Mandatory for all pull requests
- **Documentation:**
  - PHPDoc for all public methods
  - Architecture Decision Records (ADRs) for major decisions
  - Module README files

### NFR-05: Observability
- **Logging:**
  - Structured logging via Laravel Log facade (JSON format in production)
  - Log levels: DEBUG (local), INFO (staging), WARNING/ERROR (production)
  - Contextual logging with request ID, user ID, module name
- **Monitoring:**
  - Health check endpoint: `/api/health`
  - Metrics instrumentation for success criteria (Section 10)
  - Laravel Pulse for application metrics
- **Alerting:**
  - Queue job failures
  - Lottery execution errors
  - Allocation conflict incidents

### NFR-06: Data Retention (NFR-D01, NFR-D02)
- **Rule:** System must comply with organizational data retention policy
- **Retention periods:**
  - Allocation history: subject to organizational policy (configurable via `settings`)
  - Personal data: complies with GDPR and local privacy regulations
  - Audit logs: retained per organizational audit retention requirements
- **Configuration:** `settings` table includes `data_retention_policy_days` parameter
- **Privacy right requests:**
  - GDPR Article 17 (right to erasure): anonymization where legal retention obligations exist
  - Deletion requests follow organizational privacy policy with Legal/Compliance approval
  - Audit trail of anonymization/deletion maintained
- **Implementation:**
  - Scheduled job: `PruneOldDataJob` runs monthly
  - Anonymization service: replaces PII with hashed values

### NFR-07: Localization (NFR-U01)
- **Language:** Persian (Farsi) UI
- **Layout:** RTL (right-to-left) support via Tailwind CSS RTL plugin
- **Date/Time:**
  - Display: Jalali calendar (primary), Gregorian (secondary)
  - Storage: UTC timestamps in PostgreSQL `timestamp with time zone`
  - Conversion: `morilog/jalali` package for Laravel
- **Number formatting:** Persian numerals in UI (optional)

### NFR-08: Data Integrity (NFR-D02)
- **Database:**
  - PostgreSQL 17 with ACID compliance
  - Referential integrity enforced via foreign keys within module boundaries
  - Exclusion constraints for allocation overlap prevention
- **Timestamps:**
  - All timestamps stored in UTC
  - Timezone conversion handled at presentation layer
- **Transactions:**
  - Critical operations (allocation creation, lottery execution) wrapped in database transactions
  - Rollback on failure with error logging

---

## 10. Success Criteria & Monitoring

| **Metric**                     | **Target**       | **Owner Module** | **Measurement Method**                          | **Dashboard** |
|--------------------------------|------------------|------------------|-------------------------------------------------|---------------|
| Bed occupancy rate             | > 75%            | `report`         | Daily: `allocated_beds / available_beds`        | Admin Dashboard |
| Lottery fairness (Gini)        | < 0.3            | `lottery`        | Yearly: distribution of wins across employees   | Lottery Report |
| System adoption rate           | > 95%            | `report`         | Quarterly: `employees_with_requests / total_eligible_employees` | HR Dashboard |
| Allocation conflict incidents  | = 0              | `allocation`     | Per quarter: count of overlapping allocations   | Alert System |
| Average approval time          | < 2 days         | `workflow`       | Median duration: `submitted_at` → `approved_at` | Workflow Report |
| Available beds in service      | Tracked          | `dormitory`      | Daily count: `beds.physical_status = 'Active' AND occupancy_status = 'Available'` | Occupancy Dashboard |
| Lottery execution time         | < 30 seconds     | `lottery`        | Execution duration for lottery draw job          | Queue Monitor |
| Auto-allocation success rate   | > 99%            | `allocation`     | Successful automatic allocations / total lottery winners | Lottery Report |

**Implementation:**
- Metrics collected via scheduled jobs and stored in `metrics` table
- Dashboards built with Livewire components and Chart.js
- Alerts configured in `notification` module

---

## 11. Module Boundaries | **Module** | **Owned Entities** | **Invariants** | **Application Services** | **Laravel Structure** | app/Modules/Identity/
 | `| `employee` | Employee, ExternalPerson, Dependent, Person (base) | Employee uniqueness, dependent relationships | EmployeeService, DependentService | `app/Modules/Employee/` |

| `dormitory` | Dormitory, Building, Floor, Room, Bed | Capacity limits, Out-of-Service rules | DormitoryService, InventoryService | `app/Modules/Dormitory/` |

| `request` | Request, MissionRequest, MissionMember, FamilyRequest | Request type validation, mission size limits | SubmitRequestAction, CancelRequestAction | `app/Modules/Request/` |

| `workflow` | RequestApproval, ApprovalStage, ApprovalHistory | Four-stage sequence, state machine transitions | ApprovalWorkflowService, AutoApprovalEngine | `app/Modules/Workflow/` |

| `lottery` | LotteryProgram, LotteryRegistration, LotteryResult | Scoring formula, deterministic randomness, BR-06 | ExecuteLotteryAction, LotteryScoringEngine | `app/Modules/Lottery/` |

| `allocation` | Allocation, AllocationItem | Overlap prevention, BR-02, BR-03 | AllocateRequestAction, CheckOverlapService | `app/Modules/Allocation/` |

| `checkin` | CheckIn, CheckOut | Check-in/out sequence, internal dormitories only | RegisterCheckInAction, RegisterCheckOutAction| `app/Modules/CheckIn/` |

| `notification` | Notification, NotificationTemplate | Delivery tracking, read receipts | NotificationService, SendNotificationAction | `app/Modules/Notification/` |

| `audit` | AuditLog | Immutability, append-only | AuditService | `app/Modules/Audit/` |

| `report` | (Read models, projections) | Derived from other modules | OccupancyReportService, MetricsService | `app/Modules/Report/` |app/Modules/Identity/

| `employee`      | Employee, ExternalPerson, Dependent, Person (base)    | Employee uniqueness, dependent relationships         | EmployeeService, DependentService           | `app/Modules/Employee/`             |
| `dormitory`     | Dormitory, Building, Floor, Room, Bed                 | Capacity limits, Out-of-Service rules                | DormitoryService, InventoryService          | `app/Modules/Dormitory/`            |
| `request`       | Request, MissionRequest, MissionMember, FamilyRequest | Request type validation, mission size limits         | SubmitRequestAction, CancelRequestAction    | `app/Modules/Request/`              |
| `workflow`      | RequestApproval, ApprovalStage, ApprovalHistory       | Four-stage sequence, state machine transitions       | ApprovalWorkflowService, AutoApprovalEngine | `app/Modules/Workflow/`             |
| `lottery`       | LotteryProgram, LotteryRegistration, LotteryResult    | Scoring formula, deterministic randomness, BR-06     | ExecuteLotteryAction, LotteryScoringEngine  | `app/Modules/Lottery/`              |
| `allocation`    | Allocation, AllocationItem                            | Overlap prevention, BR-02, BR-03                     | AllocateRequestAction, CheckOverlapService  | `app/Modules/Allocation/`           |
| `checkin`       | CheckIn, CheckOut                                     | Check-in/out sequence, internal dormitories only     | RegisterCheckInAction, RegisterCheckOutAction| `app/Modules/CheckIn/`              |
| `notification`  | Notification, NotificationTemplate                    | Delivery tracking, read receipts                     | NotificationService, SendNotificationAction | `app/Modules/Notification/`         |
| `audit`         | AuditLog                                              | Immutability, append-only                            | AuditService                                | `app/Modules/Audit/`                |
| `report`        | (Read models, projections)                            | Derived from other modules                           | OccupancyReportService, MetricsService      | `app/Modules/Report/`               |

**Cross-Module Communication Rules:**
- Use **Application Services** for cross-module interactions
- Prefer **Domain Events** for asynchronous decoupling (e.g., `AllocationCreated` event triggers notification)
- No direct Eloquent model access across modules
- Use **DTOs** for data transfer between modules
- Repository pattern isolates domain from infrastructure

---

## 12. Role-Based Access Control (RBAC)

| **Role**                  | **Permissions**                                                                                  | **Module Access**                          |
|---------------------------|--------------------------------------------------------------------------------------------------|--------------------------------------------|
| `Employee`                | Submit requests, view own requests, view own allocation, check-in/out status, lottery registration | `request`, `lottery`, `allocation`, `checkin` (read-only) |
| `DepartmentManager`       | Approve/reject own department requests (Stage 1)                                                  | `workflow` (Stage 1)                       |
| `HRManager`               | Approve/reject all requests (Stage 2), modify approved requests, manage employees                 | `workflow` (Stage 2), `employee`, `request` (modify) |
| `DormitoryManager`        | Approve/reject requests (Stage 3), create manual allocations, view all allocations                | `workflow` (Stage 3), `allocation`, `dormitory` (read) |
| `DormitoryUnitManager`    | Approve/reject requests (Stage 4), manage inventory, mark Out-of-Service, view occupancy          | `workflow` (Stage 4), `dormitory`, `allocation` (read) |
| `Operator`                | Register check-ins/check-outs, view daily occupancy                                               | `checkin`, `allocation` (read)             |
| `SystemAuditor`           | Read-only access to audit logs, reports, metrics                                                  | `audit`, `report`                          |
| `Admin`                   | Full system access, manage users/roles, configure settings, run lottery                           | All modules                                |

**Implementation:**
- Laravel middleware: `role:Employee,HRManager` or `permission:approve-requests`
- Spatie Permission package
- Role assignment via `identity` module
- Policy classes per module for fine-grained authorization

---

## 13. Decision Authority

| **Decision Type**                        | **Authority**                  | **Escalation Path**            | **Documentation Required** |
|------------------------------------------|--------------------------------|--------------------------------|----------------------------|
| Business rule change                     | Product Owner                  | Steering Committee             | ADR + Constitution update  |
| Technology stack modification            | Tech Lead                      | Product Owner                  | ADR                        |
| Module boundary change                   | Architect + Tech Lead          | Product Owner                  | ADR + Constitution update  |
| Performance optimization trade-off       | Tech Lead                      | Product Owner (if cost impact) | ADR                        |
| Security policy exception                | Security Lead + Product Owner  | Compliance Officer             | Security ADR               |
| Data retention policy change             | Legal/Compliance + Product Owner | Steering Committee           | Policy Document + ADR      |
| Lottery formula modification             | Product Owner + Domain Expert  | Steering Committee             | ADR + Constitution update  |
| Database schema modification             | Tech Lead + Architect          | Product Owner (if breaking)    | Migration + ADR            |
| API contract breaking change             | Tech Lead + Product Owner      | Stakeholders                   | API versioning + ADR       |
| Out-of-scope feature request             | Product Owner                  | Steering Committee             | Backlog item + impact analysis |

**Escalation Process:**
1. Technical blocker → Tech Lead
2. Business ambiguity → Product Owner
3. Policy conflict → Legal/Compliance
4. Resource constraint → Project Manager
5. Unresolved conflict → Steering Committee

---

## 14. Change Management

### Constitutional Changes
- **Trigger:** Business rule modification, architecture principle change, scope adjustment
- **Process:**
  1. Proposal via GitHub issue with `constitution-change` label
  2. Impact analysis by Architect
  3. Review by Tech Lead and Product Owner
  4. Approval by Steering Committee (if major change)
  5. Version increment (major.minor.patch)
  6. Update all derived specifications (ADRs, module specs)
- **Versioning:**
  - **Major:** Breaking changes to business rules or architecture
  - **Minor:** Additive changes (new modules, non-breaking rules)
  - **Patch:** Clarifications, typos, formatting

### Derived Document Synchronization
- ADRs reference specific Constitution version
- Module specifications inherit rules from Constitution
- On Constitution update, affected specifications flagged for review
- Tooling: Git tags for Constitution versions, references in ADR frontmatter

---

## 15. Quality Gates

### Definition of Done (DoD)
A task is complete when:
1. **Code:** Passes static analysis (PHPStan level 8, Laravel Pint)
2. **Tests:** >80% coverage for domain/application layers, all tests green
3. **Documentation:** PHPDoc complete, ADR written (if architectural decision)
4. **Review:** Approved by at least one peer reviewer
5. **Audit:** State transitions logged via `AuditService`
6. **Migration:** Database changes include rollback script
7. **Security:** No new vulnerabilities reported by security scanner

### Acceptance Criteria Template
For each user story/task:
- **Given:** Preconditions and system state
- **When:** Action or event
- **Then:** Expected outcome with verifiable metrics
- **Constraint:** Business rules enforced
- **Audit:** Expected audit log entries

**Example:**
```gherkin
Feature: Execute Lottery
  Given a LotteryProgram with status "RegistrationClosed"
    And 100 approved LotteryRegistrations
    And LotteryProgram.reserve_capacity = 20
  When Admin triggers "Execute Lottery"
  Then 20 LotteryResults created with result_type="Winner"
    And 80 LotteryResults created with result_type="Reserve"
    And 20 Allocations created (if internal dormitory)
    And 20 Vouchers generated (if external dormitory)
    And AuditLog entry created with action="LOTTERY_EXECUTED"
    And Execution time < 30 seconds
```

---

## 16. Risk Register

| **Risk ID** | **Description**                              | **Probability** | **Impact** | **Mitigation**                                      | **Owner**      |
|-------------|----------------------------------------------|-----------------|------------|-----------------------------------------------------|----------------|
| R-01        | Lottery algorithm produces unfair results    | Low             | High       | Deterministic seeding, Gini coefficient monitoring  | `lottery`      |
| R-02        | Database corruption during allocation        | Low             | Critical   | Transactional integrity, daily backups              | `allocation`   |
| R-03        | Performance degradation under load           | Medium          | Medium     | Load testing, Redis caching, query optimization     | Tech Lead      |
| R-04        | Security breach exposing employee data       | Low             | Critical   | HTTPS, RBAC, audit logging, security scanning       | Security Lead  |
| R-05        | Scope creep delaying v1.0                    | High            | High       | Strict adherence to Constitution scope definition   | Product Owner  |
| R-06        | Key personnel turnover                       | Medium          | High       | Knowledge documentation, pair programming           | Project Manager|
| R-07        | Regulatory compliance failure                | Low             | Critical   | Legal review of data retention and privacy policies | Legal/Compliance|
| R-08        | Allocation overlap due to race condition     | Low             | High       | Database exclusion constraints, pessimistic locking | `allocation`   |

---

## 17. Testing Strategy

### Unit Tests (PHPUnit/Pest)
- **Target:** Domain entities, value objects, domain services
- **Coverage:** >90%
- **Example:** `LotteryScoringEngine` deterministic output verification

### Feature Tests (Pest)
- **Target:** Use cases, application services, workflows
- **Coverage:** >80%
- **Example:** End-to-end approval workflow simulation

### Integration Tests
- **Target:** Database interactions, repository implementations
- **Example:** Allocation overlap constraint enforcement

### Livewire Component Tests
- **Target:** Component rendering, user interactions, state management
- **Example:** Request submission form validation

### E2E Tests (Optional - Laravel Dusk)
- **Target:** Critical user journeys
- **Example:** Employee submits request → Approval → Allocation → Check-in

### Performance Tests
- **Tool:** JMeter or k6
- **Scenarios:**
  - 1000 concurrent users viewing occupancy dashboard
  - Lottery execution with 5000 registrations

---

## 18. Deployment & DevOps

### Environments
- **Local:** Developer machines (Laravel Sail)
- **Staging:** Pre-production mirror (same stack as production)
- **Production:** Live system

### CI/CD Pipeline
1. **Commit:** Trigger GitHub Actions
2. **Build:** Install dependencies, run static analysis
3. **Test:** Execute test suite
4. **Security Scan:** Check vulnerabilities
5. **Deploy (Staging):** Automatic on `develop` branch
6. **Manual Approval:** Required for production deployment
7. **Deploy (Production):** Blue-green deployment with rollback capability

### Infrastructure
- **Hosting:** Cloud VPS or managed Laravel hosting (e.g., Laravel Forge, Ploi)
- **Database:** Managed PostgreSQL 17 instance
- **Cache/Queue:** Managed Redis instance
- **Storage:** S3-compatible object storage for file uploads
- **Monitoring:** Sentry, Laravel Telescope (staging only)

### Zero-Downtime Deployment
- Database migrations run before code deployment
- Queue workers gracefully restarted
- Health check endpoint monitored during deployment

---

## 19. Glossary

| **Term**               | **Definition**                                                                 |
|------------------------|--------------------------------------------------------------------------------|
| ADR                    | Architecture Decision Record - documents significant architecture choices      |
| Aggregate              | Cluster of domain objects treated as a single unit for data changes            |
| DDD Lite               | Simplified Domain-Driven Design adapted for Laravel                            |
| DTO                    | Data Transfer Object - immutable value container for cross-layer communication |
| Exclusion Constraint   | PostgreSQL constraint preventing overlapping ranges                            |
| Gini Coefficient       | Statistical measure of distribution inequality (0 = perfect equality)          |
| Immutable              | Cannot be modified after creation                                              |
| Modular Monolith       | Single application with logical module boundaries                              |
| PRNG                   | Pseudo-Random Number Generator                                                 |
| RBAC                   | Role-Based Access Control                                                      |
| State Machine          | Formal model of state transitions with guards and actions                      |
| UTC                    | Coordinated Universal Time - standard for timestamp storage                    |
| Value Object           | Immutable domain concept identified by its attributes (e.g., DateRange)        |

---

## 20. Appendices

### A. Related Documents
- **Product Requirements Document (PRD):** Detailed feature specifications
- **API Specification:** RESTful endpoints and contracts (if needed)
- **Database Schema:** ER diagrams and table definitions
- **ADR Repository:** `/docs/adr/` directory
- **User Manuals:** End-user documentation

### B. Revision History

| **Version** | **Date**       | **Changes**                                          | **Author**       |
|-------------|----------------|------------------------------------------------------|------------------|
| 1.0.0       | 1405/01/15     | Initial Constitution                                 | Architect        |
| 1.1.0       | 1405/02/20     | Added External Dormitory support                     | Architect        |
| 2.0.0       | 1405/03/30     | Laravel 12 stack, BR-06 revision, RBAC expansion     | Architect        |

### C. Contact Information
- **Product Owner:** [Name/Email]
- **Tech Lead:** [Name/Email]
- **Architect:** [Name/Email]
- **Security Lead:** [Name/Email]
- **Legal/Compliance:** [Name/Email]

---

## 21. Acknowledgments
This Constitution framework adapted from:
- Domain-Driven Design (Eric Evans)
- Clean Architecture (Robert C. Martin)
- Modular Monolith Best Practices
- Laravel Documentation (Laravel 12)
- Livewire Official Docs (Livewire 3)

---

**END OF CONSTITUTION v2.0.0**

---

## Signature Block

**Approved By:**

**Product Owner:** ________________________  
**Date:** 1405/03/30

**Tech Lead:** ________________________  
**Date:** 1405/03/30

**Architect:** ________________________  
**Date:** 1405/03/30

---

**Document Status:** **APPROVED - PRODUCTION BASELINE**

This Constitution is now the authoritative source for all DormSys project governance. Any deviation requires formal amendment process per Section 14.