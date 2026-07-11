# Spec04 Integration Implementation Governance Review

## Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `INTEGRATION_IMPLEMENTATION_GOVERNANCE_ACCEPTED` |
| **Spec** | `004-accommodation-resource` |
| **Phase** | Spec04 Backend Implementation Phase 4 – Integration Implementation |
| **Review result** | `ACCEPTED_WITH_NOTES` |
| **Decision date** | 2026-07-11 |

**Acceptance statement:**

Spec04 Backend Implementation Phase 4 Integration Governance Package is accepted.

This review accepts the **prepared governance package only**. It does **not** authorize Phase 4 coding by itself, start Phase 4 implementation, resolve **OQ-4-***, or claim Spec04 Backend Closure.

---

## 1. Source Review

| Artifact | Exists | Status | Phase identity |
| -------- | ------ | ------ | -------------- |
| `spec04-integration-implementation-contract.md` | Yes | `PREPARED_FOR_REVIEW` | Phase 4 Integration Implementation |
| `spec04-integration-implementation-lock.md` | Yes | `PREPARED_FOR_REVIEW` | Phase 4 Integration Implementation |
| `spec04-integration-implementation-execution-prompt.md` | Yes | `PREPARED_FOR_REVIEW` | Phase 4 Integration Implementation |

Confirmed across all three:

- None claims Phase 4 implementation is authorized
- None is self-authorizing
- None claims Spec04 Backend is complete
- None creates final acceptance/closure of Spec04 backend

Contract and prompt require governance review **and** later explicit implementation authorization **and** required Open Question resolution before coding.

---

## 2. Phase Identity

Confirmed: artifacts are for **Spec04 Backend Implementation Phase 4 – Integration Implementation**.

Confirmed they are **not**:

- Phase 3C Mutation implementation
- Authorization phase
- HTTP/API phase
- UI phase
- Final backend closure

---

## 3. Governance Consistency

| Consistency axis | Result |
| ---------------- | ------ |
| Phase name | Pass |
| Current accepted state (Phases 1–3C locked) | Pass |
| Locked layers | Pass |
| Allowed integration scope (thin wiring only) | Pass |
| Non-goals | Pass |
| Forbidden scope | Pass |
| Occupancy boundary | Pass |
| Traceability requirement | Pass |
| Open questions OQ-4-001…009 | Pass (contract defines; lock/prompt enforce as stop/forbid) |
| Stop conditions | Pass |
| Regression requirements (31/11/14/9) | Pass |
| Implementation not authorized yet | Pass |

**Material inconsistencies:** none found.

**Minor note (non-blocking):** the Contract lists Allocation/CheckIn/Request as design-evidenced *candidates* while Lock/Prompt forbid implementing their workflows. This is intentional and consistent: candidates ≠ authorized wires.

---

## 4. Scope Review

Proposed Phase 4 scope is limited to thin internal integration wiring:

- Internal contracts/interfaces when justified
- Adapters/services that delegate only to accepted Application Read/Mutation
- Integration DTOs/mappers for boundary isolation
- Provider bindings for approved integration contracts
- Integration tests for approved wiring

Confirmed artifacts do **not** authorize:

- New Dormitory business behavior
- Consumer workflow ownership
- Allocation / check-in/check-out / reservation / voucher / billing / payment / notification behavior
- External adapters
- Events/listeners/jobs unless separately approved
- Generic framework / CQRS expansion

First consumer and concrete wires are explicitly **not selected** pending Open Questions.

---

## 5. Traceability Review

Traceability rule is present and strong:

- Every Phase 4 element must trace to an accepted Application Read or Mutation capability
- Required identity fields: capability, consumer, need, why internals are insufficient
- Failure → Open Question / stop; not authorize

Dependency bans confirmed:

- Direct DB / Eloquent from external modules
- Domain mutation outside accepted Mutation services
- Bypassing Application contracts
- Read/write repository internals
- Persistence models as public integration surface
- UI/API/controller/policy layers

**Finding:** none material. Traceability is adequate.

---

## 6. Locked Layer Review

Locked and protected:

- Domain Layer
- Persistence Layer (migrations/schema/constraints/indexes/relationships)
- Application Read Phase 3A/3B
- Application Mutation Phase 3C

Forbidden without separate approval: domain/persistence/read/mutation behavior changes and schema work.

**No acceptance blocker** on layer locks.

---

## 7. Occupancy Boundary Review

Phase 3C note correctly carried forward:

- Occupancy = Dormitory state recording only
- Must not be reinterpreted as check-in/check-out
- Must not introduce allocation or reservation ownership
- CheckIn/CheckOut and Allocation workflows remain outside Phase 4 unless separately approved
- Future occupancy wiring must remain adapter-level pass-through (prompt/lock)

Occupancy is **not** treated as workflow ownership.

---

## 8. Open Question Review

