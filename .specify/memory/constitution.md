# **CONSTITUTION v1.1.0 – DormSys Project**

## **Document Control**

* **Version:** 1.1.0  
* **Date:** 1405/03/29 (2026-06-19)  
* **Status:** Baseline  
* **Approval Status:** Approved  
* **Approved By:** Product Owner & Tech Lead  
* **Previous Version:** 1.0.0  
* **Classification:** Internal \- Project Governance

---

## **1\. Purpose of This Document**

This Constitution is the **supreme governance document** for the DormSys project. It defines immutable business rules, architecture principles, decision authority, and constraints that all agents, developers, and stakeholders must follow. It is **not** a PRD, schema doc, API spec, or implementation guide—those are derivatives governed by this Constitution.

---

## **2\. Project Identity**

* **Name:** DormSys (Organizational Dormitory Management System)  
* **Type:** Greenfield enterprise application  
* **Domain:** Employee accommodation request, allocation, lottery, and lifecycle management  
* **Target Users:** Employees, HR managers, department managers, dormitory managers, dormitory unit staff  
* **Version:** 1.1.0  
* **Status:** Baseline

---

## **3\. You Are Acting As**

* **Enterprise Software Architect**  
* **Domain Analyst**  
* **Backend Systems Designer**  
* **Spec Kit Constitution Author**

Your role is to **document principles and invariants**, not to write PRDs or implementation details. When uncertain, escalate to Decision Authority.

---

## **4\. Architecture Principles**

### **AP-01: Technology Principles**

* **Principle:** Choose boring, proven, open-source technology unless compelling reason exists  
* **Rationale:** Reduce operational risk, maximize talent availability, ensure long-term maintainability  
* **Reference stack (subject to ADR):** Python/FastAPI, PostgreSQL, SQLAlchemy, Alembic  
* **Constraint:** All technology choices require an ADR approved by Tech Lead

### **AP-02: Modular Monolith**

* **Principle:** Single deployable unit, logically partitioned into bounded modules  
* **Rationale:** Simplify deployment, avoid distributed system complexity for v1.0  
* **Constraint:** No microservices for v1.0; future decomposition requires ADR

### **AP-03: Clean Architecture \+ DDD Lite**

* **Layers:**  
  * **Domain:** Entities, value objects, aggregates, domain services  
  * **Application:** Use cases, orchestration, DTOs  
  * **Infrastructure:** Persistence, external integrations, framework adapters  
  * **Presentation:** REST API, CLI tools  
* **Constraint:**  
  * Domain layer owns business invariants and domain rules  
  * Application layer coordinates use cases and application workflows  
  * Infrastructure and Presentation layers depend on inner layers, never the reverse

### **AP-04: Shared Database with Bounded Module Ownership**

* **Principle:** Single PostgreSQL database, strict module ownership of tables  
* **Rules:**  
  * Each module owns its tables and is the **sole writer**  
  * Cross-module reads via **application services only** (no direct SQL joins across module boundaries)
  Exception:  
     Read-only reporting projections may combine data from multiple modules.  
     Such projections must not modify source module data.  
  * Cross-module foreign keys are **prohibited by default** and require ADR approval with explicit rationale  
  * Foreign keys within module boundaries are permitted and encouraged  
  * Shared reference data (e.g., `tbl_Settings`) exposed via read-only service  
* **Rationale:** Preserve logical boundaries while avoiding distributed transaction complexity  
* **Note:** This constraint ensures future microservice decomposition remains feasible  
  * Cross-module identifiers (e.g., `EmployeeID`, `RequestID`) may be stored as **immutable value references** without FK constraints  
  * Physical foreign key constraints across module boundaries require ADR approval and explicit architectural justification

### **AP-05: Explicit State Machines**

* **Principle:** All entity lifecycle transitions are modeled as explicit state machines in domain layer  
* **Constraint:** State transition logic must not be scattered in controllers or SQL triggers

### **AP-06: Audit Everything**

* **Principle:** All state transitions, approvals, rejections, allocations, lottery runs recorded in `tbl_AuditLog`  
* **Implementation:**  
  * Audit entries are **append-only** and **immutable**  
  * No UPDATE or DELETE operations permitted on audit records  
  * Schema enforced via database constraints where possible  
