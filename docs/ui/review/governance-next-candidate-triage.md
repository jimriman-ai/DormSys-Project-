# Governance Queue Triage — Next Candidate

## Document metadata

| Field | Value |
|---|---|
| **Triage date** | 2026-07-10 |
| **Mode** | Queue triage only — no implementation, contract, or lock authoring |
| **Prior closed feature** | P8 — `notification-inbox-unread-badge` (`IMPLEMENTED_VERIFIED`) |
| **Task context** | P3 governance re-intake `SUPERSEDED` (not reopened) |

---

## 1. Review Summary

This triage reviewed the full `docs/ui/` governance tree — analysis, review, contracts, locks, verification, closeout, closeouts, decisions, and reconciliation artifacts — cross-checked against current repository implementation.

The notification inbox successor chain (P2 → P8) is now **fully closed** through P8 closeout (`docs/ui/closeout/notifications/notification-inbox-unread-badge.implementation-closeout.md`, status `IMPLEMENTED_VERIFIED`). Delivered sequentially: read-only list (P2), mark-read (P5), deep-link (P6), layout navigation (P7), unread badge (P8).

The request-surface lane has P3 superseded/reconciled and P4 implementation delivered with conditional reconciliation closeout. Pre-P request baselines remain frozen or closeout-complete.

**`request-create-entrypoint-discoverability` (P3) remains superseded and is not reopened.** Re-intake decision `SUPERSEDED`: P3 was already delivered as `IMPLEMENTED_RECONCILED`; visible request-create entrypoints exist on the request list page. No further governance gate applies.

Among remaining deferred items evidenced in closed P2–P8 artifacts, **notification inbox pagination beyond the 50-item cap** is the highest-ranked open product candidate. It is not started in governance and requires a new cycle. **Mark-all-as-read** remains blocked on missing backend batch mutation capability.

---

## 2. Candidate Inventory

| Feature slug | Domain | Status | Artifact / readiness note |
|---|---|---|---|
| `notification-inbox-read-only` (P2) | notifications | **closed** | `IMPLEMENTED_RECONCILED`; reconciliation closeout |
| `request-create-entrypoint-discoverability` (P3) | requests | **superseded** | Re-intake `SUPERSEDED`; reconciliation `IMPLEMENTED_RECONCILED` |
| `request-list-filtering-sorting-pagination` (P4) | requests | **closed** | `IMPLEMENTED_WITH_VERIFICATION_BLOCKER`; implementation delivered; optional verification hygiene only |
| `notification-mark-read-mutation` (P5) | notifications | **closed** | Full chain; `IMPLEMENTED_RECONCILED` |
| `notification-inbox-deep-link-navigation` (P6) | notifications | **closed** | `IMPLEMENTED_VERIFIED`; closeout complete |
| `notification-inbox-layout-navigation` (P7) | notifications | **closed** | `P7_CLOSED_SUCCESSFULLY`; closeout 2026-07-10 |
| `notification-inbox-unread-badge` (P8) | notifications | **closed** | `IMPLEMENTED_VERIFIED`; verification + closeout 2026-07-10 |
| `notification-inbox-pagination` | notifications | **open** | Deferred in P2 contract; excluded in P5/P6/P7/P8; **no** `docs/ui/` analysis, contract, or downstream artifacts |
| `notification-mark-all-as-read` | notifications | **blocked** | Repeatedly excluded in P2/P5/P8; no `MarkAllNotificationsRead` backend contract in repository |
| `notification-badge-reactive-refresh` | notifications | **unclear** | Explicitly rejected/deferred in P8 v1 refresh policy; not a named backlog feature |
| `request-list` | requests | **closed** (frozen) | Approved contract + lock; baseline only |
| `request-show` | requests | **closed** (frozen) | Approved contract + lock; mutations excluded |
| `request-list-detail-navigation` | requests | **closed** | Closeout recorded 2026-07-08 |
| Request show workflow mutations | requests | **blocked** | Outside authorized UI backlog; frozen by `request-show` contract |
| Reporting / dashboard UI (spec11) | reporting | **blocked** | Planning-only; no `docs/ui/` artifacts; UI not authorized |
| P4 formal `implementation-verification.md` | requests | **stale** (hygiene) | Process follow-up from P4 reconciliation; not a product feature |

---

## 3. Exclusions

| Feature | Exclusion reason |
|---|---|
| `request-create-entrypoint-discoverability` (P3) | **Superseded** — re-intake `SUPERSEDED`; `IMPLEMENTED_RECONCILED`; problem absorbed by delivered entrypoints |
| `notification-inbox-read-only` (P2) | Closed reconciled baseline |
| `notification-mark-read-mutation` (P5) | Closed reconciled |
| `notification-inbox-deep-link-navigation` (P6) | Closed verified |
| `notification-inbox-layout-navigation` (P7) | Closed successfully |
| `notification-inbox-unread-badge` (P8) | Closed `IMPLEMENTED_VERIFIED` — just completed full lifecycle |
| `request-list-filtering-sorting-pagination` (P4) | Conditionally closed; implementation delivered; hygiene only, not next product feature |
| `request-list`, `request-show`, `request-list-detail-navigation` | Frozen or closeout-complete baselines |
| `notification-mark-all-as-read` | **Blocked** — no backend batch mutation contract inspected or implemented |
| Request show workflow mutations | Out of authorized backlog scope |
| Reporting UI (spec11) | Program not authorized for UI execution |
| P4 verification hygiene artifact | Process gap, not a governance-pipeline product candidate |
| P8 reactive badge refresh | Explicitly out of scope for P8 v1; not a separate governed feature slug |

