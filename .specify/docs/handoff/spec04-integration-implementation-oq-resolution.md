# Spec04 Integration Implementation OQ Resolution

## Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `OQ_RESOLUTION_RECORDED` |
| **Spec** | `004-accommodation-resource` |
| **Phase** | Spec04 Backend Implementation Phase 4 – Integration Implementation |
| **Authorization effect** | Clarification only — **does not** authorize Phase 4 implementation |
| **Self-authorizing?** | **No** |
| **Decision date** | 2026-07-11 |
| **Last updated** | 2026-07-11 — OQ-4-001 / capability mapping resolved for Request-first Phase 4 slice |
| **Readiness** | `READY_FOR_IMPLEMENTATION_AUTHORIZATION_REVIEW` |

This artifact clarifies Open Questions for Phase 4 readiness. It does **not** implement Phase 4, expand scope, redesign integration architecture, or authorize coding. Implementation requires a separate authorization artifact.

**Canonical OQ IDs** are those in `spec04-integration-implementation-contract.md` §J.

### Resolution update (human governance)

Approved human governance decisions applied in this update:

1. **First Phase 4 Consumer = Request Module** (closes OQ-4-001).
2. **Capability fit:** Request `siteExists` is satisfied by accepted `getDormitoryDetail` nullability without new Application behavior (closes the Phase 4 capability-mapping blocker for this slice; see OQ-4-003 / OQ-4-006).

Consistency with accepted governance:

- Request is an approved read-oriented Dormitory consumer direction (`spec04-integration-boundary-design.md` §7; `spec04-implementation-authorization.md` §8).
- Accepted Application Read already exposes `getDormitoryDetail(?DormitoryDetailData)` (Phase 3A).
- Business reason: Request is the earliest business entry point for accommodation requests.

---

## ID Crosswalk

| Contract ID | Theme |
| ----------- | ----- |
| OQ-4-001 | First approved consumer |
| OQ-4-002 | Synchronous service-call only? |
| OQ-4-003 | Allocation `DormitoryReadPort` vs accepted Read APIs |
| OQ-4-004 | Allocation `PhysicalStateSignalPort` in Phase 4? |
| OQ-4-005 | CheckIn/CheckOut occupancy wiring |
| OQ-4-006 | Request `siteExists` mapping |
| OQ-4-007 | Events in Phase 4? |
| OQ-4-008 | External adapters in Phase 4? |
| OQ-4-009 | Orchestration ownership |

---

## OQ Resolutions

### OQ-4-001 — First approved consumer

| Field | Value |
| ----- | ----- |
| **Status** | `RESOLVED_FOR_PHASE_4` |
| **Selected Consumer** | **Request Module** |
| **Decision source** | Approved human governance decision |
| **Business reason** | Request is the earliest business entry point for accommodation requests. |
| **Decision** | First Phase 4 consumer is Request. Scope is thin integration wiring for dormitory existence validation only — not Request workflow ownership, not Allocation, not CheckIn. |
| **Evidence** | Human governance decision (this update); consistent with Integration Boundary §7 and implementation-authorization §8 (Request read-oriented); repo `Request\Application\Contracts\DormitoryReadContract::siteExists` + Null stub awaiting live supplier. |
| **Phase 4 Impact** | Unblocks Phase 4 authorization **review** for the Request existence-mapping slice only. |
| **Restrictions** | Allocation and CheckIn remain out of scope for this first Phase 4 consumer. No workflow ownership. |

---

## OQ-4-001 First Consumer Candidate Evaluation (historical)

The candidate evaluation below is retained as prior analysis. It is **superseded** for Phase 4 first-consumer selection by the human governance decision recorded in OQ-4-001 above (**Request Module**).

Purpose of the historical section: showed why Allocation/Request were not auto-selectable from repository evidence alone before the human decision.

Accepted Application capabilities considered:

- **Read:** `listDormitories`, `getDormitoryDetail`, `listDormitoryBuildings`, `listBuildingFloors`, `listFloorRooms`, `listRoomBeds`
- **Mutation:** structure creates; status changes; occupancy recording

### Candidate A — Allocation as first consumer

| Field | Value |
| ----- | ----- |
| **Consumer name** | Allocation (read-support boundary only; not Allocation workflow) |
| **Evidence source** | Accepted: `spec04-implementation-authorization.md` §8; `spec04-integration-boundary-design.md` §5. Repository: `DormitoryReadPort` + Null adapter. |
| **Required Dormitory capability pair** | `bedExists` + `isBedAssignable` |
| **Capability already exists on accepted Application surface?** | **No.** |
| **Phase 4 compatibility assessment** | **requires separate change** — **not selected** as first Phase 4 consumer. |

