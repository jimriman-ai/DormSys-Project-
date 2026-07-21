# Workflow Module

**Posture (HD-WF-01 Option B / CD-010-A1):** **ACTIVATED** for Request approval orchestration.

## WP-WF-02 — Domain + Application
Request Approval Workflow only (OD-1).

## WP-WF-03 — Persistence
Tables + Eloquent repository; soft UUID `request_id`.

## WP-WF-04 — Request cutover
Submit/Approve/Reject → Workflow actions; RequestApproval SoT via command port.

## WP-WF-05 — Notifications (delivered)
Integration listeners → `NotificationDeliveryContract` (Decision Lock A1/B3/C1/D2).  
Workflow domain/schema unchanged; Notification owns delivery.

**Not in Workflow module:** UI, WP-DORM-04, role fan-out for S2–S4 pending.

See: `docs/features/workflow/l3-design.md`, `docs/features/workflow/wp-wf-05-notification-audit.md`
