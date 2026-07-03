# spec09 Implementation Authorization — Wave 2

**Recorded:** 2026-07-02  
**Authority:** Product / Tech governance  

---

## Authorization Header

| Field | Value |
| ----- | ----- |
| **Spec** | spec09 — Notification Delivery |
| **Wave** | **Wave 2** |
| **Authorized Scope** | **T021–T026** only |
| **Authorization Type** | Controlled Continuation Implementation Authorization |
| **authorization-status** | `superseded` |
| **Authorization Status** | **SUPERSEDED** (Wave 2 closed; active execution transferred to Wave 3) |
| **authorized-by** | Governance Review (Wave 2 activation) |
| **Effective Date** | Immediate upon issuance |
| **effective-date** | 2026-07-02 |
| **supersedes** | [`.specify/docs/handoff/spec09-implementation-authorization.md`](./spec09-implementation-authorization.md) *(active execution authority only)* |
| **superseded-by** | [`.specify/docs/handoff/spec09-implementation-authorization-wave3.md`](./spec09-implementation-authorization-wave3.md) |
| **lifecycle-reference** | `.specify/governance/_meta/authority-model.md` §4–§5 |
| **Design baseline** | `specs/009-notification-delivery/spec.md` (stabilized) |
| **Plan baseline** | `specs/009-notification-delivery/plan.md` (final) |
| **Task baseline** | `specs/009-notification-delivery/tasks.md` |
| **Nomination** | [`.specify/docs/handoff/spec09-nomination-record.md`](./spec09-nomination-record.md) |
| **Prior wave** | Wave 1 **CLOSED** — T001–T020 + CP-W2 PASS |

**Normative scope fields**

```text
authorization-status: superseded
authorized-scope: Wave 2 — T021–T026 (complete)
superseded-by: spec09-implementation-authorization-wave3.md
executable-forward-scope: —
maximum-authorized-scope: Wave 2 exit — T026 + CP-W5 PASS (satisfied)
future-continuation-scope: Wave 3 — T027–T032 (authorized under superseding record)
active-execution-scope: none
blocked-scope: T027–T032 forward under this record; active execution on Wave 3 record only
authority-constraints: R9 frozen; historical record only for active execution
```

---

## Status

**Implementation Authorized** — **SUPERSEDED** (Wave 2 closed; active execution authority transferred to Wave 3).

This record historically granted controlled execution authority for the **Notification continuation slice**: deep-link persistence verification (US3), explicit idempotency tests (US4), and check-in reminder delivery slice (US5). **Wave 2 scope T021–T026 is complete.**

**Active execution authority:** [`.specify/docs/handoff/spec09-implementation-authorization-wave3.md`](./spec09-implementation-authorization-wave3.md)

**This record does NOT authorize forward execution under any scope.**

**Historical exclusions (no longer active):**

- Wave 3 forward execution — see superseding Wave 3 authorization record
- Wave 1 rework (T001–T020) — historical scope only
- spec10 Audit, spec11 Reporting implementation
- Presentation UI (OA-09-05)
- CheckIn `ScheduleCheckInRemindersJob` or scheduler integration (UD-10 producer side)
- Modification of closed programs (spec07, spec08)

---

## Activation Record

| Field | Value |
| ----- | ----- |
| **Activation decision** | **APPROVE** |
| **Readiness basis** | Wave 1 complete (T001–T020); CP-W2 PASS; 14/14 Notification feature tests passing |
| **Promoted from** | [`spec09-implementation-authorization-wave2-DRAFT.md`](./spec09-implementation-authorization-wave2-DRAFT.md) |
| **Wave 1 supersession** | [`spec09-implementation-authorization.md`](./spec09-implementation-authorization.md) → `superseded` (active execution) |

---

## Wave 1 Exit Evidence

