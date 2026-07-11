# Spec04 Application Mutation Layer Contract

## Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `APPLICATION_MUTATION_LAYER_CONTRACT_PREPARED` |
| **Spec** | `004-accommodation-resource` |
| **Domain** | Dormitory / Accommodation Resource |
| **Phase** | Spec04 Backend Implementation Phase 3C – Application Mutation Layer |
| **Authorization effect** | Planning and scoping only — **does not** authorize implementation until this contract (and companion lock/prompt) are reviewed and accepted |
| **Previous accepted gates** | Domain; Persistence; Application Read 3A; Application Read 3B Remaining |
| **Decision date** | 2026-07-11 |

---

## 1. Purpose

Define Phase 3C as the **Application Mutation Layer only** for Spec04 Dormitory.

Phase 3C may introduce application write use cases that:

- Create hierarchy resources under the approved Dormitory → Building → Floor → Room → Bed model.
- Change Dormitory-owned physical/resource status where the accepted Domain already exposes transition behavior.
- Record physical occupancy start/end on beds through the Dormitory application boundary (Dormitory validates and persists state).

Phase 3C does **not** include:

- Integration adapters or cross-module ports
- Authorization policies, gates, or permission wiring
- UI, Livewire, Blade, or frontend work
- Workflow orchestration
- Allocation assignment behavior
- CheckIn/CheckOut operational process ownership
- Voucher behavior
- Controllers, routes, HTTP/API resources, FormRequests
- Domain redesign or persistence/schema redesign

---

## 2. Preconditions

All of the following must remain true before Phase 3C implementation may start (after contract acceptance):

| Precondition | Evidence |
| ------------ | -------- |
| Domain Layer accepted | `spec04-domain-layer-implementation-review.md` |
| Persistence Layer accepted | `spec04-persistence-implementation-review.md` |
| Application Read Phase 3A accepted | `spec04-application-read-layer-review.md` |
| Application Read Phase 3B accepted | `spec04-application-read-layer-remaining-review.md` |
| Backend foundation implementation authorized | `spec04-implementation-authorization.md` |
| Application mutation boundaries designed | `spec04-application-boundary-contract-design.md` §6 |

Baseline regression suite before mutation work: **56 passed** (Domain + Persistence + Application Read).

---

## 3. Allowed Mutation Use Cases

Only use cases that are **explicitly supported** by the approved application-boundary mutation list **and** by the accepted Domain/Persistence implementation are authorized for Phase 3C scoping.

### 3.1 Structure creation (authorized)

| Use case | Application intent | Domain support | Persistence support |
| -------- | ------------------ | -------------- | ------------------- |
| CreateDormitory | Create aggregate root site | `Dormitory::create` | `dormitories` |
| CreateBuilding | Add building under existing dormitory | `Building::create`, `Dormitory::addBuilding` | `dormitory_buildings` + parent FK |
| CreateFloor | Add floor under existing building | `Floor::create`, `Building::addFloor` | `dormitory_floors` + parent FK |
| CreateRoom | Add room under existing floor with capacity | `Room::create`, `Floor::addRoom`, `Capacity` | `dormitory_rooms` + capacity check |
| CreateBed | Add bed under existing room | `Bed::create`, `Room::registerBed` | `dormitory_beds` + room capacity invariant |

Rules for structure creation:

- Parent resource must exist (application validates before persist).
- Hierarchy ownership must match Domain invariants.
- Unique codes/labels within scoped uniqueness already enforced by persistence constraints must not be bypassed.
- New beds default to `ResourceStatus::Available` and `PhysicalOccupancyState::Vacant` unless a later accepted rule says otherwise.
- Do **not** invent external/internal site typing in Phase 3C (no accepted type column on `dormitories`).

### 3.2 Resource status changes (authorized)

| Use case | Application intent | Domain support |
| -------- | ------------------ | -------------- |
| ChangeDormitoryStatus | Update dormitory `ResourceStatus` | `Dormitory::changeStatus` |
| ChangeRoomStatus | Update room status (available / unavailable / maintenance / inactive) | `Room::changeStatus` |
| ChangeBedStatus | Update bed status with occupancy-aware guard | `Bed::changeStatus` |

These cover the application-boundary “mark room/bed available|unavailable|maintenance” intents via the accepted `ResourceStatus` enum. Separate named “Mark*” commands are optional aliases, not additional capabilities.

