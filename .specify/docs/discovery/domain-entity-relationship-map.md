---
artifact: domain_entity_relationship_map
status: RELATIONSHIP_EVIDENCE_CONSOLIDATION_COMPLETE
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
phase: CORE_COMPLETION_WAVE
gate: RELATIONSHIP_EVIDENCE_CONSOLIDATION
authorized_surface: employee-request-self-service
business_owner_status: PENDING_HUMAN_DESIGNATION
spec04_auth_readiness: BLOCKED
date: 2026-07-13
---

# Domain Entity Relationship Map (Evidence Consolidation)

**Artifact type:** Discovery — relationship evidence consolidation only  
**Mode:** Evidence-only / non-design / non-authorizing  

**Does not:** invent relations; define aggregate boundaries; resolve ownership; define actor semantics; design auth/UI; modify application files.

**Related prior map (broader entity/authority inventory):** `.specify/docs/discovery/domain-entity-relationship-authority-map.md`  
**Authorized surface (unchanged):** `employee-request-self-service`  
**Business Owner (unchanged):** `PENDING_HUMAN_DESIGNATION`  
**Spec04 Auth readiness (unchanged):** `BLOCKED`

**Scope:** Only the nine relationships listed below.

**Note on “ApprovalChain”:** No Domain Entity or table named `ApprovalChain` was found. Evidence below treats “ApprovalChain” as the **documented/runtime four-stage approval sequence** plus `RequestApproval` history owned by Request (CD-010) — not as a separate aggregate invention.

---

## Relationship Records

### 1. Employee ↔ Department

RELATIONSHIP: Employee ↔ Department  
CLASSIFICATION: EXPLICIT  
EVIDENCE_SUMMARY: Employee holds optional `departmentId`; migration FK `employee_employees.department_id` → `employee_departments.id`. Department entity exists with optional `managerId` / `parentId` (intra-Employee FKs).  
EVIDENCE_SOURCES:
- `app/Modules/Employee/Domain/Entities/Employee.php` (`?DepartmentId $departmentId`)
- `app/Modules/Employee/Domain/Entities/Department.php` (`managerId`, `parentId`)
- `database/migrations/modules/employee/2026_06_26_000002_create_employee_employees_table.php` (FK `department_id`)
- `database/migrations/modules/employee/2026_06_26_000001_create_employee_departments_table.php`
- `specs/003-employee-context/spec.md` / `data-model.md` (US2 Department)
CARDINALITY_EVIDENCE: PARTIAL  
OWNERSHIP_SIGNAL: CLEAR  
CONFLICTS_OR_AMBIGUITIES: Whether `Department.managerId` is the Stage-1 approver for that employee’s requests is **implied** by Spec03 purpose text, not closed as a catalog decision. Nullable `department_id` means assignment is optional.  
DOWNSTREAM_IMPACT: A2_ACTOR_SEMANTICS; C1_OPEN_DECISIONS  
RECOMMENDED_NEXT_GATE: A2  

---

### 2. Employee ↔ Request

RELATIONSHIP: Employee ↔ Request  
CLASSIFICATION: EXPLICIT  
EVIDENCE_SUMMARY: Request stores `employee_id` as UUID reference (no cross-module FK). Domain `Request.employeeId` (`EmployeeReferenceId`). Self-service ownership enforced via principal→employee match (`RequestPrincipalEmployeeResolver`). Spec05 defines submitting employee as request owner.  
EVIDENCE_SOURCES:
- `app/Modules/Request/Domain/Entities/Request.php` (`EmployeeReferenceId $employeeId`)
- `database/migrations/modules/request/2026_06_26_000001_create_requests_table.php` (`employee_id` indexed; no FK)
- `app/Modules/Request/Application/Services/RequestPrincipalEmployeeResolver.php`
- `specs/005-request-management/spec.md` / `data-model.md`
- `.specify/docs/discovery/domain-authority-and-organization-model-discovery.md` (§ Request owner / self-access)
CARDINALITY_EVIDENCE: PARTIAL  
OWNERSHIP_SIGNAL: CLEAR  
CONFLICTS_OR_AMBIGUITIES: Cross-module FK prohibited by design (AP-04 / Spec05) — reference is explicit without DB FK. Approver visibility of non-owned requests remains **not** defined (separate from this ownership link).  
DOWNSTREAM_IMPACT: A3_OWNERSHIP_MODEL; NO_BLOCKING_IMPACT (for self-service surface data link)  
RECOMMENDED_NEXT_GATE: NONE  

