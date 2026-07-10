# P9 — Notification Inbox Pagination — Contract Review

## Feature Identity

| Field | Value |
|---|---|
| **Feature id** | `notification-inbox-pagination` |
| **Feature title** | P9 — Notification Inbox Pagination |
| **Domain** | notifications / mixed presentation + read-model |
| **Gap classification** | `MIXED_UI_AND_READ_MODEL_GAP` |
| **Reviewed contract** | `docs/ui/contracts/notifications/notification-inbox-pagination.feature-contract.yaml` |
| **Contract version** | `0.1.0` |
| **Governance gate** | `contract-review` |
| **Review date** | 2026-07-10 |
| **Prior blocked attempt** | Superseded — previous `BLOCKED_MISSING_INPUT` is not treated as approval or rejection |

---

## Source Artifacts Reviewed

| Artifact | Role | Status |
|---|---|---|
| `.specify/governance/_meta/authority-model.md` | Authority vocabulary / non-authorization of this gate | **Present** |
| `docs/ui/analysis/notifications/notification-inbox-pagination.repo-inspection.md` | Repository truth | **Present** |
| `docs/ui/analysis/notifications/notification-inbox-pagination.feature-analysis.md` | Feature-analysis conclusions (restored) | **Present** |
| `docs/ui/review/notifications/notification-inbox-pagination.review-decision.md` | Authoritative architectural decisions (RD-P9-001–008) | **Present** |
| `docs/ui/contracts/notifications/notification-inbox-pagination.feature-contract.yaml` | Contract under review | **Present** |
| `docs/ui/contracts/notifications/*.feature-contract.yaml` | Notification contract conventions / predecessor boundaries | **Present** |

**Missing required inputs:** None.

---

## Governance Gate

| Field | Value |
|---|---|
| **Current gate** | `contract-review` |
| **Previous approved gate** | `feature-contract` (`READY_FOR_CONTRACT`) |
| **This review authorizes** | Implementation-lock drafting only |
| **Implementation authorized?** | **No** |

---

## Review Checklist

| # | Check | Result |
|---|---|---|
| 1 | Governance alignment (review-decision, feature-analysis, repo-inspection, authority model, conventions) | **Pass** |
| 2 | Required decisions encoding (RD-P9-001–008) | **Pass** |
| 3 | Predecessor boundary validation | **Pass** |
| 4 | Scope exclusion validation | **Pass** |
| 5 | Architecture validation | **Pass** |
| 6 | Acceptance criteria validation | **Pass** |
| 7 | Conflict detection | **Pass** — no blocking conflicts |

---

## 1. Governance Alignment

| Source | Alignment check | Result |
|---|---|---|
| **Review-decision** `READY_FOR_CONTRACT` | Contract `decision_reference.verdict: READY_FOR_CONTRACT`; `implementation_authorized: false` | **Pass** |
| **Feature-analysis** `MIXED_UI_AND_READ_MODEL_GAP` / `READY_FOR_REVIEW_DECISION` | Contract `gap_type: MIXED_UI_AND_READ_MODEL_GAP`; mixed UI + read-model scope; P2-only supersession | **Pass** |
| **Repo-inspection** limit-only / no metadata / no UI controls | Contract purpose/rationale and query semantics match evidenced gap | **Pass** |
| **Authority model** | This review is a governance decision record; does not grant Implementation Authorization | **Pass** |
| **Notification contract conventions** | Successor-feature structure with baseline_contracts, scope, anti_leak, acceptance_criteria — consistent with P5/P8 patterns | **Pass** |

Contract correctly treats Request P4 as **internal analogy only** (`references.internal_precedent_analogy_only`; `does_not_require` Request DTO reuse).

---

## 2. Decision Encoding Validation

