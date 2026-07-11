# Spec04 Application Mutation Layer Execution Prompt

## Prompt Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `APPLICATION_MUTATION_LAYER_EXECUTION_PROMPT_PREPARED` |
| **Phase** | Spec04 Backend Implementation Phase 3C – Application Mutation Layer |
| **Ready for** | Cursor implementation **after** contract + lock are reviewed and accepted |
| **Does this prompt alone authorize coding?** | **No** — wait for explicit implementation go-ahead referencing accepted contract/lock |
| **Decision date** | 2026-07-11 |

---

## Instructions for the Implementing Agent

You are implementing Spec04 Backend Phase 3C — Application Mutation Layer for the Dormitory module.

### Mandatory references (read first)

1. `.specify/docs/handoff/spec04-application-mutation-layer-contract.md`
2. `.specify/docs/handoff/spec04-application-mutation-layer-implementation-lock.md`
3. Accepted Domain / Persistence / Read reviews and current code under `app/Modules/Dormitory/`
4. Existing mutation conventions in sibling modules (for example Employee `*Action` + `DB::transaction`)

### Before writing code

Inspect and reuse existing conventions for:

- Application Services / Actions
- DTO style (`final readonly`)
- Repository contracts and Eloquent adapters
- `DormitoryServiceProvider` bindings
- Domain exceptions and entity methods
- Feature test layout under `tests/Feature/Modules/Dormitory/`

Do **not** redesign accepted Domain, Persistence, or Phase 3A/3B Read Layer artifacts.

---

## Allowed Mutation Use Cases (implement only these)

### Structure creation

- CreateDormitory
- CreateBuilding (parent dormitory must exist)
- CreateFloor (parent building must exist)
- CreateRoom (parent floor must exist; capacity required)
- CreateBed (parent room must exist; respect room capacity via Domain)

### Resource status

- ChangeDormitoryStatus
- ChangeRoomStatus
- ChangeBedStatus

Use accepted `ResourceStatus` values: `available`, `unavailable`, `maintenance`, `inactive`.

### Physical occupancy recording (Dormitory-owned state only)

- RecordBedOccupancyStart (`Bed::startOccupancy`)
- RecordBedOccupancyEnd (`Bed::endOccupancy`)

Do **not** implement CheckIn/CheckOut process, Allocation signaling, or integration ports.

### Do not implement open questions

Leave unimplemented unless the user explicitly expands scope after approving:

- Metadata updates (code/name/label) beyond create payloads
- Building/Floor status changes
- Room capacity updates after create
- Availability recalculation writes
- External/internal dormitory typing
- Status cascade/propagation
- Soft-delete / deactivate APIs
- Events / listeners / jobs

---

## Forbidden Scope (hard stop)

Do not add:

- Controllers, routes, API resources, FormRequests, middleware
- Authorization policies, gates, permissions, mutation auth gates
- Integration adapters
- Livewire, Blade, frontend/UI
- Workflow, Allocation, CheckIn/CheckOut, Voucher code
- Migrations / schema / constraint changes
- Domain redesign
- Read-layer redesign/duplication
- Command bus / CQRS framework / speculative infrastructure
- Events, listeners, jobs

If a use case requires any of the above, **stop** and report the blocker.

---

## Implementation Requirements

1. Prefer focused `*Action` classes (project mutation convention) or an equivalently small mutation service set.
2. Add a write repository contract + Eloquent implementation for authorized creates/updates.
3. Use `DB::transaction` on write paths.
4. Map Domain exceptions through without inventing a parallel exception tree unless sibling modules require a thin application wrapper.
5. Bind new contracts in `DormitoryServiceProvider` only if needed.
6. Preserve Phase 3A/3B read behavior and DTOs.
7. Keep Phase 3C minimal and file-count constrained; justify any unusual expansion.

---

## Testing Requirements

Create Feature tests for authorized mutations only, under a new path such as:

`tests/Feature/Modules/Dormitory/Application/Mutation/`

Cover at minimum:

- Happy path for each authorized create
- Parent-missing / hierarchy rejection
- Room capacity rejection on excess beds
- Invalid bed status / occupancy transitions
- Persistence of resulting status/occupancy values
- No unauthorized side effects (no events required; assert writes are limited to intended tables/rows)

### Required test commands before final report

```bash
php -d memory_limit=512M artisan test tests/Unit/Modules/Dormitory/Domain
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Persistence
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Application/Read
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Application/Mutation
```

(Adjust the Mutation path if the created directory name differs; report the exact path and command used.)

Existing Domain + Persistence + Read baseline (**56 tests**) must remain green.

---

## Final Implementation Report (after coding)

When implementation is finished, report only:

- Files created/modified
- Use cases implemented
- Use cases skipped (open questions)
- Test commands and results
- Confirmation that Domain / Persistence / Read locks were respected
- Any stop-condition encounters

### Explicitly do not

- Create a review/handoff acceptance artifact during implementation
- Proceed to Integration, Authorization, UI, or Spec04 closure
- Claim Phase 3 Application Layer complete beyond Phase 3C mutation delivery

---

## Governance Reminder

This prompt is a prepared execution package.

Coding starts only after the user explicitly authorizes Phase 3C implementation against the accepted contract and lock.
