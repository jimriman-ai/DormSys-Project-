---
artifact: spec04_allocation_residual_readiness_review
spec: Spec04
status: DISCOVERY_COMPLETE
readiness_state: NEEDS_REFINEMENT
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
recorded: 2026-07-12
---

# Spec04 Allocation ↔ Dormitory Residual — Readiness Review

**Artifact type:** Residual scope discovery / readiness review (non-authorizing)  
**Work item:** `spec04-allocation-dormitory-residual-readiness`  
**Upstream selection:** `.specify/docs/decision/post-spec04-ownership-next-work-selection.md`  
**Ownership (fixed):** Spec04 owns Allocation ↔ Dormitory residual (D1) — not revisited here

This artifact does **not** authorize implementation, reopen ownership, mutate specs/catalog, or create a UI/Feature Contract.

---

## 1. Discovery Summary

Repository evidence shows a **partial, stubbed bridge** between Allocation (Spec07 module) and Dormitory (Spec04 module), not a live Spec04 supplier surface.

**What Spec07 Allocation already has (consumer side, closed program):**

- `DormitoryReadPort` (`bedExists`, `isBedAssignable`) used by `CreateAllocationAction`
- `PhysicalStateSignalPort` (`reserveBed` / `occupyBed` / `releaseBed`) used on assign/release
- Runtime bindings in `AllocationServiceProvider` → **`NullDormitoryReadAdapter`** and **`NullPhysicalStateSignalAdapter`**
- Null read treats any valid UUID as existing/assignable; Null signal is no-op

**What Spec04 Dormitory already has (supplier-adjacent):**

- Structure read (`DormitoryStructureReadContract`) — hierarchy lists/details; **no** `isBedAssignable` / Allocation-facing read contract
- Structure write repo includes `bedExists`; Phase 3C occupancy mutation records physical markers locally
- Design contract exists: `specs/004-accommodation-resource/contracts/allocation-physical-state-port.md`
- Spec04 closeout explicitly deferred: Allocation ↔ Dormitory integration including `bedExists` / `isBedAssignable` and Application Read extension

**What is missing (live residual):**

- Spec04 `AllocationPhysicalStatePort` + `ApplyAllocationPhysicalStateAction` (tasks T036–T041 unchecked; no class under Dormitory Application Ports)
- Spec04 Allocation-facing `DormitoryReadContract` / assignability queries (tasks T042–T046 unchecked)
- Any `app/Integrations/` bridge for Allocation↔Dormitory (none found; Request has `DormitoryReadBridge` for `siteExists` only)
- Live replacement of Allocation Null adapters

**Boundary (code + ownership, consistent with D1/D2):**

| Concern | Owner | Current code home |
| ------- | ----- | ----------------- |
| Assignment decision / Allocation aggregate | Spec04 residual ownership (D1); runtime in Allocation module | Spec07 `CreateAllocationAction` / Allocation entity stores `bedId` UUID |
| Occupancy / check-in / resident presence | Spec07 (D2) | CheckIn module + Spec07 program; **out of this residual** |
| Physical bed markers (vacant/reserved/occupied) | Dormitory supplier via inbound port (design) | Spec04 occupancy APIs exist; Allocation-driven port **not** implemented |
| Live assignability truth for beds | Spec04 supplier residual | Still Null on Allocation consumer |

---

## 2. Scope Mapping Table

| Feature Component | Owner (Must be Spec04) | Code Location / Target | Status |
| ----------------- | ---------------------- | ---------------------- | ------ |
| Allocation-facing bed existence / assignability read | Spec04 | Planned `DormitoryReadContract` / read service (`specs/004` US5; not in `DormitoryStructureReadContract`) | **Missing** |
| `isBedAssignable` semantics (InService + vacant/etc.) | Spec04 | No Application method; `BedSummaryData` exposes `status` + `physicalOccupancyState` only | **Missing** (data partial on list DTO) |
| Inbound `AllocationPhysicalStatePort` | Spec04 | Planned `app/Modules/Dormitory/Application/Contracts/Ports/AllocationPhysicalStatePort.php` | **Missing** |
| `ApplyAllocationPhysicalStateAction` + transition matrix | Spec04 | Planned Application service per design contract | **Missing** |
| Live Integration bridge Allocation → Dormitory | Spec04 residual packet + composition root | Expected `app/Integrations/` (pattern: Request `DormitoryReadBridge`); **absent** | **Missing** |
| Allocation consumer ports | Spec07 module (already delivered) | `DormitoryReadPort`, `PhysicalStateSignalPort` | **Present** |
| Null consumer adapters | Spec07 bindings (stub until Spec04 live) | `NullDormitoryReadAdapter`, `NullPhysicalStateSignalAdapter` | **Stub** (active) |
| Wrapper adapters (`DormitoryReadAdapter`, `AllocationPhysicalStateAdapter`) | Allocation Infra | Delegate to ports; not Spec04 suppliers | **Partial** (passthrough only) |
| Request→Dormitory `siteExists` live bridge | Spec04 Phase 4 (done; different residual) | `Integrations/Request/DormitoryReadBridge` | **Present** (out of scope for Allocation residual) |

### Scope definition (what this residual would do — draft, not authorized)

If refined into an implementable Spec04 packet, the residual would:

1. **Expose Spec04 Application APIs** so Allocation can validate a bed before assignment (`bedExists` / `isBedAssignable` against real Dormitory state — not UUID-format Null).
2. **Accept Allocation physical-state signals** on Spec04 (`reserve` / `occupy` / `release`) via `AllocationPhysicalStatePort`, applying bed occupancy markers without owning check-in/process presence (D2 remains Spec07).
3. **Wire a live composition-root bridge** so Allocation Null adapters are replaced without cross-module Eloquent.

It would **not** redefine who owns check-in presence, Identity roles, or Dormitory UI.

---

## 3. Dependency Inventory

| Dependency | Required? | Notes |
| ---------- | --------- | ----- |
| New Spec04 migrations | **Unclear / likely no new tables** | Beds/occupancy columns already exist for Phase 3C; port may reuse them. Confirm in refinement — do not invent schema here. |
| New Spec04 Application services / ports | **Yes** | Read assignability + physical-state apply action |
| Spec02 Auth changes | **No for core bridge** | D3 Auth residual is separate; flag if surface policies are pulled in |
| Spec07 module logic changes | **Should avoid** | Spec07 Fully Closed; prefer Integrations binding swap only |
| Spec07 reopen / IRG | **Likely required for live binding** | Replacing Null bindings in `AllocationServiceProvider` or adding Integration bindings touches closed Spec07 composition — **blocker until packet/IRG clarified** |
| Check-in wiring | **No** | D2 Spec07 occupancy/check-in — out of residual |
| UI | **No** | D4 independent; not in this review |

---

## 4. Technical Constraints (Must Not)

This residual implementation (when later authorized) **must not**:

- Handle Check-in / Check-out / resident-presence process logic (Spec07 / D2)
- Modify Identity roles, permissions, or Spec02 auth foundation (D3)
- Invent Dormitory UI or presentation contracts (D4)
- Reopen Spec06/Spec11 regularization
- Use cross-module Eloquent across Allocation ↔ Dormitory
- Treat Phase 3C occupancy mutation APIs as CheckIn ownership
- Silently reopen Spec07 Fully Closed scope beyond an explicitly authorized Integration binding swap
- Reopen Spec04 ownership decisions D1–D4

---

## 5. Readiness Assessment

| Criterion | Result | Evidence |
| --------- | ------ | -------- |
| C1 Code Evidence (bridge clear?) | **Fail / Partial** | Consumer Null stubs clear; Spec04 live supplier + Integrations bridge **absent** |
| C2 Contract Clarity (I/O known?) | **Partial** | Physical-state design contract exists; Allocation-facing read/`isBedAssignable` Application contract **not delivered**; live binding path undefined |
| C3 Safety (closed Specs?) | **At risk** | Spec07 Fully Closed; live Null replacement needs IRG / explicit composition authorization — not safe to jump to Feature Contract without packet split |

### Readiness criteria outcome

**`NEEDS_REFINEMENT`**

Blockers / missing evidence preventing Feature Contract now:

1. **Dual capability bundle** — assignability read (US5-class) vs physical-state inbound port (US4-class) are both missing; Feature Contract cannot yet state a single bounded `authorized-scope` without splitting or sequencing.
2. **Spec07 composition risk** — live wiring requires Integration/provider change against a Fully Closed Spec07 program; IRG / reopen posture not decided.
3. **Assignability rules not Application-coded** — `isBedAssignable` semantics not exposed on Spec04 Application contracts (only list DTO fields exist).
4. **No Integrations target class** — unlike Request `siteExists` bridge, Allocation↔Dormitory live adapter path is unspecified.

Ownership clarity (D1) is **necessary but not sufficient** for contract readiness.

---

## 6. Conclusion & Next Step

| Field | Value |
| ----- | ----- |
| Final readiness state | **`NEEDS_REFINEMENT`** |
| Feature Contract now? | **No** |
| Recommended next artifact | **Residual packet refinement decision** (split/sequence Spec04 supplier ports vs Integration live-binding / IRG posture) |

Suggested next artifact path (not created here):

`.specify/docs/decision/spec04-allocation-residual-packet-refinement.md`

(or discovery-equivalent refinement package)

That refinement must decide:

1. Spec04-only supplier Feature Contract scope (read assignability and/or physical-state port) **without** Spec07 reopen, and/or  
2. Whether Integration Null-replacement is a **separate** IRG-gated packet after Spec04 supplier exists.

---

## Decision Block

```text
SPEC04_ALLOCATION_RESIDUAL_READINESS_REVIEW

Readiness State:
NEEDS_REFINEMENT

Next Required Artifact:
residual packet refinement decision
```

---

## Guardrails

- No implementation authorized
- Ownership D1–D4 not reopened
- Specs, tasks, catalog, code unchanged by this artifact
- No UI requirements invented
- Focus remains backend Allocation ↔ Dormitory bridge only

---

## Document Control

- Artifact: `spec04_allocation_residual_readiness_review`
- Path: `.specify/docs/discovery/spec04-allocation-residual-readiness-review.md`
- Spec: Spec04
- Status: `DISCOVERY_COMPLETE`
- Readiness state: `NEEDS_REFINEMENT`
- Mutation permission: none
- Execution authority: none
- Operating mode: `FEATURE_AND_SPEC_COMPLETION_MODE`
- Last Updated: 2026-07-12