| Gate | Status | Evidence |
| ---- | ------ | -------- |
| T001–T020 complete | PASS | `tasks.md` all `[x]` |
| CP-W0 | PASS | Foundation checkpoint recorded |
| CP-W1 | PASS | US1 delivery checkpoint recorded |
| CP-W2 | PASS | US2 inbox checkpoint recorded |
| Wave 1 tests | PASS | `NotificationDeliveryTest` + `NotificationInboxTest` — 14/14 |
| R9 boundary | PASS | No upstream Infrastructure imports in `app/Modules/Notification/` |
| Authorization compliance | PASS | Implementation stopped at T020 |

---

## Authorized Scope

Implementation is authorized **only** for:

| Scope | Detail |
| ----- | ------ |
| **Bounded context** | **Notification** (spec09) — continuation slice |
| **Tasks** | **T021–T026** only |
| **Phases** | Phase 4 (US3), Phase 5 (US4), Phase 6 (US5) |
| **User stories** | US3 (deep link), US4 (idempotency verification), US5 (check-in reminder delivery slice) |

### Phase map (authorized)

| Phase | Task IDs | Behavior slice |
| ----- | -------- | -------------- |
| **Phase 4 — US3** | T021–T022 | Entity reference / deep-link persistence verification + tests |
| **Phase 5 — US4** | T023–T024 | Explicit idempotency replay + concurrency tests |
| **Phase 6 — US5** | T025–T026 | `check_in_reminder` delivery slice + synthetic intent test |

### Explicitly excluded

| Excluded | Reason |
| -------- | ------ |
| **T027–T032** | Wave 3 — retention, architecture test, PHPStan/Pint closeout |
| **T001–T020 rework** | Wave 1 CLOSED — read-only verification only |
| **CheckIn `ScheduleCheckInRemindersJob`** | UD-10 — CheckIn module producer; out of spec09 |
| **Presentation UI** | OA-09-05 deferred |
| **spec07 / spec08 modification** | CLOSED programs |
| **spec10 / spec11** | Not authorized |

---

## Execution Entry and Exit

| Boundary | Value |
| -------- | ----- |
| **Entry point** | **T021** |
| **Exit point** | **T026** + **CP-W5** PASS |
| **Maximum authorized task range** | **T021–T026** |
| **Next unauthorized task** | **T027** (HALT without Wave 3 authorization) |

```text
Entry point: T021
Exit point: T026 + CP-W5 PASS
HALT on T027+ without separate authorization.
HALT on any work outside T021–T026 program boundary.
```

---

## Execution Rules

Implementation **MUST**:

- Execute **T021–T026** in phase order per `tasks.md`
- Pass **CP-W3** before T023; **CP-W4** before T025
- Preserve R9 — intents via contracts only; no upstream Infrastructure imports
- Treat Wave 1 code as baseline — reconcile partial T021/T025 coverage via verification, not scope expansion
- Use synthetic intents for US5 — no CheckIn scheduler implementation

Implementation **MUST NOT**:

- Execute T027 or beyond
- Modify spec07/spec08 modules
- Implement presentation UI, audit storage, or reporting
- Implement CheckIn reminder scheduler (producer side)
- Reopen or redesign Wave 1 delivery/inbox behavior without ADR

---

## Hard Stop Conditions

| # | Condition | Action |
| - | --------- | ------ |
| 1 | Task outside **T021–T026** attempted | **HALT** — Wave 3 not authorized |
| 2 | Phase started before prior phase checkpoint **PASS** | **HALT** |
| 3 | **R9** violation — upstream Infrastructure import or repository access | **HALT** |
| 4 | CheckIn scheduler implementation in Notification module | **HALT** |
| 5 | Scope expansion beyond Wave 2 FR/US coverage | **HALT** |

---

## Verification Gates (Wave 2 exit)

