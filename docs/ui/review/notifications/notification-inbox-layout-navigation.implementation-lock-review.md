# P7 — Notification Inbox Layout Navigation — Implementation Lock Review

## Feature

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-layout-navigation` |
| **Feature title** | P7 — Notification Inbox Layout Navigation |
| **Domain area** | notifications |
| **Review date** | 2026-07-10 |

---

## 1. Review summary

| Field | Result |
|---|---|
| **Lock artifact** | `docs/ui/locks/notifications/notification-inbox-layout-navigation.implementation-lock.yaml` |
| **Lock draft status** | `DRAFT_PENDING_LOCK_REVIEW` |
| **Verdict** | **Lock approved** |
| **Final classification** | **LOCK_APPROVED_READY_FOR_IMPLEMENTATION** |
| **Implementation authorized by this review?** | **No** — authorization takes effect only after lock artifact status and `authorization.*` flags are updated per governance workflow |

The implementation lock faithfully encodes the approved contract and contract review. Production scope is limited to one layout Blade file; tests are limited to two authorized feature test files. Frozen navigation requirements, forbidden changes, acceptance criteria AC-LN-001 through AC-LN-008, validation requirements, and rollback boundary are explicit and enforceable. No blocking issues were identified.

---

## 2. Inputs reviewed

| Artifact | Role |
|---|---|
| `docs/ui/locks/notifications/notification-inbox-layout-navigation.implementation-lock.yaml` | Primary — lock under review |
| `docs/ui/contracts/notifications/notification-inbox-layout-navigation.feature-contract.yaml` | Governing contract |
| `docs/ui/review/notifications/notification-inbox-layout-navigation.contract-review.md` | Contract review (`CONTRACT_APPROVED_READY_FOR_IMPLEMENTATION_LOCK`) |
| `docs/ui/review/notifications/notification-inbox-layout-navigation.review-decision.md` | Original approval baseline |

---

## 3. Scope authorization review

### Question 1 — Does the lock match the approved contract and contract review?

**Pass.**

| Contract / review element | Lock encoding |
|---|---|
| Layout-only surface (`app.blade.php` header nav) | `allowed_changes.files.production`, `approved_surface` equivalent in `frozen_requirements` |
| Label **اعلان‌ها** → `notifications.index` | `frozen_requirements.label`, `frozen_requirements.route` |
| Placement after **درخواست‌ها** | `frozen_requirements.placement` / `PLACE-LN-001` |
| All authenticated users; no nav conditionals | `frozen_requirements.visibility` / `FR-LN-004` |
| Active state `notifications.*` | `frozen_requirements.active_state` / `FR-LN-005` |
| Plain `href`; no `wire:navigate` | `frozen_requirements.transport` / `FR-LN-006` |
| Forbidden badge, backend, inbox/request pages, P2/P5/P6 reopening | `forbidden_changes`, `governance_boundary` |
| P7 supersedes layout-nav deferrals only | `governance.predecessor_boundary_note`, `acceptance_criteria` AC-LN-008 |
| AC-LN-001 through AC-LN-008 | `acceptance_criteria`, `test_scope.required_assertions` |
| Implementation not authorized until lock approval | `authorization.implementation_authorized: false` |

### Question 2 — Production surface limited to `app.blade.php`?

**Pass.**

- Single authorized production path: `resources/views/components/layouts/app.blade.php`
- Constraint: header `<nav>` only; add notification nav link and active-state logic
- `file_scope_rule` forbids all other production files

### Question 3 — Test files correctly restricted?

**Pass.**

| File | Lock posture |
|---|---|
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | Allowed; P7 layout-nav assertions |
| `tests/Feature/Modules/Request/RequestUiFlowTest.php` | Optional; cross-module layout regression only |
| New test files | Forbidden unless separate governance |

`forbidden_test_changes` prevents modification of P2/P5/P6 inbox behavior tests beyond additive layout-nav coverage.

---

## 4. Frozen requirement review

| Requirement | Approved value | Lock rule | Result |
|---|---|---|---|
| Label | **اعلان‌ها** | `FR-LN-001` | **Pass** |
| Route | `notifications.index` / `route('notifications.index')` | `FR-LN-002` | **Pass** |
| Placement | Immediately after **درخواست‌ها** | `FR-LN-003` positions 1–2 | **Pass** |
| Visibility | All authenticated users on shared layout | `FR-LN-004`; `nav_conditionals: none` | **Pass** |
| Active state | `request()->routeIs('notifications.*')` | `FR-LN-005` with frozen classes | **Pass** |
| Transport | Plain `href` | `FR-LN-006`; `wire_navigate: false` | **Pass** |
| No `wire:navigate` | Rejected explicitly | `transport.forbidden` | **Pass** |
| Nav pattern parity | Mirror requests nav; no new abstraction | `FR-LN-007` | **Pass** |
| Inbox page title unchanged | **اعلان‌های من** (observational) | `frozen_requirements.label.constraint` + AC-LN-004 note | **Pass** |

**Non-blocking note:** Contract AC-LN-002 text emphasizes **اعلان‌ها** visibility on `GET /requests`; lock AC-LN-002 emphasizes **درخواست‌ها** preservation. Cross-module **اعلان‌ها** visibility on `/requests` remains covered by AC-LN-001 (`any page using components.layouts.app`) and TEST-LN-001. Implementers should assert on `GET /requests` for AC-LN-001; no lock revision required.

---

## 5. Test scope review

### Question 6 — Are AC-LN-001 through AC-LN-008 implementable and testable?

**Pass.**

| AC | Lock mapping | Testability |
|---|---|---|
| AC-LN-001 | TEST-LN-001; `assertSee('اعلان‌ها')` + href to `notifications.index` | Testable on any shared-layout page (e.g. `/requests`, `/notifications`) |
| AC-LN-002 | TEST-LN-002; **درخواست‌ها** + `requests.index` href preserved | Testable on `GET /requests` |
| AC-LN-003 | TEST-LN-003; visible link + active styling on `GET /notifications` | Testable via class or markup assertion |
| AC-LN-004 | TEST-LN-004; `assertSee('اعلان‌های من')` observational only | Testable without inbox file edits |
| AC-LN-005 | TEST-LN-005; negative badge/count assertion | Testable |
| AC-LN-006 | TEST-LN-006; href present, no `wire:navigate` | Testable via markup inspection |
| AC-LN-007 | TEST-LN-007; **درخواست‌ها** before **اعلان‌ها** in nav order | Testable via HTML order assertion |
| AC-LN-008 | No inbox/Livewire edits; additive tests only | Enforced by forbidden surfaces + regression pass |

`active_state_assertion_style` appropriately defers exact assertion technique to implementation review while requiring AC-LN-003 validation.

`validation_requirements.recommended_commands` align with authorized test files.

---

## 6. Forbidden change review

### Question 5 — Are forbidden changes complete?

**Pass.**

| Exclusion category | Lock section | Result |
|---|---|---|
| Unread badge / `countUnread` | `notification_behavior`, `forbidden_test_changes`, AC-LN-005 | **Pass** |
| Backend changes | `forbidden_changes.backend` | **Pass** |
| Route / middleware changes | `forbidden_changes.routes` | **Pass** |
| Authorization / policy / gate / nav gating | `forbidden_changes.authorization` | **Pass** |
| Inbox page changes | `ui_surfaces` (inbox Blade), `notification_behavior` | **Pass** |
| Request page changes | `ui_surfaces` | **Pass** |
| P2/P5/P6 reopening | `governance_boundary`, AC-LN-008 | **Pass** |
| Home redirect, nav abstraction, dependencies | `ui_surfaces`, `architecture` | **Pass** |

No scope leaks identified beyond authorized layout nav discoverability.

---

## 7. Rollback boundary review

### Question 7 — Does rollback correctly limit to P7 only?

**Pass.**

| Rollback element | Lock posture |
|---|---|
| Applies to | P7 nav anchor + active-state logic + P7 test additions in `app.blade.php` and authorized tests |
| Must not touch | P2 list, P5 mark-read, P6 deep-link, unrelated layout |
| Rollback rule | Revert **اعلان‌ها** link and associated tests only |

Rollback boundary is correctly narrow and does not authorize reverting predecessor feature behavior.

**Non-blocking note:** `rollback_boundary.rollback_rule` text in the lock YAML spans multiple lines; governance hygiene may wrap the closing sentence for readability. Does not affect enforceability.

---

## 8. Blocking issues

**None.**

| Issue | Classification | Disposition |
|---|---|---|
| AC-LN-002 emphasis differs between contract (اعلان‌ها on `/requests`) and lock (درخواست‌ها preservation) | Non-blocking clarification | AC-LN-001 + TEST-LN-001 on `GET /requests` satisfies cross-module visibility |
| Lock `authorization.*` flags remain `false` | Expected pre-approval state | Update on formal lock approval per governance workflow |
| Active-state assertion technique deferred | Non-blocking | Acceptable; lock pins obligation, not exact assert API |

---

## 9. Final decision

**LOCK_APPROVED_READY_FOR_IMPLEMENTATION**

The implementation lock is narrow, explicit, contract-faithful, and sufficient to authorize bounded implementation after formal lock approval updates the lock artifact status and sets `implementation_authorized` and `coding_authorized` to `true`.

This review artifact **does not** itself authorize coding. It approves the lock for the implementation phase subject to governance status update on the lock file.

---

## 10. Next authorized action

| Step | Action |
|---|---|
| 1 | Update lock artifact status from `DRAFT_PENDING_LOCK_REVIEW` to approved (e.g. `APPROVED_FOR_IMPLEMENTATION`) |
| 2 | Set `authorization.implementation_authorized: true` and `authorization.coding_authorized: true` on the lock artifact |
| 3 | Implement only within `allowed_changes.files` |
| 4 | Add tests only within `test_scope.allowed_files` |
| 5 | Validate against `validation_requirements` and AC-LN-001 through AC-LN-008 |

**Authorized implementation surface (upon lock status update):**

- `resources/views/components/layouts/app.blade.php`
- `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php`
- `tests/Feature/Modules/Request/RequestUiFlowTest.php` (optional)

**Remain forbidden:** All items in `forbidden_changes`; no badge, backend, route, inbox-page, request-page, or P2/P5/P6 functional changes.

---

Recommended next governance step: **formal lock approval and bounded implementation**
