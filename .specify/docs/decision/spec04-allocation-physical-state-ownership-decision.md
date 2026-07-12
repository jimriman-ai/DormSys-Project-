---
artifact: allocation_physical_state_ownership_decision
spec: Spec04
wave: 02
status: DECISION_COMPLETE
decision_type: ownership_boundary_decision
mutation_permission: none
execution_authority: none
decision_date: 2026-07-12
---

# Spec04 Allocation Physical State Ownership Decision

**Artifact type:** Ownership boundary decision (non-authorizing)  
**Recorded:** 2026-07-12  
**Checkpoint:** `spec04-allocation-physical-state-ownership-decision`

Upstream readiness: `.specify/docs/discovery/spec04-allocation-residual-readiness-review.md` (`NEEDS_REFINEMENT`)  
Prior ownership (not reopened): `.specify/docs/decision/spec04-residual-ownership-decision.md`

---

## 1. Decision Context

### Why this residual exists

Spec04 Product residual deferred Allocation ↔ Dormitory integration, including live `bedExists` / `isBedAssignable` Application Read and inbound physical-marker application (`AllocationPhysicalStatePort` design). Allocation (runtime Spec07 module) already **consumes** assignability reads and **emits** physical-state signals, but both paths bind **Null adapters**. Live truth is therefore missing.

### Why ownership must be clear before implementation

Without an explicit owner for **bed assignability / physical inventory marker state**, a Feature Contract cannot place Application ports, Integration bridges, or tests without mixing:

- assignment responsibility (“who receives the bed”), and  
- physical inventory state (“is this bed available / what marker does it hold”), and  
- resident presence (“did the person check in / occupy as a stay”).

### Why Allocation responsibility ≠ Physical State ownership

Recorded Spec04 residual ownership already assigns **allocation assignment** to Spec04 (who got which room/bed). That does **not** automatically answer who owns **authoritative bed physical availability/marker state** used at allocation time. Assignment answers a person↔bed decision; physical state answers inventory truth on the bed. Those questions must remain separable from Spec07 **check-in / resident-presence** ownership.

Current technical posture (evidence only):

```text
Allocation (consumer/producer)
        |
        |-- DormitoryReadPort (bedExists / isBedAssignable) --> NullDormitoryReadAdapter
        |
        |-- PhysicalStateSignalPort (reserve/occupy/release) --> NullPhysicalStateSignalAdapter
```

Design direction (Spec04 contract, not reopened): physical markers are applied **inbound to Dormitory**; Allocation does not store inventory truth.

---

## 2. Architectural Boundary Analysis

| Responsibility | Governing question | Already recorded owner | This decision |
| -------------- | ------------------ | ---------------------- | ------------- |
| Allocation assignment | Who receives the assignment? | Spec04 (residual D1) | Unchanged |
| Resident presence / check-in | Did the resident enter, occupy, or leave as a stay? | Spec07 (residual D2) | Unchanged — **not** physical inventory |
| Bed assignability / physical inventory markers | Is this bed physically available, and what marker state does it hold for allocation-time validation? | Previously ambiguous | **Decided below** |

### Allocation responsibility

“Who receives the assignment?” — person/request/lottery outcome mapped to a bed reference. Spec04 owns this residual assignment question. Runtime Allocation actions may still execute under Spec07’s closed program surface; ownership of the **residual packet** remains Spec04 and is **not** reinterpreted here.

### Physical state responsibility (this artifact)

“Is this bed physically available and what is its current inventory marker state?” — vacant / reserved / occupied (and related assignability for allocation-time reads). This is **inventory truth**, not check-in process truth.

Mixing Spec07 check-in presence into allocation-time bed inventory would reopen Spec07 and blur D2. Treating Spec07 as the owner of physical inventory reads (Option A) would invert the Spec04 inbound-port design and conflict with Null-adapter wait-for-Spec04-live posture.

---

## 3. Decision Record (D1)

### D1 — Physical State Ownership

**Chosen option:** `SPEC04_OWNS_ASSIGNABILITY_STATE` (**Option B**)

| Field | Value |
| ----- | ----- |
| Decision | Spec04 owns allocation-time **bed assignability** and **physical inventory marker state** (authoritative reads + inbound marker application surface) |
| Plain meaning | Spec04 is the source of truth for “can this bed be assigned?” and bed vacant/reserved/occupied markers used for that purpose |
| Boundary clarification | Spec07 retains **check-in / resident-presence** ownership (D2). Spec04 assignability **must not** be reinterpreted as Spec07 stay/occupancy process ownership |
| Rejected Option A | Spec07 as owner of physical-state reads would invert supplier direction and reopen Spec07 for inventory truth |
| Rejected Option C | No new bounded context is required; Spec04 Accommodation / Dormitory already hosts inventory |
| Rejected Option D | Deferring with Null leaves ownership unresolved and blocks residual packet refinement |

```text
SPEC04_ALLOCATION_PHYSICAL_STATE_OWNERSHIP_DECISION

Allocation Ownership:
SPEC04

Physical State Ownership:
SPEC04_OWNS_ASSIGNABILITY_STATE

Implementation Authorization:
NOT_GRANTED

Mutation Permission:
NONE
```

---

## 4. Consequences

### What becomes clearer

- Spec04 owns authoritative **assignability reads** and **physical inventory marker application** for the Allocation ↔ Dormitory residual.
- Spec07 owns **resident presence / check-in**, not bed inventory assignability.
- Future Spec04 supplier Feature Contract / packet refinement can target Dormitory Application ports without inventing a new owner.
- Null adapters remain the current runtime posture until a later authorized implementation phase — this decision does **not** replace them.

### What future implementation may depend on (not authorized here)

- Spec04 Application Read for `bedExists` / `isBedAssignable`
- Spec04 inbound `AllocationPhysicalStatePort` (+ apply action)
- Later Integration live binding replacing Allocation Null adapters (separate IRG / composition authorization)

### What remains outside this decision

- Implementation Authorization, adapters, migrations, code
- Spec07 reopen
- Spec02 auth residual
- Dormitory UI
- Spec06/Spec11 regularization
- Residual packet sequencing / Feature Contract drafting
- Conflict-register edits

---

## 5. Guardrails

- No code changes
- No adapter implementation
- No database changes
- No Spec reopening (including Spec07)
- No UI authorization
- No lifecycle closure
- No conflict resolution
- Prior Spec04 residual ownership D1–D4 **not** reopened or rewritten
- This decision does **not** grant Implementation Authorization

---

## Document Control

- Artifact: `allocation_physical_state_ownership_decision`
- Path: `.specify/docs/decision/spec04-allocation-physical-state-ownership-decision.md`
- Spec: Spec04
- Wave: 02
- Status: `DECISION_COMPLETE`
- Decision type: `ownership_boundary_decision`
- Mutation permission: none
- Execution authority: none
- Last Updated: 2026-07-12
