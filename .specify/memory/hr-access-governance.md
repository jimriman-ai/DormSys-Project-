# Canonical Record: employee-request-self-service

# Boundary: HR Access Governance Cluster

# Phase: Spec / Domain Decision (Pre-Implementation)

# Naming convention: All roles and entities are recorded in English

# Last Updated: 2026-07-14

## Status

CLUSTER_PHASE: Pre-Implementation
ALL_ACTIVE_GAPS_IN_THIS_CLUSTER_CLOSED: true
IMPLEMENTATION_GATE_OPEN: false

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

- Access: Write, cross-department (all data within the authorized product surface)

- Delegation: Non-delegatable
Note: Resolved at the governance layer.
Delegation enforcement mechanism is downstream (Layer 4).

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
Decision: Entities `Role` + `UserRole` (pivot table `model_has_roles`) —
many-to-many relationship between `User` and `Role`

---

## DDG-HR-ROLE-ASSIGN-GUARD — Role Assignment Enforcement

Status: DEFERRED
Layer: 4 (Implementation)
Reason: Role structure and the `model_has_roles` relationship are defined;
assignment enforcement details belong to the
Authorization/Implementation phase.
No gate opened: true
Reopen condition: Entering the Authorization/Implementation phase
AND an explicit human decision.
This gap does NOT reopen automatically.

---

## Confirmation

HDAC05_RESOLVED_AT_GOVERNANCE_LAYER: true
HDAC06_RESOLVED_AT_GOVERNANCE_LAYER: true
DDG_HR_ENTITY_RESOLVED: true
DDG_HR_ACCESS_SCOPE_RESOLVED: true
DDG_HR_ROLE_LOCK_RESOLVED: true
DDG_HR_ROLE_ASSIGN_GUARD_DEFERRED: true
NO_GATE_OPENED: true
NO_IMPLEMENTATION_DETAIL_IN_GOVERNANCE_DECISIONS: true
ALL_ACTIVE_GAPS_IN_THIS_CLUSTER_CLOSED: true
IMPLEMENTATION_AUTHORIZED: false
