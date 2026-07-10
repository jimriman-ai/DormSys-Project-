# Notification Inbox Unread Badge — Review Decision

## Feature

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-unread-badge` |
| **Feature title** | P8 — Notification Inbox Unread Badge |
| **Domain area** | notifications / presentation |
| **Classification** | `UI_PRESENTATION_GAP` / `UI_ONLY_GAP` |
| **Decision date** | 2026-07-10 |

## Review objective

Determine whether P8 may proceed to **feature-contract** stage, confirm approved scope and boundaries, resolve open governance questions from feature-analysis, and authorize the exact next artifact. This review does **not** authorize implementation, draft a contract or lock, or modify application code.

---

## 1. Decision Summary

| Field | Decision |
|---|---|
| **Feature** | `notification-inbox-unread-badge` (P8) |
| **Decision** | **`APPROVED_FOR_FEATURE_CONTRACT`** |
| **Implementation authorized?** | **No** — feature contract is the next authorized step only |
| **Blockers** | **None** |
| **Next governance gate** | Feature contract drafting |

P8 is approved as a **standalone presentation-only successor feature** that supersedes only prior **badge / `countUnread` display deferrals and exclusions** from P2 and P7. The approved v1 change is an aggregate unread count badge beside the existing **اعلان‌ها** layout nav item, consuming existing `NotificationInboxReadContract::countUnread()` through an approved application-facing presentation boundary.

---

## 2. Inputs Reviewed

| Artifact | Role |
|---|---|
| `docs/ui/analysis/notifications/notification-inbox-unread-badge.repo-inspection.md` | Repository truth |
| `docs/ui/analysis/notifications/notification-inbox-unread-badge.feature-analysis.md` | Primary decision basis |
| `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` | P2 deferred badge/count exclusion |
| `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` | P2 deferred badge/count |
| `docs/ui/contracts/notifications/notification-mark-read-mutation.feature-contract.yaml` | P5 exclusions |
| `docs/ui/locks/notifications/notification-mark-read-mutation.implementation-lock.yaml` | P5 inbox `countUnread` prohibition |
| `docs/ui/contracts/notifications/notification-inbox-deep-link-navigation.feature-contract.yaml` | P6 badge exclusion |
| `docs/ui/contracts/notifications/notification-inbox-layout-navigation.feature-contract.yaml` | P7 AC-LN-005 badge exclusion |
| `docs/ui/closeouts/notifications/notification-inbox-layout-navigation.closeout.md` | P7 closed baseline |
| `docs/ui/review/notifications/notification-inbox-layout-navigation.review-decision.md` | P7 successor-overlay precedent |

---

## 3. Feature Assessment

### Validity

**Valid standalone successor feature.** Repository evidence confirms backend unread counting exists (`NotificationInboxReadContract::countUnread`) and no aggregate unread indicator is rendered in presentation. P2–P7 governance explicitly deferred badge/`countUnread` display as a separate cycle.

### User value

**Clear.** After P7 layout navigation, authenticated users can reach the inbox from any layout-wrapped page but cannot see **how many unread notifications** they have without opening the inbox and scanning per-row status. P8 addresses **aggregate unread visibility on shared chrome**, not inbox access, listing, mark-read, or deep-link behavior.

### Overlap assessment

| Predecessor | Overlap? | Assessment |
|---|---|---|
| **P2** read-only inbox | **No functional overlap** | P2 delivered list baseline; deferred badge only |
| **P5** mark-read | **No overlap** | P5 mutates per-row read state; P8 displays aggregate count |
| **P6** deep-link | **No overlap** | P6 row navigation to `requests.show` only |
| **P7** layout nav | **Adjacent, not duplicate** | P7 delivered nav link; P8 adds count affordance on same nav item without changing link destination or P7 nav semantics |

P8 does **not** reopen or amend closed P2/P5/P6/P7 deliveries except through explicit **successor supersession of badge/count exclusions**.

---

## 4. Scope Decision

### Approved scope (for contract drafting)

| Dimension | Approved boundary |
|---|---|
| **Feature type** | Presentation-only successor overlay |
| **Primary surface** | `resources/views/components/layouts/app.blade.php` header `<nav>` — badge beside existing **اعلان‌ها** nav item only |
| **Affordance** | Numeric unread count badge when count > 0 (see visibility decisions) |
| **Data source** | `NotificationInboxReadContract::countUnread($recipientEmployeeId)` via approved presentation boundary — **not** direct repository/DB access |
| **Recipient scoping** | `NotificationPrincipalEmployeeResolver` pattern for employee id resolution |
| **Nav preservation** | **اعلان‌ها** label, `notifications.index` destination, placement after **درخواست‌ها**, active-state logic, plain `href` transport — **unchanged from P7** |
| **Refresh (v1)** | Server-rendered on full page load only; stale count until next navigation acceptable |
| **Backend** | No changes to Application contract semantics, repository queries, routes, middleware, or mark-read behavior |
| **Inbox page** | No changes to `NotificationInboxPage` or inbox Blade (layout-only path) |
| **Successor supersession** | Supersedes P2 deferred badge/count and P7 AC-LN-005 badge exclusion **only** |

### Excluded scope (rejected unless future feature authorizes)

| Item | Disposition |
|---|---|
| Mark-all-as-read | **Rejected** |
| Real-time updates, polling, `wire:poll`, event-driven nav refresh | **Rejected for v1** — deferred |
| Notification filtering, sort, pagination | **Rejected** |
| Inbox list/mark-read/deep-link redesign or behavior change | **Rejected** |
| Badge on inbox page header (second surface) | **Rejected for v1** |
| Request-module surfaces | **Rejected** |
| New routes, API endpoints, middleware, Policy/Gate | **Rejected** |
| Backend/`countUnread` query or contract changes | **Rejected** |
| `NotificationInboxPage` `countUnread` consumption | **Rejected for v1** — preserves P5 architecture guard |
| Reopening P2/P5/P6/P7 closeouts or amending closed delivery scope | **Rejected** |

**Scope modification note:** Feature-analysis Option A (layout nav only) is **approved as stated**. Option B (inbox header only) and Option C (both surfaces) are **rejected for P8 v1**.

---

## 5. Architecture Assessment

### Ownership

| Layer | P8 authority |
|---|---|
| Unread count semantics | **Notification Application/Infrastructure** — unchanged |
| Count resolution | **Notification module** via `NotificationInboxReadContract` |
| Layout rendering | **Presentation** — displays supplied integer only |
| Request module | **No role** in count resolution |

### Dependency direction (required)

```
app.blade.php (render badge integer only)
  ← Notification-owned presentation adapter (contract must name allowed mechanism)
    ← NotificationInboxReadContract::countUnread($employeeId)
      ← NotificationPrincipalEmployeeResolver (employee scoping)
