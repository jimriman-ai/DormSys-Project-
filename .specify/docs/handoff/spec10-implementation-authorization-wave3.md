# spec10 Implementation Authorization — Wave 3

**Recorded:** 2026-07-02  
**Authority:** Product / Tech governance  

---

## Authorization Header

| Field | Value |
| ----- | ----- |
| **Spec** | spec10 — Audit Trail & Traceability |
| **Feature** | audit-trail |
| **Wave** | **Wave 3 — Retention, Bridge & Quality Closeout** |
| **Authorized Scope** | **T033–T040** only |
| **Authorization Type** | Controlled Continuation Implementation Authorization |
| **authorization-status** | `revoked` |
| **Authorization Status** | **CLOSED** |
| **execution-state** | `CLOSED` |
| **authorized-by** | Governance Review / Activation |
| **closure-date** | 2026-07-02 |
| **revocation-reason** | Program closure — executable scope exhausted (T033–T040 complete; CP-A5 PASS) |
| **effective-date** | 2026-07-02 |
| **activation-date** | 2026-07-02 |
| **supersedes** | — *(Wave 2 authorization revoked; no prior active Wave 3 record)* |
| **superseded-by** | — |
| **lifecycle-reference** | `.specify/governance/_meta/authority-model.md` §4–§5 |
| **Governance review** | [`spec10-wave3-governance-review.md`](./spec10-wave3-governance-review.md) — **PASS WITH CONDITIONS** |
| **Authorization basis** | Wave 3 governance review + handoff readiness |
| **Predecessor waves** | Wave 1A **CLOSED** (T001–T021); Wave 1B **CLOSED** (T022–T027); Wave 2 **CLOSED** (T028–T032) |
| **Task baseline** | `specs/010-audit-trail/tasks.md` |

**Normative scope fields**

```text
authorization-status: revoked
execution-state: CLOSED
authorized-scope: Wave 3 — T033–T040 (complete)
active-execution-scope: none
executable-forward-scope: —
retroactive-acceptance-scope: —
blocked-scope: T001–T040 (historical complete); T041+ (not defined)
completed-scope: T001–T040
entry-point: T033 (historical)
exit-point: T040 + CP-A5 PASS (satisfied)
exit-gate: CP-A5 PASS
revocation-reason: spec10 program closure — spec10-final-closure.md
authority-constraints: program closed; no forward implementation permitted; cannot reopen T001–T040 without new authorization record
```

---

## Status

**Implementation Authorized** — **REVOKED** (Wave 3 closed; spec10 program complete).

This record historically granted controlled execution authority for **retention, optional bridge (dormant by default), and quality closeout**. Wave 3 scope **T033–T040** is **complete**. Program closure recorded in [`spec10-final-closure.md`](./spec10-final-closure.md).

**Guardrails remain in force as immutable history:** bridge off by default; soft-archive retention only; no UI/notification/M4 wiring; R10 and AP-06 frozen.

---

## Wave 3 Intent Boundary

| Intent | Tasks |
| ------ | ----- |
| Retention settings reader | T033 |
| Archive expired audit logs job | T034 |
| Daily schedule registration | T035 |
| Retention feature test | T036 |
| Optional activity bridge (dormant by default) | T037 |
| Formal `config/audit.php` | T038 |
| PHPStan L8 closeout | T039 |
| Laravel Pint closeout | T040 |
| **Exit** | **CP-A5 PASS** after T040 |

**Migration phases:** M2 (optional bridge) + retention (UD-10-03) + program quality gates.

---

## Authorized Scope (upon activation)

### Task map

| Task | Authorized work |
| ---- | ---------------- |
| T033 | `AuditRetentionSettingsReader` — default retention **84** months |
| T034 | `ArchiveExpiredAuditLogsJob` — set `archived_at`; **no hard delete** |
| T035 | Register `audit:archive-expired` daily schedule in `routes/console.php` |
| T036 | `tests/Feature/Modules/Audit/AuditRetentionTest.php` |
| T037 | Optional `ActivityLogAuditBridge` — **disabled by default** |
| T038 | `config/audit.php` — `recording_enabled`, `sync_in_tests`, `activity_bridge_enabled`, `retention_months` |
| T039 | PHPStan L8 on `app/Modules/Audit/` |
| T040 | Pint on Audit module + audit tests |

### File allow-list

| Area | Permitted paths |
| ---- | ---------------- |
| Audit Application | `app/Modules/Audit/Application/` — retention reader only |
| Audit Infrastructure | `app/Modules/Audit/Infrastructure/Jobs/ArchiveExpiredAuditLogsJob.php` |
| | `app/Modules/Audit/Infrastructure/Listeners/ActivityLogAuditBridge.php` *(or equivalent)* |
| Audit module | `app/Modules/Audit/` — minimal changes for T033–T038 only |
| Config | `config/audit.php` *(new)* |
| Console | `routes/console.php` — schedule registration only |
| Tests | `tests/Feature/Modules/Audit/AuditRetentionTest.php` |
| | `tests/Feature/Modules/Audit/`, `tests/Architecture/AuditBoundaryTest.php` — T040 scope only |

