# Feature Specification: Notification Delivery (spec09)

**Feature Branch**: `009-notification-delivery`

**Created**: 2026-07-02

**Status**: **FULLY CLOSED** — Implementation Complete (T001–T032); active execution scope **none** ([`spec09-implementation-closure.md`](../../.specify/docs/handoff/spec09-implementation-closure.md))

**Catalog**: spec09 — Notification (`spec-catalog.md`)

**Depends on**: spec01 Foundation; spec02 Identity & Access (recipient identity and authorization)

**Consumes events from** (downstream only — **R9**): spec05 Request; spec06 Lottery; spec07 Allocation & CheckIn/CheckOut; spec08 Voucher — and other implemented contexts as they emit notification-worthy outcomes

**Input**: Establish the **Notification** cross-cutting capability: durable **in-app** notification delivery, inbox management, and read-state tracking for key DormSys lifecycle outcomes — without owning upstream domain policy or lifecycle decisions.

**Normative boundaries**: [`../../.specify/docs/context-map.md`](../../.specify/docs/context-map.md) **R9**; constitution §8.1 (in-app notifications in scope), §8.2 (email/SMS out of scope v1); discovery **BR-09.1**, **FR-09**.

**Governance**: Specification-definition only. **Not** Design Approval · **Not** Implementation Authorization. spec08 remains **CLOSED** ([`spec08-implementation-closure.md`](../../.specify/docs/handoff/spec08-implementation-closure.md)).

---

## Purpose

Employees, approvers, and operators need timely awareness of accommodation lifecycle events — request progress, approvals, allocations, lottery outcomes, vouchers, and operational reminders — without polling each domain module. Notification provides a **shared delivery and inbox layer** that records what was communicated, to whom, and whether it was read.

Upstream bounded contexts remain authoritative for **business outcomes**. They supply **notification intents** (who should be informed, about what, with what contextual references). Notification owns **delivery persistence**, **read/unread state**, **inbox presentation data**, and **idempotent handling** of duplicate intents — not request rules, allocation rules, or voucher policy.

This specification defines problem scope, users, constraints, functional requirements, and acceptance-oriented outcomes suitable for subsequent planning phases. It does **not** authorize implementation.

---

## Scope

| In scope | Out of scope (see § Out of Scope) |
| -------- | ----------------------------------- |
| **In-app** notification delivery (database-backed inbox) | Email, SMS, push, or third-party messaging channels |
| Notification log persistence and read/unread tracking | Audit log storage and compliance traceability (**spec10**) |
| Consumption of upstream **notification intents** via cross-boundary integration (**R9**) | Upstream domain lifecycle decisions and policy ownership |
| Recipient targeting from supplied intent (employee, approver role, operator) | Reporting projections and dashboards (**spec11**) |
| Deep links / entity references for navigation to source context | Workflow engine orchestration (**deferred**) |
| Idempotent delivery for duplicate upstream events | Native mobile applications |
| Notification type vocabulary aligned with **BR-09.1** triggers | Presentation UI implementation detail (deferred follow-on) |
| Persian (Farsi) user-facing message content requirement at delivery time | Translation infrastructure beyond message text supplied in intent |

**Program note:** Notification is a **downstream consumer** only. It must not query upstream operational stores directly or reinterpret domain state beyond the supplied intent.

---

## Governing Decisions

### R9 — Notification ← multiple contexts

| Field | Value |
| ----- | ----- |
| Direction | Downstream (consumes events / intents from many contexts) |
| Integration | Domain Event or Application Service contract — **direct upstream repository access forbidden** |
| Ownership | Notification owns **delivery record** and **inbox state**; upstream contexts own **when** a notification-worthy outcome occurs |
| Implementation | Deferred to spec09 (this specification) |

### Policy vs delivery boundary (spec-catalog provisional question)

| Layer | Owner | Responsibility |
| ----- | ----- | -------------- |
| **Domain outcome** | Source bounded context (Request, Lottery, Allocation, Voucher, CheckIn, …) | Decide that a lifecycle outcome occurred; emit notification intent with stable references |
| **Delivery** | Notification (spec09) | Persist notification, target recipient, track read state, expose inbox queries |
| **Presentation** | Deferred follow-on | Livewire inbox UI, badges, real-time refresh — not fixed at specification level |

**Recorded assumption (OA-09-01):** DormSys requires a **domain-aware delivery layer** (typed notifications, entity references, BR-09.1 vocabulary) but **not** a separate cross-domain notification **policy** engine. Policy remains in source contexts; Notification does not decide whether a request should be approved or an allocation should occur.

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Lifecycle Event In-App Alert (Priority: P1)