| ID | Topic | Status in package |
| -- | ----- | ----------------- |
| OQ-4-001 | First approved consumer | Open; stop condition |
| OQ-4-002 | Synchronous-only? | Open; stop condition |
| OQ-4-003 | Allocation read port vs accepted Read APIs | Open; stop condition |
| OQ-4-004 | Allocation physical-state signal port in Phase 4? | Open; stop condition |
| OQ-4-005 | CheckIn occupancy request wiring | Open; stop condition |
| OQ-4-006 | Request `siteExists` mapping | Open; stop condition |
| OQ-4-007 | Events out of Phase 4? | Open; forbidden unless separately approved |
| OQ-4-008 | External adapters later-only? | Open; forbidden unless separately approved |
| OQ-4-009 | Orchestration ownership outside Dormitory Integration | Open; design leans yes; confirm as invariant |

Confirmed:

- OQs clearly identified
- OQs do not authorize implementation
- OQs not silently resolved
- Unresolved OQ-4-* are stop conditions / hard forbids
- Unapproved consumers remain out of scope
- Allocation/CheckIn/Request treated as **candidates**, not authorized Phase 4 consumers

**Missing OQs:** none material for the listed concern areas; coverage is sufficient.

---

## 9. Execution Prompt Review

Execution prompt is **safe for future authorized use** because it:

- References Contract and Lock
- States it is not self-authorizing
- Requires governance review + OQ resolution + separate explicit authorization
- Restates allowed/forbidden scope, locked layers, stop conditions
- Forbids implementing OQ-4-*
- Requires smallest valid implementation
- Forbids speculative extensibility and new business capability
- Requires approved integration tests only + required regression commands
- Forbids review/closure artifact creation during implementation

**Finding (residual, non-blocking):** Phase 4 remains abstract until OQ-4-001 (and related mapping OQs) are resolved. The prompt correctly refuses to invent those decisions; it will not safely drive coding until an explicit authorization names the approved consumer-capability pair(s).

---

## 10. Regression Requirements Review

Baseline preserved in Contract, Lock, and Prompt:

| Suite | Baseline |
| ----- | -------- |
| Domain | 31 |
| Persistence | 11 |
| Read | 14 |
| Mutation | 9 |
| Total | 65 |

Required commands present in Lock and Execution Prompt:

```bash
php -d memory_limit=512M artisan test tests/Unit/Modules/Dormitory/Domain
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Persistence
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Application/Read
php -d memory_limit=512M artisan test tests/Feature/Modules/Dormitory/Application/Mutation
```

Plus any approved Phase 4 integration test path created later.

---

## 11. Risk Assessment

| Risk | Assessment |
| ---- | ---------- |
| Scope too abstract without first consumer | **Acceptable residual** — captured as OQ-4-001 + stop conditions; blocks premature coding |
| Integration becoming architecture redesign | **Low** — redesign forbidden; thin wiring only |
| Adapter becoming workflow owner | **Low** — pass-through only; OQ-4-009 / occupancy boundary |
| Occupancy → CheckIn/CheckOut | **Low** — explicit forbid + Phase 3C note |
| Candidate consumers implemented without approval | **Low** — candidates ≠ authorization; OQ stop conditions |
| Events/external adapters sneaking in | **Low** — forbidden unless separately approved |
| Direct repo/DB/model bypass | **Low** — dependency bans + traceability |
| Generic framework/CQRS | **Low** — explicitly forbidden |
| `siteExists` ambiguity | **Acceptable residual** — OQ-4-006 |
| Prompt too permissive | **Low** — multi-gate authorization + OQ hard stop |

**Verdict:** Risks are acceptable because they are captured as Open Questions and stop conditions.

---

## 12. Acceptance Decision

**ACCEPTED_WITH_NOTES**

Spec04 Backend Implementation Phase 4 Integration Governance Package is accepted.

### Notes

1. Do not treat this governance acceptance as Phase 4 implementation authorization.
2. Before any coding authorization, resolve at least **OQ-4-001** and the mapping OQs for the chosen consumer-capability pair(s).
3. Keep Allocation/CheckIn/Request as candidates until explicitly named in an implementation authorization.
4. Occupancy wiring, if later approved, remains Dormitory state-recording delegation only.

---

## 13. Next Gate

**Next allowed gate:**

Explicitly authorize Phase 4 Integration Implementation using the accepted execution prompt.

Preconditions for that authorization (from the accepted package itself):

- Required **OQ-4-*** items for the authorized batch must be resolved/approved
- Authorization must name approved consumer-capability pair(s)
- Coding must still follow Contract, Lock, and Execution Prompt stop conditions

This review does **not**:

- Implement Phase 4
- Claim Phase 4 has started
- Claim Spec04 Backend is complete
- Create final backend closure artifacts

---

## References

- [`spec04-integration-implementation-contract.md`](spec04-integration-implementation-contract.md)
- [`spec04-integration-implementation-lock.md`](spec04-integration-implementation-lock.md)
- [`spec04-integration-implementation-execution-prompt.md`](spec04-integration-implementation-execution-prompt.md)
- [`spec04-application-mutation-layer-implementation-review.md`](spec04-application-mutation-layer-implementation-review.md)
