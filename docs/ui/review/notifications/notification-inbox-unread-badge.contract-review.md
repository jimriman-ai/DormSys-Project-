# Notification Inbox Unread Badge Contract Review

## 1. Review Summary

| Field | Value |
|---|---|
| **Feature** | `notification-inbox-unread-badge` (P8 — Notification Inbox Unread Badge) |
| **Reviewed contract** | `docs/ui/contracts/notifications/notification-inbox-unread-badge.feature-contract.yaml` |
| **Authoritative baseline** | `docs/ui/review/notifications/notification-inbox-unread-badge.review-decision.md` |
| **Review date** | 2026-07-10 |
| **Decision** | **`APPROVED_FOR_IMPLEMENTATION_LOCK`** |
| **Implementation authorized?** | **No** — implementation lock is the next authorized artifact only |
| **Next gate** | Implementation lock drafting and lock review |

The P8 feature contract faithfully encodes the approved review decision. Scope is layout-nav badge only, architecture boundaries are explicit, predecessor supersession is narrowly scoped to badge/`countUnread` display exclusions, and acceptance constraints are sufficient for lock drafting without reopening closed P2/P5/P6/P7 deliveries.

---

## 2. Contract Strengths

1. **Faithful review-decision encoding** — `decision_reference.verdict: APPROVED_FOR_FEATURE_CONTRACT` and `implementation_authorized: false` align with the review decision. All approved v1 boundaries (layout-only surface, zero-count hiding, silent omission on unresolved employee, full page-load refresh) are frozen.

2. **Explicit authority chain** — `authority_chain` defines the required delegation path from layout render through Notification-owned presentation boundary to `NotificationInboxReadContract::countUnread()`, with a parallel `forbidden_consumption_path` list that blocks Blade/repository/list-derived inference.

3. **P7 nav preservation frozen** — `ui_contract.rendering_rules.nav_preservation` pins label (**اعلان‌ها**), destination (`notifications.index`), plain `href`, active-state detection, nav order, and requests-nav parity without authorizing transport or semantic changes.

4. **View model contract is implementation-agnostic but testable** — `unread_count` and `show_badge` provide a clear presentation payload without prescribing adapter mechanism or markup.

5. **Successor supersession is bounded** — `governance.supersession_scope` explicitly lists what P8 supersedes (P2 deferred badge/count, P7 AC-LN-005) and what it does not (P2 list, P5 mark-read guard, P6 deep-link, P7 nav semantics).

6. **Comprehensive exclusions** — `non_goals`, `dependencies.forbidden`, and seven acceptance constraints (AC-UB-001 through AC-UB-007) collectively block inbox-header badge, reactive refresh, backend changes, mark-read changes, and inbox-page `countUnread` consumption.

7. **Testing expectations pre-staged** — `testing_expectations` identifies required future assertions, P7 negative-test replacement, P5 guard retention, and cross-module layout regression — sufficient for lock enumeration without authorizing test implementation now.

8. **Appropriate open-decision deferral** — `OQ-UB-001` (presentation boundary mechanism) and `OQ-UB-002` (markup/CSS) are correctly deferred to implementation lock with constraints that prevent scope expansion.

---

## 3. Issues Found

### Issue 1 — Contract status hygiene

| Field | Value |
|---|---|
| **Severity** | Low |
| **Description** | `contract_status.value` remains `READY_FOR_CONTRACT_REVIEW` although this review approves the contract for lock stage. |
| **Required action** | Optional status update to `CONTRACT_APPROVED_READY_FOR_IMPLEMENTATION_LOCK` when contract artifact is reconciled post-review. Not required before lock drafting. |
| **Blocking** | **Non-blocking** |

### Issue 2 — View model omission semantics

| Field | Value |
|---|---|
| **Severity** | Low |
| **Description** | `view_model_contract.fields[unread_count].nullability` states fields are "not passed to layout when badge omitted" while `show_badge` is described as required when view data is supplied. Lock must clarify whether omitted-employee and zero-count cases pass no variables, or pass `show_badge: false` without `unread_count`. |
| **Required action** | Pin exact variable presence/absence at implementation-lock stage. Contract constraint (`show_badge` gate only) is sufficient for lock. |
| **Blocking** | **Non-blocking** |

### Issue 3 — Allowed file enumeration deferred