* **Constraint:** Audit log must capture:  
  * `Timestamp` (UTC)  
  * `Actor` (UserID or system identifier)  
  * `Action` (e.g., APPROVED, REJECTED, ALLOCATED, LOTTERY\_RUN)  
  * `EntityType` and `EntityID`  
  * `PreviousState` and `NewState` (for state transitions)  
  * `Metadata` (JSON payload with context-specific details)  
  * Physical audit storage may be centralized, but write operations are performed only through AuditService.

---

## **5\. Domain Language Authority**

| Term | Definition | Owner Module |
| ----- | ----- | ----- |
| Request | Employee application for dormitory accommodation | `request` |
| Allocation | Assignment of specific room/bed to approved request | `allocation` |
| Lottery Run | Batch process to select winners from pending requests | `lottery` |
| Eligible Snapshot | Immutable record of request state at lottery execution time | `lottery` |
| Approval Workflow | Four-stage manual or automated approval process | `workflow` |
| Weighted Score | Calculated priority value for lottery ranking | `lottery` |
| Lottery Win Counter | Number of lottery wins per employee (configurable reset policy) | `lottery` |
| Check-In | Physical arrival and occupancy start | `checkin` |
| Check-Out | Physical departure and occupancy end | `checkin` |
| Penalty | Score reduction applied for previous wins | `lottery` |
| RandomSeed | Deterministic seed value for lottery random component | `lottery` |
| RequestType | Category of request: Individual, Family, Group | `request` |
| RequestMember | Individual member record in group/family request | `request` |
| Dependent | Family member included in family request | `employee` |
| Winner | Employee whose request was selected in lottery | `lottery` |
| External Dormitory | Third-party accommodation not managed in system inventory | `allocation` |

All domain terms must be used consistently across code, docs, APIs, and UI.

---

## **6\. Problem Statement & Goals**

### **Problem**

Manual, error-prone dormitory request and allocation process leads to:

* Low transparency  
* Allocation conflicts  
* Perceived unfairness in lottery  
* Poor resource utilization  
* Long approval cycles

### **Goals**

1. Automate request submission, approval workflow, and allocation  
2. Implement transparent, auditable lottery system  
3. Eliminate double-booking  
4. Provide real-time visibility into bed availability and occupancy  
5. Reduce average approval time  
6. Ensure fairness and traceability

---

## **7\. Scope Definition**

### **In Scope (Initial Release)**

* Employee request submission (same-day allowed, no advance timing constraint)  
* Four-stage approval workflow (default manual, configurable auto-approval per stage)  
* Lottery system with weighted scoring  
* Direct mission-based allocation (bypass lottery)  
* Room and bed allocation  
* Check-in / check-out tracking  
* External dormitory handling  
* Audit logging  
* Role-based access control  
* Notification system  
* Basic reporting and monitoring

### **Out of Scope (v1.0)**

* Payment processing  
* Meal management  
* Guest pass issuance  
* Mobile app  
* Third-party integrations (LDAP, SSO)  
* Advanced analytics / BI dashboards

---

## **8\. Immutable Business Rules**

### **BR-01: Request Eligibility**

* **Rule:** Any employee can submit a request for any future date range  
* **Constraint:** No minimum advance notice required (same-day requests allowed)  
* **Source:** Domain requirements  
* **Owner:** `request` module

### **BR-02: Allocation Constraints**

* **Rule:** One person may be assigned one allocation (bed or room) at any given time  
* **Allocation modes:**  
  * **Bed-based:** Individual bed assignment within shared room  
  * **Room-based:** Exclusive room assignment  
* **Constraint:** System must enforce no overlapping allocations for the same person  
* **Penalty:** Violation \= incident; must be zero in production  
* **Policy:** Allocation strategy is defined by dormitory configuration  
* **Source:** BR-05 from file-backed content  
* **Owner:** `allocation` module

### **BR-03: Allocation Rules**

All allocation operations must satisfy the following constraints:

#### **BR-03.1: Family Request Allocation**

* **Rule:** Family requests must always receive a private room (room-based allocation)  
* **Constraint:** System must not assign family requests to shared beds  
* **Source:** BR-04 from file-backed content  
* **Owner:** `allocation` module

#### **BR-03.2: Private Room Exclusivity**

* **Rule:** A room allocated as "private" cannot be assigned to any other request during the allocation period  
* **Constraint:** Enforced via allocation conflict detection  
* **Source:** BR-05 from file-backed content  
* **Owner:** `allocation` module

