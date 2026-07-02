# Contract: Audit Entry DTO (UD-10-01)

**Version:** 1.0.0  
**Spec:** spec10 Audit Trail  
**Direction:** Upstream contexts → Audit (R10)  
**Status:** Planning — design baseline

---

## Purpose

Canonical inbound payload for cross-boundary audit recording. Upstream modules supply **fully materialized** transition facts after domain outcomes. Audit validates, normalizes, and persists — no upstream store reads.

---

## AuditEntryDto

| Field | Type | Required | Description |
| ----- | ---- | -------- | ----------- |
| `correlationId` | string | yes | Global idempotency key (max 191) |
| `eventType` | string | yes | Stable vocabulary — [data-model.md](../data-model.md) |
| `entityType` | string | yes | Subject type token |
| `entityId` | UUID string | yes | Subject identifier |
| `actorType` | `user` \| `system` | yes | Actor classification |
| `actorId` | string | yes | User UUID or system token |
| `sourceContext` | string | yes | e.g. `request`, `lottery`, `allocation`, `voucher`, `check_in`, `identity` |
| `oldValues` | object | no | JSON-serializable before snapshot |
| `newValues` | object | no | JSON-serializable after snapshot |
| `metadata` | object | no | Rejection reason, seed ref, job name, etc. |
| `occurredAt` | DateTimeImmutable | yes | UTC — when transition occurred upstream |

---

## Correlation rules (UD-10-05)

| Rule | Detail |
| ---- | ------ |
| **Uniqueness scope** | `correlationId` globally unique |
| **Generation** | Upstream owns format; recommend `{sourceContext}:{entityType}:{entityId}:{eventType}:{outcomeToken}` |
| **Replay — identical** | Same `correlationId` + same `payload_hash` → return existing `auditLogId` |
| **Replay — conflict** | Same `correlationId` + different `payload_hash` → reject `AuditDuplicateConflictException` |
| **Fan-out** | Distinct business outcomes → distinct `correlationId` values |

`payload_hash` = SHA-256 of canonical JSON (sorted keys) of all fields except `occurredAt` tolerance — computed by Audit module.

---

## Actor rules

| actorType | actorId |
| --------- | ------- |
| `user` | Identity user UUID from authenticated context |
| `system` | Token from [system-actor-tokens.md](./system-actor-tokens.md) |

**Prohibited:** null or empty `actorId` for critical operations.

---

## Example (request approved)

```json
{
  "correlationId": "request:550e8400-e29b-41d4-a716-446655440000:request.approved:deptmgr",
  "eventType": "request.approved",
  "entityType": "request",
  "entityId": "550e8400-e29b-41d4-a716-446655440000",
  "actorType": "user",
  "actorId": "6ba7b810-9dad-11d1-80b4-00c04fd430c8",
  "sourceContext": "request",
  "oldValues": { "status": "pending_hr" },
  "newValues": { "status": "pending_dorm" },
  "metadata": { "approvalStage": "hr", "comment": "تأیید منابع انسانی" },
  "occurredAt": "2026-07-02T10:30:00Z"
}
```

---

## Upstream supplier map

| Source context | Typical event types |
| -------------- | ------------------- |
| Request (spec05) | `request.submitted`, `request.state_changed`, `request.approved`, `request.rejected` |
| Lottery (spec06) | `lottery.program_created`, `lottery.executed`, `lottery.reserve_promoted` |
| Allocation (spec07) | `allocation.created`, `allocation.modified`, `allocation.cancelled` |
| CheckIn (spec07) | `check_in.recorded`, `check_out.recorded` |
| Voucher (spec08) | `voucher.issued`, `voucher.state_changed` |
| Identity (spec02) | `identity.role_changed`, `identity.permission_changed` |

*Upstream modules invoke `AuditRecordingContract::record()` — they do not write `audit_logs`.*
