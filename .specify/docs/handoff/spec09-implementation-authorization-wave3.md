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
| **Authorization Type** | Controlled Continuation Implementation Authorization |
| **authorization-status** | `active` |
| **Authorization Status** | **ACTIVE** |
| **authorized-by** | Governance Review (Wave 3 activation) |
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
authorization-status: active
authorized-scope: Wave 3 — T027–T032
executable-forward-scope: T027–T032
maximum-authorized-scope: Wave 3 exit — T032 + CP-W7 PASS
future-continuation-scope: none (spec09 program terminal at T032)
active-execution-scope: T027–T032
blocked-scope: T033+; spec10; spec11; presentation UI; CheckIn scheduler implementation
authority-constraints: R9 frozen; downstream consumer only; no upstream Infrastructure imports; spec07/spec08 closed
```

---

## Status

**Implementation Authorized** — **ACTIVE** for **Wave 3** only.

This record grants controlled execution authority for the **Notification program closeout slice**: retention/archival (FR-013, UD-11), R9 boundary enforcement test, and PHPStan/Pint quality gates.

**This record does NOT authorize:**

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
| spec09 Wave 3 (this record) | **ACTIVE** — T027–T032 |
| spec10–spec11 | **NOT AUTHORIZED** |

Upon **T032 + CP-W7 PASS**:

- This record may transition to program-closed state (`revoked` or equivalent)
- Active execution scope becomes **none**
- spec09 implementation program **FULLY CLOSED** at T032

---

## Final Execution Directive

Authorized execution under spec09 Wave 3 begins **only** as follows:

1. Execute **Phase 7** — **T027–T029** → **CP-W6 PASS**
2. Execute **Phase 8** — **T030–T032** → **CP-W7 PASS**
3. **STOP** — spec09 program complete; no T033+

```text
Entry point: T027
Exit point: T032 + CP-W7 PASS
Authorized maximum: T027–T032
HALT on T033+.
```

---

## References

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
