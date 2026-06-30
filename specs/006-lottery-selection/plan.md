# Implementation Plan: Lottery Selection (spec06)

**Branch**: `006-lottery-selection` | **Date**: 2026-06-30 | **Spec**: [spec.md](./spec.md)

**Input**: Lottery bounded context — program lifecycle, registrations, deterministic scoring, draw execution, result production; CD-011 centralization; R4 Request supplier; R5 Allocation consumer stub.

---

## Summary

Implement the **Lottery** module: **LotteryProgram**, **LotteryRegistration**, **LotteryResult** aggregates; **spatie/laravel-model-states** program lifecycle; **LotteryScoringEngine** with settings-driven formula; **ExecuteLotteryDrawJob** and **AutoLockLotteryJob**; **`RequestReadContract`** adapter for enrollment validation; **`LotteryResultReadContract`** stub for Allocation.

**MVP boundary:** US1–US4 (program lifecycle, enrollment, scoring, draw + results). US5 (scheduled jobs) and Livewire UI phased after core domain is testable via actions/jobs.

**Excluded:** Allocation execution, Voucher, Request mutations, Workflow, cross-module Eloquent.

---

## Technical Context

| Dimension | Value |
| --------- | ------- |
| **Language/Version** | PHP 8.4; Laravel 13 |
| **Primary Dependencies** | spec01 kernel; `spatie/laravel-model-states`; spec05 `RequestReadContract` |
| **UUID strategy** | UUID v7 via `HasUuid` on all `lottery_*` tables |
| **Storage** | PostgreSQL 17 — migrations under `database/migrations/modules/lottery/` |
| **Testing** | Pest PHP 4; unit (ScoringEngine, States), feature (actions, jobs), architecture (boundary) |
| **Queue** | Redis + Horizon for draw/lock jobs |
| **Constraints** | No cross-module Eloquent; scoring formula from `settings` only; draw idempotent + transactional |

---

## Constitution Check

| Principle | Compliance | Notes |
| --------- | ---------- | ----- |
| AP-05 State Machines | ✅ | LotteryProgram states in Domain |
| AP-06 Audit | ✅ | Domain events + activity on draw |
| AP-07 Background Jobs | ✅ | ExecuteLotteryDrawJob, AutoLockLotteryJob |
| AP-04 Module boundaries | ✅ | RequestReadContract inbound only |
| CD-011 | ✅ | All lottery concerns in Lottery module |

---

## Dependency Analysis

| Dependency | Required for | Status |
| ---------- | ------------ | ------ |
| spec01 module scaffold | Paths, providers | ✅ `app/Modules/Lottery/` exists |
| spec05 `RequestReadContract` | Enrollment validation | ✅ Implemented |
| Employee lottery score | Scoring input | ⚠️ Stub port (OA-06-02) |
| spec04 Dormitory read | Optional site validation | ⚠️ Null stub acceptable |

**Implementation order:** Setup → Foundational → Program lifecycle → Registration → Scoring → Draw/Results → Jobs → Supplier contract → Polish.

---

## Phase Design

| Phase | Deliverable |
| ----- | ----------- |
| **1 — Setup** | Migration path, DI, README |
| **2 — Foundational** | VOs, enums, program states, migrations |
| **3 — US1** | Program CRUD + lifecycle actions |
| **4 — US2** | Registration enrollment via Request read port |
| **5 — US3** | Scoring engine + lock snapshot |
| **6 — US4** | Draw action + results persistence + allocation stub |
| **7 — US5** | Queue jobs (lock, draw) |
| **8 — Supplier** | `LotteryResultReadContract` |
| **9 — Polish** | Architecture test, scoring reproducibility test |

---

## Contracts (to author in Phase 1 design)

| Contract | Direction | Consumer |
| -------- | --------- | -------- |
| `RequestReadContract` | Inbound | spec05 (existing) |
| `EmployeeLotteryScorePort` | Inbound stub | spec06 scoring |
| `LotteryResultReadContract` | Outbound | spec07 (stub) |
| `ProposedAllocationPort` | Outbound event/stub | spec07 (stub) |

---

## Risk Notes

| Risk | Mitigation |
| ---- | ---------- |
| Scoring formula drift | Load from `settings` at lock; version in snapshot |
| Draw non-idempotency | Unique constraint on `(program_id, registration_id)` results; job dedup key |
| Request status change after enroll | Re-validate at lock (OA-06-01) |