| Decision | Required encoding | Contract evidence | Result |
|---|---|---|---|
| **RD-P9-001** Offset/page-number | `forPage` / offset page-number; cursor rejected | `pagination_strategy: offset_page_number`; `rejected_strategies: cursor_pagination`; `pagination.model` | **Pass** |
| **RD-P9-002** New paginated method; preserve flat list | Dual-method | `listForRecipientPaginated` + `listForRecipient` preservation | **Pass** |
| **RD-P9-003** Envelope DTO fields | items, total, currentPage, perPage, lastPage | `result_envelope` + `PaginatedNotificationInboxListDTO` | **Pass** |
| Backend-authoritative metadata | UI must not compute total/lastPage | `enforcement_rules`, `anti_leak_boundaries` | **Pass** |
| **RD-P9-004** Ordering | `created_at DESC` + `id DESC` | `ordering` section; enforcement rule | **Pass** |
| **RD-P9-005** Fixed page size 50 | Contract-frozen 50 | `per_page.fixed_value: 50`; `contract_frozen: true` | **Pass** |
| **RD-P9-006** Livewire URL page state | `#[Url]` page binding | `url_state` with Livewire URL binding | **Pass** |
| **RD-P9-007** Mark-read current-page refresh | No reset to page 1 | `mutations.post_mutation_refresh`; reset_rules | **Pass** |
| **RD-P9-008** P8 independence | Badge unchanged; no `countUnread` in inbox page | `p8_unread_badge_boundary`; forbidden_changes; AC | **Pass** |
| Direct repository access from UI forbidden | Explicit | `out_of_scope`, `architecture_constraints`, `anti_leak_boundaries` | **Pass** |

All contract-blocking review-decision items are encoded. Lock-stage items (prev/next Persian copy, exact file allowlist, test file enumeration) remain appropriately deferred.

---

## 3. Predecessor Boundary Validation

| Predecessor | Required treatment | Contract evidence | Result |
|---|---|---|---|
| **P2** | Supersede **only** deferred pagination beyond 50-item cap; preserve inbox baseline | `baseline_contracts` supersession_scope; `governance.superseded_predecessor_exclusions` | **Pass** |
| **P5** | Preserve mark-read mutation; supersede only post-mutation reload transport | `baseline_contracts` P5 entry; `mutations.preserved_from_p5` | **Pass** |
| **P6** | Preserve deep-link | `preserved: Row request_show_url...`; AC functional | **Pass** |
| **P7** | Preserve nav semantics | `preserved` layout nav; AC functional | **Pass** |
| **P8** | Preserve unread badge architecture | `p8_unread_badge_boundary`; frozen_boundaries | **Pass** |

**Predecessor reopenings:** None. Contract states P9 does not amend P2/P5/P6/P7/P8 artifacts in place.

---

## 4. Exclusion Validation

| Exclusion | Contract evidence | Result |
|---|---|---|
| mark-all-as-read | `out_of_scope`, `forbidden_changes`, AC non_functional | **Pass** |
| search | `out_of_scope`, `forbidden_changes` | **Pass** |
| filtering UI | `out_of_scope` (unread-only filter UI); `unreadOnly` unused by UI v1 | **Pass** |
| sorting UI | `out_of_scope`; ordering backend-fixed | **Pass** |
| realtime / polling / websocket / SSE / wire:poll | `out_of_scope` | **Pass** |
| archive UI | `out_of_scope`, `forbidden_changes` | **Pass** |
| API endpoints | `out_of_scope`, `forbidden_changes` | **Pass** |
| SPA architecture | `out_of_scope` | **Pass** |
| notification domain redesign | `out_of_scope`, `does_not_require` | **Pass** |
| request-list pagination changes | `out_of_scope`, `forbidden_changes` | **Pass** |
| unread badge ownership changes | `out_of_scope` P8 changes; `p8_unread_badge_boundary` | **Pass** |
| direct repository / Eloquent from UI | `out_of_scope`, architecture + anti-leak | **Pass** |
| cursor pagination / configurable page size / in-memory pagination | `out_of_scope` | **Pass** |

**Scope leaks:** None identified.

---

## 5. Architecture Validation

