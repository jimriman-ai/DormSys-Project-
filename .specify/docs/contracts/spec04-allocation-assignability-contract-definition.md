---
artifact: spec04_allocation_assignability_contract_definition
spec: Spec04
status: CONTRACT_DEFINED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
recorded: 2026-07-12
---

# Spec04 Allocation Assignability — Contract Definition

**Artifact type:** Capability contract definition (non-authorizing)  
**Recorded:** 2026-07-12  
**Checkpoint:** `spec04-allocation-assignability-contract-definition`

Upstream:

- Packet: `.specify/docs/spec-packets/spec04-allocation-assignability-residual-packet.md`
- Ownership: `.specify/docs/decision/spec04-allocation-physical-state-ownership-decision.md` (`SPEC04_OWNS_ASSIGNABILITY_STATE`)

**Marker model (binding for this contract):** allocation-time physical inventory markers are `VACANT`, `RESERVED`, `OCCUPIED`. Spec04 owns `RESERVED` (allocated, not yet checked-in). Spec07 remains sole owner of occupancy / check-in / resident-presence truth.

This contract defines **capabilities, flows, and validation rules**. It does **not** lock concrete PHP signatures, DTO class names, migration DDL, or authorize implementation.

---

## 1. Port Interface Capabilities

### 1.1 Assignability read capability (Spec04 supplier)

Allocation-time consumers must be able to obtain Spec04-authoritative answers for a given bed identity:

| Capability | Required behavior |
| ---------- | ----------------- |
| Bed existence | Report whether the bed identity is known in Spec04 inventory |
| Assignability | Report whether the bed may receive a new allocation at decision time |
| Marker visibility | Expose the current allocation-time inventory marker among `VACANT` / `RESERVED` / `OCCUPIED` (and enough context to explain non-assignability, e.g. operational unavailability) |

**Assignability rule (capability-level):**

A bed is assignable only when **all** hold:

1. Bed exists in Spec04 inventory  
2. Bed is operationally usable (existing Spec04 resource/operability meaning)  
3. Allocation-time inventory marker is **`VACANT`**

Beds marked **`RESERVED`** or **`OCCUPIED`** are **not** assignable for a new allocation.

Concrete method names, port interfaces, and DTO types are **deferred** to implementation authorization / review.

### 1.2 Physical-state command capability (Spec04 inbound)

Spec04 must accept allocation-driven inventory marker transitions without owning check-in presence:

| Capability | Required behavior |
| ---------- | ----------------- |
| Reserve | Transition `VACANT` → `RESERVED` for a bed, tied to an allocation signal reference |
| Occupy (inventory marker) | Transition `RESERVED` → `OCCUPIED` when allocation policy requires the inventory occupied marker (distinct from Spec07 check-in truth) |
| Release | Transition `RESERVED` or `OCCUPIED` → `VACANT` on allocation release / unwind |

**Idempotency / safety (capability-level):**

- Same signal reference repeating an already-applied transition must be safe (no corrupt double-apply).
- Illegal transitions (e.g. reserve when not `VACANT`) must fail without silent corruption.
- Signal reference is traceability only (typically allocation identity); no person FK required on Spec04 inventory writes.

Runtime today still uses Allocation-side Null producers/consumers; this contract requires a **live Spec04 provider path** replacing Null truth for reads and Null no-ops for marker application (via approved Integration composition later). Concrete adapter class names are deferred.

---

## 2. Persistence & Schema Capabilities

Spec04 must persistently and queryably retain:

| Capability | Requirement |
| ---------- | ----------- |
| Marker persistence | Current allocation-time marker per bed: `VACANT` \| `RESERVED` \| `OCCUPIED` |
| Signal traceability | Last applied allocation signal reference for inventory transitions (audit/trace), without cross-module FK |
| Queryability | Efficient lookup by bed identity for assignability and marker reads |
| Operational usability | Existing Spec04 operability/resource status remains queryable alongside markers |

**Constraint:** This contract does **not** prescribe table names, column types, or migration shapes unless later implementation evidence requires a specific shape. Prefer extending existing Spec04 bed persistence where feasible; exact DDL is an IA/implementation concern.

**Domain extension note:** Current Spec04 code exposes Vacant/Occupied-only physical occupancy; this contract **requires** `RESERVED` as an allocation-time inventory marker owned by Spec04. That is Spec04-internal model extension already anticipated by the residual packet — not Spec07 ownership transfer.

---

## 3. Application Service Rules (Validation)

### 3.1 Pre-conditions (allocation command pipeline)

Before committing a new allocation assignment to a bed:

