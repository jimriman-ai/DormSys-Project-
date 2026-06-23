# DormSys Discovery Document v3.0

**Project:** Enterprise Dormitory Management System  
**Date:** 2026-06-20  
**Status:** Pre-Specification Phase

---

## Problem Statement

The organization operates multiple dormitories across different cities, each containing rooms with limited bed capacity. These facilities serve employees for personal stays, family accommodations, group missions, and lottery-based allocations.

### Current Challenges

- **Lack of transparency:** Room and bed occupancy status unclear across time ranges
- **Decentralized operations:** No unified system for request tracking
- **Request diversity:** Personal, family, group, and mission requests require different handling rules
- **Allocation complexity:** Assigning beds to groups and rooms to families is manual and error-prone
- **No clear process:** Priority, approval, rejection, allocation, and lottery lack transparent workflows
- **Poor reporting:** Employee and department usage history not analyzable
- **External dormitories:** Organization purchases external dormitory capacity for lottery but cannot monitor their occupancy

The core problem extends beyond lottery management—it requires a comprehensive system for managing the entire lifecycle: request submission, approval workflow, allocation, lottery execution, check-in/out, and reporting.

---

## Project Goal

Build a **centralized, transparent, and extensible system** to manage the complete lifecycle of organizational dormitory usage—from request submission through review, approval, allocation, lottery, check-in/out, to reporting.

### Key Objectives

1. **Centralized management** of internal and external dormitories
2. **Complete transparency** of occupancy status and available capacity
3. **Support for all request types:** personal, family, group mission, lottery
4. **Multi-stage approval workflow** with configurable routing
5. **Fair allocation** based on business rules
6. **Transparent lottery** with configurable scoring and automatic allocation
7. **Role-based access control** (RBAC)
8. **Comprehensive reporting** for analysis and decision-making

---

## Scope

### In Scope

**Dormitories:**
- Internal dormitories (full monitoring of rooms/beds and check-in/out)
- External dormitories (lottery-only, no physical monitoring)

**Rooms & Beds:**
- Capacity management
- Physical status tracking (Active/OutOfService)
- Automatic occupancy updates post-lottery allocation

**Employees & Departments:**
- Registration, management, usage history

**Request Types:**
- Personal
- Family Direct
- Group Mission
- Lottery Registration

**Approval Workflow:**
Department Manager → HR Manager → Dormitory Manager → Dormitory Unit Manager

**Allocation:**
- Manual allocation (MVP)
- Automatic allocation post-lottery for internal dormitories
- Automatic allocation (optional, future for non-lottery requests)

**Lottery:**
- Program creation
- Registration
- Execution with configurable scoring
- **Automatic allocation creation and bed/room status update** for internal dormitories
- **Archive-only results** for external dormitories
- Winner and reserve management

**Check-In/Out:**
- Operator-managed registration
- Only applicable to internal dormitory allocations

**Notifications:**
- In-app notifications (MVP required)

**Audit:**
- Complete activity logging

**Reports:**
- Occupancy rate
- Request status distribution
- Approval time metrics
- Lottery results
- Employee usage history

### Out of Scope (Future Phases)

- Intelligent auto-allocation for non-lottery requests
- AI recommendations
- Email/SMS notifications
- Payment/billing
- Mobile application
- Advanced analytics & BI
- Multi-language support
- Integration with financial/payroll systems

---

## Vision

Create a **comprehensive, extensible, and scalable system** enabling the organization to:

- Manage all dormitories and rooms centrally
- Monitor occupancy status in real-time
- Process employee, group, and family requests uniformly
- Execute fair allocations according to policies
- Run transparent, auditable, analyzable lotteries with automatic allocation
- Define roles and responsibilities clearly
- Support incremental feature development

---

## Success Metrics

| Metric | Baseline | Target (6 months post-deployment) |
|--------|----------|----------------------------------|
| Request processing time | avg 5 days | < 2 days |
| Occupancy transparency | 0% (manual) | 100% (real-time) |
| Employee satisfaction | - | > 80% |
| Capacity utilization rate | unknown | > 75% |
| Allocation time post-approval | avg 3 days | < 1 day |
| Lottery transparency | unknown | 100% auditable |
| Post-lottery allocation time | manual (days) | < 1 minute (automatic) |

---

## Architecture