### Candidate B — Request

| Field | Value |
| ----- | ----- |
| **Consumer name** | Request (`siteExists`) |
| **Evidence source** | Integration Boundary §7; impl-auth §8; Request `DormitoryReadContract` + Null adapter. |
| **Required Dormitory capability pair** | Existence by dormitory id |
| **Capability already exists on accepted Application surface?** | **Yes** via accepted `getDormitoryDetail` nullability (confirmed by capability-fit review + human mapping decision). |
| **Phase 4 compatibility assessment** | **compatible** for thin adapter mapping — **selected** as first Phase 4 consumer by human governance decision. |

### Candidate C — No valid Phase 4 consumer identified yet

| Field | Value |
| ----- | ----- |
| **Status** | **Superseded** — human governance selected Request (Candidate B). |

### Candidate comparison summary (post-decision)

| Candidate | Accepted evidence present? | Existing capability fully sufficient? | Requires separate Application change? | Phase 4 compatible now? | Selected? |
| --------- | -------------------------- | ------------------------------------- | ------------------------------------- | ----------------------- | --------- |
| A — Allocation | YES (direction) | NO | YES | NO | NO |
| B — Request | YES | YES (`getDormitoryDetail`) | NO | YES | **YES** |
| C — None | N/A | N/A | N/A | N/A | NO |

---

## Governance Decision Status (OQ-4-001)

**DECISION_ACCEPTED**

| Field | Value |
| ----- | ----- |
| Selected Consumer | Request Module |
| Decision source | Approved human governance decision |
| Business reason | Request is the earliest business entry point for accommodation requests. |

---

### OQ-4-002 — Synchronous service-call only

| Field | Value |
| ----- | ----- |
| **Status** | `RESOLVED_FOR_PHASE_4` |
| **Decision** | Phase 4 is limited to **synchronous service-call** integration only. |
| **Evidence** | Phase 4 Contract §G / Lock §C / Execution Prompt forbid events/listeners/jobs unless separately approved; Integration Boundary Design left sync/async open at design time but Phase 4 package closes events out of this phase. |
| **Phase 4 Impact** | Enables the *mode* of any future thin wire (sync only); does **not** by itself enable a consumer slice. |
| **Restrictions** | No queues, domain events, listeners, or async fan-out in Phase 4. |

---

### OQ-4-003 — Capability mapping for selected Phase 4 consumer (Request)

| Field | Value |
| ----- | ----- |
| **Status** | `RESOLVED_FOR_PHASE_4` |
| **Decision** | For the **Request-first** Phase 4 slice, Request’s existence check is fully satisfied by the accepted Application Read method `getDormitoryDetail` without new Application behavior. Allocation `DormitoryReadPort` (`bedExists` / `isBedAssignable`) is **not** part of this first slice and remains out of scope here (still requires a separate future gate if Allocation is later chosen). |
| **Approved mapping** | `Request\Application\Contracts\DormitoryReadContract::siteExists(string $dormitorySiteId): bool` **↓** `DormitoryStructureReadContract::getDormitoryDetail(string $dormitoryId): ?DormitoryDetailData` — existence = detail is non-null. |
| **Evidence** | Accepted `DormitoryStructureReadContract` / Phase 3A (`getDormitoryDetail` returns null when missing); Request consumer port `siteExists` only; capability-fit review conclusion `CAPABILITY_ALREADY_EXISTS`; human governance acceptance of this mapping for Phase 4. |
| **Phase 4 Impact** | Unblocks thin Integration adapter mapping for Request → Dormitory existence only. |
| **Restrictions** | Integration adapter mapping only. No new Dormitory Application Read behavior. No Application Read contract change. No Domain/Persistence/Mutation change. Do **not** use `listDormitories` or hierarchy list methods for this mapping. Allocation / CheckIn out of scope for this slice. |

**Explicit statements for this resolution:**

- No new Dormitory Application Read behavior is required.
- No Application Read contract change is required.
- No Domain / Persistence / Mutation change is required.
- This is an **integration adapter mapping only**.
- `listDormitories` and hierarchy list methods are **not** selected for the mapping.
- Allocation remains **out of scope** for this first Phase 4 consumer.
- CheckIn remains **out of scope** for this first Phase 4 consumer.
- No workflow ownership is introduced.

