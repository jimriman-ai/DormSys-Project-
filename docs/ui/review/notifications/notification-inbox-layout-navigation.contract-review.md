# P7 — Notification Inbox Layout Navigation — Contract Review

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
| **Contract artifact** | `docs/ui/contracts/notifications/notification-inbox-layout-navigation.feature-contract.yaml` |
| **Authoritative decision** | `docs/ui/review/notifications/notification-inbox-layout-navigation.review-decision.md` |
| **Verdict** | **Contract approved** |
| **Final classification** | **CONTRACT_APPROVED_READY_FOR_IMPLEMENTATION_LOCK** |
| **Implementation authorized?** | **No** — implementation lock is the next authorized artifact |
| **Blocking corrections** | **None** |

The P7 feature contract faithfully encodes the approved review decision. Scope is narrow, surfaces are bounded, frozen navigation parameters are explicit, exclusions are comprehensive, and acceptance constraints are sufficient for implementation-lock drafting without scope expansion or P2/P5/P6 reopening.

---

## 2. Inputs reviewed

| Artifact | Role |
|---|---|
| `docs/ui/contracts/notifications/notification-inbox-layout-navigation.feature-contract.yaml` | Primary — contract under review |
| `docs/ui/review/notifications/notification-inbox-layout-navigation.review-decision.md` | Authoritative approval baseline |
| `docs/ui/analysis/notifications/notification-inbox-layout-navigation.feature-analysis.md` | Supporting analysis context |
| `docs/ui/analysis/notifications/notification-inbox-layout-navigation.repo-inspection.md` | Repository truth reference |
| `docs/ui/contracts/notifications/notification-inbox-deep-link-navigation.feature-contract.yaml` | Predecessor exclusion pattern (P6) |
| `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` | P2 deferral/forbidden-change reference |

---

## 3. Contract alignment findings

### Review question 1 — Matches `APPROVED_READY_FOR_CONTRACT`?

**Pass.**

| Review decision element | Contract encoding |
|---|---|
| Verdict `APPROVED_READY_FOR_CONTRACT` | `decision_reference.verdict` |
| Presentation-only successor | `classification.implementation_type: UI_ONLY_GAP`, `purpose.constraint` |
| Layout nav only | `approved_surface`, `allowed_changes` |
| Implementation not authorized | `decision_reference.implementation_authorized: false`, `contract_status.note` |
| Contract → lock → implementation chain | `governance.prerequisites`, `governance.next_authorized_artifact` |

### Review question 2 — Standalone presentation-only feature?

**Pass.**

Contract classifies P7 as `successor-feature` with `UI_DISCOVERABILITY_GAP` / `UI_ONLY_GAP`. `purpose.summary` and `purpose.constraint` limit work to shared-layout discoverability. `dependencies.does_not_require` excludes backend, routes, and P2/P5/P6 file changes.

### Review question 3 — Shared layout navigation surface only?

**Pass.**

| Enforcement | Contract location |
|---|---|
| Single approved file | `approved_surface.primary_file: resources/views/components/layouts/app.blade.php` |
| Header nav element only | `approved_surface.element: header nav` |
| No other presentation files | `approved_surface.constraint`, `forbidden_changes.surfaces` |
| Tests deferred to lock | `allowed_changes` (tests defined at lock stage); `forbidden_changes.surfaces` allows lock to authorize test files |

### Review question 4 — Frozen label, destination, placement, visibility?

**Pass.**

| Parameter | Review decision | Contract freeze |
|---|---|---|
| Label | **اعلان‌ها** | `required_behavior.affordance.label`, `placement_rules.order[1].label` |
| Destination | `route('notifications.index')` | `required_behavior.affordance.destination` (`route_name`, `href`) |
| Placement | After **درخواست‌ها** | `placement_rules.rule_id: PLACE-LN-001`, positions 1 then 2 |
| Visibility | All authenticated users | `visibility_rules.rule_id: VIS-LN-001`, `nav_conditionals: none` |
| Page title unchanged | **اعلان‌های من** on inbox page | `required_behavior.inbox_page_title` |

No contradictory label (**اعلان‌های من** rejected for nav) appears in nav fields.

### Review question 5 — Navigation behavior and layout pattern parity?

**Pass.**