1. Resolve Spec04 assignability for the target bed (live provider — not Null UUID-format truth).  
2. **Reject** allocation if the bed does not exist.  
3. **Reject** allocation if the bed is not operationally usable.  
4. **Reject** allocation if the inventory marker is `RESERVED` or `OCCUPIED`.  
5. **Allow** allocation only if the marker is `VACANT` (and existence + usability pass).

### 3.2 Post-conditions (successful allocation)

On successful allocation assignment:

1. Spec04 inventory marker for that bed becomes **`RESERVED`** (allocated, not Spec07-checked-in).  
2. Signal reference for the transition is recorded for traceability.  
3. Subsequent assignability reads for that bed must report **not assignable** while `RESERVED` (or `OCCUPIED`) holds.

### 3.3 Release / unwind

On allocation release (or equivalent unwind):

1. Spec04 inventory marker returns to **`VACANT`** when Spec04 rules allow.  
2. Bed becomes assignable again only after returning to `VACANT` and remaining operationally usable.

### 3.4 Explicit non-rules

- Spec04 validation **must not** decide Spec07 check-in eligibility or resident presence.  
- Spec04 **must not** treat Spec07 occupancy truth as a substitute for Spec04 `RESERVED` inventory marker.

---

## 4. Integration Boundary (Spec04 ↔ Spec07)

| Concern | Owner | Allowed flow |
| ------- | ----- | ------------ |
| Allocation-time assignability + inventory markers (`VACANT`/`RESERVED`/`OCCUPIED`) | Spec04 | Spec04 is source of truth; Allocation consumes Spec04 via Application contracts / Integration bridges |
| Check-in / resident presence / stay occupancy truth | Spec07 | Spec07 sole owner |
| Synchronization | Composition only | Spec04 may **react to Allocation signals** (reserve/occupy-marker/release) through Application ports; Spec07 check-in outcomes must **not** write Spec04 tables directly |

**Hard integration rules:**

- No direct database writes from Spec07 into Spec04 tables.  
- No cross-module Eloquent across Spec04 ↔ Spec07.  
- Live replacement of Null Allocation adapters occurs through Integration / composition-root binding after Spec04 supplier capabilities exist — binding details deferred to IA, not locked here.  
- Spec07 may later consume Spec04 reads if needed for coordination; Spec07 still does not own inventory assignability.

---

## 5. Verification & Test Plan (Behavioral)

| ID | Scenario | Expected |
| -- | -------- | -------- |
| T1 | Allocation execution against a bed whose Spec04 inventory marker is `OCCUPIED` or `RESERVED` | Allocation **fails**; marker unchanged by failed attempt |
| T2 | Allocation execution against a bed whose Spec04 inventory marker is `VACANT` (and usable/exists) | Allocation **succeeds**; Spec04 marker becomes **`RESERVED`** |
| T3 | Live provider path replaces Null assignability/marker path | Reads and transitions resolve real Spec04 states accurately (not UUID-format Null assignability / no-op signals) |
| T4 (supporting) | Illegal marker transition (e.g. reserve when not `VACANT`) | Rejected without corrupting marker |
| T5 (supporting) | Release after `RESERVED` | Marker returns to `VACANT` when Spec04 rules allow; bed assignable again if usable |

Auth (Spec02) and UI scenarios are **out of scope** for this contract’s verification set.

---

## 6. Contract Decision Block

```text
SPEC04_ALLOCATION_ASSIGNABILITY_CONTRACT_DEFINITION

Contract Status:
CONTRACT_DEFINED

Next Required Artifact:
IMPLEMENTATION_AUTHORIZATION_PREP_REQUIRED

Target Code Scope:
Spec04 assignability read capabilities; VACANT/RESERVED/OCCUPIED inventory markers; inbound physical-state transition capabilities; allocation pre/post validation rules; Spec04↔Spec07 integration boundary; behavioral tests T1–T3
```

---

## 7. Guardrails

- This contract does **not** authorize implementation.
- This contract does **not** mutate code, tests, or migrations.
- Auth (Spec02) and UI layers are strictly **out of scope**.
- No direct database writes from Spec07 into Spec04 tables are permitted.
- Ownership boundaries (Spec04 assignability vs Spec07 presence) are **not** reopened.
- Concrete PHP signatures, DTO classes, and migration DDL remain **unlocked** until implementation authorization review.

---

## Document Control

- Artifact: `spec04_allocation_assignability_contract_definition`
- Path: `.specify/docs/contracts/spec04-allocation-assignability-contract-definition.md`
- Spec: Spec04
- Status: `CONTRACT_DEFINED`
- Mutation permission: none
- Execution authority: none
- Operating mode: `FEATURE_AND_SPEC_COMPLETION_MODE`
- Last Updated: 2026-07-12
