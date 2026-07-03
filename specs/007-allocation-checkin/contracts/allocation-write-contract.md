# Contract: Allocation Write Boundary (internal only)

**Version:** 1.0.0  
**Spec:** spec07 Allocation & Occupancy  
**Status:** Design — **no external write contract**

---

## Purpose

Document that **assignment mutations are not exposed** as a cross-module write contract per CD-014.

Allocation owns assignment authority **inside** its module boundary. External contexts consume assignments via `AllocationReadContract` (read-only) or receive outbound commands/events — they do not write allocation rows.

---

## Internal write surface (not a public contract)

| Operation | Application action | Module |
| --------- | ------------------- | ------ |
| Create assignment | `CreateAllocationAction` | Allocation |
| Release assignment | `ReleaseAllocationAction` | Allocation |
| Create from request | `CreateAllocationFromRequestAction` | Allocation |
| Consume lottery proposal | `ProposedAllocationConsumer` | Allocation |

---

## Explicitly forbidden

| Forbidden | Reason |
| --------- | ------ |
| `AllocationWriteContract` as cross-module API | Violates CD-014 — assignment authority is Allocation-internal |
| Request / Lottery / Dormitory writing `allocation_*` tables | AP-04 — no cross-module Eloquent |
| Dormitory implementing allocation writes | CD-014 ownership split |
| CheckIn/CheckOut creating allocations | CD-015 |

---

## Related outbound (not “write contracts”)

| Port | Direction | Role |
| ---- | --------- | ---- |
| `AllocationPhysicalStatePort` | Allocation → Dormitory | Physical-marker signals only |
| `RequestLifecycleCommandPort` | Allocation → Request | Request state transitions (OA-05-03) |
| `VoucherIssuancePort` | Allocation → Voucher | Trigger facts only (CD-016) |
