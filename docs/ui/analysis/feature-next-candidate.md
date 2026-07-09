# DormSys UI — Next Feature Candidate Analysis

## Document metadata

| Field | Value |
|---|---|
| Analysis date | 2026-07-09 |
| Authoring mode | Feature governance planner (repository-evidence only) |
| Stated completed predecessor (task input) | **P5 — Notification Mark-Read Mutation** (`IMPLEMENTED_RECONCILED`) |
| Repository position (additional evidence) | **P6 — Notification Inbox Deep Link Navigation** (`IMPLEMENTED_VERIFIED`) |
| Output type | Next-candidate recommendation (no contract, lock, or implementation) |

---

## 1. Current position

### 1.1 Backlog register source

No standalone artifact titled **DormSys Feature Backlog Priority Register** was found in the repository at analysis time.

The effective backlog was reconstructed from:

- P-numbered feature titles in `docs/ui/analysis/`, `docs/ui/closeouts/`, `docs/ui/closeout/`, `docs/ui/decisions/`, and `docs/ui/contracts/`
- Explicit deferred / excluded scope language in P2, P5, and P6 governance artifacts
- Closeout directives to proceed to the next backlog feature (`request-list-filtering-sorting-pagination.reconciliation.md`)
- Pre-P baseline request surfaces with approved contracts and closeouts

### 1.2 Reconstructed priority register (P2–P6)

| Priority | Feature | Module | Closeout / status | Governance completeness |
|---|---|---|---|---|
| **P2** | Notification Inbox (Read-Only) | Notification | `IMPLEMENTED_RECONCILED` — CLOSED | Reconciled without formal contract promotion; draft contract/lock remain in repo |
| **P3** | Request Create Entrypoint Discoverability | Request | `IMPLEMENTED_RECONCILED` | No contract/lock; `DIRECT_UI_IMPLEMENTATION_AUTHORIZED` path |
| **P4** | Request List Filtering / Sorting / Pagination | Request | `IMPLEMENTED_WITH_VERIFICATION_BLOCKER` | Contract + lock approved; implementation delivered; formal `implementation-verification.md` absent |
| **P5** | Notification Mark-Read Mutation | Notification | `IMPLEMENTED_RECONCILED` — CLOSED | Full governance chain through verification |
| **P6** | Notification Inbox Deep Link Navigation | Notification | `IMPLEMENTED_VERIFIED` — CLOSED | Full governance chain through closeout |

### 1.3 Pre-P baseline (governance-complete, not P-numbered)

| Feature | Status | Notes |
|---|---|---|
| Request List | Approved contract + lock; no closeout | Frozen baseline |
| Request Show | Approved contract + lock (`implementation_authorized: true`); no closeout | Read-only; mutations explicitly excluded |
| Request List Detail Navigation | Closeout `recorded` (2026-07-08) | Successor to frozen list navigation exclusion |

### 1.4 Repository position vs. task input

**Task input** names P5 as the completed predecessor. **Repository evidence** additionally shows P6 closed:

| Evidence | Finding |
|---|---|
| `docs/ui/closeout/notifications/notification-inbox-deep-link-navigation.closeout.md` | Status `IMPLEMENTED_VERIFIED`; full governance chain complete |
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | `request_show_url` mapping with frozen `requests.show` binding |
| `docs/ui/verification/notifications/notification-inbox-deep-link-navigation.verification.md` | `IMPLEMENTED_WITHIN_LOCK`; 25 P6-related tests passing |

This analysis treats **P6 as closed for elimination purposes** and evaluates the next candidate from the **post-P6 deferred pool**. P5 remains closed and is not reopened.

### 1.5 Current implementation phase

The active delivery phase is **notification inbox discoverability and presentation successors** atop spec09 backend capability, following request-surface work (P3, P4 conditionally closed):

- Request list/show/create surfaces are largely operational (P3 discoverability, P4 filter/sort/paginate delivered).
- Notification inbox has read (P2), mark-read (P5), and `requests.show` deep-link (P6).
- **Still absent:** global layout navigation to inbox, unread badge, pagination beyond 50, mark-all-as-read — all explicitly deferred or excluded in P2/P5/P6 artifacts.

P5 and P6 closeouts state deferred items (mark-all, badge, layout nav, pagination) require a **separate governance cycle**. Deep-link (P6) is now closed; layout nav is the highest-ranked remaining deferred item by risk and scope.

### 1.6 Non-reopen rule check

No evidence requires reopening P2–P6 or pre-P baselines. **Optional P4 governance hygiene** (missing formal `implementation-verification.md`) is process follow-up, not a new product feature candidate.

---