**Style:** Modular Monolith  
**Backend:** Laravel 13 (PHP 8.3+)  
**Frontend:** Livewire 3 + Alpine.js  
**Database:** PostgreSQL 17  
**Cache/Queue:** Redis  
**Styling:** Tailwind CSS

### Modules

/identity       # Authentication & user management
/employee       # Employees & departments
/dormitory      # Dormitories, rooms, beds
/request        # Requests & types
/workflow       # Approval workflow
/allocation     # Room/bed allocation
/lottery        # Lottery programs
/checkin        # Check-in/out
/report         # Reporting
/notification   # Notifications
/audit          # Audit logging


### Why Laravel + Livewire?

Laravel excels at **enterprise software, workflows, forms, permissions, reporting, and long-term maintenance**. Livewire reduces frontend complexity while maintaining interactivity. This stack avoids premature microservice complexity while supporting modular development within a monolith.

---

## State Machines

### Request State Machine

Draft
  → (submit) Submitted
  → PendingDepartmentManager
  → (approve) PendingHR
  → (approve) PendingDormitoryManager
  → (approve) PendingDormitoryUnit
  → (approve) Approved
  → WaitingForAllocation
  → (allocate) Allocated
  → (check-in) CheckedIn
  → (check-out) CheckedOut

Side transitions from any state:
  → Rejected (with reason)
  → Cancelled (by employee in Draft/Submitted only)
  → AllocationFailed (approved but no capacity available)


### Lottery Program State Machine

Draft
  → (submit) WaitingApproval
  → (approve) Approved
  → (open) RegistrationOpen
  → (close) RegistrationClosed
  → (lock) Locked
  → (draw) Drawn
  → (auto-allocate) Completed

Side transition from any state:
  → Cancelled


**Key State Transitions:**
- **Drawn → Completed**: Triggered automatically after lottery execution
  - For **internal dormitories**: Creates `Allocation` + `AllocationItem` records, updates bed/room status
  - For **external dormitories**: Records winners in archive only, no physical allocation

---

## Domain Model

### Core Entities

**Employee**
id: UUID
employee_code: string
first_name: string
last_name: string
national_code: string
department_id: UUID
role_id: UUID
hire_date: date
base_lottery_score: integer
created_at: datetime
updated_at: datetime


**Department**
id: UUID
name: string
code: string
manager_id: UUID
parent_id: UUID (nullable)
lottery_priority: integer
created_at: datetime
updated_at: datetime


**Role**
id: UUID
name: string
permissions: json
created_at: datetime
updated_at: datetime


**Dormitory**
id: UUID
name: string
code: string
type: enum(Internal, External)
city: string
address: string
manager_id: UUID (nullable for External)
total_rooms: integer
total_beds: integer
status: enum(Active, Inactive)
created_at: datetime
updated_at: datetime


**Room**
id: UUID
dormitory_id: UUID
number: string
floor: integer
type: enum(Suite, Double, Quad)
capacity: integer
physical_status: enum(Active, OutOfService)
out_of_service_reason: string (nullable)
out_of_service_at: datetime (nullable)
created_at: datetime
updated_at: datetime


**Bed**
id: UUID
room_id: UUID
number: integer
physical_status: enum(Active, OutOfService)
out_of_service_reason: string (nullable)
out_of_service_at: datetime (nullable)
created_at: datetime
updated_at: datetime


**Request**
id: UUID
code: string (auto: REQ-YYMMDD-NNNN)
employee_id: UUID
type: enum(Personal, FamilyDirect, Mission, LotteryRegistration)
dormitory_id: UUID
check_in_date: date
check_out_date: date
status: enum(Draft, Submitted, PendingDepartmentManager, ...)
submitted_at: datetime (nullable)
created_at: datetime
updated_at: datetime


**RequestApproval**
id: UUID
request_id: UUID
approver_id: UUID
stage: enum(DepartmentManager, HR, DormitoryManager, DormitoryUnit)
decision: enum(Approved, Rejected)
reason: string (nullable)
decided_at: datetime
created_at: datetime


**Allocation**
id: UUID
request_id: UUID (nullable for lottery-only)
lottery_result_id: UUID (nullable for non-lottery)
allocation_type: enum(PrivateRoom, BedBased)
allocated_by: UUID (nullable for automatic lottery allocation)
allocation_method: enum(Manual, LotteryAutomatic)
allocated_at: datetime
status: enum(Active, Cancelled, Completed)
cancellation_reason: string (nullable)
cancelled_at: datetime (nullable)
created_at: datetime
updated_at: datetime


