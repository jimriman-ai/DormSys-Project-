# Spec04 Integration Implementation Lock

## Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `PREPARED_FOR_REVIEW` |
| **Spec** | `004-accommodation-resource` |
| **Phase** | Spec04 Backend Implementation Phase 4 – Integration Implementation |
| **Companion contract** | `spec04-integration-implementation-contract.md` |
| **Authorization effect** | Preparation lock only — **does not** authorize implementation |
| **Decision date** | 2026-07-11 |

This lock is **not self-authorizing**. Coding requires governance review acceptance of the Phase 4 package **and** later explicit implementation authorization after required Open Questions are resolved.

---

## A. Locked Layers

The following are **locked** and must not change during Phase 4:

| Layer | Lock |
| ----- | ---- |
| Domain Layer (Phase 1) | Locked |
| Persistence Layer (Phase 2) | Locked — including migrations, schema, constraints, indexes, relationships |
| Application Read Layer Phase 3A/3B | Locked |
| Application Mutation Layer Phase 3C | Locked |

Phase 3C notes remain binding: occupancy APIs are Dormitory state recording only.

---

## B. Allowed Future Changes During Phase 4

When Phase 4 implementation is later explicitly authorized, changes are limited to the **minimum** integration files required by the accepted Phase 4 contract and resolved Open Questions, such as:

- Integration contracts/interfaces, if required
- Integration adapters/services, if required
- Integration DTOs/mappers, if required
- Service provider bindings for integration contracts, if required
- Integration tests for approved wiring only

Every allowed change must be narrowly traceable to an accepted Application Read or Mutation capability and an explicitly approved consumer boundary.

No change may introduce Allocation, CheckIn/CheckOut, Request, Workflow, voucher, billing, payment, or notification **business** behavior.

---

## C. Forbidden Changes

Forbid:

- Migrations / schema changes / constraint / index / relationship changes
- Domain behavior changes
- Persistence behavior changes
- Read use-case behavior changes
- Mutation use-case behavior changes
- Authorization / policies / permissions / roles / guards
- Controllers / routes / API endpoints / FormRequests
- Livewire / Blade / frontend / UI
- Workflow ownership
- Allocation behavior implementation
- Check-in / check-out behavior implementation
- Reservation workflow implementation
- Voucher / payment / billing behavior
- Notification behavior
- External integration behavior
- Events / jobs / listeners unless separately approved
- Speculative generic integration framework
- Command-bus / CQRS framework expansion
- New business capability
- Future-proofing beyond accepted requirements
- Implementing unresolved **OQ-4-*** items
- Wiring consumers/producers not explicitly approved
- Integration artifacts that cannot trace to accepted Application Read/Mutation capabilities

---

## D. Stop Conditions

Future Phase 4 implementation must **stop immediately** and report a blocker if it requires:

- Any schema change
- Domain redesign
- Persistence redesign
- Read or Mutation redesign
- Authorization decision
- Route / controller / API decision
- Workflow ownership decision
- Allocation behavior
- Check-in / check-out behavior
- Reservation behavior
- Voucher behavior
- Billing / payment behavior
- Notification behavior
- External integration behavior
- Event-driven behavior
- Any **OQ-4-*** resolution without approval
- Any consumer/producer not explicitly approved
- Any integration artifact that cannot be traced to an accepted Application Read/Mutation capability
- Reinterpretation of occupancy as CheckIn/CheckOut or Allocation ownership

---

## E. Regression Requirements

Future Phase 4 implementation must preserve:

| Suite | Required baseline |
| ----- | ----------------- |
| Domain | 31 tests passing |
| Persistence | 11 tests passing |
| Read | 14 tests passing |
| Mutation | 9 tests passing |

Integration tests may be added **only** for accepted Phase 4 behavior.

### Required regression commands

```bash
php -d memory_limit=512M artisan test tests/Unit/Modules/Dormitory/Domain
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Persistence
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Application/Read
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Application/Mutation
```

Plus any Phase 4 integration test path created later under an authorized implementation task.

---

## Occupancy Boundary (locked)

- Phase 3C occupancy mutations remain Dormitory state recording only.
- Phase 4 must not reinterpret occupancy as check-in/check-out.
- Phase 4 must not introduce allocation or reservation ownership.
- Check-in/check-out and allocation workflows remain outside Phase 4 unless separately approved.

---

## Consistency Reminder

This lock must remain consistent with:

- `spec04-integration-implementation-contract.md`
- `spec04-integration-implementation-execution-prompt.md`

Phase name, locked layers, forbidden scope, occupancy boundary, traceability, Open Questions, and “not authorized yet” statements must agree across all three artifacts.

---

## References

- `spec04-integration-implementation-contract.md`
- `spec04-application-mutation-layer-implementation-review.md`
- `spec04-integration-boundary-design.md`