#### **BR-03.3: Group Request Size Limit**

* **Rule:** Maximum 20 people in a group request  
* **Constraint:** Validation enforced at request submission  
* **Source:** BR-13 from file-backed content  
* **Owner:** `request` module

#### **BR-03.4: Overlap Detection in Direct Allocation**

* **Rule:** Overlap must be checked when dormitory manager performs direct allocation  
* **Constraint:** Direct allocation cannot create overlapping assignments  
* **Source:** BR-12 from file-backed content  
* **Owner:** `allocation` module

#### **BR-03.5: Direct Allocation Requires Reason**

* **Rule:** Direct allocation by dormitory manager must include a documented reason  
* **Constraint:** `AllocationReason` field is mandatory for allocations not originating from lottery  
* **Source:** BR-10 from file-backed content  
* **Owner:** `allocation` module

### **BR-04: Lottery Penalty Rule**

* **Rule:** Each lottery win reduces the employee's score for subsequent lotteries  
* **Formula (conceptual):** `Score = BaseScore - (WinCount × PenaltyWeight) + RandomBoost`  
* **Rationale:** Promote fairness by rotating access  
* **Source:** BR-14 from file-backed content  
* **Owner:** `lottery` module  
* **Constraint:** Exact formula parameters stored in `tbl_Settings`; formula logic must not be hardcoded

### **BR-05: Mission Allocation Priority**

* **Rule:** Approved mission-based requests bypass lottery and receive direct allocation via `allocation` module  
* **Owner:** `allocation` module  
* **Constraint:** Mission allocation must be audited with justification  
* **Change from v1.0.0:** Ownership clarified—mission allocation is an `allocation` module responsibility, not `request` module

### **BR-06: Lottery Execution Rules**

#### **BR-06.1: Eligibility Criteria**

* **Rule:** Only requests with completed approval workflow status \= APPROVED and within the lottery schedule window are eligible  
* **Immutable Snapshot:** At lottery execution, system creates `EligibleSnapshot` entity records capturing:  
  * `RequestID`  
  * `EmployeeID`  
  * `RawWeight` (calculated base score)  
  * `NormalizedWeight`  
  * `CDF` (cumulative distribution function value)  
  * `RandomNoise` (derived from `LotteryRun.RandomSeed`)  
  * `WeightedScore` (final ranking value)  
* **Source:** BR-06, BR-08 from file-backed content  
* The conceptual formula describes business intent only.  
   The implementation algorithm must be defined in Lottery ADR.

#### **BR-06.2: Deterministic Scoring**

* **Rule:** Lottery scoring must be reproducible given the same inputs and `RandomSeed`  
* **Implementation:**  
  * Each `LotteryRun` entity stores a `RandomSeed` value (integer or UUID)  
  * `RandomNoise` component for each eligible request is derived deterministically from:  
    * `LotteryRun.RandomSeed`  
    * `RequestID` (or `EmployeeID` as secondary key)  
  * Formula: `RandomNoise = PRNG(RandomSeed, RequestID)` where PRNG is a deterministic pseudorandom function  
  * This ensures **exact reproducibility** of lottery results for audit and dispute resolution  
* **Constraint:** The exact PRNG algorithm must be documented in implementation ADR and remain stable across lottery runs  
* **Rationale:** Enable forensic analysis and dispute resolution by re-running lottery with same seed  
* **Change from v1.0.0:** Explicit deterministic tie-breaking via seeded random component

#### **BR-06.3: Winner Selection**

* **Rule:** Winners chosen in descending order of `WeightedScore`  
* **Tie Breaking:** If two requests have identical `WeightedScore` after rounding:  
  1. Higher `RawWeight` wins  
  2. If still tied, earlier `RequestSubmissionTimestamp` wins  
  3. If still tied (edge case), lower `RequestID` wins  
* **Persistence:** Each `Winner` entity recorded, request status updated to `WON`  
   

#### **BR-06.4: Lottery Lock**

* **Rule:** After lottery lock timestamp, new registrations for that lottery window are forbidden  
* **Source:** BR-08 from file-backed content  
* **Owner:** `lottery` module

#### **BR-06.5: Reserve Winner Replacement**

* **Rule:** If a main winner withdraws, a reserve winner replaces automatically  
* **Source:** BR-09 from file-backed content  
* **Constraint:** Replacement must be audited  
* **Owner:** `lottery` module

