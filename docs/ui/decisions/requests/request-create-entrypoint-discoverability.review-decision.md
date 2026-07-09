# Request Create Entrypoint Discoverability — Review Decision

## Feature

P3 — Request Create Entrypoint Discoverability

## Inputs Reviewed

- `docs/ui/analysis/requests/request-create-entrypoint-discoverability.repo-inspection.md`
- `docs/ui/analysis/requests/request-create-entrypoint-discoverability.feature-analysis.md`

## Decision Summary

Status: REVIEWED
Disposition: DIRECT_UI_IMPLEMENTATION_AUTHORIZED
Contract: NOT_REQUIRED
Lock: NOT_REQUIRED

This feature is not ready for contract, lock, or implementation authorization yet.

The reviewed evidence consistently indicates a discoverability gap around request creation from inspected UI surfaces. However, the current analysis set contains a material repository-evidence inconsistency about whether the web create route and create page are present in the codebase. That inconsistency must be resolved before governance can classify this as either:

1. a presentation-only discoverability gap, or
2. a broader implementation gap involving missing web route/page exposure.

## What Is Confirmed

The reviewed artifacts support the following points:

- Request creation capability exists at backend/application level.
- Request creation is tested at backend level.
- The inspected request list surface does not expose an obvious create entrypoint.
- Inspected navigation/common UI surfaces do not show a discoverable entrypoint to initiate request creation.
- No existing P3 governance artifacts were found for this feature.

## Evidence Basis

### Confirmed backend/application readiness

Feature analysis records:

- Web route `requests.create` mapped to `GET /requests/create` and `RequestCreatePage` ([request-create-entrypoint-discoverability.feature-analysis.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.feature-analysis.md%3A23))
- Existing create route presence ([request-create-entrypoint-discoverability.feature-analysis.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.feature-analysis.md%3A66))
- Existing `RequestCreatePage` Livewire component presence ([request-create-entrypoint-discoverability.feature-analysis.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.feature-analysis.md%3A66))
- Existing create capability via route, page, action, API, and tests ([request-create-entrypoint-discoverability.feature-analysis.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.feature-analysis.md%3A192))

Repo inspection records backend create capability and tests:

- Backend capability implemented and tested ([request-create-entrypoint-discoverability.repo-inspection.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.repo-inspection.md%3A8))
- Full backend capability implemented ([request-create-entrypoint-discoverability.repo-inspection.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.repo-inspection.md%3A14))
- Command/handler/test evidence ([request-create-entrypoint-discoverability.repo-inspection.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.repo-inspection.md%3A16))
- Handler evidence ([request-create-entrypoint-discoverability.repo-inspection.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.repo-inspection.md%3A17))
- Test evidence ([request-create-entrypoint-discoverability.repo-inspection.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.repo-inspection.md%3A19))

### Confirmed discoverability gap on inspected UI surfaces

Repo inspection states:

- No explicit create button/link on `/requests` list page ([request-create-entrypoint-discoverability.repo-inspection.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.repo-inspection.md%3A23))
- No navigation links to create page in inspected layout/common UI areas ([request-create-entrypoint-discoverability.repo-inspection.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.repo-inspection.md%3A24))
- Request list page exists ([request-create-entrypoint-discoverability.repo-inspection.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.repo-inspection.md%3A27))
- Existing list route is `GET /requests` ([request-create-entrypoint-discoverability.repo-inspection.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.repo-inspection.md%3A30))

Feature analysis also states the suspected gap is discoverability through visible UI entrypoints:

- gap at visible UI entrypoints from list/nav/empty-state surfaces ([request-create-entrypoint-discoverability.feature-analysis.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.feature-analysis.md%3A84))
- create is implemented but not surfaced through inspected navigation affordances ([request-create-entrypoint-discoverability.feature-analysis.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.feature-analysis.md%3A121))
- reachable create page exists, but visible entrypoints are missing on inspected surfaces ([request-create-entrypoint-discoverability.feature-analysis.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.feature-analysis.md%3A206))

### Confirmed governance absence

Feature analysis records:

- no contract, lock, decision, or closeout for P3 ([request-create-entrypoint-discoverability.feature-analysis.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.feature-analysis.md%3A193))

## Blocking Ambiguity

A material inconsistency exists between the two reviewed artifacts:

Feature analysis says:

- `requests.create` route exists and maps to `RequestCreatePage` ([request-create-entrypoint-discoverability.feature-analysis.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.feature-analysis.md%3A23))
- route and Livewire create component are present ([request-create-entrypoint-discoverability.feature-analysis.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.feature-analysis.md%3A66))

But repo inspection says:

- no `request.create` route or associated Livewire component/view is found ([request-create-entrypoint-discoverability.repo-inspection.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.repo-inspection.md%3A34))
- only `GET /requests` is defined in the referenced request web routes file ([request-create-entrypoint-discoverability.repo-inspection.md](https://storage.gapgpt.app/media/code_interpreter/3b9b8f76-6680-494f-84a6-7eb532de6146/request-create-entrypoint-discoverability.repo-inspection.md%3A43))

This inconsistency is governance-blocking because scope classification depends on it:

- if create route/page already exist, this is likely a narrow presentation/discoverability feature
- if create route/page do not exist, this is not merely discoverability and may require broader implementation analysis

## Review Outcome

The feature remains open for governance review.

Approved conclusions at this stage:

- There is credible evidence of a real discoverability problem on inspected user-facing surfaces.
- There is credible evidence of backend request creation capability.
- There is not yet a clean enough evidence base to authorize contract drafting or implementation scope classification.

## Immediate Next Step

Required next step:

- Resolve the repository evidence conflict about whether `requests.create` and `RequestCreatePage` actually exist in the current codebase.

No contract should be written yet.  
No lock should be written yet.  
No implementation should be authorized yet.

## Follow-on Governance Rule

After the route/page existence ambiguity is resolved:

- If `requests.create` and `RequestCreatePage` are confirmed present:
  - classify P3 as a presentation/discoverability feature
  - then decide whether direct UI implementation can be authorized without contract, or whether a narrow contract is still required

- If `requests.create` and `RequestCreatePage` are not present:
  - reclassify P3 as broader than discoverability-only
  - return to analysis before any contract or implementation decision

## Decision

Review decision recorded as:

NEEDS_EVIDENCE_RESOLUTION_BEFORE_CONTRACT