| Behavior | Contract encoding |
|---|---|
| Active detection | `active_state_rules.notifications.detection: request()->routeIs('notifications.*')` |
| Active/inactive classes | Matches requests pattern (`font-semibold text-sky-700` / `text-slate-600 hover:text-slate-900`) |
| Requests nav unchanged | `required_behavior.requests_nav.constraint`, `active_state_rules.requests.constraint` |
| Plain `href` only | `transport_rules.approved.mechanism: plain href anchor`, `wire_navigate: false` |
| No `wire:navigate` | `transport_rules.rejected` |

### Review question 6 — Correct rejections?

**Pass.**

| Rejected item | Contract evidence |
|---|---|
| Unread badge / `countUnread` | `forbidden_changes.badge_and_counts`, `non_goals`, AC-LN-005 |
| Request-page entrypoints | `forbidden_changes.discoverability_expansion`, `non_goals` |
| Inbox-page edits | `forbidden_changes.surfaces` (NotificationInboxPage, inbox Blade) |
| Home redirect change | `forbidden_changes.routes_and_middleware`, `non_goals` |
| Nav-level employee gating | `visibility_rules.rejected`, `forbidden_changes.authorization`, `non_goals` |
| Backend / route / middleware / policy / gate | `forbidden_changes` (routes, backend, authorization sections) |

### Review question 7 — Avoids reopening P2, P5, P6?

**Pass.**

| Predecessor | Contract treatment |
|---|---|
| P2 | `predecessor_artifacts.p2.supersession` — layout nav only; list behavior unchanged |
| P5 | `predecessor_artifacts.p5.supersession` — layout nav only; mark-read unchanged |
| P6 | `predecessor_artifacts.p6.supersession` — layout nav only; deep-link unchanged |
| Explicit boundary | `supersession_boundary.p7_supersedes` vs `p7_does_not_supersede` |
| Governance | `forbidden_changes.governance` — no reopening/amending predecessor artifacts |

### Review question 8 — Acceptance constraints clear and testable?

**Pass.**

Eight acceptance constraints (AC-LN-001 through AC-LN-008) cover:

| AC | Testability |
|---|---|
| AC-LN-001 | Nav link label + href on any shared-layout page |
| AC-LN-002 | Cross-module regression on `GET /requests` |
| AC-LN-003 | Nav present + active styling on `GET /notifications` |
| AC-LN-004 | Inbox page title unchanged (observational via HTTP GET; does not authorize inbox edits) |
| AC-LN-005 | No badge/count (negative assertion) |
| AC-LN-006 | No `wire:navigate` on layout nav link |
| AC-LN-007 | Nav order (درخواست‌ها before اعلان‌ها) |
| AC-LN-008 | P2/P5/P6 behavior preservation (regression boundary) |

`implementation_notes` identify primary/secondary test files for lock enumeration. Sufficient for lock drafting.

### Review question 9 — Too broad, contradictory, or risky statements?

**No blocking issues.**

| Item | Classification | Assessment |
|---|---|---|
| `allowed_changes` includes tests "defined at lock stage" | **Non-blocking clarification** | Correct deferral; lock must enumerate test files |
| `forbidden_changes.surfaces` excepts files "unless separately authorized by lock" | **Non-blocking clarification** | Standard pattern; lock will authorize `app.blade.php` + tests only |
| AC-LN-004 asserts inbox page title on `GET /notifications` | **Non-blocking clarification** | Regression observation only; contract still forbids inbox Blade edits |
| `contract_status: READY_FOR_IMPLEMENTATION_LOCK` before this review | **Non-blocking hygiene** | Status is appropriate post-approval; no contract edit required |
| `accepted_parity_risk` for unlinked employee principals | **Deferred concern** | Documented; matches review decision Option A; not a contract defect |

No contradictory scope statements identified. Contract does not authorize backend changes, badge work, or inbox-page modifications despite AC-LN-004 observational assertion.

### Review question 10 — Ready for Implementation Lock?

**Yes.**

Contract provides enough frozen boundaries for a narrow implementation lock: single layout file, frozen label/destination/placement/visibility/active-state/transport rules, explicit forbidden surfaces, test file guidance, and acceptance criteria for lock test obligations.

---

## 4. Scope enforcement findings

