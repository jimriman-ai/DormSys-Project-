# Spec04 Application Mutation Layer Implementation Lock

## Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `APPLICATION_MUTATION_LAYER_IMPLEMENTATION_LOCK_PREPARED` |
| **Spec** | `004-accommodation-resource` |
| **Phase** | Spec04 Backend Implementation Phase 3C – Application Mutation Layer |
| **Companion contract** | `spec04-application-mutation-layer-contract.md` |
| **Authorization effect** | Scoping lock only — **does not** authorize implementation until reviewed and accepted |
| **Decision date** | 2026-07-11 |

---

## 1. Files / Layers Allowed to Change

When Phase 3C implementation is later accepted to proceed, changes are limited to:

| Allowed area | Examples |
| ------------ | -------- |
| Application mutation actions/services | `app/Modules/Dormitory/Application/Services/*Action.php` or a small mutation service set |
| Application mutation contracts | Optional write/mutation contracts under `Application/Contracts/` |
| Application input/result DTOs | `Application/DTOs/*` for authorized mutation payloads/results only |
| Infrastructure write repository | Eloquent write adapter(s) under `Infrastructure/Repositories/` implementing mutation persistence |
| Service provider bindings | `DormitoryServiceProvider` **only** if required to bind new mutation/write contracts |
| Mutation Feature tests | `tests/Feature/Modules/Dormitory/Application/` (new Mutation path preferred) |

Domain entity method calls from Application are allowed **without** changing Domain behavior.

---

## 2. Locked Layers

| Layer | Lock rule |
| ----- | --------- |
| Domain behavior | **Locked.** No entity redesign, no new Domain methods unless a separate Domain change is explicitly approved after an open question. |
| Persistence behavior | **Locked.** No model behavior redesign beyond what write repository mapping already needs (mapping only). |
| Migrations / schema / CHECK / FK / uniqueness | **Locked.** Zero schema changes. |
| Application Read Layer (3A/3B) | **Locked.** Do not redesign, rename, replace, or duplicate read contracts/services/repositories/DTOs. Tiny compatibility additions only if absolutely required, documented, and justified in the implementation report. |
| Presentation | **Locked empty** for this phase. |
| Integration / Authorization | **Locked out.** |

---

## 3. Forbidden Changes

Implementation must not add or modify:

- Migrations or schema alterations
- Persistence constraint changes
- Domain redesign (including adding Building/Floor `changeStatus`, capacity mutators, type fields, cascade rules) without prior open-question approval
- Read-layer redesign or duplicate read stacks
- Controllers, routes, API resources, FormRequests
- Authorization policies, gates, permission seeders, mutation auth gates
- Livewire / Blade / frontend / UI artifacts
- Integration adapters / ports to Allocation, CheckIn/CheckOut, Workflow, Voucher
- Allocation, CheckIn, CheckOut, Workflow, or Voucher module code
- Events, listeners, queued jobs for Dormitory mutations (unless separately approved; default: forbidden)
- Broad CQRS/command-bus/framework infrastructure

---

## 4. Batch Ceiling

Phase 3C must stay minimal:

1. Implement **only** authorized use cases from the mutation contract §3.
2. Do not implement OQ-3C-* clarification items.
3. Do not implement future Integration, Authorization, or UI phases.
4. Prefer extending existing Application/`*Action` + repository conventions.
5. Prefer a small file set; avoid speculative shared frameworks.
6. Keep write repository focused on create/status/occupancy persistence for authorized cases only.

Suggested batch shape (non-binding ceiling guidance):

- Mutation actions/services for structure create + status + occupancy
- Write repository contract + implementation
- Minimal DTOs
- Provider binding updates if needed
- Feature tests for authorized mutations only

---

## 5. Stop Conditions

**Stop implementation immediately** and escalate to governance if any authorized use case requires:

| Stop trigger | Reason |
| ------------ | ------ |
| Schema / migration change | Persistence lock |
| Domain redesign or new Domain API | Domain lock / open question |
| Persistence model redesign beyond mapping | Persistence lock |
| Authorization or permission decision | Wrong phase |
| Integration port / event / listener requirement to make the mutation “complete” | Wrong phase |
| Use case not listed in contract §3 | Out of scope / needs clarification |
| Breaking or redesigning Phase 3A/3B read layer | Read lock |

If blocked, do not invent compensating design. Document the blocker and await approval.

---

## 6. Regression Guard

Before claiming Phase 3C implementation complete (in a later task), the following must pass:

```bash
php -d memory_limit=512M artisan test tests/Unit/Modules/Dormitory/Domain
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Persistence
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Application/Read
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Application/<MutationPath>
```

Baseline read/domain/persistence expectation: existing **56** tests remain green.

---

## 7. Governance Note

This lock is paired with:

- `spec04-application-mutation-layer-contract.md`
- `spec04-application-mutation-layer-execution-prompt.md`

Together they prepare Phase 3C. They do **not** by themselves authorize coding, accept mutation implementation, complete Phase 3 Application Layer, or close Spec04 backend.

---

## References

- `spec04-application-mutation-layer-contract.md`
- `spec04-application-read-layer-remaining-review.md`
- `spec04-implementation-authorization.md`