#### **BR-06.6: Cross-Dormitory Overlap**

* **Rule:** Overlap between different dormitories in lottery is not checked  
* **Source:** BR-07 from file-backed content  
* **Rationale:** Each dormitory operates independently for lottery purposes  
* **Owner:** `lottery` module

#### **BR-06.7: Audit Requirement**

* **Rule:** Every lottery run logs:

  * Snapshot of eligible requests (`EligibleSnapshot` entities)

  * Selected winners (`Winner` entities)

  * `RandomSeed` used

  * Execution timestamp

* **Owner:** `lottery` module

### **BR-07: External Dormitory Rules**

* **Rule:** External dormitory allocations do not consume internal bed inventory  
* **Allocation strategy:** External dormitory allocation strategy is **configurable**  
* **Default policy:** External dormitory requests excluded from internal inventory lottery  
* **Alternative policy:** External dormitories may participate in separate lottery pools (requires configuration)  
* **Owner:** `allocation` module

### **BR-08: Four-Stage Approval Workflow**

**State Machine:**  
 DRAFT → PENDING →  
 → \[DeptMgr approval\] → STAGE1\_APPROVED →  
 → \[HRMgr approval\] → STAGE2\_APPROVED →  
 → \[DormMgr approval\] → STAGE3\_APPROVED →  
 → \[DormUnit approval\] → APPROVED

Each stage can reject → REJECTED (terminal state)

**Rules:**

* **Default mode:** Manual approval at each stage  
* **Configurable auto-approval:** System supports conditional auto-approval per stage  
  * Auto-approval rules based on business logic (e.g., request duration, employee score)  
  * Auto-approval toggle per stage stored in `tbl_Settings`  
  * Auto-approval events logged in `AuditLog` with `ApproverType = AUTO_RULE`  
* All transitions (manual or auto) audited  
* Rejection requires non-null `RejectionReason` (BR-11 from file-backed content)  
* Approved requests eligible for lottery or direct allocation  
* **Post-APPROVED modification:**  
  * HR Manager can modify or cancel requests in `APPROVED` status
    Post-approved request modification does not imply modification of confirmed allocation.  
     Allocation changes require allocation module authorization.  
  * Employee must be notified of any modification  
  * Modified/cancelled requests **do not decrement lottery win counter**  
  * Modification events must be audited  
* **Owner:** `workflow` module

### **BR-09: Lottery Win Counter Policy**

* **Default policy:** Employee lottery win counter **does NOT reset** automatically for the new year  
* **Rationale:** Preserve historical win data for fairness analysis and long-term scoring  
* **Configurable reset:** `tbl_Settings` may include `ResetWinCounterOnNewYear` flag  
  * If enabled, reset happens at fiscal year boundary  
  * Reset event must be audited  
* **Owner:** `lottery` module

### **BR-10: Group Request Authority**

* **Rule:** Group requests can only be registered by the authorized group lead  
* **Source:** BR-02, BR-03 from file-backed content  
* **Owner:** `request` module

### **BR-11: Stay Duration Flexibility**

* **Rule:** Employee stay may end early or be extended beyond originally allocated period  
* **Source:** BR-15 from file-backed content  
* **Constraint:** Early departure or extension must be processed via `checkin` module and audited  
* **Owner:** `checkin` module

---

## **9\. Non-Functional Requirements**

### **NFR-01: Performance (NFR-P01)**

* API response time (p95): \< 500ms  
* Page load time: \< 2 seconds  
* Lottery execution for 1000 requests: \< 30 seconds  
* Database queries optimized with proper indexing

### **NFR-02: Security (NFR-S01)**

* Authentication required for all endpoints  
* Authentication via organizational username/password  
* Role-based authorization enforced at application layer  
* No hardcoded credentials  
* Audit log access restricted to authorized roles

### **NFR-03: Availability (NFR-A01)**

* Target uptime: 99% during business hours  
* Graceful degradation for non-critical features  
* All direct allocations and critical operations must have audit trail

### **NFR-04: Maintainability (NFR-M01)**

* Test coverage: \> 80% for domain and application layers  
* All business rule changes require ADR  
* Code review mandatory for all changes  
* Layered architecture with separation of business logic

### **NFR-05: Observability**

* Structured logging (JSON format)  
* Health check endpoint  
* Metrics for success criteria (see Section 10\)

### **NFR-06: Data Retention**

