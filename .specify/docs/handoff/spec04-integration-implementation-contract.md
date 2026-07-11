# Spec04 Integration Implementation Contract

## A. Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `PREPARED_FOR_REVIEW` |
| **Spec** | `004-accommodation-resource` |
| **Phase** | Spec04 Backend Implementation Phase 4 – Integration Implementation |
| **Decision date** | 2026-07-11 |

This artifact **prepares** Phase 4 Integration Implementation governance only.

- It does **not** authorize coding.
- It does **not** authorize Phase 4 implementation.
- It is **not self-authorizing**.
- It does **not** redesign architecture or integration boundaries.
- Implementation may proceed only after governance review acceptance **and** a later explicit implementation authorization that resolves required Open Questions.

---

## B. Preconditions

Phase 4 preparation depends on:

| Precondition | Status |
| ------------ | ------ |
| Phase 1 Domain Layer accepted and locked | Yes |
| Phase 2 Persistence Layer accepted and locked | Yes |
| Phase 3A Application Read Layer Core accepted and locked | Yes |
| Phase 3B Application Read Layer Remaining accepted and locked | Yes |
| Phase 3C Application Mutation Layer accepted with notes and locked | Yes (`APPLICATION_MUTATION_IMPLEMENTATION_ACCEPTED`) |

### Regression baseline (accepted)

| Suite | Result |
| ----- | ------ |
| Domain | 31 passed |
| Persistence | 11 passed |
| Read | 14 passed |
| Mutation | 9 passed |
| **Total** | **65 passed** |

### Phase 3C notes carried forward

- Occupancy mutations remain **Dormitory state recording only**.
- Optional not-found test hardening for status/occupancy is non-blocking.
- Domain, Persistence, Read, and Mutation layers remain locked unless a later accepted governance artifact explicitly allows a narrow change.

---

## C. Phase 4 Scope

### What “Integration” means here (evidence-based)

From accepted Spec04 governance and repository evidence:

1. **Governance meaning** (`spec04-integration-boundary-design.md`): other bounded contexts must interact with Dormitory through approved application boundaries; they must not write Dormitory persistence directly.
2. **Implementation-authorization meaning** (`spec04-implementation-authorization.md` §8): approved *directions* exist for Allocation (consume reads), CheckIn/CheckOut (request occupancy start/end through Dormitory), and Request (read-oriented only). These authorize integration *scope categories* at foundation level, not a concrete Phase 4 file list.
3. **Repository meaning**: consumer modules already declare inbound ports and bind **Null** adapters awaiting a live Dormitory supplier (for example Allocation `DormitoryReadPort`, Allocation `PhysicalStateSignalPort`, Request `DormitoryReadContract`). Spec04 Dormitory currently exposes accepted Application Read/Mutation contracts and has **no** Integration folder/adapters yet.

Therefore, for Spec04 Phase 4, **Integration** means:

> Thin internal wiring (ports/adapters/bindings/tests) that connects approved internal consumers to **already accepted** Dormitory Application Read and/or Mutation capabilities, without adding Dormitory business behavior and without owning consumer workflows.

If a proposed wire cannot map 1:1 (or by explicitly approved mapping) onto an accepted Application capability, it is **out of Phase 4** until resolved as an Open Question.

### Smallest allowed Phase 4 scope (when later authorized)

Phase 4 may include **only**:

- Internal module-facing contracts/interfaces required to expose accepted Dormitory Application capabilities to an **explicitly approved** consumer
- Internal adapters/services that **delegate only** to accepted Application Read/Mutation contracts/services
- Integration DTOs/mappers only when required to prevent consumers from depending on Eloquent models, write/read repository internals, or Domain entities
- Service provider bindings for those integration contracts, if required by existing container conventions
- Integration-level tests proving the approved boundary (delegation + forbidden bypasses)
- Adapter-level orchestration **only** as pass-through delegation (no workflow ownership)

Phase 4 must remain the **smallest** set of artifacts needed for the first approved consumer-capability pair(s). No generic integration framework.

### Explicitly not yet selected

No first consumer or concrete adapter list is authorized by this preparation artifact. Selection requires Open Question resolution (see §J).

---

## D. Non-Goals

Phase 4 does **not**:

- Introduce any new business capability
- Introduce new Dormitory behavior beyond wiring accepted Application capabilities
- Change accepted Domain behavior
- Change accepted Persistence behavior
- Change accepted Read behavior
- Change accepted Mutation behavior
- Create workflow ownership
- Implement Allocation assignment behavior
- Implement CheckIn/CheckOut operational process behavior
- Implement voucher, billing, payment, notification, or external system behavior

