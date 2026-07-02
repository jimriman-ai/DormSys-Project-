# Contract: Audit Recording (AuditService facade)

**Version:** 1.0.0  
**Spec:** spec10 Audit Trail  
**Direction:** Inbound port — all upstream modules → Audit  
**Status:** Planning — design baseline

---

## Interface

```text
AuditRecordingContract::record(AuditEntryDto $entry): AuditRecordResult
```

| Method | Responsibility |
| ------ | -------------- |
| `record` | Validate DTO → compute payload hash → idempotent persist → return result |

---

## AuditRecordResult

| Field | Type | Description |
| ----- | ---- | ----------- |
| `auditLogId` | UUID | Persisted row identifier |
| `status` | `created` \| `duplicate` | Whether new row or idempotent replay |
| `recordedAt` | DateTimeImmutable | Persistence timestamp (UTC) |

---

## Behavioral contract

| Rule | Detail |
| ---- | ------ |
| **Write path** | Sole supported production write to `audit_logs` |
| **Validation** | Reject missing required fields (FR-014) |
| **Transaction** | Default **after-commit** dispatch (UD-10-04) |
| **Immutability** | No update/delete APIs exposed |
| **Side effects** | Must not mutate upstream domain state (FR-011) |
| **R10** | Audit module must not import upstream Infrastructure to enrich entry |

---

## Registration

| Binding | Implementation |
| ------- | -------------- |
| `AuditRecordingContract` | `RecordAuditAction` (Application) |
| Facade alias (optional) | `AuditService` — constitution AP-06 name |

**Provider:** `AuditServiceProvider` registers contract in container.

---

## Synchronous vs deferred

| Mode | When |
| ---- | ---- |
| **Synchronous after-commit** | Default for critical inline transitions |
| **Queued supplement** | Optional `RecordAuditJob` for bulk backfill only — not default for state transitions |

---

## Error taxonomy

| Exception | When |
| --------- | ---- |
| `InvalidAuditEntryException` | Validation failure |
| `AuditDuplicateConflictException` | Duplicate correlationId with conflicting payload |
| `AuditSnapshotTooLargeException` | Snapshot exceeds 64 KiB without truncation flag |
