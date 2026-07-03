# Planning Document: Audit Trail & Traceability (spec10)

**Branch**: `010-audit-trail` | **Date**: 2026-07-02 | **Spec**: [spec.md](./spec.md)

**Input**: Audit cross-cutting capability — centralized **AuditService**, append-only `audit_logs`, authorized history queries, **R10** downstream boundary, **RecordsActivity** migration.

**Governance**: Planning translation only. **Not** Design Approval. **Not** Implementation Authorization. **No** `tasks.md`. spec07/spec08/spec09 remain **CLOSED**.

**Tasks**: [`tasks.md`](./tasks.md) — **40 tasks** (T001–T040); lifecycle **TASKED**; implementation **not authorized**.

**Nomination**: [`spec10-nomination-record.md`](../../.specify/docs/handoff/spec10-nomination-record.md) — `active`

---

## Summary

spec10 implements a **downstream audit persistence layer** in `app/Modules/Audit/`. Upstream bounded contexts emit **`AuditEntryDto`** via **`AuditRecordingContract`** after domain outcomes commit (**after-commit**). Audit validates, deduplicates by `correlationId`, persists immutable **`audit_logs`**, and exposes **`AuditHistoryReadContract`** behind **`audit.read`** permission.

**Resolved at planning:** UD-10-01 through UD-10-06.

**Deferred:** Livewire audit explorer UI (OA-10-05); optional `notification.delivered` audit events (R-08).

---

## Technical Context

| Field | Value |
| ----- | ----- |
| **Language** | PHP 8.4, Laravel 13 |
| **Architecture** | Modular monolith — Clean Architecture + DDD Lite |
| **Storage** | PostgreSQL 17 — `audit_logs` (UUID PK, append-only) |
| **Legacy** | Spatie `activity_log` retained — not target for new critical events post-cutover |
| **Testing** | Pest PHP — unit (domain), feature (record/query), architecture (R10) |
| **Localization** | Persian metadata allowed; UTC storage; Jalali at presentation |
| **Performance** | SC-003: entity history &lt; 10s for 10k rows in acceptance datasets |
| **Scale** | Enterprise dormitory — lottery burst; indexed entity/actor queries |

---

## Constitution Check

| Gate | Status | Notes |
| ---- | ------ | ----- |
| Modular monolith layers | PASS | Domain ← Application ← Infrastructure |
| UUID primary keys | PASS | `audit_logs.id` |
| No cross-module Eloquent | PASS | R10 — contracts only for upstream emission |
| Audit append-only (AP-06) | PASS | No update/delete APIs; core deliverable |
| AuditService facade | PASS | `AuditRecordingContract` |
| State transitions emit audit | PASS | Migration plan covers critical paths |
| PHPStan L8 / Pint | PASS | Required at implementation |
| Persian RTL presentation | PASS | Deferred UI |

**Post-design re-check:** PASS — no violations.

---

## 1. ARCHITECTURE_PLAN

### AuditService structure

| Component | Layer | Responsibility |
| --------- | ----- | -------------- |
| **AuditLog** (aggregate) | Domain | Immutable audit record semantics |
| **AuditEventType**, **ActorType** | Domain | Vocabulary enums |
| **CorrelationId**, **EntityReference**, **ActorReference** | Domain | Value objects |
| **AuditEntryDto** | Application | Inbound boundary DTO |
| **RecordAuditAction** | Application | Validate → hash → idempotent persist (after-commit) |
| **QueryAuditHistoryAction** | Application | Authorized paginated reads |
| **AuditRecordingContract** | Application | Inbound port (`AuditService` facade) |
| **AuditHistoryReadContract** | Application | Read port (presentation / reporting) |
| **AuditAuthorizationPort** | Application | Outbound — `audit.read` check via Identity |
| **AuditLogRepository** | Infrastructure | Append-only persistence |
| **ArchiveExpiredAuditLogsJob** | Infrastructure | UD-10-03 retention |
| **AuditServiceProvider** | Infrastructure | DI bindings |
| **AuditLogModel** | Infrastructure | Eloquent adapter — **no** public update/delete |

### Append-only persistence model

- Table **`audit_logs`** — no `updated_at`; no repository `update()`/`delete()`
- DB policy: application-only writes; migrations rollback drops table only in dev
- Optional DB trigger (implementation detail): reject UPDATE/DELETE on `audit_logs` in production migrations

### Entity / actor / event schema

See [data-model.md](./data-model.md). Minimum persisted fields per AP-06: `entity_type`, `entity_id`, `event_type`, `actor_type`, `actor_id`, `occurred_at`, `created_at`.

### R10 boundary enforcement strategy

| Rule | Enforcement |
| ---- | ----------- |
| No upstream Infrastructure imports in Audit | `tests/Architecture/AuditBoundaryTest.php` |
| Upstream emits DTO only | Contract tests with fake upstream |
| Audit does not read Request/Lottery/etc. repos | Static analysis + architecture test |
| Audit queries own store only | Repository scoped to `audit_logs` |
| Closed programs unchanged | Adapter wiring in **future** implementation waves only |