## 2. Evaluated candidates

Candidates are drawn only from **explicitly deferred or excluded** scope in closed governance artifacts — not invented features.

### Candidate A — Notification Inbox Deep Link Navigation (P6)

| Dimension | Assessment |
|---|---|
| **Backlog reference** | P6 — deferred from P2/P5; governed as successor overlay |
| **Current status** | **CLOSED** — `IMPLEMENTED_VERIFIED` |
| **Existing governance evidence** | Repo inspection, feature analysis, review decision (`APPROVED_FOR_CONTRACT`), contract, lock, verification, closeout — all present under `docs/ui/` |
| **Current repository evidence** | `NotificationInboxPage` maps `request_show_url`; Blade renders **مشاهده** affordance; `NotificationInboxUiFlowTest` covers P6 behavior |
| **Missing capability** | None for approved v1 scope |
| **Risk level** | N/A — closed |
| **Governance complexity** | N/A — closed |
| **Recommended next action** | **Exclude from open candidates** — elimination context only |

---

### Candidate B — Notification Inbox Layout Navigation (Discoverability)

| Dimension | Assessment |
|---|---|
| **Backlog reference** | P7 (inferred next sequential successor); explicitly deferred in P2 lock, excluded in P5/P6 contracts and locks |
| **Current status** | Not started — no `docs/ui/` analysis, contract, lock, decision, or closeout for layout navigation |
| **Existing governance evidence** | P2 lock: `layout navigation link (deferred; not authorized by this lock)`; P5/P6 `scope.out_of_scope`: layout nav; P3 precedent (`DIRECT_UI_IMPLEMENTATION_AUTHORIZED`) for discoverability-only UI on request surface |
| **Current repository evidence** | `resources/views/components/layouts/app.blade.php` nav links only to `requests.index` (درخواست‌ها); no `notifications.index` link; direct URL `/notifications` works per `notification-inbox-read-only.repo-inspection.md` and P6 closeout |
| **Missing capability** | Presentation only: add `notifications.index` link to shared layout header with active-route styling consistent with existing `requests.*` pattern |
| **Backend readiness** | **High** — `notifications.index` route registered; inbox page operational |
| **Risk level** | **Low** — smallest UI diff among open candidates; shared layout blast radius wider than inbox-only changes but no mutation or read-model change |
| **Governance complexity** | **Low** — analogous to P3 discoverability; contract may be optional after inspection, but layout is cross-module and warrants explicit governance |
| **Recommended next action** | **repo-inspection required** |

---

### Candidate C — Notification Unread Badge (`countUnread`)

| Dimension | Assessment |
|---|---|
| **Backlog reference** | Deferred post-P2/P5/P6 |
| **Current status** | Not started |
| **Existing governance evidence** | P2/P5/P6 exclude `countUnread` and badge surfaces; P5 lock forbids `countUnread` in inbox page architecture guard |
| **Current repository evidence** | `NotificationInboxReadContract::countUnread()` and `NotificationRepository::countUnread()` implemented; layout has no badge; inbox page does not consume count |
| **Missing capability** | Presentation: consume `countUnread` in layout or inbox header; refresh semantics undecided (page load only vs. post mark-read) |
| **Backend readiness** | **High** for count query; **medium** for refresh contract and capability-delivery shape (UI Anti-Leak) |
| **Risk level** | **Medium** — global layout change; stale-count UX; may require Livewire layout component or per-page refresh strategy |
| **Governance complexity** | **Medium** — badge on global nav touches all pages; scope split from inbox-only successors |
| **Recommended next action** | **repo-inspection required** (then feature-analysis) |

---

### Candidate D — Notification Mark-All-as-Read

| Dimension | Assessment |
|---|---|
| **Backlog reference** | Explicitly excluded from P5; mentioned as future separate feature |
| **Current status** | **Blocked** |
| **Existing governance evidence** | P2/P5 contracts and locks repeatedly exclude mark-all; P5 review decision: no batch backend contract inspected |
| **Current repository evidence** | No `MarkAllNotificationsReadContract` or batch mutation path found |
| **Missing capability** | **Backend gap** — new Application use case, authorization, idempotency decisions required before UI |
| **Backend readiness** | **Low / absent** |
| **Risk level** | **High** |
| **Governance complexity** | **High** — new mutation contract + MPEP posture |
| **Recommended next action** | **Not ready** — backend capability decision required before any UI governance |

---

### Candidate E — Notification Inbox Pagination (beyond 50-item cap)

