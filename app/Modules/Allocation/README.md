# Allocation Module (spec07)

**Bounded context:** Assignment authority for employee dormitory allocations.  
**Spec:** `specs/007-allocation-checkin/` · **Wave 1A:** T006–T052.

## spec07 scope

Allocation owns **assignment authority** (`Allocation`, `AllocationItem`, `AllocationMethod`) — create and release person-to-bed assignments with overlap prevention. Physical bed markers and operational check-in/out are **not** owned here.

## Boundary decisions

| ID | Rule |
| -- | ---- |
| **CD-014** | Allocation owns assignment authority only — no physical bed operability or occupancy-marker persistence |
| **CD-015** | CheckIn/CheckOut is a separate active boundary — operational transitions excluded from Allocation |

## Integration relationships

| ID | Direction | Contract |
| -- | --------- | -------- |
| **R5** | Request → Allocation | `RequestReadContract` (read-only consumer) |
| **R6** | Lottery → Allocation | `LotteryResultReadContract`, `ProposedAllocationPort` (read-only consumer) |
| **R7** | Allocation → Dormitory | `DormitoryReadContract` (consumer), `AllocationPhysicalStatePort` (signal producer) |

Cross-module integration uses Application contracts only — no cross-module Eloquent.

## Module status

Setup and foundational phases complete. US1 assignment authority (`CreateAllocationAction`, `ReleaseAllocationAction`) implemented in Phase 3.

## AllocationMethod usage (architecture)

`AllocationMethod` records **how** an assignment was initiated. It does not change assignment authority rules (overlap, release) in Wave 1A.

| Enum case | Value | When used | Source reference |
| --------- | ----- | --------- | ---------------- |
| `Manual` | `manual` | Direct operator or administrative assignment | None |
| `RequestSourced` | `request_sourced` | Assignment created from an approved accommodation request (US2 / R6) | `source_request_id` UUID ref |
| `LotterySourced` | `lottery_sourced` | Assignment created from a lottery proposed allocation outcome (US2 / R5) | `source_lottery_result_id` UUID ref |

Wave 1A US1 exercises `Manual` only. Upstream-driven methods are populated by US2 adapters — Allocation still owns persistence and release; Request and Lottery supply read-only triggering facts only.
