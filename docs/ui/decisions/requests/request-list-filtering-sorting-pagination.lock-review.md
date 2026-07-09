# Request List Filtering / Sorting / Pagination — Lock Review

## Feature

P4 — Request List Filtering / Sorting / Pagination

## Lock Reviewed

- `docs/ui/locks/requests/request-list-filtering-sorting-pagination.implementation-lock.yaml` (v0.1.0, status: approved)

## Reference Inputs

- `docs/ui/contracts/requests/request-list-filtering-sorting-pagination.feature-contract.yaml` (v0.1.1, status: draft)
- `docs/ui/decisions/requests/request-list-filtering-sorting-pagination.contract-review.md` (verdict: `APPROVED_FOR_LOCK_DRAFTING`)
- `docs/ui/decisions/requests/request-list-filtering-sorting-pagination.review-decision.md`
- Predecessor artifacts:
  - `docs/ui/contracts/requests/request-list.feature-contract.yaml` (v1.0.2, approved)
  - `docs/ui/contracts/requests/request-list.implementation-lock.yaml` (v1.0.2, authorized)
  - `docs/ui/locks/requests/request-list-detail-navigation.implementation-lock.yaml` (v0.1.0, approved)

## Verdict

**APPROVED_FOR_IMPLEMENTATION**

## Rationale

The draft implementation lock is narrow, enforceable, and aligned with P4 contract v0.1.1. It authorizes only the web request-list surface, backend paginated read extension, supporting DTOs, and directly affected tests. Forbidden scope, API preservation, backend-authoritative query semantics, URL/state boundaries, pagination cardinality change, empty-state split, and predecessor supersession are all explicitly captured.

The lock correctly supersedes predecessor `filter_list`, `sort_list`, and `paginate_list` only within P4 scope. Open implementation choices are limited to delivery packaging and method naming and do not reopen contract behavior.

No over-scope or architectural conflict was identified in the lock itself.

## Review Question Assessment

| # | Question | Result |
|---|---|---|
| 1 | Lock implements approved P4 contract scope | Pass — in/out of scope mirrors contract |
| 2 | Allowed actions limited to P4 capabilities | Pass — status filter, single-field sort, fixed pagination, URL sync, page reset, empty states, refresh |
| 3 | Forbidden actions complete | Pass — search, advanced filters, API/export/dashboard/mutations/per-page/cursor/multi-sort/client-side authority blocked |
| 4 | Allowed surfaces narrow enough | Pass — list UI, P4 read path, supporting DTOs, targeted tests only |
| 5 | `listByEmployee` / `indexMine` preserved | Pass — explicit query_boundary and forbidden surfaces |
| 6 | Predecessor supersession scoped correctly | Pass — only `filter_list`, `sort_list`, `paginate_list` within P4 scope |
| 7 | Open implementation choices safe | Pass — envelope metadata vs separate read output; authority rules fixed |
| 8 | `coding_authorized` may become true | Conditional pass — see coding authorization below |

## Contract Alignment

| Contract area | Lock coverage |
|---|---|
| Web-only surface (`requests.index`) | `scope.in_scope`, `allowed_surfaces.presentation` |
| Status-only exact filter | `filter_boundary`, `allowed_actions.apply_status_filter` |
| Backend-supplied status options | `read_backend_authoritative_status_options`, `filter_boundary.status_options` |
| Single-field sort allowlist | `sort_boundary` matches contract fields and `created_at` mapping for `submitted_at` |
| Fixed page size 15 | `pagination_boundary.per_page: 15` |
| URL params `status`, `sort`, `dir`, `page` | `url_boundary` with coercion rules |
| Page reset on filter/sort change | `reset_page_on_filter_or_sort_change`, `state_boundary.reset_rules` |
| Global vs filtered empty states | `render_global_empty_state`, `render_filtered_empty_state` |
| Pre-P4 full `get()` vs P4 paginated default | `pagination_boundary.pre_p4_baseline`, `p4_default_load` |
| `listByEmployee` unchanged for API | `query_boundary.required`, `forbidden_actions.listByEmployee_behavior_change` |
| Test implications | `test_boundary` matches contract |

## Blocking Issues

**None.**

The lock does not require revision before implementation authorization.

## Coding Authorization

**Coding may proceed only after both governance preconditions are satisfied:**

1. P4 feature contract v0.1.1 status is advanced from `draft` to `approved`.
2. This implementation lock status is advanced from `draft` to `approved` and `approval_gate.coding_authorized` is set to `true`.

At lock-review time:

- Contract status remains `draft`.
- Lock status remains `draft` with `coding_authorized: false`.

Therefore: **lock review approves the lock for implementation**, but **coding must not start until contract approval and lock approval are both recorded**.

## Non-blocking Implementation Notes

1. **Status option delivery** — Implementer may choose `paginated_read_envelope_metadata` or `separate_backend_read_output` per `open_implementation_choices`; option authority rules are already fixed by contract and lock.

2. **Read method naming** — Pin exact `RequestReadContract` / `RequestReadQueryPort` method name(s) in code without expanding criteria beyond the contract envelope.

3. **Row mapping** — Existing `RequestListPage` uses local `mapContractRow()` with `RequestApiResponseFactory::serializeSummary()`; keep DTO-to-row mapping in allowed presentation files unless a lock amendment is explicitly requested.

4. **Provider binding** — `app/Modules/Request/Infrastructure/Providers/RequestServiceProvider.php` is the correct module binding location per repository layout; `RequestPresentationServiceProvider` is not required for P4 read-path binding.

5. **Unit tests** — `tests/Unit/Modules/Request/**` is allowed only if strictly necessary for P4 query behavior; prefer feature tests where sufficient.

6. **Predecessor files** — Do not edit predecessor contract or lock files during implementation; supersession is behavioral via this lock only.

## Scope / Architecture Check

| Check | Result |
|---|---|
| Scope leak into search, export, dashboard, API redesign | None |
| Client-side authoritative filter/sort/pagination | Explicitly forbidden |
| Domain mutation or workflow changes | Forbidden |
| Request Show / create / API controller changes | Forbidden surfaces |
| Cross-module or auth expansion | Forbidden |

## Recommended Next Steps

1. Advance P4 feature contract status to `approved`.
2. Advance this lock status to `approved` and set `coding_authorized: true`.
3. Begin P4 implementation strictly within allowed surfaces and governed actions.
