# Port: Allocation Lifecycle Command (producer → Request)

**Version:** 1.0.0  
**Spec:** spec07 Allocation & Occupancy  
**Direction:** Outbound command from Allocation to Request (OA-05-03)  
**Status:** Design — payload deferred (UD-10)

---

## Purpose

Documents the **producer obligations** of the Allocation module when triggering post-approval Request lifecycle transitions.

**Canonical interface name:** `RequestLifecycleCommandPort` — see [request-lifecycle-command-port.md](./request-lifecycle-command-port.md).

Request (spec05) **owns** request state. Allocation **produces** commands only; it does not mutate `request_*` tables.

---

## Producer (Allocation module)

**Namespace:** `App\Modules\Allocation\Application\Contracts\RequestLifecycleCommandPort`

**Adapter:** `RequestLifecycleCommandAdapter` in `Infrastructure/Adapters/`

---

## Intended transitions (deferred to implementation)

| Trigger | Target Request state (spec07 scope) | Notes |
| ------- | ----------------------------------- | ----- |
| Allocation started | `WaitingForAllocation` | OA-05-03 handoff |
| Allocation succeeded | `Allocated` | |
| Allocation failed | `AllocationFailed` | |

Payload shape **not defined** in architecture freeze — UD-10 remains open.

---

## Rules

| Rule | Detail |
| ---- | ------ |
| CD-014 | Allocation does not own Request approval state |
| OA-05-01 | spec05 terminal at `Approved`; post-approval states are spec07-driven |
| Stub acceptable | No-op adapter until Request consumer implements inbound handler |