---

### OQ-4-004 — Allocation `PhysicalStateSignalPort`

| Field | Value |
| ----- | ----- |
| **Status** | `DEFERRED_OUT_OF_SCOPE` |
| **Decision** | Allocation `PhysicalStateSignalPort` (`reserveBed` / `occupyBed` / `releaseBed`) is **out of Phase 4**. It conflicts with accepted ownership (assignment ≠ occupancy; CheckIn owns occupancy process; Dormitory records occupancy only) and invents reserved-state behavior not present in accepted Domain/Application Mutation. |
| **Evidence** | Repo `PhysicalStateSignalPort`; CD-014/CD-015; Integration Boundary §5–6; Phase 3C occupancy = state recording only; accepted Mutation has `recordBedOccupancyStart`/`End` only (no reserve); Phase 4 Contract forbids Allocation physical-state ownership and reservation ownership. |
| **Phase 4 Impact** | Excludes Allocation signal wiring from Phase 4. |
| **Restrictions** | Do not map Allocation signals onto occupancy recording in Phase 4. Do not add reserved occupancy. Keep Null signal adapter until a later accepted gate. |

---

### OQ-4-005 — CheckIn/CheckOut occupancy wiring

| Field | Value |
| ----- | ----- |
| **Status** | `DEFERRED_OUT_OF_SCOPE` |
| **Decision** | CheckIn/CheckOut occupancy request wiring is deferred out of Phase 4. Design allows CheckIn to *request* occupancy start/end and Dormitory to record state, but the repository has no CheckIn→Dormitory port, and Phase 4 forbids implementing check-in/check-out process/workflow. Creating that port or embedding process logic would be invention / forbidden scope. |
| **Evidence** | Integration Boundary §6; Phase 3C `recordBedOccupancyStart`/`End`; CheckIn module has no Dormitory port (repo inspection); Phase 4 Contract/Lock forbid CheckIn/CheckOut implementation and workflow ownership. |
| **Phase 4 Impact** | Excludes CheckIn occupancy adapter/process from Phase 4. |
| **Restrictions** | Occupancy APIs remain Dormitory state recording only. Future CheckIn wiring requires a separate approved consumer port design without transferring process ownership into Dormitory. |

---

### OQ-4-006 — Request `siteExists`

| Field | Value |
| ----- | ----- |
| **Status** | `RESOLVED_FOR_PHASE_4` |
| **Decision** | Live mapping of Request `siteExists` onto accepted `getDormitoryDetail` is approved for the Request-first Phase 4 slice (same mapping as OQ-4-003 resolution). |
| **Evidence** | Same as OQ-4-003 approved mapping; Request consumer port; Phase 3A detail null semantics. |
| **Phase 4 Impact** | Enables Integration adapter for Request existence check only. |
| **Restrictions** | Adapter mapping only; no Application contract change. |

---

### OQ-4-007 — Events

| Field | Value |
| ----- | ----- |
| **Status** | `DEFERRED_OUT_OF_SCOPE` |
| **Decision** | Domain/Application events, listeners, and jobs are out of Phase 4. |
| **Evidence** | Phase 4 Contract §G; Lock §C; Execution Prompt forbidden list; Integration Boundary §13 (events conceptual only, not approved for implementation here). |
| **Phase 4 Impact** | Excludes event-driven integration from Phase 4. |
| **Restrictions** | No events/listeners/jobs unless a later accepted governance artifact explicitly requires them. |

---

### OQ-4-008 — External adapters

| Field | Value |
| ----- | ----- |
| **Status** | `DEFERRED_OUT_OF_SCOPE` |
| **Decision** | External system adapters are out of Phase 4. |
| **Evidence** | Phase 4 Contract §G; Lock §C; Execution Prompt; Integration Boundary (internal BC focus). |
| **Phase 4 Impact** | Excludes external adapters from Phase 4. |
| **Restrictions** | Internal module wiring only, when later authorized. |

---

### OQ-4-009 — Orchestration ownership

| Field | Value |
| ----- | ----- |
| **Status** | `RESOLVED_FOR_PHASE_4` |
| **Decision** | Consumer modules own their orchestration/workflows. Dormitory Integration (when later authorized) is **pass-through delegation only** and must not own Allocation, CheckIn/CheckOut, reservation, or other consumer workflows. |
| **Evidence** | Integration Boundary §§5–8, 14–15; CD-014/CD-015; Phase 4 Contract §H and Non-Goals; Lock occupancy/workflow forbids; Governance Review occupancy section. |
| **Phase 4 Impact** | Confirms invariant for any future thin wire; does not by itself select a consumer. |
| **Restrictions** | Adapter-level orchestration = delegate only. No workflow ownership in Dormitory Integration. |