* **Rule:** System must comply with organizational data retention policy  
* **Constraint:**  
  * Allocation history retention period: **subject to organizational policy**  
  * Personal data retention: **must comply with applicable privacy regulations** (e.g., GDPR, local laws)  
  * Audit logs: retained per organizational audit retention requirements  
  * Configuration: `tbl_Settings` includes `DataRetentionPolicyDays` parameter  
* **Rationale:** Avoid legal/compliance risk from indefinite personal data retention  
* **Change from v1.0.0:** Removed "indefinite retention" language; aligned with compliance requirements  
* **Owner:** `system` \+ Legal/Compliance review required  
  * **Privacy right requests (e.g., GDPR Article 17):**  
    * Where legal retention obligations exist, personal data must be **anonymized** rather than deleted  
    * Deletion requests follow organizational privacy policy with Legal/Compliance approval  
    * Audit trail of anonymization/deletion must be maintained

### **NFR-07: Localization (NFR-U01)**

* Persian UI, RTL (right-to-left) layout  
* Date/time display in both Jalali and Gregorian calendars

### **NFR-08: Data Integrity (NFR-D01, NFR-D02)**

* Store all timestamps in UTC  
* Relational database with ACID support (PostgreSQL)  
* Referential integrity enforced via foreign keys within module boundaries

---

## **10\. Success Criteria & Monitoring**

| Metric | Target | Owner | Measurement |
| ----- | ----- | ----- | ----- |
| Bed occupancy rate | \> 75% | `report` | Daily: allocated beds / available beds |
| Lottery fairness (Gini) | \< 0.3 | `lottery` | Yearly: distribution of wins |
| System adoption rate | \> 95% | `report` | Eligible employees who submitted request |
| Allocation conflict incidents | \= 0 | `allocation` | Overlapping allocations per quarter |
| Average approval time | Tracked | `workflow` | PENDING → APPROVED duration (median) |
| Available beds in service | Tracked | `dormitory` | Daily count of operational beds |

---

## **11\. Module Boundaries**

| Module | Owned Entities | Invariants | Application Services |
| ----- | ----- | ----- | ----- |
| `identity` | User, Role, Permission | User ↔ Role mapping | AuthService, PermissionService |
| `employee` | Employee, Dependent | Employee uniqueness, eligibility rules | EmployeeService |
| `dormitory` | Dormitory, Room, Bed | Room ↔ Bed cardinality, capacity constraints | InventoryService, AvailabilityService |
| `request` | Request, RequestMember | Request ↔ Member relationship | RequestService |
| `workflow` | RequestApproval | Four-stage state machine | ApprovalService, WorkflowEngine |
| `allocation` | Allocation | No overlapping allocations (BR-02), Mission allocation (BR-05) | AllocationService |
| `lottery` | LotteryRun, EligibleSnapshot, Winner | Weighted scoring, win tracking, deterministic execution | LotteryService, ScoringEngine |
| `checkin` | CheckIn, CheckOut | CheckIn ↔ CheckOut pairing | CheckInService |
| `report` | (query models, no owned entities) | Read-only views | ReportService |
| `notification` | NotificationLog | Delivery tracking | NotificationService |
| `audit` | AuditLog | Append-only, immutable | AuditService |

**Cross-module interaction:** Only via application services; no direct database access across boundaries.

---

## **12\. Permission Matrix**

| Role | Request | Approve Stage 1 | Approve Stage 2 | Approve Stage 3 | Approve Stage 4 | Allocate | Run Lottery | Modify Post-APPROVED | View Reports |
| ----- | ----- | ----- | ----- | ----- | ----- | ----- | ----- | ----- | ----- |
| Employee | ✓ | — | — | — | — | — | — | — | Own data |
| Dept Manager | ✓ | ✓ | — | — | — | — | — | — | Own dept |
| HR Manager | ✓ | — | ✓ | — | — | — | — | ✓ | All |
| Dorm Manager | ✓ | — | — | ✓ | — | ✓ | ✓ | — | All |
| Dorm Unit Staff | ✓ | — | — | — | ✓ | — | — | — | Assigned dorms |
| Lottery Operator | — | — | — | — | — | — | ✓ | — | Lottery data |
| System Admin | ✓ | — | — | — | — | — | — | — | All \+ Audit |

**Notes:**