As an employee or approver, I need to receive an in-app notification when a relevant accommodation lifecycle event occurs so that I can act without manually checking each module.

**Why this priority**: BR-09.1 defines automatic triggers for core flows; without delivery, approvers and employees miss time-sensitive actions.

**Independent Test**: Supply a notification intent representing “request submitted for approval” and confirm a persisted in-app notification appears for the intended recipient with title, message, and unread state — without upstream module inbox logic.

**Acceptance Scenarios**:

1. **Given** a request submission outcome, **When** the Request context emits a notification intent for the next approver, **Then** an in-app notification is created for that approver with unread status
2. **Given** a request approval outcome, **When** an intent is emitted for the employee, **Then** the employee receives an in-app notification describing the approval
3. **Given** a request rejection outcome, **When** an intent includes a rejection reason, **Then** the employee notification message includes that reason
4. **Given** a successful allocation outcome, **When** an intent is emitted, **Then** the allocated employee receives an in-app notification
5. **Given** a lottery winner outcome (internal allocation or external voucher), **When** an intent is emitted, **Then** the winner employee receives an in-app notification with outcome summary references
6. **Given** reserve promotion, **When** an intent is emitted, **Then** the promoted reserve receives an immediate in-app notification

---

### User Story 2 - Notification Inbox and Read State (Priority: P1)

As an employee or approver, I need to view my notifications and mark them read so that I can distinguish new items from items I have already seen.

**Why this priority**: FR-09 requires read/unread tracking; inbox management is the core employee-facing value of Notification.

**Independent Test**: Create multiple notifications for one recipient, list inbox, mark one read, and confirm read state and timestamps update without affecting other recipients.

**Acceptance Scenarios**:

1. **Given** a recipient with notifications, **When** they view their inbox, **Then** notifications are listed with title, message preview, created time, and read/unread indicator
2. **Given** an unread notification, **When** the recipient marks it read, **Then** read state updates and read timestamp is recorded
3. **Given** a notification belonging to another employee, **When** a user attempts access, **Then** access is denied
4. **Given** an empty inbox, **When** listed, **Then** an empty result is returned without error

---

### User Story 3 - Deep Link to Source Context (Priority: P2)

As a notification recipient, I need to navigate from a notification to the related request, allocation, voucher, or other entity so that I can complete follow-up actions quickly.

**Why this priority**: FR-09 requires deep links to relevant entities; reduces friction after alert delivery.

**Independent Test**: Deliver notification with entity reference, resolve link target from notification record, and confirm reference identifies the correct source entity type and identifier — without Notification mutating upstream state.

**Acceptance Scenarios**:

1. **Given** a notification intent with entity type and identifier, **When** notification is persisted, **Then** the record includes a navigable reference to that entity
2. **Given** a notification with a link reference, **When** accessed read-only, **Then** Notification does not modify the referenced upstream entity
3. **Given** an intent with missing optional link data, **When** delivered, **Then** notification is still created with message content (link may be absent)

---

### User Story 4 - Idempotent Delivery (Priority: P2)

As the system, I need duplicate upstream notification intents to produce at most one inbox item per recipient so that retries and duplicate events do not spam users.

**Why this priority**: Upstream modules and queue workers may retry; Notification must be safe under at-least-once delivery.

**Independent Test**: Submit the same notification intent twice with the same correlation identifier and confirm a single inbox item exists for the recipient.

**Acceptance Scenarios**:

1. **Given** two identical intents with the same correlation identifier, **When** processed, **Then** at most one notification exists for that recipient and type
2. **Given** intents with different correlation identifiers, **When** they represent distinct business outcomes, **Then** separate notifications are created
3. **Given** duplicate processing after first delivery succeeded, **When** retried, **Then** no duplicate unread notifications appear

---

### User Story 5 - Check-In Reminder (Priority: P3)

As an employee with an upcoming internal dormitory check-in, I need a reminder notification one day before check-in so that I can prepare for arrival.

**Why this priority**: BR-09.1 explicitly requires check-in reminders for internal dormitories only; lower priority than core workflow notifications.

**Independent Test**: Supply a reminder intent for an internal allocation check-in date and confirm reminder notification is delivered to the employee — external voucher paths do not generate check-in reminders.

**Acceptance Scenarios**:

1. **Given** an internal dormitory allocation with check-in date tomorrow, **When** a reminder intent is supplied, **Then** the employee receives a reminder notification
2. **Given** an external voucher outcome, **When** reminder scheduling is evaluated upstream, **Then** no check-in reminder intent is emitted (internal-only rule)
3. **Given** a reminder already delivered for a correlation identifier, **When** duplicate intent arrives, **Then** idempotency prevents duplicate reminders

---

### Edge Cases

- Upstream intent references employee that no longer exists or is inactive? Reject or skip delivery with recorded non-delivery outcome; do not create orphan inbox entries without valid recipient
- Message text exceeds display limits? Truncate preview safely; full message retained in stored record
- Approver role has multiple eligible users? Intent must identify specific recipient(s) or role resolution contract — see **UD-09**
- Notification intent arrives before referenced entity is visible to recipient? Delivery still permitted; link may resolve when entity becomes available
- High volume burst (e.g., lottery results)? All intents processed; idempotency and per-recipient ordering preserved
- User deletes or archives notification? Soft-archive or retain per retention policy — exact UX deferred; records must not be silently purged without policy
- Cross-module direct query attempted from Notification? Forbidden — intents only (**R9**)

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST persist **in-app notifications** for authorized recipients with title, message body, type, and created timestamp
- **FR-002**: System MUST support **read/unread** state per notification with read timestamp when marked read
- **FR-003**: System MUST allow recipients to **list their own notifications** and filter by read state
- **FR-004**: System MUST accept notification delivery requests only through **cross-boundary integration** — Notification MUST NOT read upstream operational stores directly (**R9**)
- **FR-005**: System MUST record **notification type** using a stable vocabulary aligned with lifecycle events (request, approval, allocation, lottery, voucher, reserve promotion, check-in reminder)
- **FR-006**: System MUST associate each notification with a **recipient employee reference** (immutable identifier)
- **FR-007**: System MUST support optional **entity reference** (type + identifier) and **deep link** target for navigation (FR-09)
- **FR-008**: System MUST enforce **idempotent delivery** when the same correlation identifier is replayed for the same recipient and notification type
- **FR-009**: System MUST deliver notifications for **BR-09.1** automatic trigger categories when upstream contexts emit corresponding intents
- **FR-010**: System MUST **reject or skip** delivery when recipient reference is invalid or recipient is not eligible to receive the notification
- **FR-011**: System MUST ensure notification delivery does **not mutate** upstream domain lifecycle state
- **FR-012**: System MUST support **immediate delivery** semantics for time-sensitive intents (e.g., reserve promotion)
- **FR-013**: System MUST retain notification records for historical inbox view — notifications are not silently deleted
- **FR-014**: System MUST scope all inbox queries and read-state mutations to the **authenticated recipient** (or authorized operator acting on behalf of policy-defined roles)
- **FR-015**: System MUST accept message content in **Persian (Farsi)** as provided by upstream intent for user-facing text

### Key Entities

- **Notification** — in-app alert record; recipient employee reference; type; title; message; read state; optional entity reference and link; correlation identifier for idempotency; created and read timestamps
- **NotificationIntent** — inbound cross-boundary payload from upstream context: recipient(s), type, message content, entity reference, correlation identifier, optional priority or delivery hint
- **NotificationType** — stable vocabulary entry mapping lifecycle event category to inbox presentation (e.g., request submitted, approved, rejected, allocated, lottery winner, voucher issued, reserve promoted, check-in reminder)

### Notification Types (specification vocabulary — BR-09.1 alignment)

| Type (conceptual) | Typical recipient | Upstream source (facts only) |
| ----------------- | ----------------- | ---------------------------- |
| Request submitted | Next approver | Request |
| Request approved | Employee | Request |
| Request rejected | Employee | Request |
| Allocation successful | Employee | Allocation |
| Lottery winner | Employee | Lottery / Voucher (outcome facts) |
| Reserve promoted | Employee | Lottery / Voucher |
| Check-in reminder | Employee | CheckIn / Allocation (internal only) |

*Exact type identifiers and intent payload shape are deferred to planning (**UD-09**).*

---

## Success Criteria *(mandatory)*

- **SC-001**: 95% of notification intents are visible in the recipient inbox within 5 seconds under normal operating conditions
- **SC-002**: 100% of duplicate intents sharing the same correlation identifier produce at most one notification per recipient
- **SC-003**: 100% of inbox list and read-state operations return only notifications belonging to the authenticated recipient
- **SC-004**: Recipients can identify unread vs read status for every notification in their inbox without external tools
- **SC-005**: 100% of delivered notifications include sufficient entity reference or link data to navigate to the related item when upstream intent supplied link data
- **SC-006**: Reserve promotion and other time-sensitive intents are delivered on first processing attempt without batching delay under normal conditions