---

## E. Allowed Consumers / Producers (candidate evidence only)

These are **design-evidenced candidates**, not Phase 4 implementation authorizations.

| Candidate | Evidence source | Allowed interaction (design) | Accepted Dormitory capabilities that *might* relate | Limitations / gap |
| --------- | --------------- | ---------------------------- | --------------------------------------------------- | ----------------- |
| Allocation | `spec04-integration-boundary-design.md` §5; `spec04-implementation-authorization.md` §8; repo `DormitoryReadPort` + Null adapter | Consume Dormitory read/query for assignment support; assignment ≠ occupancy | Structure reads (`DormitoryStructureReadContract`); bed assignability is **not** an accepted dedicated Application API | Port methods `bedExists` / `isBedAssignable` are **not** present on accepted Read contract → **OQ-4-003** |
| Allocation (signals) | Repo `PhysicalStateSignalPort` (`reserveBed` / `occupyBed` / `releaseBed`); older Spec04 port docs | Design forbids Allocation mutating physical state directly; CD-014/015 place occupancy process with CheckIn | Phase 3C `recordBedOccupancyStart` / `End` are Dormitory recording APIs only | `reserve*` has no accepted Domain/Application capability; wiring this port risks Allocation ownership leakage → **OQ-4-004** |
| CheckIn/CheckOut | `spec04-integration-boundary-design.md` §6; CD-015; Phase 3C occupancy recording notes | May **request** occupancy start/end; Dormitory validates and records | `recordBedOccupancyStart`, `recordBedOccupancyEnd` | CheckIn module has **no** Dormitory port in repository yet; process ownership must stay in CheckIn → **OQ-4-005** |
| Request | `spec04-integration-boundary-design.md` §7; repo `DormitoryReadContract::siteExists` + Null adapter | Read-oriented only | `getDormitoryDetail` / `listDormitories` could support existence checks, but no accepted `siteExists` Application method | Exact mapping not approved → **OQ-4-006** |
| Workflow | Integration boundary §8 | Orchestration only; **no** direct Dormitory write | None for Phase 4 writes | Write integration **not** authorized |
| Notification / Audit / External | Integration boundary §§9–10, 13 | Future consumable outputs only | None accepted as Phase 4 deliverables | Events/external adapters out of Phase 4 unless separately approved → **OQ-4-007**, **OQ-4-008** |

No additional consumers/producers may be invented.

---

## F. Allowed Dependencies

### May depend on

- Accepted Application Read contracts/services (`DormitoryStructureReadContract` / service)
- Accepted Application Mutation contracts/services (`DormitoryStructureMutationContract` / service)
- Accepted Application DTOs returned by those contracts
- Existing Laravel service-container binding conventions (`DormitoryServiceProvider` and consumer providers)
- Existing module boundary conventions already evidenced by Null-port/adapter patterns in Allocation/Request

### Must not depend on / must not expose

- Direct database access from consumer modules
- Direct Eloquent model access from external modules
- Direct Domain entity mutation outside accepted Application Mutation services
- Bypassing accepted Application contracts
- Read repository internals (`DormitoryStructureReadRepository*`)
- Write repository internals (`DormitoryStructureWriteRepository*`)
- Infrastructure persistence models as a public integration surface
- UI / API / controller / FormRequest / policy layers

---

## G. Explicitly Forbidden Scope

Phase 4 forbids:

- Authorization, policies, permissions, roles, guards
- Controllers, routes, API endpoints, FormRequests
- Livewire, Blade, frontend/UI
- Migrations, schema changes, constraint/index/relationship changes
- Domain redesign; Persistence redesign; Read-layer redesign; Mutation-layer redesign
- Workflow ownership
- Allocation implementation (assignment lifecycle code)
- Check-in/check-out implementation (operational process code)
- Voucher, billing/payment, notification behavior
- Event/listener/job infrastructure unless a later accepted governance artifact explicitly requires it
- External system adapters unless explicitly approved
- Speculative extensibility / future-proofing / generic integration framework
- Command-bus / CQRS framework expansion
- New business use cases
- Implementing unresolved **OQ-4-*** items

---

## H. Occupancy Boundary

