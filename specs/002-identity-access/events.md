# Identity Domain Events (spec02)

**Version:** 1.0.0  
**Scope:** User account lifecycle in Identity context only  
**Boundary:** See [`contracts/identity-employee-boundary.md`](./contracts/identity-employee-boundary.md) — no `IdentityLinked` event

---

## Principles

- Events describe **User** (auth account) lifecycle, not Employee linkage
- Linkage (`identity_id`) is assigned in **Employee** context (spec03)
- Event names are stable contracts for Audit, Notification, and future consumers

---

## UserCreated

**Emitted when:** A User record is successfully persisted in Identity module.

```json
{
  "event": "identity.user.created",
  "version": "1.0",
  "payload": {
    "user_id": "uuid-v7",
    "occurred_at": "2026-06-26T12:00:00Z"
  }
}
```

| Field | Type | Notes |
| ----- | ---- | ----- |
| `user_id` | string (UUID v7) | Same value Employee may later store as `identity_id` |
| `occurred_at` | ISO-8601 UTC | |

**Consumers (Wave 1A):** Audit (append-only log). Employee does **not** require this event to set `identity_id` if assignment is synchronous at create time.

---

## UserDeactivated

**Emitted when:** A User account is disabled in Identity module.

```json
{
  "event": "identity.user.deactivated",
  "version": "1.0",
  "payload": {
    "user_id": "uuid-v7",
    "occurred_at": "2026-06-26T12:00:00Z"
  }
}
```

**Consumers:** Audit. Employee-side reaction policy is **deferred** (CD-012 open item).

---

## Explicitly excluded

| Event | Reason |
| ----- | ------ |
| `IdentityLinked` | CD-012: `identity_id` assignment is Employee-owned |
| `EmployeeIdentityAssigned` | Owned by spec03 if needed |

---

## Implementation notes (Wave 1A — locked in plan.md §8)

- **Transport:** Synchronous Laravel events from Application Actions (R-07)
- **Persistence:** No dedicated event store; audit via `RecordsActivity` on `UserModel`
- **Async / queue:** Out of scope for Wave 1A