```

### Anti-leak constraints (mandatory for contract)

- Layout Blade must **not** query `notification_logs`, call repositories, or infer unread count from list rows.
- Layout must **not** define what "unread" means — only render backend-supplied count.
- No `countUnread` string or direct contract invocation inside `NotificationInboxPage.php` (P5 guard preserved).
- Presentation consumption boundary (view composer, dedicated presentation service, or equivalent) must be **named and constrained in contract** — mechanism is a **contract-stage** pin, not implementation authorization here.

### Contract-stage architecture pin (authorized direction)

Contract **must** define:

- Allowed files/surfaces for count resolution and layout render
- Forbidden direct Infrastructure/Domain imports in layout Blade
- Single delegation path to `NotificationInboxReadContract`
- Optional presentation payload shape (e.g., `unread_count: int`, `show_badge: bool`) — contract may freeze

**No architecture blocker** prevents contract stage.

---

## 6. Governance Impact

| Predecessor | Closed delivery | P8 impact |
|---|---|---|
| **P2** read-only inbox | Inbox list baseline | **Successor supersession** of deferred `countUnread`/badge exclusion only; inbox list behavior unchanged |
| **P5** mark-read | Per-row mark-read on inbox | **No change** — `NotificationInboxPage` and mark-read flow untouched in approved layout-only scope; P5 `countUnread` prohibition in inbox page **remains** |
| **P6** deep-link | Row **مشاهده** to `requests.show` | **No impact** |
| **P7** layout nav | **اعلان‌ها** link without badge | **Successor supersession** of AC-LN-005 / badge exclusion only; nav link semantics preserved |

### Successor test/guard updates (authorized at contract/lock, not now)

| Item | Action when P8 proceeds |
|---|---|
| `NotificationInboxUiFlowTest` — `does not render unread badge...` | **Replace** with positive badge assertions per approved visibility rules |
| P5 architecture guard — `countUnread` forbidden in `NotificationInboxPage.php` | **Retain unchanged** (layout-only scope) |
| `RequestUiFlowTest` layout regression | **Extend** if badge visible on `/requests` |
| P7/P2 contract exclusion language for badge | **Superseded in P8 contract** only — predecessor artifacts not amended in place |

**Closed features are not reopened.** Successor overlay is explicitly authorized.

---

## 7. Open Decisions Classification

| Decision item | Classification | Resolution |
|---|---|---|
| **Zero-count badge visibility** | **Resolved at review-decision** | **Hide badge when `countUnread === 0`** — do not render numeric zero on layout nav in v1 |
| **Principal without linked employee** | **Resolved at review-decision** | **Omit badge silently** — no badge markup when employee id cannot be resolved; do not error on layout render (parity with nav visibility without page-level error surfacing) |
| **Exact presentation consumption boundary** | **Contract-stage decision** | Contract must name allowed Notification presentation adapter mechanism and forbidden surfaces; review approves application-contract-only principle |
| **Badge refresh after mark-read / delivery** | **Deferred** | v1 accepts stale layout badge until next full page load; no polling or reactive nav update required |
| **Formal contract required** | **Resolved at review-decision** | **Yes** — P7 precedent for cross-module layout overlay requires contract before lock/implementation |
| **Badge visual styling / component extraction** | **Contract-stage decision** | Contract may pin minimal presentation rules; lock may freeze markup |
| **Nav-level role/permission gating** | **Out of scope** | Matches P7 authenticated-layout parity |

No item remains **blocking before contract**.

---

## 8. Risks

### Technical risks

| Risk | Mitigation |
|---|---|
| Static layout cannot refresh badge after inbox mark-read without reload | Accepted v1 behavior; defer reactive refresh |
| Count injection into non-Livewire layout | Contract must pin allowed presentation adapter; forbid Blade-side queries |
| Stale count after new notification delivery on other pages | Accepted v1; document in contract non-goals |

### Governance risks

| Risk | Mitigation |
|---|---|
| Scope creep into inbox page or mark-read changes | Layout-only scope frozen; P5 guard retained |
| Accidental P7 nav semantic change | Contract must preserve link, label, order, active state, `href` transport |
| Supersession ambiguity | P8 contract explicit supersession of P2/P7 badge exclusions only |

### Test / regression risks

| Risk | Mitigation |
|---|---|
| P7 negative badge test conflict | Authorized successor test replacement in lock/implementation phase |
| Cross-module layout regression on `/requests` | Extend `RequestUiFlowTest` in implementation phase |
| False positive badge for wrong recipient | Tests must use recipient-scoped seed data; contract pins resolver usage |

---

## 9. Final Decision

**`APPROVED_FOR_FEATURE_CONTRACT`**

P8 — Notification Inbox Unread Badge is approved to proceed to feature-contract drafting as a bounded presentation-only successor feature. Scope is limited to layout-nav badge beside **اعلان‌ها**, consuming existing `countUnread` through an approved application boundary, with v1 full-page-load refresh only. No implementation, lock, or code changes are authorized by this decision.

---

## 10. Next Authorized Artifact

**`docs/ui/contracts/notifications/notification-inbox-unread-badge.feature-contract.yaml`**

Contract drafting must freeze:

- Layout-nav-only surface and P7 nav preservation
- `countUnread` delegation via `NotificationInboxReadContract` only
- Hide badge when count is zero; omit badge when employee unresolved
- v1 refresh = full page load only; no polling/real-time
- P2/P7 badge exclusion supersession boundary
- P5/P6 non-reopening and inbox-page non-change
- Forbidden backend, inbox-page, mark-read, and dual-surface scope
- Presentation adapter ownership within Notification module

---

*This review decision authorizes feature-contract drafting only. It does not authorize implementation, lock drafting, or modification of application code or tests.*
