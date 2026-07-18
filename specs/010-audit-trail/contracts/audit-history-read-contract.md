# Contract: Audit History Read

**Version:** 1.0.0  
**Spec:** spec10 Audit Trail  
**Direction:** Presentation / Reporting → Audit (read-only)  
**Status:** Planning — design baseline

---

## Purpose

Authorized read access to immutable audit history. Enforces **UD-10-06** permission gate.

---

## Interface

```text
AuditHistoryReadContract::query(AuditHistoryQuery $query): PaginatedAuditHistory
```

---

## AuditHistoryQuery

| Field | Type | Required | Description |
| ----- | ---- | -------- | ----------- |
| `entityType` | string | no | Filter by subject type |
| `entityId` | UUID | no | Filter by subject id |
| `actorType` | string | no | `user` \| `system` |
| `actorId` | string | no | Actor filter |
| `eventTypes` | string[] | no | One or more event types |
| `occurredFrom` | DateTimeImmutable | no | Inclusive UTC start |
| `occurredTo` | DateTimeImmutable | no | Inclusive UTC end |
| `includeArchived` | bool | no | Default `false` |
| `page` | int | no | Pagination |
| `perPage` | int | no | Default 50, max 200 |

At least one filter dimension required (entity, actor, or event+date range).

---

## AuditHistoryItem (projection)

| Field | Type |
| ----- | ---- |
| `auditLogId` | UUID |
| `correlationId` | string |
| `eventType` | string |
| `entityType` | string |
| `entityId` | UUID |
| `actorType` | string |
| `actorId` | string |
| `sourceContext` | string |
| `oldValues` | ?object |
| `newValues` | ?object |
| `metadata` | ?object |
| `occurredAt` | DateTimeImmutable |
| `createdAt` | DateTimeImmutable |

---

## Authorization (UD-10-06)

| Rule | Detail |
| ---- | ---- |
| **Permission** | `audit.read` (Spatie) |
| **Granted roles** | `Administrator`, `DormMgr`, `HRMgr` |
| **Denied** | `Employee`, `Operator`, `dormitory-manager`, `DormUnitMgr` (v1) |
| **Enforcement** | Contract implementation checks permission before query |
| **Failure** | `403` / `UnauthorizedAuditAccessException` — no partial leak |

---

## Reporting note (CD-017)

spec11 may consume this contract or a read-only projection port — **no** direct cross-module Eloquent on `audit_logs` from Reporting in v1.
