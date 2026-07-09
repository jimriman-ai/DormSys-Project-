# P5 — Notification Mark-Read Mutation — Contract Review

## Feature

- **Feature code:** `notification-mark-read-mutation`
- **Feature title:** P5 — Notification Mark-Read Mutation
- **Domain area:** notifications

## Review date

2026-07-09

## Inputs reviewed

- `docs/ui/contracts/notifications/notification-mark-read-mutation.feature-contract.yaml` (v0.1.0, status: draft) — primary
- `docs/ui/decisions/notifications/notification-mark-read-mutation.review-decision.md`
- `docs/ui/analysis/notifications/notification-mark-read-mutation.feature-analysis.md`
- `docs/ui/analysis/notifications/notification-mark-read-mutation.repo-inspection.md`
- `docs/ui/closeouts/notifications/notification-inbox-read-only.reconciliation.md`
- `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` (predecessor exclusions)
- `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` (predecessor forbidden changes)

## Contract summary

The drafted contract defines P5 as a **successor-feature** overlay on the existing `NotificationInboxPage` surface. It authorizes **single-item, per-row mark-read** for unread notifications via delegation to the existing `MarkNotificationReadContract`, with **post-success full list reload** from `NotificationInboxReadContract`, **recipient-scoped auth-only** (MPEP gate deferred for v1), and **is_read-based affordance gating** framed as presentation-only. It explicitly excludes mark-all, batch actions, broader notification management, backend/schema changes, and new API routes. One open question (affordance form and Persian copy) is deferred to the implementation lock.

## Findings

### Alignment with review decision

| Review-decision requirement | Contract evidence |
|---|---|
| Per-row mark-read on inbox via `MarkNotificationReadContract` | `scope.in_scope`, `mutations`, `acceptance_criteria.functional` |
| Exclude mark-all, API route, backend changes, badge/nav | `scope.out_of_scope`, `technical_boundaries`, `dependencies.does_not_require` |
| Recipient-scoped auth-only; MPEP deferral | `authorization.model`, `authorization.mpep_deferral` |
| is_read gating vs capability flag decision | `view_model.affordance_gating_rule`, `notes` (no `can_mark_read` in v1) |
| Post-mutation refresh behavior | `interaction_boundaries.post_mutation_refresh` |
| P2 supersession without reopening closeout | `baseline_contract`, `architecture_constraints.p2_supersession`, `governance.frozen_boundaries` |
| Test/guard obligation updates | `test_implications.required_regression_updates` |

The contract satisfies all mandatory items from the review decision without scope expansion beyond P5.

### Required review questions

| # | Question | Result |
|---|---|---|
| 1 | Single-item mark-read only? | **Pass** — `mutations.model: single_item_per_row`; repeated exclusions of mark-all/batch |
| 2 | Excludes mark-all, batch, delete/archive/dismiss, broader management? | **Pass** — `scope.out_of_scope` lines 73–85 |
| 3 | Relies on existing backend; no backend redesign? | **Pass** — `existing_capabilities.reused`, `technical_boundaries.backend`, forbidden backend changes |
| 4 | Authorization recipient-scoped; no UI policy ownership? | **Pass** — `authorization.ui_boundary`, `new_ui_authorization_model: false` |
| 5 | Avoids locking implementation design prematurely? | **Pass** — affordance form/copy deferred; error wiring noted as lock detail |
| 6 | Full-list reload acceptable at contract level? | **Pass** — see Special attention below |
| 7 | P2 guard/test updates as governed follow-on? | **Pass** — explicit in `test_implications`; not silent drift |
| 8 | Remaining open questions lock-deferrable? | **Pass** — single item: visual affordance and label copy |

### Special attention points

| Topic | Assessment |
|---|---|
| **`MarkNotificationReadContract::markRead` at contract level** | **Acceptable.** Repository inspection confirms this is the sole existing mutation port; P2 contract and `FEATURE-CONTRACT-GUIDE` expect explicit action-to-backend mapping. Naming the Application contract is governance-safe here and prevents implementers from inventing alternate write paths. |
| **Full list reload after success** | **Acceptable behavioral commitment.** It enforces backend-authoritative display refresh and forbids optimistic local `is_read` mutation (`anti_leak_boundaries`, `behavioral_rules.state_authority`). It does not dictate Livewire method structure or caching — only the post-success data authority. Appropriate for contract level. |
| **MPEP gate deferred for v1** | **Sufficient and governance-safe.** `authorization.mpep_deferral.status: accepted_for_p5_v1` documents explicit acceptance of `PendingMutationAuthorizationRegistry` pending status while backend recipient scoping remains authoritative. |
| **is_read affordance gating** | **Clearly framed as presentation-only.** `affordance_gating_rule` separates visibility gating from authorization authority and mutation legality. Consistent with UI Anti-Leak when backend already returns `isRead` on listed rows. |

