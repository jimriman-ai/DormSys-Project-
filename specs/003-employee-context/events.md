# Domain Events: Employee Context (spec03)

**Date**: 2026-06-26 | **Plan**: [plan.md](./plan.md)

Wave 1A event surface — synchronous Laravel events from Application actions.

---

## Published events

| Event | When | Payload (minimal) | Consumers |
| ----- | ---- | ----------------- | --------- |
| `EmployeeCreated` | After Employee persisted | `employeeId`, `identityId`, `occurredAt` | Audit (RecordsActivity); Notification later |

---

## Optional / deferred

| Event | Status | Notes |
| ----- | ------ | ----- |
| `EmployeeIdentityAssigned` | **Not required** Wave 1A | `identity_id` set at create in same transaction (OA-03-01) |
| `EmployeeDeactivated` | Optional tail | If `DeactivateEmployeeAction` ships in Wave 1A |
| `DependentAdded` | Deferred | Audit via activity log sufficient for Wave 1A |

---

## Not published by Employee

| Event | Reason |
| ----- | ------ |
| Identity lifecycle reactions | Identity owns `UserCreated` / `UserDeactivated` |
| `IdentityLinked` | CD-012 — linkage is Employee-owned field, not Identity event |

---

## Transport

**Wave 1A:** Synchronous dispatch from Application layer after successful persist (mirror spec02 R-07).

**Deferred:** Queue, outbox, async Notification listeners.

---

## Traceability

| Source | Reference |
| ------ | --------- |
| OA-03-01 | Synchronous identity_id at create |
| spec02 boundary | No `IdentityLinked` |
| FR-008 | Audit via RecordsActivity complementary to events |
