# Wave 3 — State Machine Design (W3-B)

**Disposition:** STOP-3A / STOP-3B **APPROVED** · Option **W3-B**  
**Date:** 2026-07-21  
**Ownership:** CD-010-A1 — Request owns product-visible state; Workflow owns approval orchestration.

---

## Decision summary

| Choice | Outcome |
|--------|---------|
| `DormitoryStudentRequestStateMachine` in Dormitory / `app/Domain/Dormitory` | **Forbidden** (STOP-3A) |
| Home of OA-05-03 states | `app/Modules/Request/Domain/States/` (Spatie + domain entity mutators) |
| Adapter | `RequestLifecycleCommandAdapter` → `RequestRepositoryContract` (no-op removed) |
| Frozen | HD-02 / HD-03 / DBT-3 untouched |

---

## Spatie matrix (post W3-B)

### Approval (unchanged)

`draft` → `submitted` → pending stages → `approved` | `rejected` | `cancelled`

### OA-05-03 operational

```
approved → waiting_for_allocation → allocated → checked_in → checked_out
                              ↘ allocation_failed
allocated → allocation_failed
```

**Terminal:** `rejected`, `cancelled`, `allocation_failed`, `checked_out`  
(`approved` is **no longer** terminal.)

---

## Runtime pattern

Application / adapters do **not** call Spatie `transitionTo`. Pattern matches existing Request code:

1. Domain mutator (`markWaitingForAllocation`, `markAllocated`, …) validates + returns new entity  
2. `RequestRepositoryContract::save` persists string status  
3. Domain events dispatched from adapter

`markAllocated` accepts `approved` by auto-advancing through `waiting_for_allocation` (supports `CreateAllocationFromRequestAction`).

---

## Domain events (OA-05-03)

| Event | Fired by |
|-------|----------|
| `RequestWaitingForAllocation` | Adapter `markWaitingForAllocation` / auto path in `markAllocated` |
| `RequestAllocated` | Adapter `markAllocated` |
| `RequestAllocationFailed` | Adapter `markAllocationFailed` |
| `RequestCheckedIn` / `RequestCheckedOut` | Defined; **no producer yet** — DEBT-W3-01 |

---

## Known risk (not a block)

**WP-WF-04 / baseline Request transition failures** (`docs/audit/wave1-baseline-known-fail.md` cluster): registered as known-risk for Wave 3. W3-B does not claim full-suite green; scoped OA-05-03 tests are the acceptance gate.

See `docs/audit/wave3-wp-wf-04-known-risk.md`.

---

## Deferred

| ID | Item |
|----|------|
| DEBT-W3-01 | **CLOSED** — CheckIn→Request stay lifecycle wired (`RequestStayLifecycleCommandPort`) |
| Spatie `transitionTo` enforcement | Optional hardening; GAP-PREUI-17 remains REGISTER-ONLY |
