# P8 — Notification Inbox Unread Badge — Implementation Lock Review

## Feature

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-unread-badge` |
| **Feature title** | P8 — Notification Inbox Unread Badge |
| **Domain area** | notifications / presentation |
| **Review date** | 2026-07-10 |

---

## 1. Validation summary

| Field | Result |
|---|---|
| **Lock artifact** | `docs/ui/locks/notifications/notification-inbox-unread-badge.implementation-lock.yaml` |
| **Lock draft status** | `DRAFT_PENDING_LOCK_REVIEW` |
| **Governing contract** | `docs/ui/contracts/notifications/notification-inbox-unread-badge.feature-contract.yaml` |
| **Contract review** | `APPROVED_FOR_IMPLEMENTATION_LOCK` |
| **Verdict** | **`APPROVED_FOR_IMPLEMENTATION`** |
| **Final classification** | **LOCK_APPROVED_READY_FOR_IMPLEMENTATION** |
| **Implementation authorized by this review?** | **No** — bounded coding is permitted only after the lock artifact status and `authorization.*` flags are updated per governance workflow |

The implementation lock faithfully encodes the approved review decision, feature contract, and contract review. Scope is limited to layout-nav unread badge beside **اعلان‌ها** via a single view composer boundary. Production and test file enumerations are explicit with no wildcards. View model, refresh policy, predecessor supersession, forbidden surfaces, and acceptance criteria AC-UB-001 through AC-UB-007 are frozen and enforceable. **No blocking issues** were identified.

---

## 2. Inputs reviewed

| Artifact | Role |
|---|---|
| `.specify/governance/_meta/authority-model.md` | Authorization vocabulary and lifecycle constraints |
| `docs/ui/review/notifications/notification-inbox-unread-badge.review-decision.md` | Original approval baseline |
| `docs/ui/contracts/notifications/notification-inbox-unread-badge.feature-contract.yaml` | Governing contract |
| `docs/ui/review/notifications/notification-inbox-unread-badge.contract-review.md` | Contract review (`APPROVED_FOR_IMPLEMENTATION_LOCK`) |
| `docs/ui/locks/notifications/notification-inbox-unread-badge.implementation-lock.yaml` | Primary — lock under review |
| `docs/ui/review/notifications/notification-inbox-layout-navigation.implementation-lock-review.md` | Lock review format precedent |

---

## 3. Scope validation

| Check | Lock evidence | Result |
|---|---|---|
| Layout navigation unread badge beside **اعلان‌ها** only | `allowed_changes.files.production` (app.blade.php nav item), `layout_constraints` | **Pass** |
| Inbox header badge excluded | `forbidden_changes.behaviors` (second-surface), `layout_constraints.badge_markup.forbidden` | **Pass** |
| `NotificationInboxPage` changes forbidden | `forbidden_changes.production_files`, `behaviors`, `GUARD-UB-P5` | **Pass** |
| Backend `countUnread` changes forbidden | `forbidden_changes` (contract, service, repository, query semantics) | **Pass** |
| Mark-read changes forbidden | `forbidden_changes` (MarkNotificationReadContract, MarkNotificationReadAction, behaviors) | **Pass** |
| Reactive refresh forbidden | `refresh_policy.explicitly_forbidden`, `forbidden_changes.behaviors` | **Pass** |
| Full page-load refresh only | `refresh_policy.v1_mechanism` | **Pass** |

**Scope leaks:** None identified.

---

## 4. Presentation boundary validation

### Required delegation chain

| Layer | Lock encoding | Result |
|---|---|---|
| `components.layouts.app` | `presentation_boundary.view_composer.binds_to` | **Pass** |
| `LayoutNavUnreadBadgeComposer` | Sole mechanism; adapter rejected | **Pass** |
| `NotificationPrincipalEmployeeResolver` | `delegation_chain`; non-throwing for layout | **Pass** |
| `NotificationInboxReadContract::countUnread()` | `delegation_chain`; composer forbidden paths | **Pass** |

### Rejected paths

| Anti-pattern | Lock encoding | Result |
|---|---|---|
| Blade service/contract resolution | `layout_blade_rule`, `allowed_changes` app.blade.php constraint | **Pass** |
| Repository usage in views/composer | `forbidden_in_composer`, `GUARD-UB-ARCH` | **Pass** |
| DB queries | `forbidden_in_composer`, `forbidden_changes.behaviors` | **Pass** |
| List-derived unread counting | `forbidden_in_composer` | **Pass** |
| Duplicate presentation adapters | `presentation_boundary.constraint`; rejected adapter mechanism | **Pass** |

**Architecture blockers:** None.

---

## 5. File scope validation

### Authorized production files

| Path | Lock authorization | Result |
|---|---|---|
| `resources/views/components/layouts/app.blade.php` | `allowed_changes.files.production[0]` | **Pass** |
| `app/Modules/Notification/Presentation/View/Composers/LayoutNavUnreadBadgeComposer.php` | `allowed_changes.files.production[1]` (new) | **Pass** |
| `app/Modules/Notification/Presentation/Providers/NotificationPresentationServiceProvider.php` | `allowed_changes.files.production[2]` | **Pass** |
| `bootstrap/providers.php` | `allowed_changes.files.production[3]` (NotificationPresentationServiceProvider only) | **Pass** |

No wildcard permissions. `file_scope_rule` forbids all other production files.

### Authorized test files

| Path | Lock authorization | Result |
|---|---|---|
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | `test_scope.allowed_files`, `allowed_changes.files.tests` | **Pass** |
| `tests/Feature/Modules/Request/RequestUiFlowTest.php` | `test_scope.allowed_files` (optional) | **Pass** |

New test files forbidden unless separate governance.

---

## 6. View model validation

| Rule | Lock encoding | Result |
|---|---|---|
| `show_badge` always available to layout | `view_model_contract.variables[show_badge].always_supplied: true` | **Pass** |
| `show_badge: false` when count zero | `cases.count_zero` | **Pass** |
| `show_badge: false` when employee unresolved | `cases.employee_unresolved` | **Pass** |
| `unread_count` only when `show_badge === true` | `supplied_only_when: show_badge === true` | **Pass** |
| `unread_count` backend-authoritative integer | `source: NotificationInboxReadContract::countUnread()` | **Pass** |
| Layout gates on `@if($show_badge)` only | `layout_blade_rule` | **Pass** |
| No numeric zero display | `cases.count_zero.layout_behavior` | **Pass** |
| No layout exception on unresolved employee | `cases.employee_unresolved.layout_behavior` | **Pass** |

---

## 7. Governance validation

### Superseded (authorized)

| Predecessor exclusion | Lock encoding | Result |
|---|---|---|
| P2 deferred badge/count exclusion | `governance_reconciliation.p8_supersedes` | **Pass** |
| P7 AC-LN-005 badge exclusion | `governance_reconciliation.p8_supersedes` | **Pass** |
| P7 TEST-LN-005 negative badge test | `p7_test_replacement` | **Pass** |

### Preserved (not reopened)

| Predecessor delivery | Lock encoding | Result |
|---|---|---|
| P5 inbox architecture guard (`countUnread` forbidden in inbox page) | `GUARD-UB-P5`, `p8_does_not_supersede` | **Pass** |
| P5 mark-read behavior | `forbidden_changes`, `p8_does_not_supersede` | **Pass** |
| P6 deep-link behavior | `forbidden_changes`, `p8_does_not_supersede` | **Pass** |
| P7 nav label, order, destination, active state, plain href | `layout_constraints.preserve_from_p7`, `forbidden_changes.behaviors` | **Pass** |
| P2 inbox list baseline | `p8_does_not_supersede` | **Pass** |

**Governance conflicts:** None.

---

## 8. Test scope validation

| Test ID | Obligation | AC mapping | Result |
|---|---|---|---|
| TEST-UB-001 | Badge visible when unread > 0 | AC-UB-001 | **Pass** |
| TEST-UB-002 | Badge omitted when count zero | AC-UB-002 | **Pass** |
| TEST-UB-003 | Badge omitted when no linked employee | AC-UB-003 | **Pass** |
| TEST-UB-004 | P7 nav preserved | AC-UB-004 | **Pass** |
| TEST-UB-005 | Recipient-scoped count | maps_to AC-UB-005 | **See non-blocking note** |
| GUARD-UB-P5 | Retain inbox `countUnread` prohibition | AC-UB-006 | **Pass** |
| GUARD-UB-ARCH | No repo/DB in views; composer delegates only | AC-UB-007 | **Pass** |

AC-UB-005 (inbox list, mark-read, deep-link unchanged) is enforced via `forbidden_changes`, file scope, and regression pass rather than a dedicated TEST-UB entry. This is acceptable.

P7 negative badge test replacement is explicitly authorized.

---

## 9. Blocking issues

**None.**

The lock is fully compliant with approved P8 scope and safe for bounded implementation.

---

## 10. Non-blocking notes

| Note | Classification | Disposition |
|---|---|---|
| `TEST-UB-005` `maps_to: AC-UB-005` conflates recipient-scoping test with inbox-behavior AC | Documentation hygiene | TEST-UB-005 obligation text is clear; implementers must still assert cross-employee isolation. Optional lock YAML fix: map TEST-UB-005 to `AUTH-UB-002` / recipient-scoping concern or add explicit AC reference. Does not block implementation. |
| `authorization.*` flags remain `false` | Expected pre-approval state | Update lock artifact on formal approval per authority model §4–§5 |
| `OQ-LOCK-UB-001` (Tailwind classes) and `OQ-LOCK-UB-002` (unresolved-employee composer technique) | Implementation discretion | Appropriately constrained within lock; not scope expansion |
| `bootstrap/providers.php` composition-root touch | Anticipated and enumerated | Allowed solely for `NotificationPresentationServiceProvider` registration |
| Lock status remains `DRAFT_PENDING_LOCK_REVIEW` | Governance hygiene | Update to `APPROVED_FOR_IMPLEMENTATION` on lock artifact reconciliation |

---

## 11. Final decision

**`APPROVED_FOR_IMPLEMENTATION`**

The implementation lock correctly freezes the approved P8 scope. Presentation boundary, file scope, view model contract, refresh policy, forbidden surfaces, and predecessor reconciliation are explicit, internally consistent, and enforceable. The lock is safe for bounded implementation after formal lock artifact approval updates authorization flags.

Per the authority model, this review artifact is **approval of the lock design**, not a substitute for updating the lock file's `authorization-status` and scope fields before coding begins.

---

## 12. Next authorized artifact / action

| Step | Action |
|---|---|
| 1 | Update lock artifact `feature.status` from `DRAFT_PENDING_LOCK_REVIEW` to `APPROVED_FOR_IMPLEMENTATION` |
| 2 | Set `authorization.implementation_authorized: true`, `authorization.coding_authorized: true`, `authorization.lock_authorized: true` on the lock artifact |
| 3 | Record `governance.approved_by.lock_review` pointing to this review |
| 4 | Implement only within `allowed_changes.files` |
| 5 | Add tests only within `test_scope.allowed_files` |
| 6 | Validate against `validation_requirements` and AC-UB-001 through AC-UB-007 |

**Authorized implementation surface (upon lock status update):**

| Layer | Path |
|---|---|
| Layout Blade | `resources/views/components/layouts/app.blade.php` |
| View composer (new) | `app/Modules/Notification/Presentation/View/Composers/LayoutNavUnreadBadgeComposer.php` |
| Provider registration | `app/Modules/Notification/Presentation/Providers/NotificationPresentationServiceProvider.php` |
| Composition root | `bootstrap/providers.php` |
| Tests | `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` |
| Tests (optional) | `tests/Feature/Modules/Request/RequestUiFlowTest.php` |

**Remain forbidden:** All items in `forbidden_changes`; inbox page, backend, routes, mark-read, reactive refresh, and P2/P5/P6/P7 deliveries beyond badge-display supersession.

---

*This implementation lock review approves the lock for bounded implementation. It does not authorize code changes until the lock artifact authorization flags are updated per governance workflow.*
