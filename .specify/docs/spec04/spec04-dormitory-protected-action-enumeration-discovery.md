---
artifact: spec04_dormitory_protected_action_enumeration_discovery
status: DISCOVERY_RECORDED
mutation_permission: none
execution_authority: none
operating_mode: NON_AUTHORIZING_DISCOVERY
decision: ACTIONS_ENUMERATED
date: 2026-07-12
---

# Spec04 Dormitory Protected-Action Enumeration — Discovery

**Artifact type:** Protected-action enumeration (non-authorizing discovery)  
**Upstream block:** `.specify/docs/spec02/spec02-dormitory-surface-permission-vocabulary-discovery.md` (`VOCABULARY_BLOCKED_BY_MISSING_DOMAIN_INPUT`)  
**Status:** `DISCOVERY_RECORDED`

This artifact lists **concrete Spec04 Dormitory actions** evidenced in Application contracts/code and Spec04 specs. It does **not** invent permission keys, roles, Check-in actions, Allocation assignment decisions, UI implementation, or Spec02 vocabulary.

---

## A. Current Context

Spec02 permission vocabulary discovery was blocked because no systematic list of Dormitory actions existed to map against. Spec04 owns **description of Dormitory business/surface actions**; Spec02 owns Auth semantics (Ownership D3). Listing actions must precede naming permissions so Spec02 does not invent product scope.

Closed Assignability / retired Check-in residuals are **not** reopened; Allocation person-to-bed assignment remains Spec07 (FR-EX-001).

---

## B. Enumerated Protected Actions

**Surface key:** All **Implemented** actions below are **Application Service** entry points (`DormitoryStructureMutationContract`, `DormitoryStructureReadContract`, `AllocationAssignabilityContract`, `AllocationPhysicalStateApplicationContract`). There is **no** Dormitory Livewire/Controller Presentation layer today. Phase H (deferred) would expose catalog/admin actions via Livewire — same action identities, deferred surface only.

**Plain-text access rules** use constitution / Spec04 ownership language only — **no permission keys**.

### Mutations (Implemented — Application)

| # | Name / Identifier | Surface | Classification | Status | Plain-text access rule | Evidence |
| - | ----------------- | ------- | -------------- | ------ | ---------------------- | -------- |
| 1 | Create dormitory site | Application `createDormitory` | Mutation | Implemented | Administrative catalog change — only actors allowed to **manage rooms/dormitory catalog** (constitution: Dormitory Manager, Dormitory Unit Staff, Admin) when exposed on a human surface | `DormitoryStructureMutationContract.php` L21; Spec04 FR-001–002 |
| 2 | Create building | Application `createBuilding` | Mutation | Implemented | Same — manage rooms/catalog; reject under external sites (business rule, not Auth) | MutationContract L23; OA-04-03 |
| 3 | Create floor | Application `createFloor` | Mutation | Implemented | Same — manage rooms/catalog | MutationContract L25 |
| 4 | Create room | Application `createRoom` | Mutation | Implemented | Same — manage rooms/catalog | MutationContract L27; FR-006 |
| 5 | Create bed | Application `createBed` | Mutation | Implemented | Same — manage rooms/catalog (atomic capacity unit) | MutationContract L29; FR-005 |
| 6 | Change dormitory status | Application `changeDormitoryStatus` | Mutation | Implemented | Same — manage rooms/catalog; deactivation blocked if occupied beds (business policy) | MutationContract L31; Spec04 edge case |
| 7 | Change room status | Application `changeRoomStatus` | Mutation | Implemented | Same — manage rooms/catalog | MutationContract L33 |
| 8 | Change bed operability / status | Application `changeBedStatus` | Mutation | Implemented | Same — manage rooms/catalog; physical operability is Dormitory-owned (FR-007) | MutationContract L35; FR-007 |
| 9 | Record bed occupancy start (Phase 3C local marker API) | Application `recordBedOccupancyStart` | Mutation | Implemented | **Ambiguous surface:** if invoked as human/admin mutation → manage-rooms administrators; Spec04 also documents Allocation-driven marker updates via separate signal path — do not treat as Check-in | MutationContract L37; FR-008 / Phase 3C notes |
| 10 | Record bed occupancy end (Phase 3C local marker API) | Application `recordBedOccupancyEnd` | Mutation | Implemented | Same ambiguity as #9 | MutationContract L39 |
| 11 | Apply Allocation physical-state signal (reserve / occupy-marker / release) | Application `AllocationPhysicalStateApplicationContract::apply` (Integration from Allocation) | Mutation | Implemented | **System/integration consumer** (Allocation → Spec04 via Integration bridge) — not a human Dormitory Livewire action; must not be reinterpreted as Check-in or person-assignment authority | `AllocationPhysicalStateApplicationContract.php`; Assignability closeout |

### Reads (Implemented — Application / Integration)