### Integration points

```
┌─────────────┐     AuditEntryDto           ┌──────────────────┐
│   Request   │ ───────────────────────────►│                  │
│   Lottery   │   AuditRecordingContract    │      Audit       │
│  Allocation │ ───────────────────────────►│     (spec10)     │
│   Voucher   │                             │                  │
│   CheckIn   │ ───────────────────────────►│   audit_logs     │
│  Identity   │                             │                  │
└─────────────┘                             └────────┬─────────┘
                                                   │ audit.read
                                                   ▼
                                         AuditHistoryReadContract
                                         (Presentation — deferred)
```

| Module | Integration mode | Wave |
| ------ | ---------------- | ---- |
| Identity | Role/permission change audit | M1+ |
| Request | State transition / approval audit | M1+ (when authorized) |
| Lottery | Execution / promotion audit | M1+ |
| Allocation / CheckIn | Assignment / operational audit | Adapter — minimal closed-program wiring |
| Voucher | Transition audit | Adapter — facts from existing transition records |
| Notification | Optional — **deferred** (R-08) | Post-MVP |

### Module structure (implementation target)

```text
app/Modules/Audit/
├── Domain/
│   ├── Models/AuditLog.php
│   ├── Enums/AuditEventType.php
│   ├── ValueObjects/CorrelationId.php
│   └── Exceptions/
├── Application/
│   ├── Contracts/AuditRecordingContract.php
│   ├── Contracts/AuditHistoryReadContract.php
│   ├── Contracts/AuditAuthorizationPort.php
│   ├── DTOs/AuditEntryDto.php
│   └── Services/RecordAuditAction.php
├── Infrastructure/
│   ├── Persistence/Models/AuditLogModel.php
│   ├── Repositories/AuditLogRepository.php
│   ├── Jobs/ArchiveExpiredAuditLogsJob.php
│   └── Providers/AuditServiceProvider.php
└── Presentation/   (deferred — OA-10-05)

tests/
├── Unit/Modules/Audit/
├── Feature/Modules/Audit/
└── Architecture/AuditBoundaryTest.php
```

---

## 2. MIGRATION_STRATEGY

### RecordsActivity → AuditService transition phases

| Phase | Name | Scope | Backward compatibility |
| ----- | ---- | ----- | ---------------------- |
| **M0** | Audit foundation | `audit_logs` schema, `AuditService`, core tests | `activity_log` unchanged; `BaseModel` still logs |
| **M1** | Explicit critical emission | Application Actions for state transitions call `record()` with domain event types | Dual path: Spatie + Audit for same transition **avoided** via correlation discipline |
| **M2** | Activity bridge (optional) | `ActivityLogAuditBridge` listener maps configured Spatie events → `AuditEntryDto` with `correlationId = activity:{id}` | Legacy model saves still produce queryable audit until M3 |
| **M3** | Narrow RecordsActivity | Override `getActivitylogOptions()` on `BaseModel` to `logOnly([])` or logName filter excluding critical models | Critical paths rely on AuditService only |
| **M4** | Upstream cutover waves | Per-module implementation authorization — wire ports in Request, Lottery, etc. | Closed programs: **adapter-only** minimal diffs |

### Backward compatibility

- Historical **`activity_log`** rows remain readable (Spatie APIs / DB)
- New critical events post-M0 **must** use `AuditRecordingContract`
- `BaseModel` trait **not removed** in spec10 Wave 1 — narrowed in M3
- Identity/Employee CRUD tests using `RecordsActivity` continue until M3

### Rollback safety

| Control | Purpose |
| ------- | ------- |
| `audit.recording_enabled` config | Disable new `audit_logs` writes; upstream falls back to pre-wire behavior |
| Append-only `audit_logs` | Rollback does not DELETE rows — no forensic loss |
| Feature-flagged bridge M2 | Disable listener without dropping table |
| Migration `down()` | Dev/test only — production forward-only |

---

## 3. EVENT_CONTRACT_MODEL

See [contracts/audit-entry-dto.md](./contracts/audit-entry-dto.md).

### Audit event structure

`AuditEntryDto` — correlation, event type, entity reference, actor reference, snapshots, metadata, `occurredAt`.

### Correlation identifiers

- Format: `{sourceContext}:{entityType}:{entityId}:{eventType}:{outcomeToken}`
- Global unique index on `correlation_id`

### Idempotency strategy (UD-10-05)

```
record(entry)
  → compute payload_hash
  → SELECT by correlation_id
      → not found → INSERT
      → found + same hash → return { status: duplicate }
      → found + different hash → throw AuditDuplicateConflictException
  → race unique violation → recover existing row
```

### Transaction boundary (UD-10-04)