| Gate | Criterion |
| ---- | --------- |
| **G-W2-01** | T021–T026 marked complete per task criteria |
| **G-W2-02** | `NotificationDeepLinkTest.php` — PASS |
| **G-W2-03** | US4 idempotency tests (replay + concurrency) — PASS |
| **G-W2-04** | US5 synthetic `check_in_reminder` delivery test — PASS |
| **G-W2-05** | No regressions in Wave 1 test suite (14 tests) |
| **G-W2-06** | **CP-W5** recorded PASS |

**Deferred to Wave 3:** T030 (`NotificationBoundaryTest`), T031 (PHPStan), T032 (Pint), T027–T029 (retention job)

---

## Mandatory Checkpoints

| Checkpoint | After | Mandatory verifications |
| ---------- | ----- | ------------------------ |
| **CP-W3** | T021–T022 | Entity fields in projection; deep-link test PASS; SC-005 |
| **CP-W4** | T023–T024 | Explicit duplicate replay + concurrency tests PASS |
| **CP-W5** | T025–T026 | `check_in_reminder` delivery test PASS; no scheduler code in Notification |

---

## Wave 2 Reconciliation Notes

| Task | Wave 1 carry-forward | Wave 2 action |
| ---- | -------------------- | ------------- |
| **T021** | Schema + repository + projection DTO already persist entity/deep-link fields | Verify end-to-end; complete if gaps found |
| **T023** | Basic dedup replay covered in `NotificationDeliveryTest` | Add dedicated US4 test file per task spec |
| **T025** | `NotificationType::CheckInReminder` exists in enum | Verify delivery path; add T026 test |

---

## Authority Confirmation

| Constraint | Wave 2 disposition |
| ---------- | ------------------ |
| **R9 downstream-only** | **ENFORCED** |
| **No upstream repository reads** | **ENFORCED** |
| **No cross-domain policy execution** | **ENFORCED** |
| **CheckIn scheduler** | **OUT OF SCOPE** |
| **T027–T032** | **BLOCKED** |

---

## Authority Transition

| Item | State |
| ---- | ----- |
| spec07 | **CLOSED** — no active execution |
| spec08 | **CLOSED** — no active execution |
| spec09 Wave 1 | **SUPERSEDED** (active execution) — governs completed T001–T020 |
| spec09 Wave 2 (this record) | **SUPERSEDED** — governs completed T021–T026 only |
| spec09 Wave 3 | **ACTIVE** — [`spec09-implementation-authorization-wave3.md`](./spec09-implementation-authorization-wave3.md) — T027–T032 |
| spec10–spec11 | **NOT AUTHORIZED** |

Upon Wave 3 activation (2026-07-02):

- This record is **`superseded`** for active execution
- Historical validity for completed **T021–T026** is preserved
- Forward execution authority resides in Wave 3 authorization record only

---

## Final Execution Directive

Authorized execution under spec09 Wave 2 begins **only** as follows:

1. Execute **Phase 4** — **T021–T022** → **CP-W3 PASS**
2. Execute **Phase 5** — **T023–T024** → **CP-W4 PASS**
3. Execute **Phase 6** — **T025–T026** → **CP-W5 PASS**
4. **STOP** — do not proceed to T027 without separate authorization

```text
Entry point: T021
Exit point: T026 + CP-W5 PASS
Authorized maximum: T021–T026
HALT on T027+ without follow-up Implementation Authorization.
```

---

## References

- [`spec09-implementation-authorization-wave3.md`](./spec09-implementation-authorization-wave3.md) — Wave 3 (active execution)
- [`spec09-implementation-authorization.md`](./spec09-implementation-authorization.md) — Wave 1 (superseded for active execution)
- [`spec09-nomination-record.md`](./spec09-nomination-record.md)
- `specs/009-notification-delivery/spec.md`
- `specs/009-notification-delivery/plan.md`
- `specs/009-notification-delivery/tasks.md`
- `.specify/governance/execution-policy.md`
- `.specify/governance/_meta/authority-model.md` §4–§5

---

**End of authorization record.**
