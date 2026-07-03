# Quickstart: Audit Trail & Traceability (spec10)

**Date**: 2026-07-02  
**Spec**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md)

Validation guide for post-implementation verification. **No code in this artifact** — references contracts and data model.

---

## Prerequisites

- Laravel Sail environment running
- Migrations applied including `database/migrations/modules/audit/`
- Identity roles seeded with `audit.read` on `Administrator`, `DormMgr`, `HRMgr`
- spec10 implementation authorized (not required to read this guide)

---

## Scenario 1 — Record critical audit entry (US1)

1. Build `AuditEntryDto` per [audit-entry-dto.md](./contracts/audit-entry-dto.md) (e.g. `request.approved`).
2. Invoke `AuditRecordingContract::record($dto)` as authenticated user.
3. **Expect:** `status: created`, UUID `auditLogId` returned.
4. Query DB: row in `audit_logs` with matching `correlation_id`, `entity_type`, `entity_id`, `actor_id`.
5. **Expect:** No upstream domain tables modified by Audit module.

---

## Scenario 2 — Idempotent replay (UD-10-05)

1. Record same DTO twice.
2. **Expect:** Second call returns `status: duplicate`, same `auditLogId`.
3. **Expect:** Single row in `audit_logs`.

---

## Scenario 3 — Conflict detection

1. Record entry with `correlationId` X and payload A.
2. Record different payload B with same `correlationId` X.
3. **Expect:** `AuditDuplicateConflictException`; still single row (payload A).

---

## Scenario 4 — Authorized history query (US2)

1. Authenticate as user with `audit.read`.
2. Call `AuditHistoryReadContract::query()` filtered by `entityType` + `entityId`.
3. **Expect:** Paginated results, `occurred_at DESC`.
4. Authenticate as `Employee` without permission.
5. **Expect:** Access denied; no row data leaked.

---

## Scenario 5 — Immutability (FR-002)

1. Attempt update/delete on `audit_logs` via application APIs or repository.
2. **Expect:** Operation unavailable or rejected.

---

## Scenario 6 — System actor (US4)

1. Record entry with `actorType: system`, `actorId: system:lottery_draw`.
2. Query by actor filter.
3. **Expect:** Entry distinguishable from human `actorType: user` entries.

---

## Scenario 7 — After-commit safety (UD-10-04)

1. In feature test, wrap domain operation in transaction that rolls back.
2. Emit audit via `RecordAuditAction` (production path).
3. **Expect:** No `audit_logs` row after rollback.

---

## Scenario 8 — R10 boundary

```bash
php artisan test tests/Architecture/AuditBoundaryTest.php
```

**Expect:** PASS — no imports from upstream module Infrastructure namespaces.

---

## Scenario 9 — Retention archival (UD-10-03)

1. Seed audit row with `occurred_at` older than `audit.retention_months`.
2. Run `ArchiveExpiredAuditLogsJob`.
3. **Expect:** `archived_at` set; row still present (no hard delete).
4. Default query (`includeArchived=false`) excludes archived row.

---

## Test commands (post-implementation)

```bash
php artisan test tests/Feature/Modules/Audit/
php artisan test tests/Architecture/AuditBoundaryTest.php
php vendor/bin/phpstan analyse app/Modules/Audit/
php vendor/bin/pint app/Modules/Audit/
```

---

## References

- [data-model.md](./data-model.md)
- [contracts/audit-recording-contract.md](./contracts/audit-recording-contract.md)
- [contracts/audit-history-read-contract.md](./contracts/audit-history-read-contract.md)
- [research.md](./research.md)