| # | Name / Identifier | Surface | Classification | Status | Plain-text access rule | Evidence |
| - | ----------------- | ------- | -------------- | ------ | ---------------------- | -------- |
| 12 | List dormitories | Application `listDormitories` | Read | Implemented | Human admin catalog browse → manage-rooms roles when on Phase H/UI; machine consumers use Integration/supplier contracts | `DormitoryStructureReadContract.php` L23; FR-010 |
| 13 | Get dormitory detail | Application `getDormitoryDetail` | Read | Implemented | Same split: admin UI vs supplier/Integration | ReadContract L25 |
| 14 | List buildings for dormitory | Application `listDormitoryBuildings` | Read | Implemented | Same | ReadContract L29 |
| 15 | List floors for building | Application `listBuildingFloors` | Read | Implemented | Same | ReadContract L34 |
| 16 | List rooms for floor | Application `listFloorRooms` | Read | Implemented | Same | ReadContract L39 |
| 17 | List beds for room | Application `listRoomBeds` | Read | Implemented | Same | ReadContract L44 |
| 18 | Bed exists (assignability) | Application `bedExists` via Integration to Allocation | Read | Implemented | **Allocation Integration consumer** — not Spec04 person-assignment; system read for assignment pipeline | `AllocationAssignabilityContract.php` L11 |
| 19 | Is bed assignable | Application `isBedAssignable` via Integration | Read | Implemented | Same — Allocation Integration consumer | AssignabilityContract L16 |
| 20 | Get physical occupancy state | Application `getPhysicalOccupancyState` via Integration | Read | Implemented | Same — Allocation Integration consumer | AssignabilityContract L18 |
| 21 | Site exists (Request dormitory existence) | Integration `DormitoryReadBridge` → Spec04 detail (Request `siteExists`) | Read | Implemented | **Request Integration consumer** — existence check for request submit; not Dormitory admin UI | Request `DormitoryReadContract`; Spec04 Phase 4 bridge |

### Deferred surface (not new actions)

| Item | Status | Note |
| ---- | ------ | ---- |
| Phase H Livewire dormitory catalog admin | Deferred | Spec04 `plan.md` Phase H — would expose catalog mutations/reads (#1–#8, #12–#17) on a human Presentation surface; **no additional action identities** evidenced beyond Application methods |
| HTTP / API / FormRequests for Dormitory | Deferred | Spec04 residual table — same Application actions if later exposed |

**Not enumerated (out of Spec04 Auth residual / boundary):**

- Person-to-bed **Assign / Allocate** (Spec07 Allocation — FR-EX-001)  
- Check-in / Check-out (Spec07 — FR-EX-004; residual retired)  
- Identity login / OA-02-01  

---

## C. Gaps and Ambiguities

| Gap | Detail |
| --- | ------ |
| Dual occupancy mutation paths | `#9–#10` Phase 3C local occupancy APIs vs `#11` Allocation signal `apply` — Spec04 must not conflate with Check-in; Spec02 mapping should treat Integration signal path separately from human admin catalog mutations |
| Human vs Integration consumers | Catalog mutations/reads need constitution **Manage Rooms** rules when on UI; assignability/signal/Request siteExists are **module Integration** consumers — access rule is not the same “Manager role” wording |
| Phase H action subset | Deferred UI does not document which Application methods appear on which screens |
| No Presentation enforcement today | Zero Livewire/Controller guards — enumeration is Application/Spec based |

These gaps do **not** prevent listing actions for Spec02 mapping; they constrain how Spec02 should separate human-admin vs Integration vocabularies later.

---

## D. Discovery Outcome

`ACTIONS_ENUMERATED`

A clear, evidence-backed action inventory exists (21 Application/Integration actions + deferred Phase H as surface note). Ready as **domain input** for Spec02 permission vocabulary discovery resume — without Spec04 inventing permission keys.

---

## E. Immediate Next Step

**Resume Spec02-owned Dormitory-surface permission vocabulary definition discovery**, using this action enumeration as the required Spec04 domain input.

That Spec02 step must:

- map only from this list (no speculative actions)  
- not invent UI/HTTP that does not exist  
- keep Spec02 as Auth owner; Spec04 as action-description owner  
- not authorize implementation or reopen Assignability/Check-in  

---

## Required Final Decision Block

```text
DORMITORY_PROTECTED_ACTION_DISCOVERY

Decision:
ACTIONS_ENUMERATED

Total Actions Enumerated:
21

Mutations:
11

Reads:
10

Actions in Code:
21

Actions in Spec Only:
0

Primary Owner:
SPEC04

Spec04 Boundary Status:
NOT_REOPENED (DISCOVERY_ONLY)

Selection Basis:
Dormitory Application mutation/read/assignability/signal contracts enumerate concrete Spec04 actions in code; constitution Manage Rooms covers human catalog admin; Integration consumers are system paths; Phase H adds no new action identities — sufficient domain input for Spec02 vocabulary without permission naming here.

Immediate Next Step:
Resume Spec02 Dormitory-surface permission vocabulary definition discovery using this action list

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED
```

---

## Scope Integrity Confirmation

| Check | Result |
| ----- | ------ |
| No permission keys invented | **Confirmed** |
| No Check-in / Assign Bed Spec04 claims | **Confirmed** |
| No code / Spec04 core / Spec02 file changes | **Confirmed** |
| Assignability / Check-in residuals not reopened | **Confirmed** |
| Only this discovery artifact written | **Confirmed** |

---

## No-Change Confirmation

`No application, test, catalog, Spec02, or Spec04 source-spec files were modified.`

Only this artifact was created:

- `.specify/docs/spec04/spec04-dormitory-protected-action-enumeration-discovery.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`DISCOVERY_RECORDED`** / **`ACTIONS_ENUMERATED`**  
- Actions: 21 (11 mutation / 10 read), all Implemented in Application/Integration code  
- Last Updated: 2026-07-12