---

## Summary Table

| OQ | Status |
| -- | ------ |
| OQ-4-001 | `RESOLVED_FOR_PHASE_4` (Request Module) |
| OQ-4-002 | `RESOLVED_FOR_PHASE_4` |
| OQ-4-003 | `RESOLVED_FOR_PHASE_4` (Request ↔ `getDormitoryDetail` mapping; Allocation port out of first slice) |
| OQ-4-004 | `DEFERRED_OUT_OF_SCOPE` |
| OQ-4-005 | `DEFERRED_OUT_OF_SCOPE` |
| OQ-4-006 | `RESOLVED_FOR_PHASE_4` |
| OQ-4-007 | `DEFERRED_OUT_OF_SCOPE` |
| OQ-4-008 | `DEFERRED_OUT_OF_SCOPE` |
| OQ-4-009 | `RESOLVED_FOR_PHASE_4` |

---

## Required Outcome

### A. Authorization Readiness

**READY_FOR_IMPLEMENTATION_AUTHORIZATION_REVIEW**

Because:

- OQ-4-001 is `RESOLVED_FOR_PHASE_4` (Request Module).
- OQ-4-003 is `RESOLVED_FOR_PHASE_4` for the Request existence mapping.
- OQ-4-006 is aligned (`RESOLVED_FOR_PHASE_4`).
- No new Application Read/Mutation/Domain/Persistence behavior is required for this slice.

This readiness means Phase 4 may proceed to an **implementation authorization** artifact review. It does **not** itself authorize coding.

### B. Candidate Implementation Scope (for upcoming authorization only)

| Item | Value |
| ---- | ----- |
| Approved consumer | Request Module |
| Consumer contract | `Request\Application\Contracts\DormitoryReadContract::siteExists` |
| Accepted Dormitory capability | `DormitoryStructureReadContract::getDormitoryDetail` |
| Mapping | `siteExists($id) === (getDormitoryDetail($id) !== null)` |
| Allowed work (when later authorized) | Thin Integration adapter + provider binding replacing Request Null adapter; integration tests for existence mapping only |
| Exclusions | Allocation; CheckIn; workflow ownership; `listDormitories` / hierarchy lists for this mapping; Application contract changes; Domain/Persistence/Mutation changes; auth/HTTP/UI/events |

### C. Carry-Forward Restrictions

- Domain Layer remains locked
- Persistence Layer remains locked
- Application Read Layer remains locked (no new Read methods for this slice)
- Application Mutation Layer remains locked
- Occupancy remains Dormitory state recording only
- No workflow ownership
- No allocation implementation
- No check-in/check-out implementation
- No authorization
- No HTTP/API/routes/controllers/FormRequests
- No UI
- No migrations/schema changes
- No events/jobs/listeners unless separately approved
- No external adapters
- No OQ implementation without approval
- No direct database, Eloquent, repository-internal, or Domain mutation bypass

---

## Stop Boundary

This artifact does **not**:

- Authorize Phase 4 implementation
- Start Phase 4 coding
- Expand Phase 4 beyond Request existence mapping
- Create integration files or tests
- Claim Spec04 Backend Closure

---

## Next Required Artifact

**Next artifact:** `.specify/docs/handoff/spec04-integration-implementation-authorization.md`

**Purpose:** Authorize thin Phase 4 integration implementation for **Request → Dormitory existence mapping only** (`siteExists` ← `getDormitoryDetail`), under the Phase 4 Contract, Lock, and Execution Prompt.

---

## References

- [`spec04-integration-implementation-contract.md`](spec04-integration-implementation-contract.md)
- [`spec04-integration-implementation-lock.md`](spec04-integration-implementation-lock.md)
- [`spec04-integration-implementation-execution-prompt.md`](spec04-integration-implementation-execution-prompt.md)
- [`spec04-integration-implementation-governance-review.md`](spec04-integration-implementation-governance-review.md)
- [`spec04-integration-boundary-design.md`](spec04-integration-boundary-design.md)
- [`spec04-application-mutation-layer-implementation-review.md`](spec04-application-mutation-layer-implementation-review.md)
- CD-014 / CD-015