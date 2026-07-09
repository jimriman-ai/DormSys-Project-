# UI Feature Status Repository Inspection

## Inspection Scope

Inspected directories under `docs/ui/`:

| Directory | Files found |
|---|---|
| `docs/ui/closeouts/` | 3 |
| `docs/ui/contracts/` | 7 |
| `docs/ui/locks/` | 2 |
| `docs/ui/decisions/` | 1 |
| `docs/ui/analysis/` | 2 (at inspection time; this file adds a third) |

Additional related artifact outside `docs/ui/contracts/` scope:

| Path | Note |
|---|---|
| `docs/features/request/request-list-detail-navigation.feature-contract.yaml` | Duplicate-class feature contract copy under `docs/features/` |

No files found under `docs/ui/closeouts/notifications/` with `.yaml` or `.closeout` suffix.  
No files matching `*create*entrypoint*` or `*request-create*` under inspected `docs/ui/` directories.

---

## Feature Evidence

### Request List Detail Navigation

#### Found Artifacts

| Path | Filename | Artifact type |
|---|---|---|
| `docs/ui/contracts/requests/` | `request-list-detail-navigation.feature-contract.yaml` | contract |
| `docs/ui/contracts/requests/` | `request-list-detail-navigation.implementation-lock.yaml` | lock |
| `docs/ui/locks/requests/` | `request-list-detail-navigation.implementation-lock.yaml` | lock |
| `docs/ui/closeouts/requests/` | `request-list-detail-navigation.closeout.yaml` | closeout |
| `docs/ui/closeouts/requests/` | `request-list-navigation-reconciliation.md` | reconciliation |
| `docs/features/request/` | `request-list-detail-navigation.feature-contract.yaml` | contract |

No decision artifact found under `docs/ui/decisions/` for this feature.  
No analysis artifact found under `docs/ui/analysis/` for this feature.

#### Evidence Status

`CLOSED_EVIDENCE_FOUND`

#### Repository Facts

- Feature contract (`request-list-detail-navigation.feature-contract.yaml`) records `status: approved`, `version: 0.1.0`, `classification: successor-feature`.
- Lock in `docs/ui/locks/requests/request-list-detail-navigation.implementation-lock.yaml` records top-level `status: approved`; same file also records `approval_gate.coding_authorized: false`.
- Lock in `docs/ui/contracts/requests/request-list-detail-navigation.implementation-lock.yaml` records `status: approved`.
- Closeout (`request-list-detail-navigation.closeout.yaml`) records:
  - `status.implementation: completed`
  - `status.closeout: recorded`
  - `closeout_recorded_at: "2026-07-08"`
  - `completion_evidence.implementation_completed: true`
  - `changed_files.modified`: `resources/views/livewire/request/request-list-page.blade.php`
  - `changed_files.created`: `tests/Feature/Modules/Request/RequestListDetailNavigationUiFlowTest.php`
- Reconciliation note (`request-list-navigation-reconciliation.md`) records predecessor Request List navigation exclusion superseded by this successor feature; references closeout as current authority.
- Two implementation-lock files exist for the same feature id in different directories (`docs/ui/locks/` and `docs/ui/contracts/`).

---

### Request Create Entrypoint Discoverability

#### Found Artifacts

No contract, lock, decision, closeout, reconciliation, or analysis artifact found under inspected directories naming or identifying **Request Create Entrypoint Discoverability** as a feature.

Related text only (not a feature artifact for this name):

| Path | Reference |
|---|---|
| `docs/ui/contracts/requests/request-list.feature-contract.yaml` | `scope.out_of_scope` includes `create request entrypoint` |
| `docs/ui/contracts/requests/request-list.implementation-lock.yaml` | `scope_authorization.out_of_scope` includes `create request entrypoint` |

#### Evidence Status

`NO_ARTIFACT_FOUND`

#### Repository Facts

- No file under `docs/ui/closeouts/`, `docs/ui/contracts/`, `docs/ui/locks/`, `docs/ui/decisions/`, or `docs/ui/analysis/` matches **Request Create Entrypoint Discoverability** as a feature id, name, or dedicated artifact.
- Request List contract and lock list `create request entrypoint` as out-of-scope for Request List only.

---

### Notification Inbox (Read-Only)

#### Found Artifacts

| Path | Filename | Artifact type |
|---|---|---|
| `docs/ui/contracts/notifications/` | `notification-inbox-read-only-list.feature-contract.yaml` | contract |
| `docs/ui/locks/notifications/` | `notification-inbox-read-only-list.implementation-lock.md` | lock |
| `docs/ui/decisions/notifications/` | `notification-inbox-read-only-list.open-decisions-resolution.yaml` | decision |
| `docs/ui/closeouts/notifications/` | `notification-inbox-read-only.reconciliation.md` | reconciliation |
| `docs/ui/analysis/notifications/` | `notification-inbox-read-only.repo-inspection.md` | analysis |
| `docs/ui/analysis/notifications/` | `notification-inbox-read-only.feature-analysis.md` | analysis |

No `.closeout.yaml` file found under `docs/ui/closeouts/notifications/`.

#### Evidence Status