| Dimension | Assessment |
|---|---|
| **Backlog reference** | Deferred in P2 contract |
| **Current status** | Not started |
| **Existing governance evidence** | P2 contract: pagination beyond 50 deferred; P5/P6 exclude pagination |
| **Current repository evidence** | `NotificationInboxPage` uses fixed `LIST_LIMIT = 50`; `listForRecipient` accepts limit but no page/offset contract |
| **Missing capability** | **Mixed** — likely read-contract/query extension (offset/cursor) plus UI controls |
| **Backend readiness** | **Partial** — limit exists; paginated read envelope pattern exists on Request module (P4 precedent) but not on Notification read contract |
| **Risk level** | **Medium–high** — crosses presentation + read-model boundary (similar to P4 classification) |
| **Governance complexity** | **High** — full contract → lock → implementation path expected |
| **Recommended next action** | **repo-inspection required** (expect `MIXED_UI_AND_READ_MODEL_GAP` classification) |

---

### Candidate F — P4 formal implementation verification (governance hygiene)

| Dimension | Assessment |
|---|---|
| **Backlog reference** | Open follow-up from P4 closeout |
| **Current status** | Process gap only |
| **Existing governance evidence** | `request-list-filtering-sorting-pagination.reconciliation.md` records missing `implementation-verification.md`; targeted P4 tests passed (9/9) |
| **Current repository evidence** | P4 implementation present in `RequestListPage`, `RequestReadQuery`, and related DTOs |
| **Missing capability** | None — process artifact only |
| **Risk level** | **None** (product) |
| **Governance complexity** | **Low** |
| **Recommended next action** | Parallel hygiene — **not** the next product feature |

---

### Candidate G — Request Show workflow mutations (submit / cancel / approve)

| Dimension | Assessment |
|---|---|
| **Backlog reference** | Not in reconstructed P2–P6 backlog |
| **Current status** | **Out of backlog** — frozen |
| **Existing governance evidence** | `request-show.feature-contract.yaml` explicitly excludes submit, cancel, approve, mutation forms |
| **Current repository evidence** | Show surface read-only per approved contract |
| **Missing capability** | Backend workflow + capability delivery not in current UI pilot scope |
| **Backend readiness** | Unknown for UI consumption; not a deferred P2–P6 item |
| **Risk level** | **High** |
| **Governance complexity** | **High** — new feature family |
| **Recommended next action** | **Exclude** — would invent scope outside reconstructed backlog |

---

### Candidate H — Reporting / dashboard UI (spec11)

| Dimension | Assessment |
|---|---|
| **Backlog reference** | spec11 — not P-numbered in UI backlog |
| **Current status** | **Blocked** — planning-only |
| **Existing governance evidence** | `specs/011-reporting-projections/spec.md`: UI delivery conceptual only; P4+ HALT; no `docs/ui/` artifacts |
| **Current repository evidence** | No UI presentation artifacts for reporting |
| **Missing capability** | Design approval, P2 authorization, implementation authorization |
| **Backend readiness** | **Not applicable** for UI phase |
| **Risk level** | **High** |
| **Governance complexity** | **Very high** |
| **Recommended next action** | **Exclude** — program not authorized for UI execution |

---

## 3. Candidate ranking

Ranked for **next product feature** selection (closed P6 and hygiene item F excluded from ranking):

| Rank | Candidate | Score rationale |
|---|---|---|
| **1** | **B — Notification Inbox Layout Navigation** | Highest backend readiness among open candidates; narrowest UI-only scope; lowest risk; lowest governance complexity; strongest deferral evidence (P2 lock through P6 exclusions); completes inbox discoverability gap noted since P2 |
| **2** | **C — Notification Unread Badge** | Backend count ready; higher UX/governance questions on global layout and refresh semantics |
| **3** | **E — Notification Inbox Pagination** | Valid deferred P2 item but mixed read-model gap — higher cost than B/C |
| **4** | **D — Mark-All-as-Read** | Blocked on missing backend batch contract |
| **—** | **A** | Closed (P6) — elimination context |
| **—** | **G, H** | Outside authorized backlog / frozen scope |

---

## 4. Recommended next feature

### **P7 — Notification Inbox Layout Navigation (Discoverability)** (working title)

**Feature code (proposed):** `notification-inbox-layout-navigation`

**Classification (expected):** `discoverability` / presentation-only (P3 precedent)

**Scope sketch (for governance only — not authorized):**

- Add `notifications.index` navigation link to `resources/views/components/layouts/app.blade.php` header nav
- Active-route styling consistent with existing `requests.*` pattern
- No inbox behavior change, no badge, no countUnread, no mark-read or deep-link changes
- No new routes or backend contract changes

---

## 5. Reasoning

