# spec09 Implementation Authorization — Wave 1

**Recorded:** 2026-07-02  
**Authority:** Product / Tech governance  

---

## Authorization Header

| Field | Value |
| ----- | ----- |
| **Spec** | spec09 — Notification Delivery |
| **Wave** | **Wave 1** (MVP) |
| **Authorized Scope** | **T001–T020** only |
| **Authorization Type** | Controlled Initial Implementation Authorization |
| **authorization-status** | `superseded` |
| **Authorization Status** | **SUPERSEDED** (Wave 1 closed; active execution transferred to Wave 2) |
| **authorized-by** | Governance Review |
| **Effective Date** | Immediate upon issuance |
| **effective-date** | 2026-07-02 |
| **supersedes** | — |
| **superseded-by** | [`.specify/docs/handoff/spec09-implementation-authorization-wave2.md`](./spec09-implementation-authorization-wave2.md) |
| **lifecycle-reference** | `.specify/governance/_meta/authority-model.md` §4–§5 |
| **Design baseline** | `specs/009-notification-delivery/spec.md` (stabilized) |
| **Plan baseline** | `specs/009-notification-delivery/plan.md` (final) |
| **Task baseline** | `specs/009-notification-delivery/tasks.md` |
| **Nomination** | [`.specify/docs/handoff/spec09-nomination-record.md`](./spec09-nomination-record.md) |

**Normative scope fields**

```text
authorization-status: superseded
authorized-scope: Wave 1 — T001–T020 (complete)
superseded-by: spec09-implementation-authorization-wave2.md
executable-forward-scope: —
maximum-authorized-scope: Wave 1 exit — T020 + CP-W2 PASS (satisfied)
future-continuation-scope: Wave 2 — T021–T026 (authorized under superseding record)
active-execution-scope: none
blocked-scope: T021–T032 forward under this record; active execution on Wave 2 record only
authority-constraints: R9 frozen; historical record only for active execution
```

---

## Status

**Implementation Authorized** — **SUPERSEDED** (Wave 1 closed; active execution authority transferred to Wave 2).

This record historically granted controlled execution authority for the **initial Notification program slice**: module foundation, lifecycle intent delivery (US1), and inbox read/unread state (US2). **Wave 1 scope T001–T020 is complete.**

**Active execution authority:** [`.specify/docs/handoff/spec09-implementation-authorization-wave2.md`](./spec09-implementation-authorization-wave2.md)

**This record does NOT authorize forward execution under any scope.**

- Wave 2 forward execution — see superseding Wave 2 authorization record
- Wave 3 (T027–T032) — retention/archive job, R9 architecture test, PHPStan/Pint closeout gates
- spec10 Audit, spec11 Reporting implementation
- Presentation UI (OA-09-05)
- CheckIn `ScheduleCheckInRemindersJob` or scheduler integration (UD-10 producer side)
- Modification of closed programs (spec07, spec08)
- spec04 Dormitory implementation

---

## Baseline

### Specification baseline

| Reference | Value |
| --------- | ----- |
| Specification | [`specs/009-notification-delivery/spec.md`](../../specs/009-notification-delivery/spec.md) — **stabilized** |
| Plan | [`specs/009-notification-delivery/plan.md`](../../specs/009-notification-delivery/plan.md) — **final** |
| Tasks | [`specs/009-notification-delivery/tasks.md`](../../specs/009-notification-delivery/tasks.md) — **approved for implementation** |
| Data model | [`specs/009-notification-delivery/data-model.md`](../../specs/009-notification-delivery/data-model.md) |
| Contracts | [`specs/009-notification-delivery/contracts/`](../../specs/009-notification-delivery/contracts/) (5 contracts) |
| Research | [`specs/009-notification-delivery/research.md`](../../specs/009-notification-delivery/research.md) |
| Quickstart | [`specs/009-notification-delivery/quickstart.md`](../../specs/009-notification-delivery/quickstart.md) |

### Frozen boundaries (immutable for this authorization)

| Decision | Requirement |
| -------- | ----------- |
| **R9** | Notification consumes **notification intents** only — **no** direct upstream repository or Infrastructure access |
| **OA-09-01** | Upstream owns business policy and intent emission; Notification owns delivery and inbox state |
| **OA-09-02** | In-app (database-backed) channel only — no email/SMS/push (constitution §8.2) |
| **UD-09** | `NotificationIntentDto` + dedup key `(correlationId, recipientEmployeeId, notificationType)` — frozen |
| **UD-10** | CheckIn owns scheduler; Notification delivers reminder intents only — scheduler **out of Wave 1** |
| **UD-11** | 24-month soft-archive — inbox filter in Wave 1 (T018); archive job deferred to Wave 3 |
| **CD-017** | Reporting read-only downstream — reference only; not in Wave 1 write path |

