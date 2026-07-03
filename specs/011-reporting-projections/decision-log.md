# spec11 Decision Log

**Status**: **DL-01–DL-03 RESOLVED** at architecture clarification (2026-07-02)  
**Predecessor**: spec10 FROZEN — [`spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md)  
**Clarification artifact**: [`architecture-clarification.md`](./architecture-clarification.md)

---

## Purpose

Record architectural decisions for spec11 evolution planning. **Resolved decisions guide P2 technical planning only — they do not authorize implementation.**

---

## DL-01 — Projection storage strategy

| Field | Value |
| ----- | ----- |
| **Status** | **RESOLVED** |
| **Track** | E-02 |
| **Question** | Where do audit reporting projections live? |
| **Decision** | **Option C — Hybrid** |
| **Recorded** | 2026-07-02 |

### Resolution

| Tier | Mechanism | Owner |
| ---- | --------- | ----- |
| **T0** | Direct `AuditHistoryReadContract` | Audit Application (frozen) |
| **T1** | Reporting-owned materialized projections | Reporting Infrastructure |
| **T2** | Ephemeral request-time assembly | Reporting Application |

Investigative entity/actor drill-down uses **T0**. Correlation indexing, time-window aggregates, and dashboard cards use **T1**. Projections are derived — never authoritative over `audit_logs`.

### Rejected alternatives

| Option | Reason |
| ------ | ------ |
| A only | Insufficient for correlation and aggregate scale |
| B only | Does not address read-heavy analytics and correlation gap |

---

## DL-02 — Explorer vs API-first consumption

| Field | Value |
| ----- | ----- |
| **Status** | **RESOLVED** |
| **Track** | E-03 |
| **Question** | What is the first consumer surface after spec11 authorization? |
| **Decision** | **Layered — B then A** |
| **Recorded** | 2026-07-02 |

### Resolution

1. **First wave**: Reporting read API / export façade (Application read ports over T0/T1).
2. **Subsequent wave**: Operator Audit Explorer UI in Presentation — consumes Reporting ports only; not inside Audit module.

### Rationale

- Preserves OA-10-05 deferred UI boundary from spec10.
- Reduces risk of embedding query logic in Presentation before read ports stabilize.
- Explorer and compliance dashboards share the same Reporting abstraction.

### Rejected alternatives

| Option | Reason |
| ------ | ------ |
| A first (UI) | Couples UX to unstable query layer; violates layering discipline |
| C first (compliance dashboard) | Depends on T1 projections not yet defined at first wave |

---

## DL-03 — Archived row visibility in reporting

| Field | Value |
| ----- | ----- |
| **Status** | **RESOLVED** |
| **Track** | E-01, E-05 |
| **Question** | Should reporting default exclude soft-archived audit rows or include for compliance? |
| **Decision** | **Option B — Role-gated `includeArchived`** |
| **Recorded** | 2026-07-02 |

### Resolution

| Context | `includeArchived` default | Override |
| ------- | ------------------------- | -------- |
| Operational reporting | `false` | None |
| Security investigation | `false` | `Administrator` (and future governance roles) |
| Compliance reporting / export | `false` | Compliance-authorized roles may set `true` |

Reporting visibility policy enforces gate **before** T0/T1 delegation. No retention policy change. Option C (separate archive-tier projection) deferred — may be revisited at P2 if performance requires partition without policy change.

### Rejected alternatives

| Option | Reason |
| ------ | ------ |
| A only | Insufficient for long-horizon compliance evidence |
| C at clarification | Premature — partition strategy belongs in P2 `data-model.md` |

---

## Open items (not blocking clarification)

| ID | Question | Status |
| -- | -------- | ------ |
| UD-11-01 | Separate `reporting.read` permission vs extend `audit.read` | OPEN — default extend `audit.read` |
| UD-11-02 | Introduce `SecurityAuditor` role | OPEN — governance at authorization |
| P-013 | Compliance KPI stakeholder interviews | OPEN |

---

**End of decision log. Planning decisions only — no execution authorized.**