* **Lottery Operator:** Specialized role for lottery execution; may be assigned to Dorm Manager or dedicated staff  
* **Change from v1.0.0:** Added Lottery Operator role explicitly for RBAC clarity

---

## **13\. Architecture Constraints**

### **AC-01: No Distributed Transactions**

* Single database; no two-phase commit

### **AC-02: No External API Calls in Domain Layer**

* Domain layer must remain pure; external integrations in Infrastructure layer only

### **AC-03: No Business Logic in SQL**

* Complex calculations and rules implemented in application/domain layers  
* SQL views and triggers allowed only for read optimization and audit enforcement

### **AC-04: No Hardcoding**

Configuration Ownership Rule:

Business configuration → `tbl_Settings`  
Technical configuration → application configuration / environment variables  
Security configuration → security configuration management

Magic numbers prohibited.


*   
* Magic numbers prohibited

### **AC-05: Idempotent Operations**

* Lottery runs, allocations, approvals must be idempotent or explicitly prevent duplicate execution

---

## **14\. Decision Authority Hierarchy**

| Decision Type | Authority | Requires ADR? | Escalation Path |
| ----- | ----- | ----- | ----- |
| Business rule change | Product Owner | Yes | Executive Sponsor |
| Architecture principle change | Tech Lead | Yes | CTO |
| Technology stack change | Tech Lead | Yes | CTO |
| Module boundary change | Tech Lead | Yes | Product Owner \+ CTO |
| API contract change (breaking) | Tech Lead | Yes | Product Owner |
| Database schema change | Tech Lead | Yes (migration) | DBA (review) |
| Permission matrix change | Product Owner | No | Security Officer (review) |
| Success metric threshold change | Product Owner | No | Executive Sponsor |
| Constitution amendment | Product Owner \+ CTO | Yes | Executive Sponsor |

---

## **15\. Constitution Amendment Policy**

### **Process**

1. **Proposal:** Any stakeholder can propose an amendment via ADR  
2. **Review:** Tech Lead \+ Product Owner review for impact  
3. **Approval:** Requires approval from both Product Owner and Tech Lead  
4. **Notification:** All agents and developers notified of amendment  
5. **Versioning:** Constitution version incremented; change logged in Approval & Change Log

### **Prohibited Amendments**

* Removal of audit requirements  
* Weakening of allocation conflict prevention (BR-02)  
* Introduction of hardcoded business rules  
* Bypassing decision authority hierarchy

---

## **16\. Risks & Mitigation Strategies**

| Risk | Likelihood | Impact | Mitigation |
| ----- | ----- | ----- | ----- |
| Allocation conflicts | Medium | High | Enforce BR-02 with DB constraints \+ app-layer checks |
| Lottery perceived as unfair | Medium | High | Transparent scoring; publish Gini coefficient; deterministic reproducibility |
| Slow approval cycles | High | Medium | Enable configurable auto-approval; monitor avg time |
| Low system adoption | Medium | High | User training; phased rollout; feedback loops |
| Configuration errors | Medium | Medium | Schema validation for `tbl_Settings`; audit changes |
| Performance degradation at scale | Low | Medium | Load testing; query optimization; indexing strategy |
| Data retention compliance issues | Medium | High | Align with organizational retention policy; legal review |

---

## **17\. Agent Operating Protocol**

### **When Working on This Project**

1. **Read this Constitution first.** Do not guess or assume.  
2. **Check Decision Authority** before making architectural or business rule changes.  
3. **Write an ADR** for any decision that affects multiple modules or changes a principle.  
4. **Verify invariants** after every change:  
   * No overlapping allocations (BR-02)  
   * State transitions follow defined state machines  
   * Audit logs written for all critical events  
5. **Use domain language** from Section 5 consistently.  
6. **Do not hardcode.** Read configuration from `tbl_Settings`.  
7. **Test coverage:** Write tests for new business rules and state transitions.  
8. **Ask, don't assume.** If a requirement is ambiguous, escalate to Product Owner.

### **Prohibited Actions Without ADR Approval**

* Changing module boundaries  
* Adding new approval stages  
* Modifying lottery formula logic  
* Altering lottery win counter reset policy  
* Introducing new database tables outside module ownership  
* Bypassing audit logging

---

## **18\. Development Standards**

The system shall follow **Clean Architecture** principles and **DDD Lite** patterns as defined in Section 4\.

Detailed implementation conventions including:

* Repository structure  
* Naming conventions  
* Testing strategy  
* Code review checklist

