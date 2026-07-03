# spec09 Implementation Authorization — Wave 3

**Recorded:** 2026-07-02  
**Authority:** Product / Tech governance  

---

## Authorization Header

| Field | Value |
| ----- | ----- |
| **Spec** | spec09 — Notification Delivery |
| **Wave** | **Wave 3** (terminal program wave) |
| **Authorized Scope** | **T027–T032** only |
| **Authorization Type** | Program Closure Authorization (terminal wave) |
| **authorization-status** | `revoked` |
| **Authorization Status** | **CLOSED** |
| **authorized-by** | Governance Review (Wave 3 activation) |
| **closure-date** | 2026-07-02 |
| **revocation-reason** | Program closure — executable scope exhausted (T027–T032 complete) |
| **Effective Date** | Immediate upon issuance |
| **effective-date** | 2026-07-02 |
| **supersedes** | [`.specify/docs/handoff/spec09-implementation-authorization-wave2.md`](./spec09-implementation-authorization-wave2.md) *(active execution authority only)* |
| **superseded-by** | — |
| **lifecycle-reference** | `.specify/governance/_meta/authority-model.md` §4–§5 |
| **Design baseline** | `specs/009-notification-delivery/spec.md` (stabilized) |
| **Plan baseline** | `specs/009-notification-delivery/plan.md` (final) |
| **Task baseline** | `specs/009-notification-delivery/tasks.md` |
| **Nomination** | [`.specify/docs/handoff/spec09-nomination-record.md`](./spec09-nomination-record.md) |
| **Prior wave** | Wave 2 **CLOSED** — T021–T026 + CP-W5 PASS |

**Normative scope fields**

```text
authorization-status: revoked
authorized-scope: Wave 3 — T027–T032 (complete)
executable-forward-scope: —
retroactive-acceptance-scope: Wave 3 — T027–T032 (complete)
blocked-scope: —
active-execution-scope: none
authority-constraints: program closed; no forward implementation permitted; cannot authorize spec10+ under this record; cannot reopen T001–T032 without new authorization record
```

---

## Status

**Implementation Authorized** — **CLOSED** (Wave 3 complete; spec09 program fully closed).

Wave 3 scope **T027–T032** is complete. Active execution authority is **none**. Program closure is recorded in [`spec09-implementation-closure.md`](./spec09-implementation-closure.md).

**This record does NOT authorize forward execution under any scope.**

**Historical exclusions (no longer active):**

- Tasks beyond **T032** (spec09 program boundary)
- Wave 1–2 rework (T001–T026) — historical scope only
- spec10 Audit, spec11 Reporting implementation
- Presentation UI (OA-09-05)
- CheckIn `ScheduleCheckInRemindersJob` or scheduler integration (UD-10 producer side)
- Modification of closed programs (spec07, spec08)

---

## Activation Record

| Field | Value |
| ----- | ----- |
| **Activation decision** | **APPROVE** |
| **Readiness basis** | Wave 3 gate = AUTHORIZED; Wave 2 complete (T021–T026); CP-W3/W4/W5 PASS; R9 PASS |
| **Governance gate** | Wave 3 Authorization Gate — readiness assessment PASS |
| **Wave 2 supersession** | [`spec09-implementation-authorization-wave2.md`](./spec09-implementation-authorization-wave2.md) → `superseded` (active execution) |

---

## Wave 2 Exit Evidence

| Gate | Status | Evidence |
| ---- | ------ | -------- |
| T021–T026 complete | PASS | `tasks.md` all `[x]` |
| CP-W3 | PASS | US3 deep-link checkpoint recorded |
| CP-W4 | PASS | US4 idempotency checkpoint recorded |
| CP-W5 | PASS | US5 check-in reminder slice checkpoint recorded |
| Wave 2 tests | PASS | `NotificationDeepLinkTest`, `NotificationIdempotencyTest`, `NotificationCheckInReminderTest` + Wave 1 suite |
| R9 boundary | PASS | No upstream Infrastructure imports in `app/Modules/Notification/` |
| Authorization compliance | PASS | Implementation stopped at T026 |

---

## Authorized Scope

Implementation is authorized **only** for:

| Scope | Detail |
| ----- | ------ |
| **Bounded context** | **Notification** (spec09) — closeout slice |
| **Tasks** | **T027–T032** only |
| **Phases** | Phase 7 (retention), Phase 8 (boundary + quality) |
| **Capabilities** | Archive job, schedule registration, retention test, R9 architecture test, PHPStan, Pint |

### Phase map (authorized)

| Phase | Task IDs | Behavior slice |
| ----- | -------- | -------------- |
| **Phase 7 — Retention** | T027–T029 | `ArchiveExpiredNotificationsJob`, daily schedule, retention feature test |
| **Phase 8 — Closeout** | T030–T032 | `NotificationBoundaryTest`, PHPStan L8, Pint |

### Explicitly excluded

| Excluded | Reason |
| -------- | ------ |
| **T033+** | Beyond spec09 program scope |
| **T001–T026 rework** | Waves 1–2 CLOSED — read-only verification only |
| **CheckIn `ScheduleCheckInRemindersJob`** | UD-10 — CheckIn module producer |
| **Presentation UI** | OA-09-05 deferred |
| **spec07 / spec08 modification** | CLOSED programs |
| **spec10 / spec11** | Not authorized |

---

## Execution Entry and Exit

