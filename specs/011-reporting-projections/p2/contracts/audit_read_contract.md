# Audit Read Contract — Source Dependency Boundary (Planning)

**Task**: P-021  
**Spec**: spec11 — Reporting & Audit Consumption Evolution  
**Status**: **Planning-only** — no implementation authority  
**Owner**: spec10 Audit Application (frozen) — Reporting is **consumer only**  
**Baseline**: `architecture-clarification.md` §1, §3.6; `data-model.md` §3.1, §5.1; P2-C-07

---

## 1. PURPOSE

Define the **frozen upstream read dependency** between spec11 Reporting and spec10 Audit. This contract documents what Reporting may consume from **Tier 0 (T0)** via the frozen `AuditHistoryReadContract` without asserting authority over spec10 contract shape, query capabilities, or persistence.

---

## 2. FROZEN SOURCE CONTRACT

| Field | Value |
| ----- | ----- |
| **Contract** | `AuditHistoryReadContract` |
| **Module owner** | spec10 — Audit Application |
| **Lifecycle** | **CLOSED / FROZEN** — no extension authorized in P2 |
| **Query type** | `AuditHistoryQuery` (frozen filter set) |
| **Result item** | `AuditHistoryItem` (conceptual read DTO) |
| **Ingress to audit store** | `AuditRecordingContract` — **not** used by Reporting |

Reporting treats this contract as the **sole authorized audit read port** at T0. No alternate path to `audit_logs` is permitted.

---

## 3. WHAT REPORTING MAY CONSUME FROM T0

### 3.1 Allowed consumption patterns

| Pattern | Description | Reporting use |
| ------- | ----------- | ------------- |
| **Paginated query** | `query(AuditHistoryQuery)` with frozen filters | Entity timelines, line-item drill-down, export line items, projection refresh ingest |
| **Entity-scoped investigation** | Filters: `entityType`, `entityId`, optional `eventTypes`, `occurredFrom`, `occurredTo` | `EntityAuditTimelineReadModel` primary path |
| **Actor-scoped investigation** | Filters available on frozen query for actor dimensions | Security verification, T0 fallback |
| **Time-bounded raw reads** | `occurredFrom` / `occurredTo` on frozen query | Drill-down from T1 aggregates |
| **Archive visibility** | `includeArchived` per spec10 contract semantics | Delegated after Reporting visibility gate (DL-03) |
| **Pagination** | Inherits spec10 page size cap (max 200 per page) | All multi-page ingest and investigative reads |

### 3.2 Consumable item attributes (planning)

Reporting may read these conceptual fields from each `AuditHistoryItem` for T0 responses and T1 projection ingest:

| Attribute | Consumption |
| --------- | ----------- |
| `id` | Line-item identity; `sourceAuditLogId` in projections |
| `occurredAt` | Ordering, windowing, span calculation |
| `entityType`, `entityId` | Entity-centric and participant views |
| `actorType`, `actorId` | Actor-centric and security views |
| `eventType` | Aggregation dimensions |
| `sourceContext` | Producer/module dimension |
| `correlationId` | T1 correlation index ingest only — **not** a frozen query filter |
| Archive state | DL-03 visibility partitioning |
| Payload summary | Display context — opaque at projection layer |

### 3.3 Forbidden consumption patterns

| Pattern | Verdict |
| ------- | ------- |
| Direct `audit_logs` SQL or Eloquent access | **Forbidden** (R10, R11) |
| Import of Audit Infrastructure types | **Forbidden** |
| `correlationId` as `AuditHistoryQuery` filter | **Forbidden** — not in frozen contract (P2-C-07) |
| Mutation of audit rows via any Reporting path | **Forbidden** (AP-06) |
| Extension or wrapper that changes spec10 contract semantics | **Forbidden** in P2 |
| Bypass of `audit.read` permission gate | **Forbidden** |

---

## 4. AUTHORITATIVE-SOURCE RULES

| Rule ID | Rule |
| ------- | ---- |
| **AR-01** | `AuditHistoryReadContract` results are **authoritative** for audit line-item evidence |
| **AR-02** | T1 projection rows are **non-authoritative** relative to T0 |
| **AR-03** | On conflict between T1 and T0, **T0 wins** for disputes and evidentiary export |
| **AR-04** | Reporting must label non-T0 responses with provenance (`sourceTier`, `refreshedAt`, `projectionVersion`) |
| **AR-05** | Projection refresh reads T0 via paginated contract queries only — never via Infrastructure bypass |

---

## 5. LINE-ITEM EVIDENCE AND DISPUTE RESOLUTION

### 5.1 Evidentiary position

- **Compliance exports** and **security investigations** requiring immutable audit proof must resolve line items through T0 `AuditHistoryItem` results identified by `id` (audit log UUID).
- T1 aggregates, correlation indexes, and caches are **supporting material** — insufficient alone for evidentiary conclusions (`data-model.md` R-DM-12).

### 5.2 Dispute resolution

| Scenario | Resolution |
| -------- | ---------- |
| T1 aggregate disagrees with T0 recount | T0 paginated query is authoritative |
| Missing row in T1 correlation index | Re-fetch via T0 if `correlationId` known; accept projection lag as non-authoritative gap |
| Archive visibility mismatch | Re-query T0 with corrected `includeArchived` after role gate |
| Stale `refreshedAt` on T1 response | Disclose lag; offer T0 drill-down |

---

## 6. NON-AUTHORITY OVER SPEC10 CONTRACT SHAPE

This planning artifact explicitly confirms:

| Assertion | Status |
| --------- | ------ |
| Reporting does **not** own `AuditHistoryReadContract` | **Yes** |
| Reporting does **not** define new `AuditHistoryQuery` filters | **Yes** |
| Reporting does **not** request spec10 schema changes | **Yes** |
| Reporting does **not** alter spec10 closure or task state | **Yes** |
| This document is **not** a spec10 contract amendment | **Yes** |

Any future need for additional audit query dimensions requires a **separate governance change request** against frozen spec10 — outside P2 scope.

---

## 7. PERMISSION BASELINE

| Field | Planning disposition |
| ----- | -------------------- |
| **Permission** | Extend existing `audit.read` (P2-C-01) |
| **Roles** | `Administrator`, `DormMgr`, `HRMgr` |
| **Security reporting audience** | `Administrator` primary; `SecurityAuditor` deferred (P2-C-02) |
| **Reporting gate** | Visibility policy (`includeArchived`) enforced **before** T0 delegation |

---

## 8. NON-GOALS

This contract planning artifact does **NOT** define:

| Exclusion |
| --------- |
| PHP interface signatures or class names |
| HTTP/API transport |
| SQL or repository adapters |
| Projection refresh mechanics |
| Queue or job orchestration |
| spec10 implementation changes |
| Implementation Authorization |

---

**End of audit read contract planning artifact. Consumer boundary only. spec10 unchanged.**