| Field | Value |
|---|---|
| **Severity** | Low |
| **Description** | Contract authorizes `app.blade.php` and a Notification-owned presentation boundary but does not yet enumerate new PHP files (view composer, adapter, provider registration). This is intentional contract-stage deferral. |
| **Required action** | Implementation lock must pin exact allowed file paths and forbid all others. |
| **Blocking** | **Non-blocking** |

**Blocking issues:** None.

---

## 4. Scope Validation

| Check | Contract evidence | Result |
|---|---|---|
| Layout navigation badge only | `ui_contract.surface.primary_file`, `placement.rule_id: PLACE-UB-001`, `non_goals` (no inbox header badge) | **Pass** |
| No inbox header badge | `non_goals` first item; no inbox Blade in `dependencies.allowed` | **Pass** |
| No backend changes | `purpose.constraint`, `dependencies.forbidden` (contract signature, query semantics), `non_goals` | **Pass** |
| No notification behavior changes | `dependencies.forbidden` (NotificationInboxPage, inbox Blade, MarkNotificationReadContract), AC-UB-005 | **Pass** |
| No mark-read changes | `dependencies.forbidden`, `non_goals`, P5 impact: "no change" | **Pass** |
| No real-time/polling refresh | `refresh_policy.explicitly_deferred`, `non_goals` | **Pass** |
| No route/permission changes | `dependencies.forbidden`, `authorization.visibility.nav_role_gating: none` | **Pass** |
| No dual-surface scope | Option B/C rejected in review decision; contract excludes inbox header | **Pass** |

**Scope leaks:** None identified.

---

## 5. Architecture Validation

### Allowed path verification

| Layer | Contract encoding | Result |
|---|---|---|
| Layout (display) | `ui_contract.surface`, `layout_blade_constraint` | **Pass** |
| Notification presentation boundary | `presentation_boundary` (Notification module owned) | **Pass** |
| `NotificationInboxReadContract::countUnread()` | `authority_chain`, `view_model_contract` | **Pass** |
| `NotificationPrincipalEmployeeResolver` | `authorization.principal_resolution` | **Pass** |
| Existing service/repository chain | `authority_chain.allowed_consumption_path` (unchanged) | **Pass** |

### Forbidden path verification

| Anti-pattern | Contract encoding | Result |
|---|---|---|
| Blade repository access | `forbidden_consumption_path`, `dependencies.forbidden`, AC-UB-007 | **Pass** |
| Database queries from views | `dependencies.forbidden` (notification_logs, Eloquent) | **Pass** |
| `NotificationInboxPage` `countUnread` usage | `forbidden_consumption_path`, `dependencies.forbidden`, AC-UB-006 | **Pass** |
| Deriving count from list data | `forbidden_consumption_path`, `dependencies.forbidden` | **Pass** |
| Business rules in presentation | `forbidden_consumption_path`, `view_model_contract.constraint` | **Pass** |
| Request module involvement | `dependencies.forbidden` | **Pass** |

**Architecture blockers:** None.

---

## 6. Governance Validation

| Predecessor | Closed status | Contract treatment | Reopened? | Result |
|---|---|---|---|---|
| **P2** read-only inbox | CLOSED | Supersedes deferred `countUnread`/badge exclusion only; list baseline preserved | **No** | **Pass** |
| **P5** mark-read | CLOSED | No change; `NotificationInboxPage` `countUnread` prohibition retained | **No** | **Pass** |
| **P6** deep-link | IMPLEMENTED_VERIFIED | No impact stated | **No** | **Pass** |
| **P7** layout nav | CLOSED | Supersedes AC-LN-005 badge exclusion only; nav semantics preserved | **No** | **Pass** |

### Supersession boundary check

| Requirement | Contract evidence | Result |
|---|---|---|
| Only badge exclusions superseded | `governance.supersession_scope.p8_supersedes` (two items only) | **Pass** |
| P7 nav link semantics preserved | `ui_contract.rendering_rules.nav_preservation`, AC-UB-004 | **Pass** |
| P5 inbox-page guard preserved | `governance.predecessors.p5.impact`, AC-UB-006, testing_expectations | **Pass** |
| Predecessor artifacts not amended in place | `governance.supersession_scope.p8_does_not_supersede` | **Pass** |
| Successor test replacement authorized | `testing_expectations.regression_updates` (P7 negative test) | **Pass** |