`PARTIAL_ARTIFACT_FOUND`

#### Repository Facts

- Feature contract (`notification-inbox-read-only-list.feature-contract.yaml`) records `status: draft`, `version: 0.1.0`, `classification: greenfield-presentation`; text states `No notification inbox route or presentation surface currently exists in the codebase.`
- Implementation lock (`notification-inbox-read-only-list.implementation-lock.md`) records `Lock status: draft`, `Coding authorized: false`.
- Open decisions resolution (`notification-inbox-read-only-list.open-decisions-resolution.yaml`) records `status: resolved`; resolved items include route, principal resolution, and authorization semantics.
- Reconciliation (`notification-inbox-read-only.reconciliation.md`) records:
  - `Reconciliation Status: IMPLEMENTED_RECONCILED`
  - `Feature Status: CLOSED`
  - `Feature Contract: Not required`
  - `Additional Implementation: Not required for approved read-only inbox baseline`
- Analysis artifacts exist: repo-inspection and feature-analysis under `docs/ui/analysis/notifications/`.
- Contract status remains `draft` while reconciliation records `CLOSED`.
- Lock status remains `draft` with `Coding authorized: false` while reconciliation states implementation already present.

---

## Request Features

Artifacts found for Request module UI governance (all inspected directories):

| Feature (artifact name) | Contract | Lock | Decision | Closeout / reconciliation | Analysis |
|---|---|---|---|---|---|
| Request List (`requests/request-list`) | `request-list.feature-contract.yaml` — `status: approved` | `request-list.implementation-lock.yaml` — `status: authorized` | none | none | none |
| Request Show (`request-show`) | `request-show.feature-contract.yaml` — `status: approved` | `request-show.implementation-lock.yaml` — `status: approved`, `implementation_authorized: true` | none | none | none |
| Request List Detail Navigation | `request-list-detail-navigation.feature-contract.yaml` — `status: approved` | two lock files — `status: approved` | none | `request-list-detail-navigation.closeout.yaml` — `implementation: completed`, `closeout: recorded`; `request-list-navigation-reconciliation.md` | none |

### Request List Detail Navigation

Evidence status: `CLOSED_EVIDENCE_FOUND` (see Feature Evidence section).

### Request Create Entrypoint Discoverability

Evidence status: `NO_ARTIFACT_FOUND`.

No dedicated contract, lock, decision, closeout, reconciliation, or analysis artifact located in inspected directories.

---

## Notification Features

Artifacts found for Notification module UI governance:

| Feature (artifact name) | Contract | Lock | Decision | Closeout / reconciliation | Analysis |
|---|---|---|---|---|---|
| Notification Inbox (Read-Only List) | `notification-inbox-read-only-list.feature-contract.yaml` — `status: draft` | `notification-inbox-read-only-list.implementation-lock.md` — `Lock status: draft` | `notification-inbox-read-only-list.open-decisions-resolution.yaml` — `status: resolved` | `notification-inbox-read-only.reconciliation.md` — `IMPLEMENTED_RECONCILED`, `Feature Status: CLOSED` | repo-inspection + feature-analysis |

### Notification Inbox (Read-Only) lifecycle evidence

| Lifecycle artifact | Present | Recorded state in artifact |
|---|---|---|
| Contract | yes | `draft` |
| Lock | yes | `draft`; `Coding authorized: false` |
| Open decisions resolution | yes | `resolved` |
| Closeout (`.closeout.yaml`) | no | — |
| Reconciliation | yes | `IMPLEMENTED_RECONCILED`; `Feature Status: CLOSED` |
| Analysis | yes | repo-inspection; feature-analysis |

Contract and lock artifacts remain `draft` while reconciliation artifact records `CLOSED`.

---

## Summary Table

| Feature | Contract | Lock | Decision | Closeout | Status |
|---|---|---|---|---|---|
| Request List Detail Navigation | `approved` (`request-list-detail-navigation.feature-contract.yaml`) | `approved` (two lock files in `docs/ui/locks/` and `docs/ui/contracts/`) | — | `recorded` (`request-list-detail-navigation.closeout.yaml`: `implementation: completed`, `closeout: recorded`, `2026-07-08`) | Closeout records `implementation: completed` |
| Request Create Entrypoint Discoverability | — | — | — | — | No artifact found |
| Notification Inbox (Read-Only) | `draft` (`notification-inbox-read-only-list.feature-contract.yaml`) | `draft` (`notification-inbox-read-only-list.implementation-lock.md`; `Coding authorized: false`) | `resolved` (`notification-inbox-read-only-list.open-decisions-resolution.yaml`) | Reconciliation only (`notification-inbox-read-only.reconciliation.md`: `IMPLEMENTED_RECONCILED`, `Feature Status: CLOSED`); no `.closeout.yaml` | Reconciliation records `IMPLEMENTED_RECONCILED` and `CLOSED`; contract and lock remain `draft` |
| Request List (related) | `approved` | `authorized` | — | — | No closeout artifact found |
| Request Show (related) | `approved` | `approved` (`implementation_authorized: true`) | — | — | No closeout artifact found |

---

*Inspection mode: repository artifacts only. No implementation code inspected for this document.*