**Prohibited paths:** Identity, Voucher, Request, Lottery, Allocation, CheckIn, Notification, Dormitory modules; spec07/08/09 closed-program logic; presentation UI; M4 producer wiring.

---

## Execution Constraints

**Do NOT (even when activated):**

- reopen or rework **T001–T032** except minimal blocking-defect fixes
- expand scope beyond **T033–T040**
- enable `activity_bridge_enabled` in production without explicit change record
- hard-delete rows from `audit_logs`
- introduce upstream Infrastructure imports in Audit module
- wire Request/Lottery/Allocation/CheckIn/Notification audit producers
- implement presentation UI (OA-10-05) or notification audit (R-08)
- modify closed programs (**spec07**, **spec08**, **spec09**) beyond read-only conformity

**Stop conditions**

| Condition | Action |
| --------- | ------ |
| Execution while `authorization-status` is `prepared` | **HALT** |
| Forward work outside **T033–T040** | **HALT** |
| Bridge enabled by default | **HALT** |
| Hard delete on audit content | **HALT** — AP-06 violation |
| Architectural deviation | **HALT** — ADR / change request required |

---

## Allowed Changes Policy

- Changes must remain **local to Audit module**, `config/audit.php`, `routes/console.php`, and audit tests
- Retention job may **UPDATE `archived_at` only** — not audit payload columns
- Bridge listener must be **feature-flagged off** by default
- T039–T040: minimal fixes only to satisfy static analysis / formatting within authorized paths

---

## Verification and Exit Conditions (CP-A5)

Wave 3 is **COMPLETE** only when:

| Gate | Criterion |
| ---- | --------- |
| **G-01** | T033–T040 marked complete per `tasks.md` |
| **G-02** | `AuditRetentionTest` PASS |
| **G-03** | Full `tests/Feature/Modules/Audit/` + `AuditBoundaryTest` PASS |
| **G-04** | PHPStan L8 zero errors on `app/Modules/Audit/` |
| **G-05** | Pint zero violations on authorized paths |
| **G-06** | Activity bridge **off** by default |
| **G-07** | No scope drift beyond T040 |
| **G-08** | **CP-A5** recorded PASS |

---

## Activation Record

**Activated:** 2026-07-02  
**Actor:** Governance Review / Activation  
**Basis:** [`spec10-wave3-governance-review.md`](./spec10-wave3-governance-review.md) — **PASS WITH CONDITIONS**

| Transition | From | To |
| ---------- | ---- | -- |
| `authorization-status` | `prepared` | `active` |
| `execution-state` | `NONE` | `AUTHORIZED` |
| `active-execution-scope` | `none` | `T033–T040` |

```text
Entry point: T033
Exit point: T040 + CP-A5 PASS
active-execution-scope: T033–T040
blocked-scope: T001–T032; T041+
```

Per `.specify/governance/execution-policy.md` § Pre-Execution Requirements: execution under spec10 Wave 3 requires this record to be **`active`**.

---

## Authority Transition

| Item | State |
| ---- | ----- |
| **Wave 1A** | **CLOSED** — T001–T021 |
| **Wave 1B** | **CLOSED** — T022–T027 |
| **Wave 2** | **CLOSED** — T028–T032 |
| **Wave 3 (this record)** | **CLOSED** (`revoked`) — T033–T040 |
| **spec10 program** | **FULLY CLOSED** — see [`spec10-final-closure.md`](./spec10-final-closure.md) |
| **T041+** | **NOT DEFINED** — HALT |
| spec07 / spec09 | **CLOSED** — no reopening |
| spec08 Voucher | **CLOSED** — Wave 2 adapter only |

---

## References

- [`spec10-wave3-governance-review.md`](./spec10-wave3-governance-review.md)
- [`spec10-wave3-governance-handoff.md`](./spec10-wave3-governance-handoff.md)
- [`spec10-wave2-implementation-closure.md`](./spec10-wave2-implementation-closure.md)
- [`spec10-implementation-authorization-wave2.md`](./spec10-implementation-authorization-wave2.md)
- `specs/010-audit-trail/tasks.md`
- `.specify/governance/execution-policy.md`

---

## Final Execution Directive

Wave 3 execution is **complete**. spec10 program closure is recorded in [`spec10-final-closure.md`](./spec10-final-closure.md).

```text
Program status: CLOSED
active-execution-scope: none
future-execution: DISABLED until new spec definition
HALT on T041+ without follow-up Implementation Authorization.
```

---

**End of authorization record. REVOKED — program closed.**
