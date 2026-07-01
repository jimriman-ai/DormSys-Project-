# Adapters: Dormitory Integration (R7 / ADIC)

**Version:** 1.0.0  
**Spec:** spec07 Allocation & Occupancy  
**Integration contract:** [ADIC-2026-07-01-001](../../../.specify/governance/contracts/allocation-dormitory-integration-contract.md)  
**Status:** Design — spec04 supplier not live (UD-07)

---

## Purpose

Define Allocation-side adapters for Dormitory coordination. Allocation is **consumer** of physical read data and **producer** of physical-marker signals.

---

## Consumer: Dormitory read (spec04 supplier)

| Field | Value |
| ----- | ----- |
| **Contract** | `App\Modules\Dormitory\Application\Contracts\DormitoryReadContract` |
| **Supplier** | Dormitory module (spec04) |
| **Allocation adapter** | `DormitoryReadAdapter` in `app/Modules/Allocation/Infrastructure/Adapters/` |
| **Wave 1 stub** | `NullDormitoryReadAdapter` — returns permissive defaults until spec04 live |

**Forbidden:** Binding `App\Modules\Request\Application\Contracts\DormitoryReadContract` for occupancy or assignability logic. Request’s port is a minimal site-validation stub for Request submit only — not the R7 physical-capacity supplier.

---

## Producer: Physical state signals (to spec04)

| Field | Value |
| ----- | ----- |
| **Port** | `App\Modules\Dormitory\Application\Contracts\Ports\AllocationPhysicalStatePort` |
| **Consumer** | Dormitory module (spec04) |
| **Allocation adapter** | `AllocationPhysicalStateAdapter` |
| **Reference** | [spec04 allocation-physical-state-port.md](../../004-accommodation-resource/contracts/allocation-physical-state-port.md) |

| Signal | Port methods | Integration event |
| ------ | ------------ | ----------------- |
| Assign | `reserveBed`, `occupyBed` | `AllocationAssigned` |
| Release | `releaseBed` | `AllocationReleased` |

---

## Switchover criteria (UD-07)

| Phase | Binding |
| ----- | ------- |
| Before spec04 implementation | `NullDormitoryReadAdapter` + test double for `AllocationPhysicalStatePort` |
| After spec04 `DormitoryReadContract` live | Wire real Dormitory supplier in `AllocationServiceProvider` |
| E2E integration tests | Require both ports live — runtime sequencing dependency, not architecture blocker |

---

## Rules

| Rule | Detail |
| ---- | ------ |
| CD-014 | Allocation does not implement `DormitoryReadContract` |
| CD-014 | Dormitory does not decide assignments |
| Unidirectional | Allocation → Dormitory for marker signals (R7) |
| INV-2 | Assignable capacity excludes non-`InService` beds — pre-check via read port |