| Rule | Statement |
| ---- | --------- |
| Phase 3C meaning | Occupancy mutations are **Dormitory state recording only** |
| Phase 4 reinterpretation | Must **not** reinterpret occupancy as check-in/check-out |
| Allocation | Must **not** introduce allocation ownership via integration |
| Reservation | Must **not** introduce reservation ownership |
| Voucher/payment/billing | Must **not** introduce those ownerships |
| CheckIn/CheckOut workflow | Belongs **outside** Phase 4 unless separately approved |
| Allocation workflow | Belongs **outside** Phase 4 unless separately approved |
| Consumer rule | Any future consumer may call accepted Dormitory capabilities **only** through approved integration boundaries |

---

## I. Traceability Requirement

Every allowed Phase 4 integration element must trace directly to an accepted Application Read or Application Mutation capability.

No integration artifact may introduce new business behavior.

For every proposed integration contract, adapter, DTO, mapper, or binding, governance/implementation must identify:

1. The accepted application capability it delegates to
2. The consumer boundary it serves
3. Why it is needed
4. Why direct consumer use of internals would be inappropriate or insufficient

If traceability cannot be established, the item becomes an **Open Question** and must not be authorized.

### Accepted Application capabilities available for traceability

**Read (`DormitoryStructureReadContract`):**

- `listDormitories`
- `getDormitoryDetail`
- `listDormitoryBuildings`
- `listBuildingFloors`
- `listFloorRooms`
- `listRoomBeds`

**Mutation (`DormitoryStructureMutationContract`):**

- Structure creates: CreateDormitory/Building/Floor/Room/Bed
- Status changes: ChangeDormitory/Room/BedStatus
- Occupancy recording: RecordBedOccupancyStart / RecordBedOccupancyEnd

---

## J. Open Questions

Open Questions do **not** authorize implementation and are **not** resolved by this artifact.

| ID | Question |
| -- | -------- |
| **OQ-4-001** | Which module is the **first approved consumer** for Phase 4 integration wiring? |
| **OQ-4-002** | Is Phase 4 limited to **synchronous service-call** integration only? |
| **OQ-4-003** | Can Allocation `DormitoryReadPort` (`bedExists`, `isBedAssignable`) be satisfied by accepted Read capabilities without adding Application Read methods? If not, is a narrow Read extension required in a separate accepted gate? |
| **OQ-4-004** | Is Allocation `PhysicalStateSignalPort` (`reserveBed` / `occupyBed` / `releaseBed`) in Phase 4 scope at all, given CD-014/015, Phase 3C occupancy-recording notes, and absence of reserved-state Application capability? |
| **OQ-4-005** | How should CheckIn/CheckOut request occupancy start/end—via a new Dormitory-facing port, consumer-side adapter, or direct binding to `DormitoryStructureMutationContract`—without transferring process ownership into Dormitory Integration? |
| **OQ-4-006** | Should Request `siteExists` be mapped to accepted dormitory detail/list reads, or does it require a dedicated accepted Application capability? |
| **OQ-4-007** | Are Domain/Application **events** expected later and explicitly out of Phase 4 now? |
| **OQ-4-008** | Are **external** adapters in scope for a later phase only? |
| **OQ-4-009** | Should reservation/check-in modules own workflow orchestration separately from Dormitory Integration in all Phase 4 designs? (Design says yes; confirm as Phase 4 invariant.) |

---

## K. Acceptance Criteria (for future Phase 4 implementation)

Future Phase 4 implementation is acceptable only if:

1. Implementation remains limited to approved integration artifacts for resolved consumer-capability pairs
2. Every integration artifact traces to an accepted Application Read/Mutation capability
3. No new business behavior is introduced
4. No Domain / Persistence / Read / Mutation redesign occurs
5. No forbidden layers are modified
6. No **OQ-4-*** item is implemented without separate approval
7. Regression suites remain passing at or above the 65-test baseline composition
8. Integration tests cover only approved integration behavior (delegation and boundary protection)

---

## Stop Boundary

This contract prepares Phase 4 only. Next required gate is **governance review** of Phase 4 artifacts, then (if accepted) explicit resolution of required Open Questions and a separate implementation authorization before coding.

---

## References

- `spec04-integration-boundary-design.md`
- `spec04-implementation-authorization.md`
- `spec04-application-mutation-layer-implementation-review.md`
- `spec04-application-read-layer-review.md`
- `spec04-application-read-layer-remaining-review.md`
- CD-014 / CD-015 in `.specify/docs/catalog-decisions.md`
