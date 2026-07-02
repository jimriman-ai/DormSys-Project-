# spec10 Wave 3 Governance Handoff

**Recorded:** 2026-07-02  
**Authority:** Product / Tech governance  
**Decision class:** Governance handoff (non-operational)

---

## Document classification

| Property | Value |
| -------- | ----- |
| **Handoff status** | **SUPERSEDED** — Wave 3 authorization **ACTIVE** |
| **Active authorization** | [`spec10-implementation-authorization-wave3.md`](./spec10-implementation-authorization-wave3.md) — `authorization-status: active` |

This document records **readiness context** for the next governance cycle. It does **not** authorize implementation of T033–T040.

---

## Current state (post-activation)

| Item | State |
| ---- | ----- |
| Wave 1A / 1B / 2 | **CLOSED** |
| Wave 3 governance review | **PASS WITH CONDITIONS** |
| Wave 3 authorization | **ACTIVE** — [`spec10-implementation-authorization-wave3.md`](./spec10-implementation-authorization-wave3.md) |
| Active execution scope | **T033–T040** |
| spec10 feature lifecycle | **AUTHORIZED** (feature level) |
| Implementation authorized (forward) | **YES** — T033–T040 only |

---

## Next candidate scope

| Field | Value |
| ----- | ----- |
| **Wave name** | **Wave 3 — Retention, Bridge & Quality Closeout** |
| **Task range** | **T033–T040** |
| **Phases** | Phase 6 (T033–T036) + Phase 7 (T037–T040) |
| **Exit checkpoint** | **CP-A5** — retention, bridge off-by-default, quality gates |
| **Governance review** | **PASS WITH CONDITIONS** — [`spec10-wave3-governance-review.md`](./spec10-wave3-governance-review.md) |
| **Prepared authorization** | [`spec10-implementation-authorization-wave3.md`](./spec10-implementation-authorization-wave3.md) — **`prepared` / NOT ACTIVE** |

### Task summary

| Task | Purpose | Cluster |
| ---- | ------- | ------- |
| T033 | `AuditRetentionSettingsReader` — default 84 months | PC-06 |
| T034 | `ArchiveExpiredAuditLogsJob` — soft-archive via `archived_at` | PC-06 |
| T035 | Schedule `audit:archive-expired` daily | PC-06 |
| T036 | `AuditRetentionTest` — archived rows excluded from default read | PC-06 |
| T037 | Optional `ActivityLogAuditBridge` — **disabled by default** | PC-07 |
| T038 | `config/audit.php` — flags + retention config | PC-07 |
| T039 | PHPStan L8 on Audit module | DoD |
| T040 | Laravel Pint on Audit module + tests | DoD |

---

## Dependency status

| Prerequisite | Status |
| ------------ | ------ |
| Wave 1A complete (T001–T021) | ✅ **SATISFIED** |
| Wave 1B complete (T022–T027) | ✅ **SATISFIED** |
| Wave 2 complete (T028–T032) | ✅ **SATISFIED** |
| CP-A1 / CP-A2 / CP-A3 / CP-A4 / CP-A4.1 | ✅ **SATISFIED** |
| `AuditRecordingContract` operational | ✅ **SATISFIED** |
| Identity + Voucher adapters operational | ✅ **SATISFIED** |
| `AuditBoundaryTest` operational | ✅ **SATISFIED** |
| Wave 2 closure recorded | ✅ **SATISFIED** |

---

## Readiness assessment

| Gate | Status |
| ---- | ------ |
| **Readiness for Wave 3 governance review** | **COMPLETE** |
| **Readiness for implementation** | **YES** — authorization **active** |
| **Blockers** | **none** |
| **Implementation permission (T033–T040)** | **YES** — activated 2026-07-02 |

---

## Mandatory conditions for next authorization record

A separate **`spec10-implementation-authorization-wave3.md`** (or equivalent) **must** embed:

### Scope containment

| Constraint | Requirement |
| ---------- | ----------- |
| **Authorized scope** | **T033–T040 only** |
| **HALT beyond T040** | Program closeout only; no M4 producer wiring without new authorization |
| **Bridge default** | `activity_bridge_enabled=false`; T037 inactive in production until explicitly enabled |
| **Retention** | Soft-archive only — **no hard delete** (AP-06) |
| **No UI** | OA-10-05 deferred |
| **No notification audit** | R-08 deferred |
| **No upstream adapter expansion** | Request/Lottery/Allocation/CheckIn wiring **prohibited** unless separately authorized |
| **R10 frozen** | Audit remains downstream-only |
| **AP-06 frozen** | Append-only invariant preserved |

### Closed-program protection

| Module | Policy |
| ------ | ------ |
| **spec07 / spec09** | **No modification** |
| **spec08 Voucher** | **No modification** beyond existing Wave 2 adapter (read-only conformity) |
| **Identity** | **No modification** beyond existing Wave 2 adapter |

### Verification requirements (exit gate CP-A5)

| Gate | Mandatory evidence |
| ---- | ------------------ |
| **CP-A5** | Retention job + `AuditRetentionTest` PASS |
| | Full `tests/Feature/Modules/Audit/` + `AuditBoundaryTest` PASS |
| | PHPStan L8 zero errors on `app/Modules/Audit/` |
| | Pint zero violations on Audit module + tests |
| | Activity bridge **off** by default |
| | `tasks.md` reconciliation at program closeout |

### Recommended governance split (advisory)

Prior Wave 2 handoff noted optional sub-waves for risk containment. Governance may authorize:

| Sub-wave | Tasks | Risk profile |
| -------- | ----- | ------------ |
| Wave 3A | T033–T036 | Retention — scheduled job, read-query impact |
| Wave 3B | T037–T038 | Bridge + config — feature-flag safety critical |
| Wave 3C | T039–T040 | Quality closeout — low risk |

Single Wave 3 authorization for T033–T040 is also valid if governance accepts combined scope.

---

## Explicitly excluded from Wave 3 handoff

| Scope | Status |
| ----- | ------ |
| Presentation UI (OA-10-05) | Deferred |
| Notification delivery audit (R-08) | Deferred |
| M4 producer wiring (Request/Lottery/Allocation/CheckIn) | Separate authorization |
| spec11 Reporting | Not authorized |
| Reopening Wave 1A/1B/2 tasks | Prohibited |

---

## Execution path (activated)

1. ~~Wave 2 closure recorded~~ ✅ **COMPLETE**
2. ~~Wave 3 governance review~~ ✅ **PASS WITH CONDITIONS**
3. ~~Activate Wave 3 authorization~~ ✅ **ACTIVE** — 2026-07-02
4. `active-execution-scope: T033–T040` ✅ **SET**
5. **HALT** at **T040 + CP-A5 PASS** unless superseding authorization issued

```text
Entry point: T033
Exit point: T040 + CP-A5 PASS
active-execution-scope: T033–T040
blocked-scope: T001–T032; T041+
next_stop_boundary: T040
Implementation permission: YES (Wave 3 only)
Allowed next action: /speckit-implement T033–T040
```

---

## References

- [`spec10-wave2-implementation-closure.md`](./spec10-wave2-implementation-closure.md)
- [`spec10-wave2-governance-review.md`](./spec10-wave2-governance-review.md)
- [`spec10-implementation-authorization-wave2.md`](./spec10-implementation-authorization-wave2.md)
- [`spec10-nomination-record.md`](./spec10-nomination-record.md)
- `specs/010-audit-trail/tasks.md` — T033–T040
- [`context-map.md`](../context-map.md) R10

---

**End of Wave 3 governance handoff record.**