---

## 4. Selected Next Candidate

| Field | Value |
|---|---|
| **Feature slug** | `notification-inbox-pagination` |
| **Working title** | P9 — Notification Inbox Pagination (beyond 50-item cap) |
| **Domain / category** | notifications / mixed presentation + read-model |
| **Current governance state** | **Open** — not started; no repo-inspection or downstream artifacts |
| **Expected classification** | `MIXED_UI_AND_READ_MODEL_GAP` (per P4 precedent and P2 contract language) |

### Why this is the correct next candidate now

1. **Successor-chain continuity** — P2 contract explicitly deferred pagination beyond the 50-item presentation cap. P5, P6, P7, and P8 contracts/locks/closeouts repeatedly excluded pagination. With layout nav (P7) and unread badge (P8) closed, pagination is the next explicitly deferred notification inbox gap in the reconstructed register.

2. **Repository-grounded deferral** — `NotificationInboxPage` still uses `LIST_LIMIT = 50`; `NotificationInboxReadContract::listForRecipient()` accepts `limit` only with no offset, cursor, or pagination metadata envelope (unlike P4's paginated request list pattern).

3. **No competing in-flight feature** — No other open candidate has partial governance artifacts or authorized implementation pending.

4. **Blocked alternatives rank lower** — Mark-all-as-read requires a new backend mutation contract before UI governance can begin. Request-show mutations and reporting UI are outside the authorized backlog.

5. **Does not reopen closed work** — Pagination is a new governance cycle superseding only P2's deferred pagination exclusion, analogous to how P7/P8 superseded specific P2 deferrals without amending closed deliveries.

6. **Not inferred from stale backlog cards** — Selection is from closed-artifact deferral language and repository state, not from `docs/ui/analysis/feature-next-candidate.md` alone (that document predates P7/P8 closeout).

---

## 5. Recommended Next Governance Gate

| Gate | **`repo-inspection`** |
|---|---|

### Why this is the earliest valid next step

No `docs/ui/analysis/notifications/notification-inbox-pagination.repo-inspection.md` (or equivalent) exists. Without repository evidence, feature-analysis, review-decision, and contract stages would rely on stale P2 assumptions or P4 analogy alone.

Expected artifact path:

`docs/ui/analysis/notifications/notification-inbox-pagination.repo-inspection.md`

Inspection focus:

- Confirm `NotificationInboxPage` fixed 50-item cap and `listForRecipient` signature (limit-only, no offset/cursor)
- Inventory whether any pagination UI, query, or test coverage exists in Notification module
- Compare Notification read contract to P4 paginated request-list precedent (`PaginatedRequestSummaryListDTO`, `forPage()`, URL state)
- Document P2/P5/P6/P7/P8 exclusion language and what pagination must not collide with
- Assess backend readiness gap (offset/cursor vs. in-memory slice anti-patterns)
- Identify test baseline and architecture-guard risks

Do **not** recommend feature-analysis, contract drafting, or implementation in this triage. Repo-inspection is the sole authorized next gate.

---

## 6. Risks / Ambiguity

| Item | Classification | Impact |
|---|---|---|
| Expected `MIXED_UI_AND_READ_MODEL_GAP` — likely needs read-contract extension, not UI-only | **Non-blocking uncertainty** | Shapes inspection conclusions; does not block starting repo-inspection |
| No offset/cursor in `NotificationInboxReadContract` today | **Non-blocking uncertainty** | Core gap to document; P4 provides pattern reference only |
| `docs/ui/analysis/feature-next-candidate.md` predates P7/P8 closeout | **Stale artifact risk** | Ranked pagination as #3 when badge was open; superseded by current closeout state |
| `docs/ui/analysis/feature-status-repository-inspection.md` undercounts artifacts | **Stale artifact risk** | Non-blocking; triage used live `docs/ui/` inventory |
| P3 governance re-intake artifact path cited in task context not found in repo | **Non-blocking uncertainty** | P3 reconciliation closeout + task `SUPERSEDED` decision sufficient to exclude P3 |
| Mark-all-as-read may appear attractive but lacks backend contract | **Real blocker** for that alternative only | Does not block pagination repo-inspection |
| P4 `IMPLEMENTED_WITH_VERIFICATION_BLOCKER` may confuse queue position | **Non-blocking uncertainty** | P4 implementation is delivered; hygiene is parallel, not queue-competing |
| Pagination page-size / cursor semantics undecided | **Non-blocking uncertainty** | Inspection and analysis stages must resolve; not a triage blocker |

No repository ambiguity prevents starting repo-inspection for `notification-inbox-pagination`. No stale governance contradiction requires `TRIAGE_BLOCKED_BY_STALE_GOVERNANCE` or `TRIAGE_BLOCKED_BY_REPOSITORY_AMBIGUITY`.

---

## 7. Final Decision

**`NEXT_CANDIDATE_SELECTED`**

| Field | Value |
|---|---|
| **Next candidate** | `notification-inbox-pagination` (P9 — Notification Inbox Pagination) |
| **Next governance gate** | `repo-inspection` |
| **Stop boundary** | Do not proceed to feature-analysis, contract, lock, implementation, verification, or closeout within this triage |

---

*This artifact selects the next governance target and gate only. It does not authorize implementation, contract drafting, lock drafting, or code changes.*
