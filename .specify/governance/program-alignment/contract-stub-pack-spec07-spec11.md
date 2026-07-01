# Contract Stub Pack — spec07..spec11

**Pack ID:** CSP-spec07-spec11-2026-07-01-001  
**Version:** 1.0.0  
**Status:** STUB — REVIEW ONLY  
**Resolves:** PAR PB-03 (contract direction artifacts)  
**Sources:** `catalog-decisions.md` v2.8.1, `context-map.md` v0.4.1, `program-alignment-spec07-spec11.md` v1.0.0

---

## RequestLifecycleCommandPort

| Field | Value |
| ----- | ----- |
| **Contract name** | `RequestLifecycleCommandPort` |
| **Owner** | Request (spec05) |
| **Producer** | Allocation, CheckIn/CheckOut (spec07 program) |
| **Consumer(s)** | Request (spec05) |
| **Direction** | sync |
| **Purpose** | Inbound command boundary for post-approval request lifecycle transitions deferred from spec05 (OA-05-03). Request retains state ownership; spec07 triggers transitions only. |
| **Reference CD** | CD-014 |
| **Dependency note** | Requires spec05 `Approved` handoff; payload shape deferred to spec07/spec05 joint authoring. |

---

## AllocationReadContract

| Field | Value |
| ----- | ----- |
| **Contract name** | `AllocationReadContract` |
| **Owner** | Allocation (spec07) |
| **Producer** | Allocation (spec07) |
| **Consumer(s)** | Employee (spec03), Reporting (spec11) |
| **Direction** | sync |
| **Purpose** | Read-only supplier surface for active-allocation and assignment queries across module boundaries. |
| **Reference CD** | CD-014 |
| **Dependency note** | Intended to replace `ActiveAllocationReadPort` null adapter binding from spec03. |

---

## CheckInCommandPort

| Field | Value |
| ----- | ----- |
| **Contract name** | `CheckInCommandPort` |
| **Owner** | CheckIn/CheckOut (spec07) |
| **Producer** | Operator presentation layer; Allocation handoff (spec07) |
| **Consumer(s)** | CheckIn/CheckOut (spec07) |
| **Direction** | sync |
| **Purpose** | Inbound command boundary for operational `CheckedIn` / `CheckedOut` transitions. Operator role only for physical internal dormitories. |
| **Reference CD** | CD-015 |
| **Dependency note** | Consumes assignment facts from Allocation; does not own assignment authority. |

---

## VoucherIssuancePort

| Field | Value |
| ----- | ----- |
| **Contract name** | `VoucherIssuancePort` |
| **Owner** | Voucher (spec08) |
| **Producer** | Lottery (spec06), Allocation (spec07) |
| **Consumer(s)** | Voucher (spec08) |
| **Direction** | sync |
| **Purpose** | Inbound trigger boundary for voucher eligibility evaluation and issuance initiation. Upstream supplies facts only; Voucher owns issuance decisions. |
| **Reference CD** | CD-016 |
| **Dependency note** | Exact upstream input contract not decided (CD-016); stub records direction only. |

---

## VoucherReadContract

| Field | Value |
| ----- | ----- |
| **Contract name** | `VoucherReadContract` |
| **Owner** | Voucher (spec08) |
| **Producer** | Voucher (spec08) |
| **Consumer(s)** | Reporting (spec11) |
| **Direction** | sync |
| **Purpose** | Read-only supplier surface for voucher issuance and eligibility outcomes. No write authority for consumers. |
| **Reference CD** | CD-016, CD-017 |
| **Dependency note** | Reporting consumer must remain read-only per CD-017. |

---

## AuditService (facade)

| Field | Value |
| ----- | ----- |
| **Contract name** | `AuditService` |
| **Owner** | Audit (spec10) |
| **Producer** | Allocation, CheckIn/CheckOut, Voucher, Request, Lottery, Dormitory (critical operations) |
| **Consumer(s)** | Audit (spec10) |
| **Direction** | sync |
| **Purpose** | Central append-only audit entry facade for sensitive operations and state transitions. Replaces per-module `RecordsActivity` as integration target. |
| **Reference CD** | — |
| **Dependency note** | Constitution AP-06 / context-map R10; append-only `audit_logs`. |

---

## Domain Event Catalog v1

| Field | Value |
| ----- | ----- |
| **Contract name** | Domain Event Catalog v1 |
| **Owner** | Program index (cross-cutting; emitting BC owns each event) |
| **Producer** | See event table below |
| **Consumer(s)** | Audit (spec10), Notification (spec09), Reporting (spec11), intra-domain projections |
| **Direction** | event (async) |
| **Purpose** | Named index of domain events for cross-module integration. Names and purpose only; payloads not defined in this stub pack. |
| **Reference CD** | CD-014, CD-015, CD-016, CD-017 |
| **Dependency note** | Detailed shapes deferred (CD-015 UD-01); single-domain projections owned by emitting module per system-flow §5.2. |

### Event index (names and purpose only)

| Event name | Owner (producer BC) | Consumer(s) | Purpose |
| ---------- | ------------------- | ----------- | ------- |
| `RequestApproved` | Request (spec05) | Allocation, Audit, Notification, Reporting | Approved request available for downstream allocation or lottery handoff. |
| `RequestWaitingForAllocation` | Request (spec05) | Allocation, Audit, Notification | Post-approval state entered; allocation processing may begin. |
| `RequestAllocated` | Request (spec05) | CheckIn/CheckOut, Audit, Notification, Reporting | Request linked to successful assignment outcome. |
| `RequestAllocationFailed` | Request (spec05) | Voucher, Audit, Notification, Reporting | Approved request could not be fulfilled internally. |
| `LotteryDrawCompleted` | Lottery (spec06) | Allocation, Audit, Notification, Reporting | Draw finished; results available for allocation consumption. |
| `ProposedAllocationEmitted` | Lottery (spec06) | Allocation, Audit | Lottery proposes winners for allocation processing (R5). |
| `AllocationCreated` | Allocation (spec07) | Dormitory, CheckIn/CheckOut, Audit, Notification, Reporting | Assignment authority recorded. |
| `AllocationReleased` | Allocation (spec07) | Dormitory, Audit, Notification | Assignment released; physical markers may be updated. |
| `BedOccupancyMarkerChanged` | Dormitory (spec04) | Audit, Reporting | Physical occupancy marker updated on bed. |
| `CheckedIn` | CheckIn/CheckOut (spec07) | Audit, Notification, Reporting | Operational check-in transition completed. |
| `CheckedOut` | CheckIn/CheckOut (spec07) | Audit, Notification, Reporting | Operational check-out transition completed. |
| `VoucherIssuanceRequested` | Voucher (spec08) | Audit, Notification | Voucher evaluation or issuance flow initiated within Voucher boundary. |
| `VoucherIssued` | Voucher (spec08) | Audit, Notification, Reporting | Voucher issuance lifecycle completed. |

---

**End of stub pack.**
