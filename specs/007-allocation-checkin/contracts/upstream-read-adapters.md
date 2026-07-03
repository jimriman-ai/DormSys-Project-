# Adapters: Upstream Read (Request + Lottery)

**Version:** 1.0.0  
**Spec:** spec07 Allocation & Occupancy  
**Direction:** Inbound read-only (R5, R6)  
**Status:** Design — suppliers live (spec05, spec06)

---

## Purpose

Allocation consumes upstream facts via Application contracts only. No cross-module Eloquent.

---

## Request (R6)

| Field | Value |
| ----- | ----- |
| **Contract** | `App\Modules\Request\Application\Contracts\RequestReadContract` |
| **Supplier** | Request module (spec05) |
| **Allocation adapter** | `RequestReadAdapter` |
| **Use** | Approved accommodation requests for `CreateAllocationFromRequestAction` |

---

## Lottery (R5)

| Field | Value |
| ----- | ----- |
| **Contracts** | `App\Modules\Lottery\Application\Contracts\LotteryResultReadContract` |
| | `App\Modules\Lottery\Application\Contracts\ProposedAllocationPort` |
| **Supplier** | Lottery module (spec06) |
| **Allocation adapters** | `LotteryResultReadAdapter`, `ProposedAllocationConsumer` |
| **Use** | Draw outcomes and proposed winner payloads |

**Reference:** [spec06 contracts](../../006-lottery-selection/contracts/)

---

## Rules

| Rule | Detail |
| ---- | ------ |
| Read-only | No mutation of Request or Lottery state from Allocation |
| CD-011 | Lottery rules remain in Lottery |
| CD-010 | Request approval state remains in Request |