**AllocationItem**
id: UUID
allocation_id: UUID
room_id: UUID
bed_id: UUID (nullable for PrivateRoom)
start_date: date
end_date: date
created_at: datetime
updated_at: datetime


**Mission**
id: UUID
request_id: UUID
mission_document_url: string
leader_id: UUID
description: string
created_at: datetime
updated_at: datetime


**Person** (Base)
id: UUID
person_type: enum(Employee, External)
first_name: string
last_name: string
national_code: string (nullable)


**Employee extends Person**
employee_code: string
department_id: UUID
hire_date: date


**ExternalPerson extends Person**
organization: string (nullable)
role: string (nullable)


**MissionMember**
id: UUID
mission_id: UUID
person_id: UUID
mission_role: string
created_at: datetime


**Dependent**
id: UUID
request_id: UUID
first_name: string
last_name: string
relationship: enum(Spouse, Child, Parent)
age: integer (nullable)
national_code: string (nullable)
created_at: datetime


**LotteryProgram**
id: UUID
code: string (auto-generated)
name: string
dormitory_id: UUID
check_in_date: date
check_out_date: date
registration_start: datetime
registration_end: datetime
max_winners: integer
max_reserves: integer
status: enum(Draft, WaitingApproval, ...)
scoring_config: json
auto_allocate: boolean (default: true for Internal, false for External)
created_at: datetime
updated_at: datetime


**LotteryRegistration**
id: UUID
lottery_id: UUID
employee_id: UUID
room_type: enum(Suite, Double, Quad) (nullable for external)
family_members: json
calculated_score: float
final_score: float
status: enum(Pending, Winner, Reserve, Lost)
registered_at: datetime
created_at: datetime
updated_at: datetime


**LotteryResult**
id: UUID
lottery_id: UUID
registration_id: UUID
result_type: enum(Winner, Reserve)
draw_order: integer
allocation_id: UUID (nullable, only for internal dormitories)
voucher_code: string (nullable, only for external dormitories)
executed_at: datetime
created_at: datetime


**CheckInOut**
id: UUID
allocation_id: UUID
type: enum(CheckIn, CheckOut)
operator_id: UUID
performed_at: datetime
notes: string (nullable)
created_at: datetime


**Notification**
id: UUID
employee_id: UUID
type: enum(RequestSubmitted, Approved, Rejected, ...)
title: string
message: string
read: boolean
link: string (nullable)
created_at: datetime
read_at: datetime (nullable)


**AuditLog**
id: UUID
entity_type: string
entity_id: UUID
action: string
performed_by: UUID
timestamp: datetime
before: json (nullable)
after: json (nullable)
ip_address: string (nullable)


---

## Aggregates

### Dormitory Aggregate

**Root:** Dormitory

Dormitory
  ├── Room
  │   └── Bed


**Invariants:**
- Room cannot exist without dormitory
- Bed cannot exist without room
- Room capacity = count of active beds
- External dormitory has no rooms/beds monitoring

### Request Aggregate

**Root:** Request

Request
  └── RequestApproval (multiple)


**Invariants:**
- Request must have at least one approval
- Approvals must follow stage order
- Same stage cannot be approved twice

### Allocation Aggregate

**Root:** Allocation

Allocation
  ├── AllocationItem (multiple)
  └── CheckInOut (multiple)


**Invariants:**
- Allocation must have at least one AllocationItem
- AllocationItems must not overlap
- CheckIn only allowed in `Allocated` status
- CheckOut only allowed in `CheckedIn` status
- Lottery-based allocations are read-only (cannot be manually modified)

### Lottery Aggregate

**Root:** LotteryProgram

LotteryProgram
  ├── LotteryRegistration (multiple)
  └── LotteryResult (multiple)
      └── Allocation (optional, internal only)


**Invariants:**
- Registration only allowed during registration period
- Draw only allowed after registration closes
- Winners cannot exceed max_winners
- Reserves cannot exceed max_reserves
- Auto-allocation only for internal dormitories

---

## Business Rules

### BR-01: Request Rules

**BR-01.1: Request Types**
- **Personal**: One bed for employee
- **FamilyDirect**: Complete room for employee + family (no lottery)
- **Mission**: Multiple beds for mission group members
- **LotteryRegistration**: Registration for lottery program

