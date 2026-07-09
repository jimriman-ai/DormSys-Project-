# Governance Reconciliation: Request List Navigation Exclusion

**Feature:** Request List Detail Navigation  
**Document type:** Governance reconciliation note  
**Recorded:** 2026-07-09

---

## Purpose

This note records that the predecessor **Request List** governance artifacts intentionally excluded row/detail navigation, and that this exclusion was later superseded by the approved successor feature **Request List Detail Navigation**.

This document is **governance reconciliation only**. It does not authorize new implementation, reopen closed work, or modify any predecessor or successor contract or lock.

---

## Historical Predecessor Position

The original **Request List** feature deliberately excluded list-to-detail navigation from its approved scope.

| Artifact | Reference | Exclusion |
|---|---|---|
| Feature contract | `docs/ui/contracts/requests/request-list.feature-contract.yaml` (v1.0.2) | `scope.out_of_scope` includes `row navigation` |
| Implementation lock | `docs/ui/contracts/requests/request-list.implementation-lock.yaml` | `interaction_lock.forbidden_actions` includes `navigate_to_detail` |

At the time of Request List approval, navigation from list rows to the Request Show page was intentionally deferred and forbidden within that feature boundary.

---

## Successor Authorization

The successor feature **Request List Detail Navigation** was approved as a separate, presentation-scoped feature to authorize exactly what the predecessor excluded.

| Artifact | Reference | Status |
|---|---|---|
| Feature contract | `docs/ui/contracts/requests/request-list-detail-navigation.feature-contract.yaml` (v0.1.0) | Approved |
| Implementation lock | `docs/ui/locks/requests/request-list-detail-navigation.implementation-lock.yaml` (v0.1.0) | Approved |
| Closeout | `docs/ui/closeouts/requests/request-list-detail-navigation.closeout.yaml` | Implementation completed; closeout recorded (2026-07-08) |

The successor feature authorized a single read-only navigation affordance per Request List row, linking to the existing `requests.show` route using the list view-model identifier (`RequestSummaryDTO::id`).

---

## Current Authority

**The successor closeout is the current authority for list-to-detail navigation.**

`docs/ui/closeouts/requests/request-list-detail-navigation.closeout.yaml` records that:

- Implementation is completed and lock scope was complied with.
- Only `resources/views/livewire/request/request-list-page.blade.php` was modified for navigation exposure.
- Feature tests in `tests/Feature/Modules/Request/RequestListDetailNavigationUiFlowTest.php` passed.
- Request Show, backend layers, DTOs, services, repositories, queries, auth, and route registration remained unchanged.

The current implementation is **valid** under the successor feature contract, implementation lock, and closeout.

---

## No Technical Change

This reconciliation introduces **no** backend, route, authorization, database, DTO, or architecture change.

Navigation was and remains a presentation-layer exposure of an existing named route (`requests.show`) using data already present in the approved list view-model. Ownership and access control continue to be enforced by existing backend authority on the show page.

---

## Predecessor Artifacts: Historical Records, Not Active Blockers

The predecessor Request List contract and implementation lock **remain unchanged** and serve as **historical records** of the original pilot scope.

Their navigation exclusions (`row navigation`, `navigate_to_detail`) reflect the state of Request List at approval time. They are **not active blockers** against the completed successor feature or its closeout.

Readers encountering a navigation exclusion in predecessor artifacts should treat this reconciliation note and the successor closeout as the governing interpretation for current repository state.

---

## Summary

| Question | Answer |
|---|---|
| Did Request List originally exclude navigation? | Yes — intentionally, by approved contract and lock. |
| Was navigation later authorized? | Yes — by the successor feature Request List Detail Navigation. |
| Is the current implementation valid? | Yes — under the successor closeout. |
| Does this note change code or contracts? | No — governance reconciliation only. |
| What is the current authority? | `docs/ui/closeouts/requests/request-list-detail-navigation.closeout.yaml` |
