# WP-WF-05 — Notification Integration

**Status:** **IMPLEMENTED** under Decision Lock (1405/04/30 \| 2026-07-21)  
**Lock:** STOP-A=A1 · STOP-B=B3 · STOP-C=C1 · STOP-D=D2  

## Decision Lock (recorded)

| ID | Selection | Effect |
|----|-----------|--------|
| A1 | Add `request_approval_pending` | `NotificationType::RequestApprovalPending` |
| B3 | Dual source + dedup | Request + Workflow terminal events; same correlation keys |
| C1 | Stage-1 concrete only | Pending/submitted next-approver via Identity→Employee; no role fan-out |
| D2 | Spec09 submitted | `request_submitted` → Stage-1 next approver (not requester) |

## Implementation

| Piece | Path |
|-------|------|
| Type | `NotificationType::RequestApprovalPending` |
| Correlation | `app/Integrations/Notification/RequestApprovalNotificationCorrelation.php` |
| Delivery | `RequestApprovalNotificationDelivery` → `NotificationDeliveryContract` |
| Subscriber | `RequestApprovalNotificationSubscriber` (IntegrationServiceProvider) |

### Correlation keys (B3)

- `request:{id}:submitted`
- `request:{id}:pending:department_manager`
- `request:{id}:approved`
- `request:{id}:rejected`

### Listeners

| Event | Intent |
|-------|--------|
| `RequestSubmitted` | `request_submitted` → Stage-1 employee |
| `WorkflowStepActivated` (stage1 only) | `request_approval_pending` → Stage-1 employee |
| `RequestApproved` / `WorkflowInstanceCompleted` | `request_approved` → requester (deduped) |
| `RequestRejected` / `WorkflowInstanceRejected` | `request_rejected` → requester (deduped) |

## Ownership preserved

- Notification owns persistence/delivery
- RequestApproval remains canonical product history
- Workflow step log remains orchestration audit
- No Workflow domain / schema / migration changes

## Tests

`tests/Feature/Modules/Notification/RequestApprovalWorkflowNotificationTest.php`