**BR-01.2: Date Validation**
- `check_in_date` cannot be in the past
- `check_out_date` must be after `check_in_date`
- Maximum stay duration: 30 days (configurable)

**BR-01.3: Submission Rules**
- Employee can only cancel in `Draft` status
- After `Submitted`, only `Rejected` or workflow progression allowed

**BR-01.4: Overlap Detection**
- Employee cannot have overlapping active requests
- Overlap check before submission and before allocation

---

### BR-02: Approval Workflow Rules

**BR-02.1: Approval Stages**
Default path:
1. DepartmentManager
2. HR
3. DormitoryManager
4. DormitoryUnitManager

**BR-02.2: Approval Rules**
- Stages must be approved in order
- Rejection at any stage stops the request (`Rejected`)
- Approver cannot approve their own request
- Rejection reason is mandatory

**BR-02.3: Special Paths**
- **Mission requests**: May require additional approvals
- **Urgent requests**: Can be fast-tracked (with special permission)

---

### BR-03: Allocation Rules

**BR-03.1: Manual Allocation Algorithm (MVP)**
- `DormitoryUnitManager` manually selects rooms/beds
- System only displays available rooms/beds

**BR-03.2: Inventory Rules**
- **Personal/Mission**: Check bed availability in date range
- **FamilyDirect**: Check complete room availability
- `OutOfService` beds not shown in list

**BR-03.3: Overlap Prevention**
- System prevents simultaneous allocation of same bed/room
- Overlap check before creating `AllocationItem`

**BR-03.4: Cancellation Rules**
- Only `DormitoryUnitManager` can cancel allocation
- Cancellation before Check-In: status → `Cancelled`
- Cancellation after Check-In: requires forced Check-Out

**BR-03.5: Allocation Failure Management**
When request is approved but no capacity available:
- Request status → `AllocationFailed`
- Possible actions:
  - **Wait**: Wait for capacity to free up
  - **Change Date**: Modify check-in/check-out dates
  - **Suggest Alternative**: Propose different dormitory
  - **Cancel**: Cancel the request

---

### BR-04: Out-of-Service Rules

**BR-04.1: Status Change**
- Only `DormitoryUnitManager` can change room/bed status to `OutOfService`
- Reason and date are mandatory

**BR-04.2: Impact on Allocation**
- `OutOfService` rooms/beds not shown in allocation list
- If active allocation exists:
  - System shows warning
  - Requires re-allocation or cancellation

**BR-04.3: Return to Active**
- `DormitoryUnitManager` can return status to `Active`
- Reason and date are recorded

---

### BR-05: Check-In / Check-Out Rules

**BR-05.1: Check-In Registration**
- Only `Operator` can register Check-In
- Check-In only allowed on or after check-in date
- Early Check-In requires approval

**BR-05.2: Check-Out Registration**
- Only `Operator` can register Check-Out
- Check-Out allowed anytime after Check-In
- Late Check-Out shows warning but is allowed

**BR-05.3: Status Impact**
- Check-In: status → `CheckedIn`
- Check-Out: status → `CheckedOut`, inventory freed

**BR-05.4: Restrictions for External Dormitories**
- Check-In/Check-Out **not applicable** for external dormitory lottery winners
- External lottery winners receive vouchers only

---

### BR-06: Lottery Rules (Updated)

**BR-06.1: Lottery Program Creation**
- Only `DormitoryManager` can create program
- Program includes:
  - Dormitory
  - Check-in/check-out dates
  - Registration period
  - Number of winners and reserves
  - `scoring_config` (scoring formula as JSON)
  - `auto_allocate` flag (automatic for internal, manual override possible)

**BR-06.2: Scoring Formula (Configurable)**
Example `scoring_config`:
```json
{
  "base_score": "employee.base_lottery_score",
  "factors": [
    {
      "name": "seniority_years",
      "formula": "years_since_hire * 2"
    },
    {
      "name": "department_priority",
      "formula": "department.lottery_priority * 5"
    },
    {
      "name": "previous_wins",
      "formula": "previous_lottery_wins * -10"
    },
    {
      "name": "family_size",
      "formula": "family_members_count * 3"
    }
  ]
}
```

**BR-06.3: Registration**
- Employee registers during specified period
- Selects room type (for internal dormitories only)
- Declares family members (optional)
- Score calculated based on `scoring_config`

