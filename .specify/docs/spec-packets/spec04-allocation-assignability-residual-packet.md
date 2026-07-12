---
artifact: spec04_allocation_assignability_residual_packet
spec: Spec04
status: PACKET_COMPLETE
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
recorded: 2026-07-12
---

# Spec04 Allocation Assignability Residual Packet

**Artifact type:** Residual packet definition (non-authorizing)  
**Recorded:** 2026-07-12  
**Checkpoint:** `spec04-allocation-assignability-residual-packet`

Ownership inputs (fixed, not reopened):

- `.specify/docs/decision/spec04-residual-ownership-decision.md` — Spec04 owns Allocation ↔ Dormitory residual
- `.specify/docs/decision/spec04-allocation-physical-state-ownership-decision.md` — `SPEC04_OWNS_ASSIGNABILITY_STATE`

Readiness input: `.specify/docs/discovery/spec04-allocation-residual-readiness-review.md` (`NEEDS_REFINEMENT`)

---

## 1. Packet Context

- Wave 02 governance completion is done; regularization mode exited.
- Spec04 residual ownership and assignability-state ownership are fixed to Spec04.
- Spec07 remains limited to occupancy / check-in / resident-presence lifecycle truth.
- This packet defines the **smallest safe residual implementation unit** only after verifying it stays inside Spec04 (with an identified Spec04-internal domain extension need).
- This packet does **not** authorize implementation.

---

## 2. Repository Evidence Summary

| Area | Current evidence |
| ---- | ---------------- |
| Null / stub path | `AllocationServiceProvider` binds `DormitoryReadPort` → `NullDormitoryReadAdapter` (UUID-valid ⇒ exists/assignable); `PhysicalStateSignalPort` → `NullPhysicalStateSignalAdapter` (no-op) |
| Allocation consumption | `CreateAllocationAction` / release path call `isBedAssignable` and `reserveBed` / `occupyBed` / `releaseBed` |
| Spec04 live supplier | Missing Application assignability reads; missing inbound `AllocationPhysicalStatePort` + apply action |
| Integrations bridge | No Allocation↔Dormitory live bridge under `app/Integrations/` (Request `siteExists` bridge is a different residual) |
| Existing Spec04 inventory structures | Bed hierarchy; `ResourceStatus` (Available/Unavailable/Maintenance/Inactive); `PhysicalOccupancyState` (**Vacant / Occupied only**); `BedSummaryData` exposes `status` + `physicalOccupancyState`; Phase 3C occupancy mutation APIs exist |
| Design vs code tension | Spec04 design contract `allocation-physical-state-port.md` requires **Reserved** transitions; current `PhysicalOccupancyState` enum explicitly excludes reservation from physical occupancy |

---

## 3. Boundary Verification Summary

| Question | Answer |
| -------- | ------ |
| Does Spec04 already contain required domain concepts? | **Partially** — Bed, resource status, vacant/occupied markers exist |
| Does assignability require new domain concepts? | **Yes, Spec04-internal** — allocation-time **Reserved** (or equivalent inventory lock) is designed but **not** present in current Spec04 enum/model |
| Does assignability require ownership transfer from another module? | **No** — Spec04 owns assignability; Spec07 keeps check-in presence |
| Spec04 residual vs new capability / boundary extension? | Remains a **Spec04 Product residual**, but requires **Spec04 domain extension** of inventory markers before a safe Feature Contract |

**Result:** Residual **fits Spec04 ownership** and does **not** require a new bounded context or Spec07 transfer. It **does** require Spec04-internal domain extension (inventory marker model) before contract/implementation.

---

## 4. Scope Table

| Component | Ownership Basis | Current State | Required Change Type | In / Out | Boundary Impact | Notes |
| --------- | --------------- | ------------- | -------------------- | -------- | --------------- | ----- |
| Allocation-time `bedExists` / `isBedAssignable` Application reads | Spec04 assignability ownership | Missing Application API | New Spec04 Application Read (+ service) | **In** | Within Spec04 | Derive from bed + ResourceStatus + inventory markers |
| Inventory markers for allocation-time truth | Spec04 physical inventory | Vacant/Occupied only; design wants Reserved | Spec04 domain/persistence extension | **In** | **Domain extension** | Narrow marker-model decision required first |
| Inbound `AllocationPhysicalStatePort` + apply action | Spec04 | Design contract only; not implemented | New Spec04 Application Port + action | **In** | Within Spec04 after marker model | Maps Allocation signals → Spec04 markers |
| Live Integration binding replacing Null adapters | Spec04 residual + composition | Null active in Allocation provider | Integration bridge + binding swap | **Deferred follow-on** | Spec07 composition / IRG risk | **Out of this packet’s first unit**; after Spec04 supplier exists |
| Check-in / resident presence | Spec07 | Separate | None | **Out** | Must not pull into Spec04 | D2 unchanged |
| Spec02 auth | Spec02 | Frozen | None | **Out** | — | Separate residual |
| Dormitory UI | Independent UI feature | Not authorized | None | **Out** | — | — |
| Full dormitory inventory productization | Spec04 Product residuals generally | Structure already delivered | None beyond assignability | **Out** | Avoid scope creep | Packet ≠ full inventory ownership expansion |