### Prior governance

| Reference | State |
| --------- | ----- |
| [`spec09-nomination-record.md`](./spec09-nomination-record.md) | **ACTIVE** |
| spec07 program | **CLOSED** — [`spec07-implementation-authorization-wave1b.md`](./spec07-implementation-authorization-wave1b.md) |
| spec08 program | **CLOSED** — [`spec08-implementation-closure.md`](./spec08-implementation-closure.md) |
| Active execution scope (global) | spec07: **none**; spec08: **none**; spec09: **Wave 1** per this record |

---

## Authorized Scope

Implementation is authorized **only** for:

| Scope | Detail |
| ----- | ------ |
| **Bounded context** | **Notification** (spec09) — in-app delivery and inbox state only |
| **Tasks** | **T001–T020** only — per `tasks.md` |
| **Wave** | **Wave 1** only |
| **Phases** | Phase 1 (Foundation), Phase 2 (US1), Phase 3 (US2) |
| **User stories (authorized)** | US1 (lifecycle delivery), US2 (inbox and read state) |
| **Specification alignment** | Implementation must conform to stabilized `spec.md` and final `plan.md`; no scope expansion |

### Phase map (authorized)

| Phase | Task IDs | Behavior slice |
| ----- | -------- | -------------- |
| **Phase 1 — Foundation** | T001–T008 | Schema, domain, contracts, repository, DI |
| **Phase 2 — US1** | T009–T015 | Intent delivery, dedup, queues, recipient validation, BR-09.1 delivery tests |
| **Phase 3 — US2** | T016–T020 | Inbox read, mark read, recipient isolation |

### Future continuation scope (NOT authorized)

| Wave | Task IDs | Status |
| ---- | -------- | ------ |
| **Wave 2** | T021–T026 | **NOT AUTHORIZED** — US3 deep links, US4 idempotency tests, US5 reminder delivery slice |
| **Wave 3** | T027–T032 | **NOT AUTHORIZED** — retention job, R9 architecture test, PHPStan/Pint gates |

**Progression beyond T020 requires a separate follow-up Implementation Authorization record.**

### Implementation paths

| Area | Path |
| ---- | ---- |
| Notification module | `app/Modules/Notification/` |
| Migrations | `database/migrations/modules/notification/` |
| Unit tests | `tests/Unit/Modules/Notification/` |
| Feature tests | `tests/Feature/Modules/Notification/` |

---

## Explicitly Excluded Scope

| Excluded | Reason |
| -------- | ------ |
| **T021–T032** | Waves 2–3 — not authorized by this record |
| **US3** deep link dedicated tests | Wave 2 — T021–T022 |
| **US4** explicit idempotency verification tests | Wave 2 — T023–T024 (dedup **implementation** T010 is in Wave 1) |
| **US5** check-in reminder delivery | Wave 2 — T025–T026 |
| **ArchiveExpiredNotificationsJob** | Wave 3 — T027–T029 |
| **NotificationBoundaryTest** | Wave 3 — T030 |
| **PHPStan / Pint closeout** | Wave 3 — T031–T032 |
| **Presentation UI** | OA-09-05 deferred |
| **CheckIn scheduler** | UD-10 — CheckIn module; not spec09 Wave 1 |
| **Live Employee module adapter** | Stub acceptable — `StubEmployeeExistenceReadAdapter` (T014) |
| **Upstream intent producers** | No modification to spec05–08 modules |
| **spec07 / spec08** | **CLOSED** — no reopening |
| **spec10 Audit storage** | Out of scope |
| **spec11 Reporting** | Out of scope |
| **Email / SMS / push** | Constitution §8.2 — out of scope |

---

## Preconditions

The following **must** be satisfied before code execution begins:

| ID | Precondition | Status |
| -- | ------------ | ------ |
| P-01 | spec09 `spec.md` stabilized | ✅ |
| P-02 | spec09 `plan.md` final | ✅ |
| P-03 | spec09 `tasks.md` approved for implementation | ✅ |
| P-04 | Phase 0 artifacts complete (data-model, contracts, quickstart) | ✅ |
| P-05 | UD-09, UD-10, UD-11 resolved at planning | ✅ |
| P-06 | R9 boundary frozen | ✅ |
| P-07 | spec07 and spec08 **CLOSED** | ✅ |
| P-08 | spec09 nomination active | ✅ [`spec09-nomination-record.md`](./spec09-nomination-record.md) |
| P-09 | This Implementation Authorization record issued (`authorization-status: active`) | ✅ |

---

## Execution Entry and Exit

| Boundary | Value |
| -------- | ----- |
| **Entry point** | **T001** (Phase 1 — Foundation) |
| **Exit point (this authorization)** | **T020** + **CP-W2** PASS |
| **Maximum authorized task range** | **T001–T020** |
| **Next unauthorized task** | **T021** (Wave 2 — HALT without follow-up authorization) |