1. **Backlog continuity:** P2 lock deferred layout navigation; P5, P6 contracts and locks explicitly excluded it. With P6 (deep-link) closed, layout nav is the next explicitly deferred notification presentation gap in the reconstructed register.

2. **Repository-grounded position:** Although task input names P5 as completed, repository evidence shows P6 `IMPLEMENTED_VERIFIED`. Ranking uses the post-P6 deferred pool; recommending P6 would contradict closeout evidence.

3. **Backend authority preserved:** `notifications.index` already exists and is tested. UI adds a transport link only — no business semantics, capability inference, or read-model change.

4. **Lowest risk among open candidates:** Unlike Candidate E, no read-contract extension is evidenced as required. Unlike Candidate D, no new mutation backend is needed. Unlike Candidate C, no count refresh semantics or global badge UX decisions are required for a minimal v1 link.

5. **P3 precedent:** Request create discoverability was authorized as `DIRECT_UI_IMPLEMENTATION_AUTHORIZED` after inspection and analysis. Layout navigation is structurally similar (existing route, missing visible entrypoint), though shared layout scope may still warrant contract review after inspection.

6. **Phase alignment:** Notification inbox functional loop (read → mark-read → deep-link) is closed through P6. Discoverability (layout nav) is the remaining access gap preventing users from finding the inbox without direct URL knowledge.

7. **No reopening of closed features:** P7 as a narrow layout affordance does not amend P2/P5/P6 artifacts or reintroduce excluded badge, mark-all, or pagination scope.

8. **P4 verification hygiene** should proceed in parallel but does not block P7 governance.

---

## 6. Required next governance step

| Step | Value |
|---|---|
| **Immediate next governance step** | **repo-inspection required** |
| **Artifact path (expected)** | `docs/ui/analysis/notifications/notification-inbox-layout-navigation.repo-inspection.md` |
| **Inspection focus** | Confirm `app.blade.php` has no `notifications.index` link; verify route name and middleware parity with P2/P6 inbox tests; inventory all layout/nav surfaces that could host the link; confirm P2/P5/P6 test guards do not forbid layout nav changes; document P3 discoverability precedent applicability |
| **Subsequent steps (ordered, not authorized now)** | feature-analysis required → review decision → contract may be optional per P3 precedent, or `contract required` if shared-layout blast radius warrants formal scope → lock (if contract required) → implementation path |
| **Parallel optional hygiene** | Add `docs/ui/verification/requests/request-list-filtering-sorting-pagination.implementation-verification.md` to advance P4 from `IMPLEMENTED_WITH_VERIFICATION_BLOCKER` toward `IMPLEMENTED_RECONCILED` |

### Allowed recommendation values applied

| Stage | Applies to P7 |
|---|---|
| repo-inspection required | **Yes — now** |
| feature-analysis required | Next after inspection |
| contract required | Conditional — after analysis; P3 precedent may authorize direct UI path |
| ready for implementation path | **No** — governance chain not started |

---

## 7. Decision boundary

| Boundary | Statement |
|---|---|
| This document | Selects the next feature candidate only |
| Does not authorize | Implementation, contract drafting, or lock drafting |
| Does not bypass | Repo inspection → feature analysis → review decision → contract (if required) → lock (if required) → implementation |
| Does not reopen | P2, P5, P6, or pre-P baselines |
| Does not invent | Features outside reconstructed deferred/excluded backlog |

---

## Appendix — Evidence index

| Topic | Primary artifacts |
|---|---|
| P5 closed | `docs/ui/closeouts/notifications/notification-mark-read-mutation.reconciliation.md` |
| P6 closed | `docs/ui/closeout/notifications/notification-inbox-deep-link-navigation.closeout.md` |
| P5/P6 deferred items | `docs/ui/contracts/notifications/notification-mark-read-mutation.feature-contract.yaml` (`scope.out_of_scope`); P6 contract exclusions |
| P2 layout nav deferral | `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` |
| Layout nav gap | `resources/views/components/layouts/app.blade.php`; `docs/ui/analysis/notifications/notification-inbox-read-only.repo-inspection.md` |
| P3 discoverability precedent | `docs/ui/decisions/requests/request-create-entrypoint-discoverability.review-decision.md` |
| P4 next-feature directive | `docs/ui/closeouts/requests/request-list-filtering-sorting-pagination.reconciliation.md` |
| countUnread backend | `NotificationInboxReadContract.php`, `notification-inbox-read-only.repo-inspection.md` |
| spec09 | `specs/009-notification-delivery/spec.md` |

---

*This document recommends the next governance target only. It does not authorize implementation, contract drafting, or lock drafting.*

Recommended next governance step: repo-inspection required
