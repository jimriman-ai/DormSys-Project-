# Spec04 Integration Implementation Execution Prompt

## Prompt Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `PREPARED_FOR_REVIEW` |
| **Phase** | Spec04 Backend Implementation Phase 4 – Integration Implementation |
| **Ready for** | Cursor implementation **only after** Phase 4 governance review acceptance **and** explicit implementation authorization |
| **Self-authorizing?** | **No** |
| **Decision date** | 2026-07-11 |

---

## Mandatory references

1. `.specify/docs/handoff/spec04-integration-implementation-contract.md`
2. `.specify/docs/handoff/spec04-integration-implementation-lock.md`
3. Accepted Application Read/Mutation contracts under `app/Modules/Dormitory/Application/`
4. Phase 3C acceptance notes in `spec04-application-mutation-layer-implementation-review.md`

Do **not** treat this prompt as permission to code.

Implementation requires:

1. Governance review acceptance of the Phase 4 package
2. Explicit resolution/approval of required **OQ-4-*** items for the authorized batch
3. A separate explicit user authorization to implement Phase 4

---

## Allowed scope (when later authorized)

Implement only thin internal integration wiring that:

- Connects an **explicitly approved** internal consumer to accepted Dormitory Application Read and/or Mutation capabilities
- Delegates exclusively to those accepted Application contracts/services
- Adds integration contracts/adapters/DTOs/mappers/bindings/tests only when required for that approved wire
- Preserves module boundaries so consumers do not touch Eloquent models, repository internals, or Domain entities directly

Choose the **smallest** valid implementation that satisfies the accepted contract for the authorized consumer-capability pair(s).

### Traceability rule (mandatory)

Every integration artifact must identify:

- accepted Application capability delegated to
- consumer boundary served
- why the adapter is needed
- why bypassing Application contracts is forbidden

If traceability fails, **stop** and report a blocker.

---

## Forbidden scope (hard stop)

Do **not**:

- Implement unresolved **OQ-4-*** items
- Change Domain / Persistence / Read / Mutation behavior
- Create controllers, routes, API endpoints, FormRequests
- Create authorization, policies, permissions, roles, guards
- Create UI / Livewire / Blade / frontend
- Implement Allocation assignment behavior
- Implement CheckIn/CheckOut operational process behavior
- Implement reservation workflow
- Implement voucher / billing / payment behavior
- Implement notification behavior
- Add events / listeners / jobs unless separately approved
- Add external adapters unless separately approved
- Create generic frameworks / CQRS / command-bus expansion
- Future-proof beyond accepted requirements
- Introduce new business capability
- Reinterpret occupancy as check-in/check-out
- Introduce allocation or reservation ownership through Dormitory Integration

---

## Locked layers

Do not modify behavior of:

- Domain Layer
- Persistence Layer (including migrations/schema/constraints)
- Application Read Layer Phase 3A/3B
- Application Mutation Layer Phase 3C

Occupancy remains Dormitory **state recording only**.

---

## Stop conditions

Stop and report a blocker if implementation requires:

- schema / migration change
- domain / persistence / read / mutation redesign
- authorization decision
- route / controller / API decision
- workflow ownership decision
- allocation / check-in / check-out / reservation / voucher / billing / payment / notification / external integration behavior
- event-driven behavior
- any **OQ-4-*** without approval
- any consumer/producer not explicitly approved
- any artifact that cannot trace to accepted Application Read/Mutation capability

---

## Convention inspection rules

- Inspect existing Dormitory and sibling-module patterns **only for naming/structure consistency**.
- Do **not** expand Phase 4 scope based on patterns found in other modules.
- Do **not** infer new architecture from sibling modules.
- Prefer the smallest pass-through adapter over speculative abstractions.

---

## Testing requirements

Add tests **only** for approved Phase 4 integration behavior.

Required regression commands before final report:

```bash
php -d memory_limit=512M artisan test tests/Unit/Modules/Dormitory/Domain
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Persistence
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Application/Read
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Application/Mutation
```

Plus the Phase 4 integration test path created during authorized implementation (exact path to be reported).

Preserve baseline: Domain 31, Persistence 11, Read 14, Mutation 9.

---

## Final implementation report (after future authorized execution only)

Report:

- files created/modified
- consumer-capability pairs wired
- Open Questions left unimplemented
- test commands and results
- confirmation that locks and occupancy boundary were respected
- any stop-condition encounters

### Explicitly do not during implementation

- Create review / acceptance / closure / handoff artifacts
- Claim Phase 4 acceptance
- Claim Spec04 Backend Closure
- Proceed to Authorization / UI / Workflow phases

---

## Open Questions reminder

Do not implement without separate approval:

- OQ-4-001 … OQ-4-009 (see Phase 4 contract)

---

## Governance reminder

This prompt is a prepared execution package only.

Phase 4 coding starts only after governance review and explicit implementation authorization.