---

### 3. Employee ↔ Dependent

RELATIONSHIP: Employee ↔ Dependent  
CLASSIFICATION: EXPLICIT  
EVIDENCE_SUMMARY: Dependent entity requires `employeeId`; migration FK `employee_dependents.employee_id` → `employee_employees` with cascade delete. CD-009: Dependent ∈ Employee; Request holds snapshots only.  
EVIDENCE_SOURCES:
- `app/Modules/Employee/Domain/Entities/Dependent.php`
- `database/migrations/modules/employee/2026_07_11_000003_create_employee_dependents_table.php`
- `.specify/docs/catalog-decisions.md` (CD-009)
- `specs/003-employee-context/data-model.md`
- Request `DependentSnapshot` / `request_dependent_snapshots` (snapshot, not ownership of Dependent aggregate)
CARDINALITY_EVIDENCE: CLEAR  
OWNERSHIP_SIGNAL: CLEAR  
CONFLICTS_OR_AMBIGUITIES: Historical constitution wording conflict resolved by CD-009 (Employee owns Dependent; Request snapshots). No remaining ownership conflict in accepted catalog.  
DOWNSTREAM_IMPACT: A3_OWNERSHIP_MODEL; NO_BLOCKING_IMPACT  
RECOMMENDED_NEXT_GATE: NONE  

---

### 4. Request ↔ ApprovalChain

RELATIONSHIP: Request ↔ ApprovalChain  
CLASSIFICATION: EXPLICIT  
EVIDENCE_SUMMARY: Four-stage approval sequence is explicit in Spec05 FR-008, constitution, `ApprovalStage` enum, pending states, and `ApprovalStageResolver`. Approval decisions persisted as `RequestApproval` rows with FK `request_id` → `requests`. CD-010: Request owns approval state/history while Workflow module is deferred. No separate `ApprovalChain` aggregate/table found.  
EVIDENCE_SOURCES:
- `app/Modules/Request/Domain/Enums/ApprovalStage.php`
- `app/Modules/Request/Domain/Entities/RequestApproval.php`
- `app/Modules/Request/Domain/Services/ApprovalStageResolver.php`
- `app/Modules/Request/Domain/States/RequestState.php` (allowed transitions)
- `database/migrations/modules/request/2026_06_26_000002_create_request_approvals_table.php`
- `specs/005-request-management/spec.md` (FR-008)
- `.specify/docs/catalog-decisions.md` (CD-010)
- `.specify/memory/constitution.md` (four-stage chain)
CARDINALITY_EVIDENCE: CLEAR  
OWNERSHIP_SIGNAL: CLEAR  
CONFLICTS_OR_AMBIGUITIES: “ApprovalChain” is a process/sequence label, not a named Domain Entity. Stage labels (e.g. HR, DormitoryUnit) are workflow-stage / role-adjacent labels — **who** acts at each stage (binding/visibility) remains open (not a gap in Request↔approvals persistence). Manager approval is **out of scope** for authorized surface `employee-request-self-service`.  
DOWNSTREAM_IMPACT: A2_ACTOR_SEMANTICS; A4_LIFECYCLE_MODEL; C1_OPEN_DECISIONS  
RECOMMENDED_NEXT_GATE: A2  

---

### 5. Dormitory ↔ Building

RELATIONSHIP: Dormitory ↔ Building  
CLASSIFICATION: EXPLICIT  
EVIDENCE_SUMMARY: Building has `dormitoryId`; Domain methods `belongsToDormitory` / `Dormitory::addBuilding`; migration FK `dormitory_buildings.dormitory_id` → `dormitories`.  
EVIDENCE_SOURCES:
- `app/Modules/Dormitory/Domain/Entities/Dormitory.php`
- `app/Modules/Dormitory/Domain/Entities/Building.php`
- `database/migrations/modules/dormitory/2026_07_10_000002_create_dormitory_buildings_table.php`
- `specs/004-accommodation-resource/spec.md`
CARDINALITY_EVIDENCE: CLEAR  
OWNERSHIP_SIGNAL: CLEAR  
CONFLICTS_OR_AMBIGUITIES: None material for this structural link.  
DOWNSTREAM_IMPACT: NO_BLOCKING_IMPACT  
RECOMMENDED_NEXT_GATE: NONE  