---

## Assumptions

- **OA-09-01**: Notification owns **delivery and inbox state** only; upstream contexts own **business policy** and emit intents after outcomes are committed
- **OA-09-02**: MVP channel is **in-app (database-backed)** only per constitution §8.2 — email and SMS are explicitly out of scope for v1
- **OA-09-03**: Recipient identity resolves through **Employee** context; authentication and authorization through **Identity** (spec02)
- **OA-09-04**: Message text (title/body) is composed upstream or in a shared presentation mapper outside Notification domain policy — Notification stores delivered text as received in intent
- **OA-09-05**: Livewire inbox UI, badge counts, and real-time polling/websocket refresh are **deferred** presentation follow-on
- **OA-09-06**: Check-in reminder **scheduling** (when to emit intent) is owned by operational contexts (CheckIn/Allocation); Notification only delivers reminder intents
- **OA-09-07**: Lottery winner notifications for **external** paths include voucher outcome references supplied by Voucher intents; Lottery does not format voucher policy text

### Open Planning Items

| ID | Item | Status |
| -- | ---- | ------ |
| **UD-09** | Cross-boundary **notification intent** payload contract | **Resolved** — [plan.md](./plan.md) §4; [contracts/notification-intent-dto.md](./contracts/notification-intent-dto.md) |
| **UD-10** | Check-in reminder scheduling trigger ownership and timing | **Resolved** — CheckIn scheduler; [contracts/check-in-reminder-scheduler-port.md](./contracts/check-in-reminder-scheduler-port.md) |
| **UD-11** | Notification retention and archival policy | **Resolved** — 24-month soft-archive; [data-model.md](./data-model.md) |

---

## Dependencies

| Dependency | Relationship | Notes |
| ---------- | ------------ | ----- |
| **spec01** Foundation | Required | Platform conventions, queue infrastructure |
| **spec02** Identity & Access | Required | Authentication; recipient authorization |
| **spec03** Employee | Required | Recipient employee references |
| **spec05** Request | Upstream supplier | Request lifecycle notification intents |
| **spec06** Lottery | Upstream supplier | Draw/winner/reserve intents |
| **spec07** Allocation / CheckIn | Upstream supplier | Allocation and check-in reminder intents (**closed** — facts only) |
| **spec08** Voucher | Upstream supplier | Voucher lifecycle transition intents (**closed** — facts only) |
| **spec10** Audit | Adjacent | Audit records critical operations separately; notification delivery may be auditable but audit storage is spec10 |
| **spec11** Reporting | Downstream optional | May project notification metrics read-only (CD-017) |

---

## Out of Scope (spec09)

- Email, SMS, push, or external messaging providers (constitution §8.2)
- Audit log storage and compliance traceability (**Audit** — spec10)
- Reporting dashboards and cross-domain projections (**Reporting** — spec11)
- Upstream domain lifecycle rules (Request approval, Allocation assignment, Voucher eligibility, Lottery scoring)
- Workflow engine orchestration (**deferred**)
- Direct repository access to Request, Lottery, Allocation, Voucher, or other upstream stores (**R9** violation)
- Employee/operator presentation UI implementation (deferred follow-on)
- Native mobile applications
- User-configurable notification preferences / opt-out catalog (deferred — all BR-09.1 triggers delivered by default in v1)
- Reopening or modifying closed programs (spec07, spec08)

---

## Governance Traceability

| Reference | Relevance |
| --------- | --------- |
| `spec-catalog.md` spec09 | Notification — Planned; cross-cutting delivery capability |
| `context-map.md` R9 | Downstream consumer; event/intent integration |
| `handoff/spec08-implementation-closure.md` | spec08 closed; voucher transition intents available as upstream facts |
| Constitution §8.1, §8.2 | In-app in scope; email/SMS out of scope v1 |
| Discovery BR-09.1, FR-09 | Automatic triggers; read/unread; deep links |
| `dormsys-architecture.md` §4-7 | In-app MVP; SendNotificationJob pattern (planning reference only) |

**Planning authority:** Specification-definition only. Design Approval, architecture freeze, `plan.md`, `tasks.md`, and Implementation Authorization require separate governance records.