**BR-06.4: Lottery Execution (Updated)**
Algorithm:
1. Lock program (status → `Locked`)
2. Calculate `final_score` for all registrations
3. Sort descending by score
4. Select winners (up to `max_winners`)
5. Select reserves (up to `max_reserves`)
6. Check overlap: if winner has active allocation → remove and promote reserve
7. **If dormitory type = Internal:**
   - **Automatic allocation creation:**
     - Create `Allocation` record with `allocation_method = 'LotteryAutomatic'`
     - Create `AllocationItem` records for assigned beds/rooms
     - Update bed/room occupancy status
     - Link `LotteryResult.allocation_id` to created allocation
   - Transition allocation status to `Allocated`
8. **If dormitory type = External:**
   - Generate `voucher_code` for each winner
   - Store in `LotteryResult` for archival purposes only
   - No allocation or physical resource tracking
9. Status → `Drawn`
10. Trigger completion process (status → `Completed`)

**BR-06.5: Reserve Management**
- If winner cancels → first reserve promoted
- Automatic notification to promoted reserve
- **If internal dormitory**: automatic allocation created for promoted reserve
- **If external dormitory**: voucher code generated for promoted reserve

**BR-06.6: Post-Lottery Allocation Rules**
- Lottery-based allocations are **immutable** (cannot be manually edited)
- Cancellation requires special permission from `DormitoryManager`
- Cancellation triggers reserve promotion automatically

---

### BR-07: External Dormitory Rules (Updated)

**BR-07.1: Limitations**
- **Lottery-only** functionality
- **No monitoring**: Rooms/Beds not tracked
- **No Check-In/Out** capability

**BR-07.2: Winner Process**
- Winners receive voucher codes or reservation information
- System records lottery results in archive only
- No physical allocation or occupancy tracking
- Voucher information included in notifications

**BR-07.3: Voucher Management**
- Voucher code auto-generated upon lottery draw
- Stored in `LotteryResult.voucher_code`
- Accessible via employee dashboard and notifications
- Valid for specified check-in/check-out period only

---

### BR-08: Access Control (RBAC)

**BR-08.1: Roles**
Employee
  - View own requests
  - Create/edit/cancel requests (Draft only)
  - Register for lottery
  - View notifications
  - View own lottery results and vouchers

DepartmentManager
  - Approve/reject department requests (stage 1)

HRManager
  - Approve/reject all requests (stage 2)

DormitoryManager
  - Create/manage dormitories
  - Create/manage lottery programs
  - Approve/reject requests (stage 3)
  - Execute lottery draw
  - View all reports

DormitoryUnitManager
  - Manage rooms and beds
  - Manual allocation
  - Change OutOfService status
  - Final approval of requests (stage 4)
  - Override automatic allocations (with audit)

Operator
  - Register Check-In/Check-Out
  - View active allocations

Admin
  - Full access to all sections
  - Manage employees and roles


---

### BR-09: Notification Rules

**BR-09.1: Automatic Triggers**
- Request submission → notification to next approver
- Approval → notification to employee
- Rejection → notification to employee (with reason)
- Successful allocation → notification to employee
- Lottery winner → notification to employee (with allocation/voucher info)
- Reserve promoted → immediate notification
- Check-in reminder → notification 1 day before (for internal dormitories only)

---

### BR-10: Audit Rules

**BR-10.1: Auditable Events**
- Create/edit/delete all entities
- Request status changes
- Approval/rejection in workflow
- Allocation/cancellation
- Check-In/Check-Out
- Lottery execution and automatic allocation
- Reserve promotion

**BR-10.2: Recorded Information**
- Performing user
- IP address
- Precise timestamp
- Before and after state (JSON)
- Allocation method (manual vs automatic)

---

## Functional Requirements

### FR-01: Employee Management
- Register employees with department and role
- Track employment history and seniority
- Manage base lottery scores

### FR-02: Dormitory Management
- Register internal/external dormitories
- Manage rooms and beds (internal only)
- Track physical status
- Real-time occupancy monitoring (internal only)

### FR-03: Request Management
- Create requests (Personal, FamilyDirect, Mission)
- View request history
- Cancel requests (Draft status only)
- Track status through workflow

### FR-04: Workflow & Approval
- View pending approvals by role
- Approve/reject with reasons
- Automatic routing to next stage
- Notifications at each stage

### FR-05: Allocation
- Manual allocation by DormitoryUnitManager
- Check overlap and status
- Re-allocation capability
- Cancellation with reason
- Handle AllocationFailed state
- **Automatic allocation post-lottery** (internal dormitories)
- View allocation method (manual vs automatic)