---

### 6. Building ↔ Floor

RELATIONSHIP: Building ↔ Floor  
CLASSIFICATION: EXPLICIT  
EVIDENCE_SUMMARY: Floor has `buildingId`; Building holds floors collection with hierarchy checks; migration FK `dormitory_floors.building_id` → `dormitory_buildings`. Floor is a Domain Entity (Spec04 Floor Aggregate disposition).  
EVIDENCE_SOURCES:
- `app/Modules/Dormitory/Domain/Entities/Building.php`
- `app/Modules/Dormitory/Domain/Entities/Floor.php`
- `database/migrations/modules/dormitory/2026_07_10_000003_create_dormitory_floors_table.php`
- Spec04 governance notes on Floor Aggregate (cited in prior authority map)
CARDINALITY_EVIDENCE: CLEAR  
OWNERSHIP_SIGNAL: CLEAR  
CONFLICTS_OR_AMBIGUITIES: Historical OA-04-01 “Floor as attribute vs aggregate” tension is superseded for current Spec04 disposition by Floor Entity presence — not reopened here.  
DOWNSTREAM_IMPACT: NO_BLOCKING_IMPACT  
RECOMMENDED_NEXT_GATE: NONE  

---

### 7. Floor ↔ Room

RELATIONSHIP: Floor ↔ Room  
CLASSIFICATION: EXPLICIT  
EVIDENCE_SUMMARY: Room has `floorId`; Floor holds rooms; migration FK `dormitory_rooms.floor_id` → `dormitory_floors`.  
EVIDENCE_SOURCES:
- `app/Modules/Dormitory/Domain/Entities/Floor.php`
- `app/Modules/Dormitory/Domain/Entities/Room.php`
- `database/migrations/modules/dormitory/2026_07_10_000004_create_dormitory_rooms_table.php`
CARDINALITY_EVIDENCE: CLEAR  
OWNERSHIP_SIGNAL: CLEAR  
CONFLICTS_OR_AMBIGUITIES: None material.  
DOWNSTREAM_IMPACT: NO_BLOCKING_IMPACT  
RECOMMENDED_NEXT_GATE: NONE  

---

### 8. Room ↔ Bed

RELATIONSHIP: Room ↔ Bed  
CLASSIFICATION: EXPLICIT  
EVIDENCE_SUMMARY: Bed has `roomId`; Room owns bed collection / capacity; migration FK `dormitory_beds.room_id` → `dormitory_rooms`.  
EVIDENCE_SOURCES:
- `app/Modules/Dormitory/Domain/Entities/Room.php`
- `app/Modules/Dormitory/Domain/Entities/Bed.php`
- `database/migrations/modules/dormitory/2026_07_10_000005_create_dormitory_beds_table.php`
- CD-014 (Allocation vs occupancy ownership — adjacent; not expanded as a scoped relationship here)
CARDINALITY_EVIDENCE: CLEAR  
OWNERSHIP_SIGNAL: CLEAR  
CONFLICTS_OR_AMBIGUITIES: None material for Room↔Bed structural link.  
DOWNSTREAM_IMPACT: NO_BLOCKING_IMPACT  
RECOMMENDED_NEXT_GATE: NONE  

---

### 9. Department ↔ Dormitory

RELATIONSHIP: Department ↔ Dormitory  
CLASSIFICATION: GAP  
EVIDENCE_SUMMARY: No Domain Entity field, migration FK, or catalog decision linking Department to Dormitory as organizational or site structure. Both appear as **adjacent labels** in the approval stage sequence (DepartmentManager → … → DormitoryManager), which is Request workflow staging — not a Department↔Dormitory structural relation.  
EVIDENCE_SOURCES:
- Negative evidence: no matches for department↔dormitory FK/entity link in Employee/Dormitory modules (scan)
- Contrast: approval chain prose in constitution / Spec05 / Discovery (stage sequence only)
- `.specify/docs/discovery/domain-entity-relationship-authority-map.md` (Organization / Department↔Dormitory missing)
CARDINALITY_EVIDENCE: NONE  
OWNERSHIP_SIGNAL: NONE  
CONFLICTS_OR_AMBIGUITIES: Required — no structural link found. Do not invent org ownership between departments and dormitory sites. Approval-chain co-occurrence must not be upgraded to an org relation.  
DOWNSTREAM_IMPACT: B1_HUMAN_DOMAIN_AUTHORITY; C1_OPEN_DECISIONS; A2_ACTOR_SEMANTICS  
RECOMMENDED_NEXT_GATE: B1  