### 3.3 Physical occupancy recording (authorized)

| Use case | Application intent | Domain support | Ownership note |
| -------- | ------------------ | -------------- | -------------- |
| RecordBedOccupancyStart | Persist vacant → occupied after Domain validation | `Bed::startOccupancy` | Dormitory records state; CheckIn/CheckOut process ownership remains out of Phase 3C (CD-015) |
| RecordBedOccupancyEnd | Persist occupied → vacant after Domain validation | `Bed::endOccupancy` | Same |

Phase 3C may expose these as Dormitory application mutations so later Integration can call them. Phase 3C must **not** implement CheckIn/CheckOut orchestration, Allocation signaling, or integration ports.

### 3.4 Explicitly not authorized in Phase 3C (even if named in older Spec04 tasks)

- Allocation physical-state port / ApplyAllocationPhysicalState
- Reserved occupancy marker beyond accepted `vacant|occupied`
- Artisan presentation commands
- Domain event dispatch as a required deliverable
- Soft-delete / hard-delete resource lifecycle APIs

---

## 4. Forbidden Mutation / Use-Case Scope

Phase 3C must not implement:

| Forbidden area | Rule |
| -------------- | ---- |
| Allocation | No assignment writes; no Allocation ports |
| CheckIn / CheckOut | No operational process; no CheckIn module changes |
| Workflow | No workflow mutation or UI |
| Voucher | No voucher code |
| HTTP / API / controllers / routes / FormRequests | No Presentation write surface |
| Authorization / policies / gates / permissions | Deferred (later authorization phase) |
| External integration adapters | Deferred (Integration phase) |
| UI / Livewire / Blade / frontend | Forbidden |
| Events / listeners / jobs | Forbidden unless a later accepted gate explicitly authorizes them; **not** authorized by this contract |
| Schema / migrations / constraint changes | Forbidden |
| Domain entity redesign | Forbidden |
| Read-layer redesign (3A/3B) | Forbidden |

---

## 5. Application Contracts

### Existing conventions

- Spec04 read layer uses `*Contract` + Application Service + Infrastructure read repository.
- Other modules’ writes commonly use `Application/Services/*Action` with `DB::transaction(...)`.
- Phase 3C must **not** introduce a query/command bus, CQRS framework, mediator, or speculative write UoW abstraction.

### Phase 3C contract shape (required)

Prefer the mutation pattern already used elsewhere in the monolith:

1. One focused `*Action` (or a small cohesive mutation service) per authorized use-case family is acceptable.
2. A mutation contract interface **may** be introduced only if needed for binding/testing parity with the read layer — not a new architectural style.
3. Introduce a **write repository contract** + Eloquent adapter for create/update persistence of hierarchy rows. This is required infrastructure for mutation and was deferred from Persistence Phase 2 by design; it is not a persistence redesign.

Do **not** replace or duplicate `DormitoryStructureReadContract` / Service / Repository.

---

## 6. DTOs / Commands

Define only minimum input/result types for authorized use cases. Avoid broad reusable payload bags.

### Suggested input DTOs (names indicative; final names follow project Pint/PHPStan style)

| DTO | Fields (minimum) |
| --- | ---------------- |
| `CreateDormitoryData` | `code`, `name`, optional `status` |
| `CreateBuildingData` | `dormitoryId`, `code`, `name`, optional `status` |
| `CreateFloorData` | `buildingId`, `label`, optional `status` |
| `CreateRoomData` | `floorId`, `code`, `name`, `capacityTotal`, optional `status` |
| `CreateBedData` | `roomId`, `label`, optional `status` |
| `ChangeResourceStatusData` | `id`, `status` (or dedicated per-resource variants) |
| `RecordBedOccupancyData` | `bedId` |

### Suggested result DTOs

| DTO | Intent |
| --- | ------ |
| `CreatedResourceResult` or resource-specific `*CreatedData` | Return created `id` (+ optionally echo key fields) |
| `ResourceStatusChangedData` / `BedOccupancyChangedData` | Return id + resulting status/occupancy |

Reuse Phase 3A/3B summary DTOs only when returning read-compatible projections is natural. Do **not** leak Eloquent models across the application boundary.

---

## 7. Error Handling

Align with accepted Domain exceptions; do not invent a parallel hierarchy unless convention requires an application wrapper:

| Condition | Expected exception / outcome |
| --------- | ---------------------------- |
| Parent missing | Application-level not-found / hierarchy error (map to existing Domain `InvalidDormitoryHierarchy` or a thin application exception consistent with sibling modules) |
| Hierarchy ownership violation | `InvalidDormitoryHierarchy` |
| Room bed capacity exceeded | `InvalidCapacity` |
| Invalid status transition | `InvalidResourceStateTransition` |
| Invalid occupancy transition | `InvalidOccupancyTransition` |
| Duplicate scoped code/label | Surface persistence uniqueness failure or pre-check; do not weaken DB constraints |

Authorization failures are **out of scope** (no auth in Phase 3C).

---

## 8. Transaction Behavior

- Application mutation actions/services **must** use `DB::transaction` for write paths, matching Employee/Lottery mutation convention.
- Keep transactions at the application service/action boundary.
- Do not add custom unit-of-work infrastructure, distributed transactions, or saga frameworks.
- Single-row updates may still run inside a transaction for consistency with project convention.

---

## 9. Acceptance Criteria

Phase 3C implementation (when later authorized) is acceptable only if:

1. Allowed mutations create/update only approved Dormitory hierarchy/state rows.
2. Invalid inputs and illegal Domain transitions are rejected via Domain/application rules above.
3. Persistence changes occur only for authorized use cases.
4. Phase 3A/3B read contracts remain compatible and green.
5. No integration, authorization, controller/route/API, UI, workflow, allocation, check-in/check-out, or voucher code is added.
6. Existing regression suite continues to pass:
   - Domain tests
   - Persistence tests
   - Application Read tests (**56** baseline)
7. New Feature tests under `tests/Feature/Modules/Dormitory/Application/` cover each authorized mutation use case, including failure paths for Domain guards.

---

## 10. Open Questions (Require User Approval Before Expanding Scope)

These items appear in design language but are **not** authorized for Phase 3C until clarified:

| ID | Question | Why blocked |
| -- | -------- | ----------- |
| OQ-3C-01 | Should Phase 3C allow metadata updates (code/name/label) for dormitory/building/floor/room/bed? | Application boundary lists “update metadata”; accepted Domain has no dedicated update methods beyond public properties. |
| OQ-3C-02 | Should Building/Floor status changes be application mutations? | Application boundary lists them; accepted Domain has no `changeStatus` on Building/Floor. Would require Domain change (locked). |
| OQ-3C-03 | Should room capacity updates be allowed? | Application boundary lists capacity update; accepted Domain has no capacity-change method; changing capacity after beds exist needs invariant rules. |
| OQ-3C-04 | Is “recalculate availability” a mutation or a derived read? | `Room::calculateAvailability` is derived; no accepted stored availability projection table. |
| OQ-3C-05 | Internal vs external dormitory typing and structure rejection for external sites? | Older Spec04 materials require it; accepted Persistence `dormitories` table has no type column. |
| OQ-3C-06 | Should maintenance/inactive propagate to descendants? | Mentioned as validation responsibility; not implemented in accepted Domain. |
| OQ-3C-07 | Soft-delete / deactivate policies when occupied beds exist? | Spec narrative exists; no accepted Domain delete API in Phase 1. |
| OQ-3C-08 | Should mutations emit Domain/Application events in Phase 3C? | Older Spec04 plan mentions events; Integration/Application gates did not authorize event implementation for this phase; Phase 3C lock forbids events unless separately approved. |

**Default Phase 3C position:** implement only §3 authorized use cases; leave OQ-3C-* out until explicitly approved.

---

## 11. Stop Boundary

This contract prepares Phase 3C scoping only.

- It does **not** authorize coding until reviewed and accepted with the implementation lock and execution prompt.
- It does **not** complete Phase 3 Application Layer by itself (mutation must still be implemented and reviewed later).
- It does **not** claim Spec04 Backend Closure.

---

## References

- `spec04-application-boundary-contract-design.md`
- `spec04-domain-design.md` / `spec04-domain-layer-implementation-review.md`
- `spec04-persistence-design.md` / `spec04-persistence-implementation-review.md`
- `spec04-integration-boundary-design.md` (occupancy ownership; Integration deferred)
- `spec04-implementation-authorization.md`
- `spec04-application-read-layer-review.md`
- `spec04-application-read-layer-remaining-review.md`
- `.specify/docs/catalog-decisions.md` — CD-014, CD-015