| Requirement | Contract evidence | Result |
|---|---|---|
| Thin Livewire/Blade boundary | `architecture_constraints.ui`; anti-leak required/forbidden | **Pass** |
| Notification module owns read model/query | Application contract + repository extension in `allowed_changes` | **Pass** |
| No UI → repository / Eloquent | Forbidden paths + AC non_functional | **Pass** |
| No Request module coupling | `does_not_require` Request DTO; forbidden Request pagination implementation | **Pass** |
| P8 badge ownership outside inbox page | `LayoutNavUnreadBadgeComposer` unchanged | **Pass** |
| No `countUnread()` in `NotificationInboxPage` | Explicit constraints + regression_safety guard retention | **Pass** |
| Backend-paged execution (`forPage` + count) | `enforcement_rules`; forbids in-memory full-list pagination | **Pass** |

**Architecture blockers:** None.

---

## 6. Acceptance Criteria Validation

| Quality | Assessment | Result |
|---|---|---|
| Testable | Functional ACs map to observable load, navigation, envelope fields, URL restore, mark-read page retention, ordering, control visibility | **Pass** |
| Implementation-neutral | ACs specify behavior and contract methods, not Blade markup or CSS | **Pass** |
| Aligned with review-decision | Matches RD-P9-001–008 and required contract inputs §7 | **Pass** |
| Scoped to P9 | No mark-all, filter, search, sort, realtime, badge, Request, or domain redesign requirements | **Pass** |
| Free of unrelated features | Explicit non_functional exclusions | **Pass** |

`test_implications` correctly stages successor replacement of P2 `listForRecipient(..., 50)` assertions and retention of the P8 `countUnread` inbox-page guard — lock/implementation concerns, not blockers.

---

## 7. Conflicts or Gaps

### Blocking

None.

### Non-blocking (acceptable for lock stage)

| Item | Severity | Notes |
|---|---|---|
| Contract `status: draft` | Low | Hygiene; may update to approved after this review; not required before lock drafting |
| YAML `read_operations` nesting | Low | `flat_list` / `paginated_list` appear as siblings of `read_operations` in indentation; semantic intent is unambiguous; lock may normalize structure if desired |
| Exact prev/next Persian labels / markup | Lock-stage | Explicitly deferred (RD-P9-010); contract notes lock pins |
| Allowed file enumeration | Lock-stage | Deferred appropriately (RD-P9-010 / OQ-P9-010) |

### Not conflicts

- Pinning method/DTO names (`listForRecipientPaginated`, `NotificationInboxListQueryDTO`, `PaginatedNotificationInboxListDTO`) is authorized contract-stage work (RD-P9-009).
- Superseding P5 reload transport only (current-page paginated refresh) is intentional and bounded — not a mark-read behavior reopen.

---

## Final Verdict

**`APPROVED_FOR_LOCK_DRAFTING`**

The P9 feature contract faithfully encodes the approved review-decision, aligns with restored feature-analysis and repo-inspection evidence, preserves predecessor boundaries, excludes out-of-scope capabilities, and maintains anti-leak architecture. No contract revision is required before implementation-lock drafting.

| Classification | Disposition |
|---|---|
| Blocking correction | None |
| Non-blocking clarification | Status hygiene; optional YAML nesting cleanup; lock-stage UI/file pins |
| Architecture blocker | None |
| Governance blocker | None |
| Scope leak | None |

Implementation remains **not authorized** until implementation lock approval completes.

---

## Next Governance Gate

| Field | Value |
|---|---|
| **Next gate** | `implementation-lock` |
| **Expected artifact** | `docs/ui/locks/notifications/notification-inbox-pagination.implementation-lock.yaml` (or repository-equivalent lock path) |
| **Lock must pin** | Allowed/forbidden files; prev/next UI pattern and Persian labels; test file list and AC mapping; successor replacement of P2 limit-50 inbox assertions; P8 `countUnread` guard retention; provider/binding updates if any |
| **Not authorized** | Source code, tests, lock-review, or implementation |

---

*This contract review authorizes implementation-lock drafting only. It does not authorize implementation, code changes, or test modifications. The prior `BLOCKED_MISSING_INPUT` review is superseded by this complete-input review.*