## Scope validation

| Check | Result |
|---|---|
| MVF discipline (one mutation capability) | Pass |
| No mark-all or bulk expansion paths | Pass |
| No notification management creep | Pass |
| No P2 feature reopening | Pass — successor overlay; closeout remains CLOSED |
| No backend/DTO/schema expansion | Pass |
| No layout nav, badge, detail, deep-link scope bleed | Pass |
| Single surface (`NotificationInboxPage`) | Pass |
| Single route (`notifications.index`) | Pass |

**Scope leaks:** None identified.

## Governance validation

| Check | Result |
|---|---|
| P2 separation preserved | Pass — `baseline_contract.note` limits supersession to mark-read UI prohibition only |
| Backend authority intact | Pass — mutation and refresh both delegate to Application contracts |
| UI Anti-Leak alignment | Pass — `anti_leak_boundaries`, single delegation, no orchestration |
| Authorization not mirrored in UI | Pass |
| Test obligations explicit | Pass — new UI tests + P2 guard replacement scoped |
| Predecessor backend tests protected | Pass — `forbidden_test_changes` |
| Lock prerequisite stated | Pass — `governance.prerequisites`, `definition_of_done` |
| Review decision fully addressed | Pass |

**Architecture / governance conflicts:** None blocking.

## Risks / concerns

### Non-blocking (lock drafting notes)

1. **Affordance form and copy** — Correctly deferred to lock via `open_questions.mark_read_affordance_form`. Lock must pin control type and Persian label without expanding mutation scope.

2. **Implementation pattern references** — Contract mentions `HandlesUiMutationFeedback`, `wire:click`, and Livewire method injection as precedents or equivalents. These are appropriately qualified (`or equivalent`, lock detail for error wiring) and do not block approval.

3. **Contract lifecycle status** — Artifact remains `status: draft`. Formal approval of this contract review should update contract status to `approved` as part of governance hygiene before or concurrent with lock drafting (repository convention per P4 cycle).

4. **`mutation_in_progress` UI state** — Permitted in `state_separation.ui` without prescribing UX. Lock may pin per-row loading behavior if needed; not a contract gap.

### Blocking

**None.**

## Verdict

**`APPROVED_FOR_LOCK_DRAFTING`**

The contract is narrow, explicit, governance-safe, and aligned with the review decision. It is sufficient to authorize P5 implementation-lock drafting without reopening behavioral scope or P2 closeout.

`REVISION_REQUIRED` is not warranted: no material ambiguity, scope leak, or over-constraining implementation detail was identified that would block lock drafting.

`INSUFFICIENT_CLARITY` is not warranted: fundamental scope, authorization stance, mutation boundary, and P2 relationship are resolved in contract text.

## Required revisions

**None.**

No blocking contract edits required before implementation-lock drafting.

### Optional non-blocking hygiene (not required for verdict)

- Update contract `status` from `draft` to `approved` when this review is accepted in the governance workflow.

## Next step

**Draft P5 implementation lock**

Create `docs/ui/locks/notifications/notification-mark-read-mutation.implementation-lock.yaml` (or repository-equivalent lock artifact) to pin:

- Governed Livewire action name and file boundaries
- Affordance form and Persian label copy per `open_questions.mark_read_affordance_form`
- Error feedback wiring pattern
- P2 architecture guard replacement obligations
- Allowed/forbidden file and test changes within P5 scope
- Explicit supersession of P2 lock mark-read prohibitions within P5 scope only

Do not implement code in this step.

## Rationale

1. **Review-decision obligations are fully encoded.** The contract resolves every item the review decision required at contract level: single-item scope, exclusions, backend reuse, authorization stance, refresh model, P2 supersession, and test implications.

2. **P2 remains closed.** The contract treats P5 as a successor feature with bounded supersession language. It does not amend P2 artifacts or imply P2 scope expansion.

3. **Backend authority is preserved.** Mutation delegates to one Application contract; display refresh reloads from the read contract; optimistic local state mutation is forbidden. UI does not own authorization or business capability.

4. **Contract specificity is appropriate, not excessive.** Concrete references to `MarkNotificationReadContract` and `NotificationInboxReadContract` match repository reality and project contract conventions. Remaining visual/wiring choices are correctly deferred to the lock.

5. **MVF discipline holds.** One governed mutation on one surface with explicit exclusions and a single minor open question suitable for lock resolution. No fundamental ambiguity remains.