| Check | Result |
|---|---|
| MVF discipline (one nav link affordance) | **Pass** |
| Single surface (`app.blade.php` header nav) | **Pass** |
| No badge/countUnread scope bleed | **Pass** |
| No request-page discoverability expansion | **Pass** |
| No inbox-page behavior change authorized | **Pass** |
| No backend/DTO/route/middleware expansion | **Pass** |
| No nav-level authorization mirroring | **Pass** |
| P2/P5/P6 non-reopening | **Pass** |
| Successor supersession limited to layout nav deferral/exclusion | **Pass** |

**Scope leaks:** None identified.

---

## 5. Acceptance-constraint review

| Criterion | Assessment |
|---|---|
| Coverage of approved behavior | **Sufficient** — nav presence, href, placement, active state, transport, no badge |
| Cross-module regression | **Sufficient** — AC-LN-002 on `/requests` |
| Predecessor preservation | **Sufficient** — AC-LN-008 |
| Negative constraints testable | **Sufficient** — AC-LN-005 (no badge), AC-LN-006 (no wire:navigate) |
| Lock derivability | **Sufficient** — ACs map directly to HTTP/feature test assertions |

**Gap for lock (non-blocking):** Lock should pin exact assertion helpers (e.g. `assertSee('اعلان‌ها')`, `assertSeeHtml` with `route('notifications.index')`) and whether AC-LN-003 active-state assertion uses class string or structural HTML check. Contract level appropriately defers this detail.

---

## 6. Risk review

| Risk | Severity | Contract mitigation | Review assessment |
|---|---|---|---|
| Shared layout blast radius | Medium | Single-file `approved_surface`; surgical diff in `implementation_notes` | **Mitigated** |
| Governance conflict with P2/P5/P6 | High | `supersession_boundary`, `forbidden_changes.governance` | **Mitigated** |
| Badge/countUnread scope creep | Medium | Triple exclusion (forbidden, non_goals, AC-LN-005) | **Mitigated** |
| Employee resolver UX after nav click | Low | `accepted_parity_risk` documented | **Accepted deferred concern** |
| Accidental inbox/request page edits | Medium | Explicit `forbidden_changes.surfaces` | **Mitigated** |
| `wire:navigate` inconsistency | Low | `transport_rules` + AC-LN-006 | **Mitigated** |
| Over-broad test file changes | Low | Tests authorized only via lock | **Defer to lock** |

**Blocking risks:** None.

---

## 7. Required corrections

### Blocking corrections

**None.**

No contract revision is required before implementation-lock drafting.

### Non-blocking clarifications (for lock drafting, not contract revision)

1. **Test file enumeration** — Lock must explicitly allow `resources/views/components/layouts/app.blade.php`, `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php`, and optionally `tests/Feature/Modules/Request/RequestUiFlowTest.php` only.

2. **Active-state assertion technique** — Lock may pin whether AC-LN-003 uses class-string assertion (`font-semibold text-sky-700`) or equivalent HTML structure check.

3. **AC-LN-004 scope note** — Lock should state that inbox page title verification is read-only regression; no inbox Blade modification is authorized.

### Deferred concerns (out of P7 contract scope)

- Nav-level employee gating if product later rejects Option A parity risk
- Unread badge as separate future candidate

---

## 8. Final decision

**CONTRACT_APPROVED_READY_FOR_IMPLEMENTATION_LOCK**

The P7 feature contract correctly and safely encodes the approved review decision. It is narrow, explicit, internally consistent, and sufficient to authorize implementation-lock drafting. Implementation remains **not authorized** until lock approval completes.

| Classification | Disposition |
|---|---|
| Blocking correction | None |
| Non-blocking clarification | Test enumeration and active-state assertion detail (lock stage) |
| Deferred concern | Employee gating UX if product rejects Option A |
| Out-of-scope concern | Unread badge (correctly excluded) |

---

## 9. Authorized next artifact

| Field | Value |
|---|---|
| **Next authorized artifact** | Implementation lock |
| **Expected path** | `docs/ui/locks/notifications/notification-inbox-layout-navigation.implementation-lock.yaml` (or repository-equivalent lock artifact) |
| **Lock must pin** | Allowed files (`app.blade.php` + designated tests only); frozen nav markup rules; test obligations mapped to AC-LN-001–008; explicit supersession of P2/P5/P6 layout-nav deferrals only; forbidden surfaces and backend changes |
| **Not authorized** | Code changes, test implementation, direct UI implementation |

---

Recommended next governance step: **draft implementation lock**