**Governance conflicts:** None.

---

## 7. Implementation Lock Readiness

### Contract completeness checklist

| Required freeze | Contract section | Status |
|---|---|---|
| UI surface | `ui_contract.surface`, `placement` | **Frozen** |
| Data source | `authority_chain`, `view_model_contract` | **Frozen** |
| Zero-count behavior | `ui_contract.zero_state_behavior`, `rendering_rules.hide_badge_when` | **Frozen** |
| Unresolved employee behavior | `authorization.principal_resolution`, `zero_state_behavior` | **Frozen** |
| Refresh semantics | `refresh_policy` | **Frozen** |
| Authorization boundary | `authorization` | **Frozen** |
| Forbidden behaviors | `dependencies.forbidden`, `forbidden_consumption_path`, `non_goals` | **Frozen** |
| Acceptance constraints | `acceptance_constraints` AC-UB-001–007 | **Frozen** |

### Remaining decisions acceptable for lock stage

| Open decision | Status | Lock suitability |
|---|---|---|
| **OQ-UB-001** — presentation boundary mechanism (view composer vs dedicated adapter) | Deferred with constraint (Notification-owned, single delegation path) | **Acceptable** — matches P7 precedent for mechanism pinning at lock |
| **OQ-UB-002** — badge markup/CSS | Deferred with constraint (numeric count, no nav structure change) | **Acceptable** — visual detail appropriately deferred |

### Lock must pin (guidance for next artifact)

1. Exact allowed files: `resources/views/components/layouts/app.blade.php`, new Notification presentation boundary file(s), provider registration if needed, designated test files only.
2. Selected presentation mechanism from `allowed_mechanism_categories`.
3. View variable contract for zero-count and unresolved-employee cases.
4. Test obligations mapped to AC-UB-001–007 and TEST-UB-001–005.
5. Explicit supersession note replacing P7 AC-LN-005 negative badge assertion.
6. P5 architecture guard retention statement.

**Ready for implementation lock:** **Yes.**

---

## 8. Final Decision

**`APPROVED_FOR_IMPLEMENTATION_LOCK`**

The P8 feature contract is sufficiently precise, internally consistent, and safe for implementation-lock drafting. It correctly encodes the approved review decision without scope expansion, architecture leakage, or predecessor reopening. No contract revision is required before lock drafting.

| Classification | Disposition |
|---|---|
| Blocking correction | None |
| Non-blocking clarification | Contract status hygiene; view-model omission semantics; file enumeration (all lock stage) |
| Architecture blocker | None |
| Governance blocker | None |

Implementation remains **not authorized** until implementation lock approval completes.

---

## 9. Next Authorized Artifact

| Field | Value |
|---|---|
| **Next authorized artifact** | Implementation lock |
| **Expected path** | `docs/ui/locks/notifications/notification-inbox-unread-badge.implementation-lock.yaml` (or repository-equivalent lock artifact per project convention) |
| **Lock must pin** | Allowed files; presentation boundary mechanism; view model variable contract; badge markup rules; test file list and AC mapping; P2/P7 badge supersession; P5 guard retention; forbidden surfaces |
| **Not authorized** | Code changes, test implementation, Blade/Livewire edits, backend changes |

---

## Inputs Reviewed

| Artifact | Role |
|---|---|
| `docs/ui/contracts/notifications/notification-inbox-unread-badge.feature-contract.yaml` | Primary — contract under review |
| `docs/ui/review/notifications/notification-inbox-unread-badge.review-decision.md` | Authoritative approval baseline |
| `docs/ui/analysis/notifications/notification-inbox-unread-badge.feature-analysis.md` | Supporting analysis context |
| `docs/ui/analysis/notifications/notification-inbox-unread-badge.repo-inspection.md` | Repository truth reference |
| `docs/ui/contracts/notifications/notification-inbox-layout-navigation.feature-contract.yaml` | P7 predecessor; AC-LN-005 supersession target |
| `docs/ui/review/notifications/notification-inbox-layout-navigation.contract-review.md` | Contract review format precedent |

---

*This contract review authorizes implementation-lock drafting only. It does not authorize implementation, code changes, or test modifications.*