| Environment | Rule |
| ----------- | ---- |
| **Production** | `DB::afterCommit(fn () => persist)` in `RecordAuditAction` |
| **Tests** | Config `audit.sync_in_tests=true` allows inline persist |
| **Queue supplement** | `RecordAuditJob` only for backfill — must not bypass after-commit for live transitions |

---

## 4. OPEN_QUESTIONS_RESOLUTION_MAP

| ID | Decision | Artifact |
| -- | -------- | -------- |
| **UD-10-01** | **RESOLVED** — `AuditEntryDto` + event vocabulary in [data-model.md](./data-model.md); inbound [audit-entry-dto.md](./contracts/audit-entry-dto.md) | contracts |
| **UD-10-02** | **RESOLVED** — Phased M0–M4 migration; optional M2 bridge; no BaseModel trait removal in Wave 1 | §2 Migration + [research.md](./research.md) R-05 |
| **UD-10-03** | **RESOLVED** — 84-month default; `archived_at` soft-archive; `audit.retention_months`; no hard delete v1 | [data-model.md](./data-model.md) § Retention |
| **UD-10-04** | **RESOLVED** — After-commit default; sync only in tests | [research.md](./research.md) R-02 |
| **UD-10-05** | **RESOLVED** — Idempotent accept on matching payload; conflict on hash mismatch | [contracts/audit-entry-dto.md](./contracts/audit-entry-dto.md) |
| **UD-10-06** | **RESOLVED** — Permission `audit.read` for `Administrator`, `DormMgr`, `HRMgr` | [contracts/audit-history-read-contract.md](./contracts/audit-history-read-contract.md) |

---

## Requirement Grouping (Planning Clusters)

| Cluster | Name | User stories | FRs |
| ------- | ---- | ------------ | --- |
| **PC-01** | Audit recording & validation | US1, US3, US4 | FR-001, FR-003–FR-008, FR-011, FR-012, FR-014 |
| **PC-02** | Idempotency & correlation | US3 | FR-008 (dedup semantics) |
| **PC-03** | Append-only persistence | US1, US2 | FR-002, FR-015 |
| **PC-04** | Authorized history query | US2 | FR-009, FR-010 |
| **PC-05** | AP-06 event coverage | US1 | FR-006, FR-012 |
| **PC-06** | Retention & archival | — | FR-015, UD-10-03 |
| **PC-07** | RecordsActivity migration | US3 | FR-013 |
| **PC-08** | R10 boundary enforcement | all | FR-007, architecture tests |

---

## 5. RISK_AND_CONSTRAINTS

| Risk | Mitigation |
| ---- | ---------- |
| **Duplicate audit rows** | Unique `correlation_id` + payload hash conflict detection |
| **Orphan audit on rollback** | After-commit persistence |
| **Cross-domain leakage** | `AuditBoundaryTest` |
| **Closed spec modification** | Adapter-only wiring under future authorization |
| **activity_log vs audit_logs confusion** | Documentation; M3 narrows Spatie scope |
| **Snapshot bloat** | 64 KiB cap + truncation metadata (R-07) |
| **Unauthorized audit access** | `audit.read` gate on read contract |

---

## 6. READINESS_FOR_TASKS

| Field | Value |
| ----- | ----- |
| **ready_for_tasks** | **yes** |
| **blockers** | **none** |
| **assumptions** | OA-10-01–OA-10-08; M4 upstream wiring requires separate implementation authorization per module; presentation UI deferred |

---

## 7. NEXT_STATE

| Field | Value |
| ----- | ----- |
| **lifecycle_stage** | **PLANNED** |
| **allowed_next_command** | `/speckit-tasks` |
| **governance_after_tasks** | Design Approval → Implementation Authorization |

---

## Project Structure

### Documentation (this feature)

```text
specs/010-audit-trail/
├── spec.md
├── plan.md              # This file
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── audit-entry-dto.md
│   ├── audit-recording-contract.md
│   ├── audit-history-read-contract.md
│   └── system-actor-tokens.md
├── checklists/requirements.md
└── tasks.md             # /speckit-tasks (not yet created)
```

---

## Dependencies

| Module | Relationship |
| ------ | ------------ |
| spec01 Foundation | Platform, migrations, `activity_log` |
| spec02 Identity | Actor UUID; `audit.read` permission seed |
| spec03–09 | Upstream audit entry producers (adapters when authorized) |
| spec11 Reporting | Optional read consumer (CD-017) |

---

## Governance Traceability

| Reference | Relevance |
| --------- | --------- |
| `context-map.md` R10 | Downstream consumer |
| `spec10-nomination-record.md` | Active nomination |
| `spec09-implementation-closure.md` | No carryover execution |
| Constitution AP-06 | Audit Everything |
| `program-alignment-spec07-spec11.md` | C-12 AuditService |

**Planning authority only.** Implementation requires separate authorization record.
