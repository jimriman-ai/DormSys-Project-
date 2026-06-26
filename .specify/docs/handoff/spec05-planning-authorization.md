# spec05 Planning Authorization Record

**Recorded:** 2026-06-23  
**Baseline:** `spec02-baseline` @ `7983c27` (tag: `spec04-design-approved` on design commit `097741a`)  
**Authority:** Product / Tech governance  

**Checkpoint:** `spec05-planning-review` = **PASS** (OA-05-09 read-only `PendingRequestReadPort` boundary recorded)

---

## Decision

| Level | Status |
| ----- | ------ |
| **Planning** | **Authorized** |
| **Phase 1 Design** | **Authorized** (`research.md`, `data-model.md`, `contracts/`, `quickstart.md`) |
| **Tasks** (`/speckit-tasks`) | **Not authorized** |
| **Implementation** | **Not authorized** |
| **Code** (migrations, module, tests) | **Not authorized** |

**spec05 Request Management — planning and Phase 1 design are authorized.**

Implementation is **not** authorized until `spec.md`, `plan.md`, Phase 1 design artifacts, and `tasks.md` are approved under a separate **implementation authorization** (after design-approved checkpoint).

---

## Rationale

- Catalog ordering guidance places spec05 after spec03 Wave 1A + 1B (`spec-catalog.md` §Ordering).
- `spec05-planning-review` checkpoint passed: specification, boundary review, architecture review, and plan validated (`specs/005-request-management/`).
- Request BC is upstream supplier for Lottery (spec06) and Allocation (spec07) per `context-map.md` R4, R6.
- CD-009, CD-010, CD-013 boundaries documented in spec and plan; OA-05-09 closes `PendingRequestReadPort` read-only coupling risk.
- spec04 remains **design-approved / frozen** — spec05 does not require spec04 implementation (`dormitory_id` UUID + optional read stub).

---

## Authorization scope

### Allowed

| Activity | Scope |
| -------- | ----- |
| Phase 1 design | `research.md`, `data-model.md`, `contracts/`, `quickstart.md` under `specs/005-request-management/` |
| Resolve open modeling questions | Within spec05 bounded context; no catalog boundary change without `catalog-decisions.md` |
| Refine state machine / approval routing | Approval-phase states only (OA-05-01); post-approval states remain spec07 |
| Contract documentation | `RequestReadContract`, `PendingRequestReadPort` adapter boundary (OA-05-09), Employee/Identity inbound contracts |
| Design review checkpoint | Tag (e.g. `spec05-design-approved`) after Phase 1 complete — separate step |

### Not allowed

| Activity | Reason |
| -------- | ------ |
| `/speckit-tasks` or `tasks.md` | Blocked until design-approved checkpoint |
| Request module implementation | Separate implementation authorization required |
| Migrations, Eloquent models, Application Actions | Code not authorized |
| spec04 changes | spec04 frozen — design-approved / implementation hold |
| spec06 / spec07 implementation | Downstream — not authorized |
| Workflow module activation | Deferred per catalog |
| spec03 US3 / US4 implementation (T035+) | Hold per `handoff/spec03-post-mvp-authorization.md` |
| Reopening spec03 US2 (T027–T034) | Closed — complete |

---

## Approved scope (planning artifacts)

| Artifact | Path | Status at authorization |
| -------- | ---- | ----------------------- |
| Specification | `specs/005-request-management/spec.md` | ✅ Planning review PASS |
| Plan | `specs/005-request-management/plan.md` | ✅ Planning review PASS |
| Checklist | `specs/005-request-management/checklists/requirements.md` | ✅ PASS |
| Phase 1 design | `research.md`, `data-model.md`, `contracts/`, `quickstart.md` | ⏳ Authorized to create — not started |

**User stories in scope (documentation):** US1–US6 per `spec.md` (implementation waves: 1A = US1–3, 1B = US4, 1C = US5–6 per `plan.md`).

**MVP planning boundary:** Request lifecycle through **`Approved`** terminal state; no `WaitingForAllocation`+ states (OA-05-03 → spec07).

---

## Dependencies