---

## Summary

### 1. Relationship Coverage Summary

| Classification | Count |
| -------------- | ----- |
| EXPLICIT | 8 |
| IMPLIED | 0 |
| AMBIGUOUS | 0 |
| GAP | 1 |

**Scoped relationships reviewed:** 9 / 9  

### 2. High-Risk Ambiguities

Relationships that materially affect actor semantics, ownership, authority, or auth-basis preparation (within this scoped list):

| Relationship | Why material |
| ------------ | ------------ |
| Employee ↔ Department | Structure EXPLICIT; Stage-1 approver binding via `managerId` still open (A2) — **out of approved self-service scope** but relevant if Auth packet later expands |
| Request ↔ ApprovalChain | Persistence/sequence EXPLICIT; **actor binding / approver visibility** not closed (A2) — excluded from surface capabilities but blocks broader Auth residual narrative |
| Department ↔ Dormitory | **GAP** — no org/site link; blocks inventing department-scoped dormitory authority |

**Not high-risk for current authorized surface data path:** Dormitory hierarchy links (5–8); Employee↔Request self-ownership; Employee↔Dependent (CD-009).

**Authority blocker outside relationship invention:** Business Owner remains `PENDING_HUMAN_DESIGNATION` (prior gates) — blocks Spec04 Auth packet regardless of relationship EXPLICIT counts.

### 3. Readiness Statement

```text
RELATIONSHIP_EVIDENCE_CONSOLIDATED = YES
```

All scoped relationships were reviewed and classified. Consolidation does **not** mean ownership/actor/auth gaps are closed.

### 4. Next Recommended Gate

```text
B1_HUMAN_DOMAIN_AUTHORITY_CLARIFICATION
```

**Routing rationale (evidence-only):** Structural request/self-service relationships are largely EXPLICIT. Remaining material blockers for Auth readiness are human domain authority / Business Owner designation (and related org-authority gaps such as Department↔Dormitory), not missing Employee↔Request or physical hierarchy evidence. Aligns with current `PENDING_HUMAN_DESIGNATION` / Spec04 Auth **BLOCKED**.

---

## Governance Continuity (unchanged)

| Field | Value |
| ----- | ----- |
| Authorized surface | `employee-request-self-service` |
| Product surface | Authorized (prior decision) |
| Business Owner | `PENDING_HUMAN_DESIGNATION` |
| Spec04 Auth | `BLOCKED` |
| UI / implementation | **Not authorized** |
| Auth packet / auth design | **Not started** |

> **Footnote (DOC hygiene 1405/04/24 — framing only, no new decision):**  
> **F2 login AUTHORIZED ≠ Spec04 Auth unblocked.** Product authorization for boundary `employee-auth-ui` (`docs/product/product-authorization-employee-auth-ui.md`) covers employee login/session UI only. Rows above for Spec04 Auth `BLOCKED` / Business Owner `PENDING_HUMAN_DESIGNATION` / “UI not authorized” remain the Auth-packet and self-service *expansion* posture — they are not contradicted by F2 login authorization, nor do they authorize Spec04 Auth or invent Organization/Unit aggregates.

---

## Required Final Lines

```text
RELATIONSHIP_EVIDENCE_CONSOLIDATION_STATUS: COMPLETE

AUTHORIZED_SURFACE: employee-request-self-service

SPEC04_AUTH_READINESS: BLOCKED

BUSINESS_OWNER_STATUS: PENDING_HUMAN_DESIGNATION

NEXT_RECOMMENDED_GATE: B1_HUMAN_DOMAIN_AUTHORITY_CLARIFICATION

APPLICATION_FILES_MODIFIED: NO
```