| Boundary | Value |
| -------- | ----- |
| **Entry point** | **T027** |
| **Exit point** | **T032** + **CP-W7** PASS |
| **Maximum authorized task range** | **T027–T032** |
| **Stop boundary** | **T033** (HALT — program complete at T032) |

```text
Entry point: T027
Exit point: T032 + CP-W7 PASS
HALT on T033+.
HALT on any work outside T027–T032 program boundary.
```

---

## Execution Rules

Implementation **MUST**:

- Execute **T027–T032** in phase order per `tasks.md`
- Pass **CP-W6** after T027–T029 before T030
- Preserve R9 — archive job touches `notification_logs` only; no upstream Infrastructure imports
- Register **only** `ArchiveExpiredNotificationsJob` on schedule (T028)
- Run PHPStan/Pint only on Notification module paths (T031–T032)

Implementation **MUST NOT**:

- Execute T033 or beyond
- Hard-delete notification rows (UD-11 soft-archive only)
- Modify spec07/spec08 modules
- Implement presentation UI, audit storage, or reporting
- Implement CheckIn reminder scheduler (producer side)
- Reopen Waves 1–2 behavior without ADR

---

## Hard Stop Conditions

| # | Condition | Action |
| - | --------- | ------ |
| 1 | Task outside **T027–T032** attempted | **HALT** — program boundary |
| 2 | Phase 8 started before **CP-W6** PASS | **HALT** |
| 3 | **R9** violation — upstream Infrastructure import or repository access | **HALT** |
| 4 | CheckIn scheduler or UI work in scope | **HALT** |
| 5 | Hard delete of notification records | **HALT** |

---

## Verification Gates (Wave 3 exit)

| Gate | Criterion |
| ---- | --------- |
| **G-W3-01** | T027–T032 marked complete per task criteria |
| **G-W3-02** | `NotificationRetentionTest.php` — PASS |
| **G-W3-03** | `NotificationBoundaryTest.php` — PASS |
| **G-W3-04** | PHPStan level 8 on `app/Modules/Notification/` — zero errors |
| **G-W3-05** | Pint on Notification module + related tests — zero violations |
| **G-W3-06** | No regressions in full Notification feature suite |
| **G-W3-07** | **CP-W7** recorded PASS |

---

## Mandatory Checkpoints

| Checkpoint | After | Mandatory verifications |
| ---------- | ----- | ------------------------ |
| **CP-W6** | T027–T029 | Archive job sets `archived_at`; expired rows excluded from inbox; FR-013 |
| **CP-W7** | T030–T032 | R9 architecture test PASS; PHPStan/Pint PASS; spec09 program closeout |

---

## Authority Confirmation

| Constraint | Wave 3 disposition |
| ---------- | ------------------ |
| **R9 downstream-only** | **ENFORCED** |
| **No upstream repository reads** | **ENFORCED** |
| **Retention confined to Notification store** | **ENFORCED** |
| **CheckIn scheduler** | **OUT OF SCOPE** |
| **Presentation UI** | **OUT OF SCOPE** |
| **T033+** | **BLOCKED** |

---

## Authority Transition

| Item | State |
| ---- | ----- |
| spec07 | **CLOSED** — no active execution |
| spec08 | **CLOSED** — no active execution |
| spec09 Wave 1 | **SUPERSEDED** — governs completed T001–T020 |
| spec09 Wave 2 | **SUPERSEDED** — governs completed T021–T026 |
| spec09 Wave 3 (this record) | **REVOKED** — governs completed T027–T032 only |
| spec09 program | **FULLY CLOSED** — [`spec09-implementation-closure.md`](./spec09-implementation-closure.md) |
| spec10–spec11 | **NOT AUTHORIZED** |

Upon program closure (2026-07-02):

- This record is **`revoked`** for active execution
- Historical validity for completed **T027–T032** is preserved
- **Active execution scope** is **none**

---

## Program Closure Record

**Checkpoint:** `spec09-implementation-closure` = **RECORDED**  
**Closed:** 2026-07-02  
**Actor:** Governance Review  

| Item | State |
| ---- | ----- |
| Wave 1 | **CLOSED** (T001–T020) |
| Wave 2 | **CLOSED** (T021–T026) |
| Wave 3 | **CLOSED** (T027–T032) |
| CP-W6 / CP-W7 | **PASS** |
| Active execution scope | **NONE** |
| spec10+ | **NOT AUTHORIZED** |

This record is **terminal** for active execution. See [`spec09-implementation-closure.md`](./spec09-implementation-closure.md).

---

## Final Execution Directive

**Program complete.** No forward execution permitted under this record.

```text
Entry point: (none — program closed)
Exit point: T032 + CP-W7 PASS — SATISFIED
Active execution scope: none
HALT on any work under spec09 without new authorization.
```

---

## References

- [`spec09-implementation-closure.md`](./spec09-implementation-closure.md) — program closure
- [`spec09-implementation-authorization-wave2.md`](./spec09-implementation-authorization-wave2.md) — Wave 2 (superseded for active execution)
- [`spec09-implementation-authorization.md`](./spec09-implementation-authorization.md) — Wave 1 (superseded)
- [`spec09-nomination-record.md`](./spec09-nomination-record.md)
- `specs/009-notification-delivery/spec.md`
- `specs/009-notification-delivery/plan.md`
- `specs/009-notification-delivery/tasks.md`
- `.specify/governance/execution-policy.md`
- `.specify/governance/_meta/authority-model.md` §4–§5

---

**End of authorization record.**
