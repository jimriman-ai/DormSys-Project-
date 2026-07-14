# Canonical Record: employee-request-self-service

# Boundary: HR Access Governance Cluster

# Phase: Spec / Domain Decision (Pre-Implementation)

# Naming convention: All roles and entities are recorded in English

# Last Updated: 2026-07-14

## Status

CLUSTER_PHASE: Pre-Implementation
ALL_ACTIVE_GAPS_IN_THIS_CLUSTER_CLOSED: true
IMPLEMENTATION_GATE_OPEN: true
IMPLEMENTATION_AUTHORIZED: true # scope-limited: HR Manager / student_records only
IMPLEMENTATION_SCOPE_NOTE: HR Manager / student_records cycle only

---

## HDAC-05 — Business Owner

Status: RESOLVED (Layer 1–2, Governance Decision)
Decision: Business Owner = HR Manager
Accountable Role: HR Manager
Change Authority: HR Manager
Note: Resolved at the governance layer (Business/Governance).
Implementation is downstream; this record does not open it.

---

## HDAC-06 — HR Manager Authority Scope

Status: RESOLVED (Layer 1–2, Governance Decision)
Decision:

- Access: HR Manager can read and edit student records across all departments within the authorized product surface; non-delegatable.

- Delegation: Non-delegatable
Note: Resolved at the governance layer.
Delegation enforcement mechanism is downstream (Layer 4).

### Permission Inventory (Ratified 2026-07-14)
| Permission              | Capability                        |
|-------------------------|-----------------------------------|
| student_records.read    | Read student records (all depts)  |
| student_records.edit    | Edit student records (all depts)  |

Role mapping: Display name: HR Manager | Spatie identifier: HRMgr

Subject binding for student_records.* — Deferred Pending Evidence (no Student entity in codebase)

### Guard Binding
- Auth guard for student_records flow: `api` (Identity `UserModel` provider)
- Authorization principal type: `UserModel` (unchanged)
- Spatie permission/role rows: guard name `web` (matches `UserModel::guardName()` / HRMgr seed)
- Decision: D-L6-4 A (2026-07-14)
- OPEN-1 RESOLVED: `App\Models\User` has no `HasRoles`
- OPEN-2 RESOLVED: default FormRequest `web` principal mismatched; FormRequest now uses `$this->user('api')`

Caveat: Feature tests that call the PEP directly with a `UserModel` bypass HTTP/guard. Green unit of PEP ≠ runtime-ready. HTTP-level test under `api` guard is required before closure.

### Runtime wiring (GAP — open)
- GAP-ID: D-L6-4-C2
- Status: OPEN
- Title: student_records edit route + `api` middleware group not wired
- Note: D-L6-4 A alone does not make the flow runtime-complete. Track separately; do not treat as deferred subject-binding.

### Enforcement Loci
| Permission           | Primary Locus                         | Supplementary         |
|----------------------|---------------------------------------|-----------------------|
| student_records.read | PolicyEnforcementPoint pattern        | —                     |
| student_records.edit | PolicyEnforcementPoint + Form Request | —                     |

Note: Middleware and Livewire component checks are supplementary only.
They are not the canonical authority contract.

---

## DDG-HR-ENTITY — HR Entity Definition

Status: RESOLVED (Layer 3, Domain Decision)
Decision:

- Type: Role assigned to the existing User entity
- Scope: System-wide
Downstream Gap (open, not blocking):
- Define HR as an entity/aggregate in the domain model
- Reopen: during the Domain Modeling phase, by explicit human decision

---

## DDG-HR-ACCESS-SCOPE — Access Enforcement Mechanism

Status: RESOLVED (Layer 4, Domain Decision)
Decision: Dynamic runtime check against the `roles` table

---

## DDG-HR-ROLE-LOCK — Role Table Structure

Status: RESOLVED (Layer 4, Domain Decision)
Decision: Entities `Role` + `Spatie morph (model_has_roles)` (pivot table `model_has_roles`) —
many-to-many relationship between `User` and `Role`

---

## DDG-HR-ROLE-ASSIGN-GUARD — Role Assignment Enforcement

Status: RESOLVED
Layer: 4 (Implementation)
Evidence: "Spatie guard = model guardName (‘web’), UserModel.php:39-42;
auth guard ‘api’ (driver: session, provider ‘identity’ ->
App\Modules\Identity\Infrastructure\Persistence\Models\UserModel,
auth.php:16-19, 28-31) is orthogonal to Spatie guard.
Verified at runtime by 8-test HTTP pass:
200 HRMgr / 403 Administrator / 401 guest under guard ‘api’."

---

## Confirmation

HDAC05_RESOLVED_AT_GOVERNANCE_LAYER: true
HDAC06_RESOLVED_AT_GOVERNANCE_LAYER: true
DDG_HR_ENTITY_RESOLVED: true
DDG_HR_ACCESS_SCOPE_RESOLVED: true
DDG_HR_ROLE_LOCK_RESOLVED: true
DDG_HR_ROLE_ASSIGN_GUARD_DEFERRED: true
NO_GATE_OPENED: false
NO_IMPLEMENTATION_DETAIL_IN_GOVERNANCE_DECISIONS: true
ALL_ACTIVE_GAPS_IN_THIS_CLUSTER_CLOSED: true
IMPLEMENTATION_AUTHORIZED: true
IMPLEMENTATION_AUTHORIZED_SCOPE: HR Manager / student_records cycle only

---

## Decision Ledger (closure log)

D-L6-4: CLOSED — Option A ratified. EditStudentRecordRequest::authorize()
resolves principal via $this->user(‘api’) -> UserModel (HasRoles).

D-L6-5: RESOLVED — permissions + role ‘HRMgr’ seeded with guard_name=‘web’,
matching UserModel::guardName(); no seeder change required.

D-L6-4-C2: OPEN — Deferred Pending Evidence. No production route/middleware
for student-record edit flow; blocked on student entity (aligned with D-L6-2).
