# Research: Audit Trail & Traceability (spec10)

**Date**: 2026-07-02  
**Spec**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md)

---

## R-01 — Central store vs Spatie `activity_log` only

**Decision:** Introduce dedicated **`audit_logs`** table owned by Audit module; **AuditService** is the constitutional write path. Retain existing **`activity_log`** for interim/historical technical logs.

**Rationale:** Constitution AP-06 and spec01 data-model distinguish custom `audit_logs` from package `activity_log`. Domain-aware event vocabulary, correlation idempotency, and query authorization require a first-class schema — not morphic Spatie rows alone.

**Alternatives considered:**

| Alternative | Rejected because |
| ----------- | ---------------- |
| Extend Spatie only | Weak domain event vocabulary; harder immutability enforcement; cross-module query contract coupling |
| Replace `activity_log` immediately | Loses historical rows; high-risk big-bang migration |

---

## R-02 — Transaction boundary (UD-10-04)

**Decision:** **After-commit** persistence for all production audit writes.

**Rationale:** Prevents orphan audit records when domain transactions roll back (spec edge case). Laravel `DB::afterCommit()` wrapper in `RecordAuditAction` is the default path.

**Alternatives considered:**

| Alternative | Rejected because |
| ----------- | ---------------- |
| Same transaction | Audit survives domain rollback — forensic false positives |
| Async queue only | Adds latency to compliance path; acceptable as optional supplement for bulk backfill only |

**Exception:** Test harness may use synchronous in-transaction recording behind `audit.sync_in_tests` config.

---

## R-03 — Idempotency (UD-10-05)

**Decision:** **Idempotent accept** on duplicate `(correlation_id)`; reject if duplicate key with **conflicting payload hash**.

**Rationale:** Upstream retries and queue redelivery must not create twin rows. Conflicting replay indicates a bug and must surface loudly.

**Alternatives considered:**

| Alternative | Rejected because |
| ----------- | ---------------- |
| Always insert | Duplicate rows break compliance queries |
| Always reject duplicate | Legitimate retries would fail |

---

## R-04 — Retention (UD-10-03)

**Decision:** **84-month (7-year)** default retention; **soft-archive** via `archived_at`; **no hard delete** in v1. Setting key `audit.retention_months`.

**Rationale:** Compliance traceability exceeds operational notification retention (spec09 24-month). Aligns with enterprise dormitory audit expectations. Mirrors proven soft-archive pattern from spec09.

**Alternatives considered:**

| Alternative | Rejected because |
| ----------- | ---------------- |
| Indefinite only | Operational query performance degrades without archive tier |
| Hard delete after N months | Violates append-only spirit for compliance review |

---

## R-05 — RecordsActivity migration (UD-10-02)

**Decision:** **Phased migration** — (M0) Audit core → (M1) explicit `AuditService::record()` in critical Application Actions → (M2) optional activity bridge for legacy rows → (M3) narrow `RecordsActivity` to non-critical attributes on `BaseModel` → (M4) upstream module cutover waves per implementation authorization.

**Rationale:** `BaseModel` globally enables `RecordsActivity`; big-bang removal breaks existing Identity/Employee tests. Critical transitions (state machines, approvals, lottery execution) get explicit domain events first.

**Alternatives considered:**

| Alternative | Rejected because |
| ----------- | ---------------- |
| Immediate BaseModel trait removal | Breaks all modules; unauthorized scope on closed programs |
| Dual-write everywhere forever | Duplicate noise without correlation mapping |

---

## R-06 — Read authorization (UD-10-06)

**Decision:** Spatie permission **`audit.read`** required for all audit history queries. Granted to roles **`Administrator`**, **`DormMgr`**, **`HRMgr`** in Identity baseline. Employees and Operators denied in v1.

**Rationale:** Audit history is compliance-sensitive — not employee self-service. Matches OA-10-04 and FR-009.

**Alternatives considered:**

| Alternative | Rejected because |
| ----------- | ---------------- |
| Any authenticated user | SC-004 violation risk |
| Per-entity ownership filter in v1 | Complexity; defer department-scoped read to post-MVP |

---

## R-07 — Snapshot size policy

**Decision:** Max **64 KiB** per `old_values` / `new_values` JSON column; overflow sets `metadata.snapshot_truncated = true` and stores hash of full payload in metadata — never drop actor/entity/event fields.

**Rationale:** Spec edge case — prevent runaway lottery snapshots from failing inserts.

---

## R-08 — Notification delivery audit (OA-10-06)

**Decision:** **Optional v1** — notification delivery traceability remains in `notification_logs`; explicit `notification.delivered` audit event **deferred** to post-MVP unless implementation wave authorizes.

**Rationale:** Avoid duplicate traceability paths in Wave 1; notification log already durable.