```text
Entry point: T001
Exit point: T020 + CP-W2 PASS
HALT on T021+ without separate authorization.
HALT on any work outside T001–T020 program boundary.
```

---

## Execution Rules

Implementation **MUST**:

- Start from **T001**
- Follow **`tasks.md`** phase and dependency order within **T001–T020**
- Complete phases **sequentially**: Phase 1 → Phase 2 → Phase 3
- Pass mandatory validation checkpoint after each phase before proceeding
- Preserve stabilized `spec.md` and final `plan.md` semantics — no redesign
- Implement **behavior-level tasks only** — no scope expansion beyond FR mapping for Wave 1
- Consume upstream facts **only** via `NotificationIntentDto` / `NotificationDeliveryContract` — no upstream store reads
- Use **`StubEmployeeExistenceReadAdapter`** for `EmployeeExistenceReadPort` until live Employee supplier is separately authorized
- Implement idempotent dedup in **T010** as part of US1 delivery path
- Route `urgent` priority to `notifications-urgent` queue per **FR-012** / **SC-006**
- Scope all inbox queries to `recipient_employee_id` per **FR-014**
- Exclude `archived_at IS NOT NULL` from default inbox list per **T018** / **UD-11** filter (archive job itself deferred)

Implementation **MUST NOT**:

- Execute **T021** or beyond under this record
- Import from `App\Modules\{Request,Lottery,Allocation,Voucher,CheckIn}\Infrastructure\*`
- Query upstream operational repositories or Eloquent models
- Execute upstream business policy (approval routing, allocation rules, voucher eligibility)
- Modify `spec.md`, `plan.md`, or `tasks.md` as part of execution
- Reopen or modify spec07 / spec08 artifacts
- Implement presentation UI, Audit storage, or Reporting projections
- Implement CheckIn reminder **scheduler** (producer side)
- Introduce email, SMS, or push channels
- Resolve scope beyond Wave 1 FR/US coverage

---

## Hard Stop Conditions

Execution **MUST HALT** immediately when any of the following occur:

| # | Condition | Action |
| - | --------- | ------ |
| 1 | Task outside **T001–T020** attempted | **HALT** — Wave 2–3 not authorized |
| 2 | Phase started before prior phase checkpoint **PASS** | **HALT** |
| 3 | Design ambiguity requires reinterpretation of spec or plan | **HALT** — change request required |
| 4 | Architecture drift — design beyond authorized behavior | **HALT** — ADR / change request required |
| 5 | **R9** violation — upstream Infrastructure import or repository access | **HALT** |
| 6 | Upstream policy execution inside Notification module | **HALT** |
| 7 | spec07 or spec08 reopening or modification attempted | **HALT** |
| 8 | Email/SMS/push channel introduced | **HALT** |
| 9 | New capability required not in Wave 1 scope | **HALT** |
| 10 | `spec.md`, `plan.md`, or `tasks.md` modification required to proceed | **HALT** |

Stop conditions **override** wave progress. No implicit authorization to continue.

Per `.specify/governance/execution-policy.md` § Pre-Execution Requirements: if this record is missing, revoked, or superseded, report:

> `Missing or invalid implementation authorization record.`

---

## Mandatory Validation Checkpoints

Execution **must not** advance past a phase until its checkpoint **PASS**es.

| Checkpoint | After | Mandatory verifications |
| ---------- | ----- | ------------------------ |
| **CP-W0** | Phase 1 (T001–T008) | Migration up/down; contracts resolvable; dedup unique index exists; DI bindings load |
| **CP-W1** | Phase 2 (T009–T015) | `DeliverNotificationAction` delivers lifecycle intents; dedup on replay; urgent queue routing; invalid recipient skip; `NotificationDeliveryTest` PASS — US1 equivalent |
| **CP-W2** | Phase 3 (T016–T020) | Inbox list/read/unread; mark read; recipient isolation; archived filter on default list; `NotificationInboxTest` PASS — US2 equivalent |

**Per-checkpoint drift checks (all CP-*):**

- No spec/plan/tasks reinterpretation
- R9 intact — no upstream Infrastructure imports
- No new capabilities introduced beyond Wave 1
- Dependency graph in `tasks.md` unchanged

**Wave 1 completion** is the terminal success state for **this** authorization record.

---

## Verification Gates (Wave 1 exit)

Wave 1 is **COMPLETE** only when all of the following are true:

| Gate | Criterion |
| ---- | --------- |
| **G-01** | T001–T020 marked complete per task completion criteria |
| **G-02** | `tests/Feature/Modules/Notification/NotificationDeliveryTest.php` — PASS |
| **G-03** | `tests/Feature/Modules/Notification/NotificationInboxTest.php` — PASS (includes recipient isolation) |
| **G-04** | Dedup behavior verified via delivery path (T010); explicit US4 concurrency tests deferred to Wave 2 |
| **G-05** | No regressions in existing test suite for touched paths |
| **G-06** | **CP-W2** recorded PASS |

**Deferred to Wave 3 (not blocking Wave 1 exit):** PHPStan level 8 (T031), Laravel Pint (T032), `NotificationBoundaryTest` (T030).

**Deferred to Wave 2 (not blocking Wave 1 exit):** `NotificationDeepLinkTest`, US4 duplicate/concurrency tests, US5 reminder tests.

---

## Open Dependencies (carried — stub path acceptable)

| ID | Item | Wave 1 handling |
| -- | ---- | --------------- |
| **OA-09-05** | Presentation UI | **Deferred** — not in Wave 1 |
| **Employee live supplier** | `EmployeeExistenceReadPort` from spec03 | **Stub** — `StubEmployeeExistenceReadAdapter` (T014) |
| **Upstream intent producers** | Request, Lottery, Allocation, Voucher, CheckIn | **Synthetic intents in tests** — no upstream module modification |
| **UD-10 scheduler** | CheckIn `ScheduleCheckInRemindersJob` | **Out of scope** — Wave 2 delivery slice only |
| **UD-11 archive job** | `ArchiveExpiredNotificationsJob` | **Deferred** — Wave 3; inbox filter (T018) in Wave 1 |
| **SC-001 latency** | 95% visible &lt; 5s | Operational tuning — no dedicated load test in Wave 1 |

---

## Authority Confirmation

| Constraint | Wave 1 disposition |
| ---------- | ------------------ |
| **R9 downstream-only** | **ENFORCED** — intents via contract only; no upstream Infrastructure imports |
| **No upstream repository reads** | **ENFORCED** — `EmployeeExistenceReadPort` is the sole outbound read; stub in Wave 1 |
| **No cross-domain policy execution** | **ENFORCED** — delivery persistence only |
| **CheckIn scheduler integration** | **OUT OF SCOPE** — producer remains CheckIn; Wave 1 does not implement scheduler |
| **UI presentation** | **OUT OF SCOPE** — OA-09-05 deferred |
| **In-app channel only** | **ENFORCED** — constitution §8.1–8.2 |
| **spec07 / spec08** | **CLOSED** — adapter stubs / synthetic intents only |

---

## Authority Transition

| Item | State |
| ---- | ----- |
| spec07 | **CLOSED** — no active execution |
| spec08 | **CLOSED** — no active execution |
| spec09 Wave 1 (this record) | **SUPERSEDED** — governs completed T001–T020 only |
| spec09 Wave 2 | **ACTIVE** — [`spec09-implementation-authorization-wave2.md`](./spec09-implementation-authorization-wave2.md) — T021–T026 |
| spec09 Wave 3 | **NOT AUTHORIZED** |
| spec10–spec11 | **NOT AUTHORIZED** |

Upon **CP-W2 PASS** and completion of T001–T020:

- This record is **`superseded`** for active execution as of 2026-07-02
- Historical validity for completed **T001–T020** is preserved
- Forward execution authority resides in Wave 2 authorization record only

---

## Final Execution Directive

Authorized execution under spec09 Wave 1 begins **only** as follows:

1. Verify preconditions **P-01** through **P-09**
2. Execute **Phase 1** — **T001–T008** → **CP-W0 PASS**
3. Execute **Phase 2** — **T009–T015** → **CP-W1 PASS**
4. Execute **Phase 3** — **T016–T020** → **CP-W2 PASS**
5. **STOP** — do not proceed to T021 without separate authorization

```text
Entry point: T001
Exit point: T020 + CP-W2 PASS
Authorized maximum: T001–T020
HALT on T021+ without follow-up Implementation Authorization.
```

---

## References

- [`spec09-implementation-authorization-wave2.md`](./spec09-implementation-authorization-wave2.md) — Wave 2 (active execution)
- [`spec09-nomination-record.md`](./spec09-nomination-record.md)
- [`spec08-implementation-closure.md`](./spec08-implementation-closure.md)
- [`spec07-implementation-authorization-wave1b.md`](./spec07-implementation-authorization-wave1b.md)
- [`context-map.md`](../context-map.md) R9
- [`catalog-decisions.md`](../catalog-decisions.md) CD-017
- `specs/009-notification-delivery/spec.md`
- `specs/009-notification-delivery/plan.md`
- `specs/009-notification-delivery/tasks.md`
- `.specify/governance/execution-policy.md`
- `.specify/governance/_meta/authority-model.md` §4–§5

---

**End of authorization record.**
