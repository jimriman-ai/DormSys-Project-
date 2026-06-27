# spec05 Implementation Authorization

**Recorded:** 2026-06-23  
**Authority:** Product / Tech governance  

---

## Status

**Implementation Authorized**

---

## Baseline

### Design baseline

| Reference | Value |
| --------- | ----- |
| Tag | `spec05-design-approved` |
| Commit | `6ce0e94` |

### Task baseline

| Reference | Value |
| --------- | ----- |
| Commit | `61e2a48` |
| File | `specs/005-request-management/tasks.md` |

**Prior authorization:** [`spec05-planning-authorization.md`](./spec05-planning-authorization.md), [`spec05-design-approved.md`](./spec05-design-approved.md)

---

## Authorized Scope

Implementation is authorized **only** for:

| Scope | Detail |
| ----- | ------ |
| Specification tree | `specs/005-request-management/` (implementation alignment only — no redesign without change request) |
| Bounded context | **Request** |
| Tasks | **T001–T052** only — per `tasks.md` |
| Migrations | `database/migrations/modules/request/` — Request module only |
| Code | `app/Modules/Request/` — Request module only |
| Tests | `tests/Feature/Modules/Request/`, `tests/Unit/Modules/Request/`, `tests/Architecture/RequestConsumerBoundaryTest.php` |
| Adapters | Approved contracts only — `EmployeeEligibilityContract` consumer, `PendingRequestReadPort` adapter, `RequestReadContract` supplier, optional `DormitoryReadContract` / `NullDormitoryReadAdapter` |

### Implementation waves (from tasks.md)

| Wave | Task IDs | Notes |
| ---- | -------- | ----- |
| Wave 1A | T001–T036, T045–T052 | Personal, lifecycle, approvals, supplier, polish |
| Wave 1B | T037–T039 | FamilyDirect — spec03 US3 gate preserved |
| Wave 1C | T040–T044 | Mission, LotteryRegistration |

---

## Execution Rules

Implementation **MUST**:

- Start from **T001**
- Follow **`tasks.md`** order and phase structure
- Complete tasks **sequentially** within authorized waves
- Preserve approved design decisions (`research.md`, `data-model.md`, `contracts/`)
- Create **ADR / change request** before any architectural deviation

**No task skipping.**

```text
Implementation may begin only from tasks.md.
No task skipping.
No redesign during implementation without ADR/change request.
```

---

## Protected Boundaries

The implementation **MUST NOT**:

| Prohibited | Reason |
| ---------- | ------ |
| Change spec01 kernel | Platform foundation frozen |
| Change UUID foundation (`HasUuid`, assignment rules) | spec01 contract |
| Change Constitution rules | Governance document — not in scope |
| Change CD-009 / CD-010 / CD-013 | Closed catalog decisions |
| Change Context Map | Relationship inventory frozen |
| Change Employee ownership | spec03 bounded context |
| Change Dormitory ownership | spec04 frozen |
| Implement Workflow module | Deferred per catalog |
| Implement Lottery logic | spec06 — not authorized |
| Implement Allocation logic | spec07 — not authorized |
| Modify spec01 / spec02 / spec03 / spec04 artifacts | Change request required |
| Reopen spec03 US3 / US4 without separate authorization | Hold preserved |

---

## Boundary Rules

### Request owns

- Request lifecycle state
- `RequestApproval` history (append-only)
- Request submission rules (BR-01 enforce + date rules)
- Dependent **snapshots** on FamilyDirect (CD-009)
- `RequestMember` for Mission requests

### Request does NOT own

| Concern | Owner |
| ------- | ----- |
| Employee aggregate | Employee (spec03) |
| Dependent lifecycle | Employee (spec03) |
| Dormitory catalog | Dormitory (spec04) |
| Allocation lifecycle | Allocation (spec07) |
| Lottery execution | Lottery (spec06) |
| Workflow orchestration | Workflow (deferred) |

---

## Required Dependency Direction

### Allowed

```text
Request → EmployeeEligibilityContract          (compute at submit)
Request → IdentityUserReadContract             (optional approver validation)
Request → DormitoryReadContract                (optional site validation)
Request → approved Application contracts only
Employee  ← PendingRequestReadPort adapter     (read-only; OA-05-09)
Downstream ← RequestReadContract               (read-only projections)
```

### Forbidden

```text
Request → employee_* database tables           (direct Eloquent / SQL)
Request → dormitory_* database tables
Request → allocation_* database tables
Request → lottery_* database tables
Request → identity_* database tables           (cross-module FK prohibited)
PendingRequestReadPort → Request commands      (query-only port)
RequestReadContract → lifecycle mutation       (read-only supplier)
```

**Normative contracts:** `specs/005-request-management/contracts/`

---

## Dependency status (at authorization)

| Dependency | Status | Usage |
| ---------- | ------ | ----- |
| spec01 Foundation | Approved | Module scaffold, kernel |
| spec02 Identity | Frozen Wave 1A | Auth, optional approver read |
| spec03 Employee | Wave 1A+1B | `EmployeeEligibilityContract`; US3 hold for Wave 1B |
| spec04 Dormitory | Design approved / impl hold | `NullDormitoryReadAdapter` until spec04 impl |

---

## Protected status (unchanged)

| Scope | State |
| ----- | ----- |
| spec04 | **Frozen** — implementation hold |
| spec03 US3 / US4 | **Hold** |
| spec06–spec11 | **Not authorized** |
| Workflow | **Deferred** |

---

## Final Gate

After this authorization:

**Implementation may begin at T001.**

No implementation work was authorized before this document.

---

## References

- `spec-catalog.md` — spec05 Implementation Authorized
- `specs/005-request-management/tasks.md` — T001–T052
- Tag `spec05-design-approved` @ `6ce0e94`
- Tasks commit `61e2a48`