### FR-06: Lottery
- Create lottery programs with scoring config
- Employee registration
- Execute lottery draw with automatic allocation
- **Automatic bed/room allocation** for internal dormitory winners
- **Voucher generation** for external dormitory winners
- Manage winners and reserves
- Automatic reserve promotion

### FR-07: Check-In / Check-Out
- Register by Operator
- Date validation
- Late check-out warnings
- **Only applicable to internal dormitory allocations**

### FR-08: Reporting
- Occupancy rate by dormitory
- Request status distribution
- Approval time metrics
- Usage history by employee/department
- Lottery results and statistics
- Allocation method analytics

### FR-09: Notification
- In-app notifications for key events
- Read/unread status tracking
- Deep links to relevant entities

### FR-10: Audit
- Automatic logging of critical changes
- View audit log with filters
- Track allocation method and automation events

---

## Non-Functional Requirements

### Performance
- API response time < 500ms (95th percentile)
- Dashboard real-time updates < 10s delay
- Lottery draw execution < 5s for 1000 registrations
- **Automatic allocation post-lottery < 1 minute** for 100 winners

### Scalability
- Support 1000 concurrent employees
- Support 50 dormitories
- Support 5000 beds across all dormitories
- Handle 10,000 lottery registrations per program

### Security
- JWT-based authentication
- RBAC with granular permissions
- Audit logging for all sensitive operations
- HTTPS enforcement
- Protection against lottery manipulation

### Usability
- Farsi UI with RTL support
- Intuitive navigation
- Mobile-responsive design (Livewire components)
- Clear status indicators
- **Clear distinction between automatic and manual allocations**

### Reliability
- 99.5% uptime
- Automatic transaction rollback on allocation failures
- Data consistency checks for lottery execution
- **Atomic lottery execution** (all-or-nothing allocation creation)

---

## MVP Scope

### Included in MVP
- Employee, Department, Role management
- Dormitory, Room, Bed management (internal)
- External dormitory (lottery-only, no monitoring)
- Request types: Personal, Family, Mission
- Multi-stage approval workflow
- Manual allocation
- Out-of-service management
- **Lottery with automatic allocation** for internal dormitories
- **Lottery with voucher generation** for external dormitories
- Check-In/Out (internal only)
- Core reports
- In-app notifications
- Audit logging
- **Automatic bed/room occupancy updates post-lottery**

### Excluded from MVP
- Mobile application
- AI-based allocation
- External system integrations
- Multi-tenant architecture
- Advanced BI dashboards
- Email/SMS notifications
- Payment/billing
- Intelligent auto-allocation for non-lottery requests

---

## Risks & Mitigation

**Risk 1: Lottery Allocation Complexity**
- Automatic allocation may fail due to concurrent modifications
- **Mitigation:** Database transactions with pessimistic locking, rollback on failure, clear error messaging

**Risk 2: Workflow Errors**
- Complex approval routing may cause stuck requests
- **Mitigation:** Clear state machine, approval history logging, admin override capability

**Risk 3: Capacity Inconsistency**
- Physical and system state may diverge
- **Mitigation:** Regular reconciliation jobs, manual correction interface, audit trail

**Risk 4: Lottery Fairness Concerns**
- Scoring algorithm may be perceived as unfair
- **Mitigation:** Transparent scoring config, audit trail, reproducible results, ability to re-run with different config

**Risk 5: Automatic Allocation Failures**
- System failures during lottery execution could leave incomplete allocations
- **Mitigation:** Transactional integrity, idempotent operations, automatic retry with exponential backoff, manual intervention capability

**Risk 6: External Dormitory Voucher Management**
- Voucher codes must be unique and secure
- **Mitigation:** Cryptographically secure random generation, uniqueness validation, expiration tracking

---

## Next Steps

1. Convert this document to formal specification
2. Finalize domain model (Entity/Value Object details)
3. Define API contracts for all operations
4. Design final ERD with relationships
5. Create UI wireframes for key flows
6. Prepare acceptance tests for MVP features
7. Review and detail edge cases
8. **Design automatic allocation transaction flow**
9. **Define voucher code generation and validation logic**
10. **Specify lottery execution error handling and rollback procedures**

---

**Document Status:** Ready for Specification Phase  
**Approval Required:** Product Owner, Technical Lead, Stakeholders