### Hard dependencies (catalog)

| Spec | Artifact / relationship | Status |
| ---- | ----------------------- | ------ |
| spec01 | Module scaffold, UUID kernel, migrations pattern | Approved |
| spec02 | Auth, RBAC, `IdentityUserReadContract` (optional approver validation) | Frozen — Wave 1A |
| spec03 | `employee_id`; `EmployeeEligibilityContract` (CD-013) | Wave 1A + 1B complete |
| spec03 | `PendingRequestReadPort` interface (Employee owns); Request implements read-only adapter (OA-05-09) | Contract defined; stub adapter Wave 1A |

### Soft dependency (not catalog-blocking)

| Spec | Usage | Status |
| ---- | ----- | ------ |
| spec04 | `dormitory_id` UUID; optional `DormitoryReadContract` validation | Design approved; implementation hold — stub sufficient for Phase 1 |

### Downstream (documented only — not authorized)

| Spec | Relationship |
| ---- | ------------ |
| spec06 Lottery | R4 — consumes approved `LotteryRegistration` requests |
| spec07 Allocation | R6 — consumes approved requests; post-approval states |

---

## Boundaries (normative)

| Decision | Request responsibility |
| -------- | ---------------------- |
| **CD-009** | Dependent **snapshots** on FamilyDirect only — not Dependent aggregate ownership |
| **CD-010** | Owns `RequestApproval` state and history; inline four-stage routing Wave 1; Workflow deferred |
| **CD-013** | **Enforces** BR-01 at submit via `EmployeeEligibilityContract`; Employee **computes** |
| **OA-05-09** | `PendingRequestReadPort` is **read-only pull** — `hasPendingRequest()` only; no Request commands or lifecycle mutations from Employee port |
| **OA-05-01** | State machine: `Draft` … `Approved` \| `Rejected` \| `Cancelled` — no Allocation-phase states in spec05 |
| Cross-module FK | **Prohibited** — `employee_id`, `dormitory_id`, `approver_id` as immutable UUID refs only |
| Cross-module Eloquent | **Prohibited** — supplier contracts only |

```
Employee Context                          Request Context
────────────────                          ───────────────
owns: EmployeeEligibilityContract    ←── enforce at submit
owns: PendingRequestReadPort (iface) ←── adapter: hasPendingRequest() [read-only]
                                         owns: Request state, RequestApproval, transitions
```

---

## Protected status (unchanged)

| Scope | State |
| ----- | ----- |
| spec04 | **Design approved / frozen** — tag `spec04-design-approved`; implementation hold |
| spec03 Wave 1B (US2) | **Closed — Complete** |
| spec03 US3 / US4 (T035+) | **Hold — Unauthorized** |
| spec06–spec11 | **Planned** — not authorized |
| Workflow | **Deferred** |

---

## Open questions for Phase 1 design

| Topic | Source | Phase 1 action |
| ----- | ------ | -------------- |
| Dependent snapshot field set | CD-009, OA-05-04 | Define in `data-model.md`; blocked on spec03 US3 for live supplier |
| Request code format | Discovery `REQ-YYMMDD-NNNN` | Confirm in `research.md` |
| Auto-approval settings keys | AP-08, OA-05-02 | Document per-stage `settings` keys |
| `RequestReadContract` DTOs | OA-05-06 | Finalize in `contracts/` |
| Mission `RequestMember` identity refs | OA-05-05 | Employee UUID vs embedded person — resolve in design |
| Post-approval handoff event | OA-05-03 | Document contract stub only; spec07 owns transition |

---

## References

- `spec-catalog.md` v1.0.6 — spec05 Planning Authorized
- `specs/005-request-management/spec.md` — OA-05-01 through OA-05-09
- `specs/005-request-management/plan.md` — MVP waves, BT-R01–R09
- `handoff/spec04-planning-authorization.md` — spec04 scope; spec05 was not authorized at spec04 record time
- `handoff/spec03-post-mvp-authorization.md` — US3+ hold
- `context-map.md` — Request row; R2, R3, R4, R6
- `catalog-decisions.md` — CD-009, CD-010, CD-013