---

## 5. Technical Packet Definition

### Packet name

`Spec04 Allocation Assignability Residual` (supplier-first)

### Smallest safe unit (after marker-model clarification)

1. **Assignability facts Spec04 must own**
   - Bed exists in Spec04 inventory
   - Bed is operationally usable (`ResourceStatus` usable/available)
   - Bed inventory marker permits assignment (post–marker-model decision)
   - Exposed as Spec04 Application reads consumed by Allocation ports (`bedExists`, `isBedAssignable`)

2. **Physical inventory markers required**
   - Authoritative Spec04 markers used for allocation-time truth
   - Must reconcile design `reserve` / `occupy` / `release` with current Vacant/Occupied-only enum (**domain extension**)

3. **Live replacement of Null path (Spec04 side)**
   - Implement Spec04 supplier surfaces so a later Integration adapter can stop relying on UUID-format Null truth
   - This packet **defines** Spec04 supplier completeness; **does not** itself authorize Spec07 provider rebinding

4. **Consumers**
   - Allocation `CreateAllocationAction` / release physical-signal path (existing) — consume live reads/signals later via Integration
   - Spec04 apply action for inbound marker transitions

5. **Minimal storage / read-model / integration shape**
   - Prefer reuse of existing bed tables/columns where possible
   - Add Spec04 Application contracts/services/ports
   - Migration only if marker-model extension requires new persisted state (to be decided in narrow boundary decision)
   - Integration live bridge = **follow-on packet**, not part of the first Spec04 supplier unit

### Domain objects / services / tests (bounded)

| Layer | Affected (planned) |
| ----- | ------------------ |
| Domain | Inventory marker model extension (Reserved or alternate); assignability rule using ResourceStatus + markers |
| Application | Allocation-facing read contract/service; `AllocationPhysicalStatePort`; apply action |
| Infrastructure | Repository queries; Spec04 adapters as needed; **not** Allocation Null binding swap in this unit |
| Tests | Spec04 unit/feature for assignability + marker transitions; no Spec07 reopen suite as primary |

---

## 6. Risks and Non-Goals

### Risks

- Implementing `reserveBed` against Vacant/Occupied-only enum without a marker-model decision would **falsify** Spec04 domain comments and design contract.
- Bundling Spec07 Null-replacement into the first packet risks reopening Fully Closed Spec07 composition without IRG.
- Over-expanding into “full dormitory inventory” or check-in presence collapses D1/D2 ownership.

### Non-goals

- Check-in lifecycle / resident presence / occupancy history as Spec07 stay truth
- UI workflows
- Auth/permission redesign
- Reopening Spec07 boundaries for inventory ownership
- Converting Spec04 into unrelated inventory product work beyond allocation-time assignability
- Immediate Implementation Authorization

### Dependencies still blocking Feature Contract (narrow)

- **Spec04 inventory marker model** (Reserved vs Vacant/Occupied-only) must be decided inside Spec04 before Feature Contract locks I/O.
- Spec07 live binding remains a **later** dependency and does **not** block defining Spec04 supplier scope, but blocks end-to-end Null replacement.

---

## 7. Next Artifact Decision

```text
SPEC04_ALLOCATION_ASSIGNABILITY_RESIDUAL_PACKET

Packet Result:
PACKET_DEFINED_WITH_DOMAIN_EXTENSION

Next Required Artifact:
NARROW_BOUNDARY_DECISION_REQUIRED

Implementation Scope:
Spec04 supplier: allocation-time assignability reads + inbound physical inventory marker port (after Spec04 Reserved/marker-model decision); Integration Null-replacement deferred
```

**Rationale for next artifact:** Ownership is fixed to Spec04, but current `PhysicalOccupancyState` (Vacant/Occupied only) conflicts with the designed Reserved transition path. A narrow Spec04 marker-model decision is required before a Feature Contract can lock inputs/outputs safely.

---

## 8. Guardrails

- This packet does **not** authorize implementation.
- This packet does **not** reopen ownership decisions.
- This packet does **not** transfer occupancy lifecycle / check-in into Spec04.
- This packet does **not** create UI scope.
- This packet does **not** imply full dormitory inventory ownership expansion by Spec04.
- Downstream execution still requires proper authority (narrow decision → Feature Contract → IA as applicable).

---

## Document Control

- Artifact: `spec04_allocation_assignability_residual_packet`
- Path: `.specify/docs/spec-packets/spec04-allocation-assignability-residual-packet.md`
- Spec: Spec04
- Status: `PACKET_COMPLETE`
- Mutation permission: none
- Execution authority: none
- Operating mode: `FEATURE_AND_SPEC_COMPLETION_MODE`
- Last Updated: 2026-07-12