are defined in `/docs/standards/development-standard.md`.

---

## **19\. Input Sources & Traceability**

| Source | Type | Authority | Usage |
| ----- | ----- | ----- | ----- |
| `pasted-text.txt` | Legacy code/schema | Reference only | Extract existing business rules and schema |
| `Untitled document (2).md` | Domain model \+ ER | Primary | Entity definitions, relationships, BR-01 to BR-15 |
| `Untitled document (1).md` | Problem statement | Primary | Goals, scope, vision |
| `diagram (1).mmd` | Visual diagrams | Supporting | Visual reference |
| `diagram (2).mmd` | Visual diagrams | Supporting | Visual reference |
| `diagram.mmd` | Visual diagrams | Supporting | Visual reference |

**Traceability:**

* All business rules map to entities in domain model  
* All entities map to tables in schema  
* All use cases map to application services

---

## **20\. Specification Hierarchy**

Priority order:

1. Constitution  
2. ADRs  
3. Domain Specifications  
4. API Specifications  
5. Database Specifications  
6. Implementation Code

Lower-level documents cannot override higher-level decisions.

## **21\. Quality Checklist**

Before marking any feature complete:

* Business rules from this Constitution implemented and tested  
* State machine transitions enforced  
* Audit logging in place  
* No hardcoded configuration  
* Module boundaries respected  
* API documentation updated  
* Test coverage \> 80% for new code  
* Code review completed  
* ADR written (if required)  
* Success metrics instrumented

---

# **Constitution Evolution History**

This document archives the complete evolution of the DormSys Constitution from initial draft to baseline.

---

## **Pre-Baseline Iterations (v0.1 → v0.9)**

| Version | Date | Author | Change |
| ----- | ----- | ----- | ----- |
| 0.1 | 2026-06-19 | AI Assistant | Initial draft |
| 0.2 | 2026-06-19 | AI Assistant | Added BR-07 auto-approval rules |
| 0.3 | 2026-06-19 | AI Assistant | Clarified BR-07 post-APPROVED mod |
| 0.4 | 2026-06-19 | AI Assistant | Removed 3-day advance rule (BR-01) |
| 0.5 | 2026-06-19 | AI Assistant | Added success criteria & monitoring |
| 0.6 | 2026-06-19 | AI Assistant | Clarified BR-06 external scoring |
| 0.7 | 2026-06-19 | AI Assistant | Added BR-08 lottery win counter policy |
| 0.8 | 2026-06-19 | AI Assistant | Separated implementation from principles |
| 0.9 | 2026-06-19 | AI Assistant | Fixed terminology and constraint clarity |

---

## **Rationale for Archival**

Pre-1.0 versions represent **internal iteration** during Constitution drafting. They are preserved here for:

1. **Forensic analysis** of design decisions  
2. **Learning reference** for future Constitution authors  
3. **Traceability** of removed/modified constraints

For operational governance, refer to **Section 21: Change Log** in the Constitution, which tracks only **baseline and production versions**.

---

## **22\. Change Log**

*For pre-baseline iteration history (v0.1 → v0.9), see `/docs/adr/constitution-history.md`*

---

| Version | Date | Author | Change | Approved By |
| ----- | ----- | ----- | ----- | ----- |
| 1.0.0 | 2026-06-19 | AI Assistant | Initial production baseline | Product Owner & Tech Lead |
| 1.1.0 | 2026-06-19 | AI Assistant | **Critical updates:** • Document Control: Status → Baseline, Approval Status → Approved • BR-05: Mission allocation ownership → `allocation` module • BR-06.2: Deterministic lottery via `RandomSeed` with explicit PRNG • BR-06.3: Explicit tie-breaking rules • NFR-06: Data retention aligned with organizational policy & GDPR • Permission Matrix: Added Lottery Operator role • BR renumbering: Eliminated BR-05 duplication, consolidated allocation rules under BR-03.x • AP-04: Clarified cross-module FK prohibition • AP-06: Explicit immutable audit log constraints • Added `EligibleSnapshot` entity to Domain Language Authority • Integrated file-backed BR-01 to BR-15, NFR-P01 to NFR-D02 | Product Owner & Tech Lead |

**Note:** Pre-1.0 iteration history (v0.1 → v0.9) archived in `/docs/adr/constitution-history.md`.

---

**END OF CONSTITUTION v1.1.0**

