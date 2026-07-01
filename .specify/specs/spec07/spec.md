# Feature Specification: Allocation & Occupancy (spec07)

**Status:** Architecture skeleton — review only  
**Catalog:** spec07 — Allocation & Occupancy (`spec-catalog.md`)  
**Governance:** CD-014, CD-015  
**Sources:** `catalog-decisions.md` v2.8.1, `context-map.md` v0.4.1 (R7), `program-alignment-spec07-spec11.md`, `contract-stub-pack-spec07-spec11.md`

---

## 1. Purpose

Assign rooms and beds under the Allocation bounded context and coordinate operational stay transitions under CheckIn/CheckOut within a single spec07 delivery program. spec07 owns assignment authority and operational occupancy transitions only. Physical bed state remains in Dormitory (spec04). External accommodation and cross-domain reporting are out of scope.

---

## 2. Boundaries

### Belongs to spec07

| Bounded context | Scope |
| --------------- | ----- |
| **Allocation** | `Allocation`, `AllocationItem`, `AllocationMethod` — assignment authority (CD-014) |
| **CheckIn/CheckOut** | Operational transitions `CheckedIn`, `CheckedOut` (CD-015) |

### Does NOT belong to spec07

| Concern | Owner |
| ------- | ----- |
| Physical room/bed capacity and occupancy markers | Dormitory — spec04 (CD-014) |
| Voucher eligibility and issuance lifecycle | Voucher — spec08 (CD-016) |
| Cross-domain read projections and management reports | Reporting — spec11 (CD-017) |
| Notification delivery | Notification — spec09 |
| Central audit persistence | Audit — spec10 |
| Lottery rules and draw lifecycle | Lottery — spec06 |
| Request approval state and history | Request — spec05 |

---

## 3. Ownership model

### Allocation (CD-014)

| Responsibility | Notes |
| -------------- | ----- |
| Assignment authority | Who is assigned to what |
| Upstream driver | Publishes assignment outcomes; drives Dormitory physical-marker signals via port |
| Overlap invariant | Effective bed occupancy derived from Allocation + CheckIn/CheckOut — not stored authoritatively in one place (CD-014) |

### CheckIn/CheckOut (CD-015)

| Responsibility | Notes |
| -------------- | ----- |
| Operational transitions | `CheckedIn`, `CheckedOut` |
| Consumes assignment | Enables transitions from Allocation assignment facts; does not own assignment |
| Operator constraint | Check-in/out for internal dormitories — Operator role (architecture source) |

---

## 4. Upstream dependencies

| Upstream | Relationship | Integration (context-map) |
| -------- | ------------ | ------------------------- |
| **spec04 — Dormitory** | Physical capacity and bed markers | R7 — `DormitoryReadContract` (consumer); `AllocationPhysicalStatePort` (producer) |
| **spec05 — Request** | Approved accommodation requests | R6 — `RequestReadContract`; post-approval lifecycle via `RequestLifecycleCommandPort` (OA-05-03 handoff) |
| **spec06 — Lottery** | Draw results and proposed allocations | R5 — `LotteryResultReadContract`, `ProposedAllocationPort` |
| **spec01 — Foundation** | Platform scaffold | Module structure, shared kernel |

---

## 5. Downstream consumers

| Downstream | Relationship | Notes |
| ---------- | ------------ | ----- |
| **spec08 — Voucher** | Upstream trigger (R8) | Allocation may supply triggering facts; Voucher owns issuance (CD-016) |
| **spec09 — Notification** | Event consumer (R9) | Consumes domain events from Allocation and CheckIn/CheckOut operations |
| **spec11 — Reporting** | Read-only consumer (R11) | `AllocationReadContract` supplier; CD-017 — no write authority |
| **spec03 — Employee** | Read consumer | `AllocationReadContract` replaces `ActiveAllocationReadPort` stub |
| **spec10 — Audit** | Traceability consumer (R10) | `AuditService` facade for critical operations |

---

## 6. Contract references

Contracts from **Contract Stub Pack** used by spec07:

| Contract | spec07 role |
| -------- | ----------- |
| `RequestLifecycleCommandPort` | Producer (Allocation, CheckIn/CheckOut) |
| `AllocationReadContract` | Owner / producer |
| `CheckInCommandPort` | Owner (CheckIn/CheckOut) |
| `VoucherIssuancePort` | Producer (Allocation — trigger facts only) |
| `AuditService` | Producer (Allocation, CheckIn/CheckOut) |

**Upstream contracts (not in stub pack; referenced by alignment package):** `LotteryResultReadContract`, `ProposedAllocationPort`, `DormitoryReadContract`, `AllocationPhysicalStatePort`, `RequestReadContract`.

---

## 7. Event boundaries (names only)

### Emitted by spec07

- `AllocationCreated`
- `AllocationReleased`
- `CheckedIn`
- `CheckedOut`

### Consumed or coordinated at spec07 boundary (Domain Event Catalog v1)

- `RequestApproved`
- `RequestWaitingForAllocation`
- `RequestAllocated`
- `RequestAllocationFailed`
- `LotteryDrawCompleted`
- `ProposedAllocationEmitted`
- `BedOccupancyMarkerChanged`

---

## 8. Open dependencies (not solved in this skeleton)

| ID | Topic |
| -- | ----- |
| UD-01 | Event contract between Allocation, Dormitory, CheckIn/CheckOut |
| UD-02 | Reconciliation when Allocation and Dormitory state diverge |
| UD-03 | Exact Voucher input contract from Lottery/Allocation |
| UD-04 | Reporting model structure and refresh mechanism |
| UD-05 | Notification policy layer vs pure delivery |
| UD-06 | Domain vs technical audit semantics per module |
| UD-07 | spec04 not implemented — `DormitoryReadContract` + `AllocationPhysicalStatePort` not live |
| UD-08 | Contract stubs C-07..C-13 not fully migrated to spec-level contract artifacts |
| UD-09 | spec07 `spec.md` skeleton (this document — architecture level only) |
| UD-10 | `RequestLifecycleCommandPort` payload undefined |
| UD-11 | Employee `ActiveAllocationReadPort` still null |

---

**End of skeleton.**
