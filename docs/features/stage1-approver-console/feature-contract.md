# Feature Contract — stage1-approver-console

> Format mirrors `docs/features/request/request-list-detail-navigation.feature-contract.yaml` section-for-section.
> Status: **ACCEPTED** (F-W07-04-D2). F2 for F-W07-04 = **PASS**.

```yaml
feature:
  id: F-W07-04
  slug: stage1-approver-console
  name: Stage-1 Approver Console
  module: Request
  classification: successor-feature
  version: 0.1.0
  status: accepted
  decision_reference: F-W07-04-D1
  acceptance_reference: F-W07-04-D2
  decision_date: "2026-07-18"
  # Evidence: docs/governance/governance-log.md:15 (D1); docs/governance/governance-log.md:16 (D2); docs/features/f-w07-04-candidate-catalog.md:14,23

purpose:
  summary: >
    Productize ops hardening for the existing Stage-1 approver console surface
    under slug stage1-approver-console (minted; not department-request-approver-console).
  rationale:
    - F-W07-04-D1 selected the Stage-1 approver console candidate as the next post-login UI slug.
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:23; docs/governance/governance-log.md:15
    - Catalog purpose is polish/list/filter for /approvals/stage1/* with dormitory-manager (DGAP-13).
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:23
    - department-request-approver-console remains reserved/historical and is not the feature slug.
      # Evidence: docs/governance/governance-log.md:15; docs/features/f-w07-04-candidate-catalog.md:39

business_goal:
  primary:
    - Harden the Stage-1 approver console UX for list/filter/polish over the existing Stage-1 approval path.
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:23
    - Keep Stage-1 auth role as dormitory-manager on the identity guard; do not rename business-stage vocabulary.
      # Evidence: specs/005-request-management/spec.md:208–216
  success_outcome:
    - A dormitory-manager identity principal can use the Stage-1 console under /approvals/stage1 with approve/reject gates that reject non-approver identities (403).
      # Evidence: routes/web.php:43–46; tests/Feature/Modules/Request/Stage1ApproverConsoleActionsTest.php:133–145,147–161,163–179

existing_capabilities:
  reused:
    - Route group prefix approvals/stage1 with auth:identity and identity.role dormitory-manager (IdentityRoleGuard::ROLE_DORMITORY_MANAGER)
      # Evidence: routes/web.php:43–46
    - ApproveStage1RequestAction / RejectStage1RequestAction Stage-1 mutation path covered by feature tests
      # Evidence: Stage1ApproverConsoleActionsTest.php:9–11,147–179
    - Spatie identity role dormitory-manager as Stage-1 gate (IdentityRoleGuard::ROLE_DORMITORY_MANAGER)
      # Evidence: Stage1ApproverConsoleActionsTest.php:65–66,115–131
    - Submitted personal requests enter pending_department_manager business-stage status (PendingDepartmentManagerState)
      # Evidence: Stage1ApproverConsoleActionsTest.php:110; specs/005-request-management/spec.md:212
    - Stage-1 approver identity snapshot coherence on create/submit
      # Evidence: Stage1ApproverConsoleActionsTest.php:181–219
  assumptions:
    - IMPL-PERMIT-03 Stage-1 console baseline is already closed; this FC scopes product hardening, not greenfield auth invent.
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:23
    - Shared dormitory-manager role with UI-M1 remains accepted (SB-D5); separation stays route/assignment based.
      # Evidence: docs/governance/governance-log.md:13; docs/features/f-w07-04-candidate-catalog.md:23
    - No new schema is required for catalog-described hardening.
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:23

scope:
  included:
    - Stage-1 approver console ops hardening characterized as polish/list/filter for /approvals/stage1/*
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:23
    - Preserve Stage-1 gate role dormitory-manager (identity) and existing approve/reject action semantics already tested
      # Evidence: routes/web.php:43–46; Stage1ApproverConsoleActionsTest.php:147–179
    - Preserve dual-model: business-stage pending_department_manager / ApprovalStage::DepartmentManager vs auth role dormitory-manager
      # Evidence: specs/005-request-management/spec.md:208–216
  excluded:
    - Role splitting of shared dormitory-manager (Stage-1 vs UI-M1) — SB-D5 ACCEPTED-AS-IS
      # Evidence: docs/governance/governance-log.md:13
    - ROLE_DEPT_MGR revival as a distinct/forbidden-role identity — deprecated alias per SB-D1; negative tests use employee-only
      # Evidence: docs/governance/governance-log.md:9; Stage1ApproverConsoleActionsTest.php:76–88,133–145
    - Renaming persisted stage strings (pending_department_manager, department_manager settings keys) — SB-D2 dual-model
      # Evidence: specs/005-request-management/spec.md:208–216
    - NOT SELECTED catalog candidates (historical record only):
      - Employee request self-service (expansion)
      - Dormitory manager dashboard (UI-M1 follow-ons)
      - Unit-manager dashboard (UI-M2 follow-ons)
      - Assignment admin (UI-A2 proposal)
      - Audit / reporting UI entry
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:22,24–27
    - Flipping program F2 from PARTIAL in this contract (separate governance-log entry after L3 review gate)
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:13; docs/governance/governance-log.md:15
    - Adopting department-request-approver-console as the feature slug
      # Evidence: docs/governance/governance-log.md:15

architecture_constraints:
  ui:
    - UI remains presentation over existing Stage-1 approval routes and actions.
      # Evidence: routes/web.php:43–46; docs/features/f-w07-04-candidate-catalog.md:23
    - UI must not invent a second Spatie role for Stage-1 actors.
      # Evidence: docs/governance/governance-log.md:13; specs/005-request-management/spec.md:216
  backend:
    - Existing Stage-1 approve/reject application actions remain the mutation authority.
      # Evidence: Stage1ApproverConsoleActionsTest.php:9–11,147–179
    - Auth gate remains identity.role dormitory-manager on the Stage-1 route group.
      # Evidence: routes/web.php:43–46
  governance:
    - Successor-feature under F-W07-04; implementation requires a separate implementation lock after this DRAFT is accepted at L3 review.
      # Evidence: docs/features/request/request-list-detail-navigation.feature-contract.yaml:140–152; docs/governance/governance-log.md:16
    - Feature slug directory is docs/features/stage1-approver-console/ (directory convention ratified).
      # Evidence: docs/governance/governance-log.md:16

allowed_changes:
  areas:
    - Stage-1 approver console presentation (list/filter/polish) under /approvals/stage1
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:23
    - Feature-specific tests for permitted console UX behaviors
    - Feature-specific governance artifacts under docs/features/stage1-approver-console/
  constraint: Changes must remain localized to Stage-1 console hardening; no role-model or stage-vocabulary renames.

forbidden_changes:
  areas:
    - Splitting or duplicating the dormitory-manager Spatie role for Stage-1 vs UI-M1
      # Evidence: docs/governance/governance-log.md:13
    - Reviving ROLE_DEPT_MGR as a distinct runtime role string or using it as a “forbidden” test identity
      # Evidence: docs/governance/governance-log.md:9; Stage1ApproverConsoleActionsTest.php:76–77
    - Persisted business-stage string renames (pending_department_manager / department_manager)
      # Evidence: specs/005-request-management/spec.md:216
    - Scope bleed into the five NOT SELECTED candidates
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:22,24–27
    - New migrations / schema for catalog-described hardening
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:23
  escalation_rule: If implementation appears to require any forbidden change, this contract must be rejected and re-scoped before coding.

interaction_model:
  user_story:
    - As a dormitory-manager identity principal, when I open /approvals/stage1, I can work Stage-1 pending requests (approve/reject) under hardened list/filter/polish UX.
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:23; routes/web.php:43–46; Stage1ApproverConsoleActionsTest.php:147–179
  entrypoint: Stage-1 approval route group /approvals/stage1
    # Evidence: routes/web.php:43–46
  destination: Existing Stage-1 console presentation bound by RequestPresentationServiceProvider::stage1ApprovalWebRoutePath()
    # Evidence: routes/web.php:46
  navigation_pattern: Ops console hardening within the Stage-1 prefix only

acceptance_criteria:
  functional:
    - Route group /approvals/stage1 remains gated by auth:identity and dormitory-manager role middleware.
      # Evidence: routes/web.php:43–46
    - dormitory-manager can approve a Stage-1 pending personal request into PendingHR.
      # Evidence: Stage1ApproverConsoleActionsTest.php:147–161
    - dormitory-manager can reject a Stage-1 pending personal request with a reason.
      # Evidence: Stage1ApproverConsoleActionsTest.php:163–179
    - Identities without dormitory-manager (including employee-only) receive 403 on Stage-1 approve gate.
      # Evidence: Stage1ApproverConsoleActionsTest.php:115–145
  non_functional:
    - No role split of dormitory-manager is introduced.
      # Evidence: docs/governance/governance-log.md:13
    - No persisted stage-vocabulary rename is introduced.
      # Evidence: specs/005-request-management/spec.md:216
  regression_safety:
    - Existing Stage1ApproverConsoleActionsTest expectations remain valid baselines for gate/approve/reject.
      # Evidence: Stage1ApproverConsoleActionsTest.php:115–219

test_expectations:
  required:
    - Retain/extend feature coverage for Stage-1 approve/reject and 403 negative gates
      # Evidence: Stage1ApproverConsoleActionsTest.php:115–179
    - Add feature tests for any new list/filter/polish UX authorized under a future implementation lock
  boundary:
    - Tests must not reintroduce ROLE_DEPT_MGR as a forbidden-role fixture
      # Evidence: Stage1ApproverConsoleActionsTest.php:76–77

risk:
  level: medium
  rationale:
    - Shared dormitory-manager role across Stage-1 and UI-M1 increases accidental cross-surface UX bleed risk; SB-D5 accepts shared role with route separation.
      # Evidence: docs/governance/governance-log.md:13
    - Dual-model stage-name vs auth-role can confuse implementers into renaming persisted strings — forbidden by SB-D2.
      # Evidence: specs/005-request-management/spec.md:208–216

dependencies:
  requires:
    - Existing /approvals/stage1 route group and Stage-1 approve/reject actions
      # Evidence: routes/web.php:43–46; Stage1ApproverConsoleActionsTest.php:9–11
    - F-W07-04-D1 slug decision
      # Evidence: docs/governance/governance-log.md:15
    - Spec05 dual-model OA-05-00
      # Evidence: specs/005-request-management/spec.md:208–216
  does_not_require:
    - New schema / migrations for catalog hardening
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:23
    - Role split or ROLE_DEPT_MGR revival
      # Evidence: docs/governance/governance-log.md:9,13

governance:
  classification: successor-feature
  prerequisites:
    - This feature contract approved at L3 review gate (status currently draft)
    - Separate implementation lock approved before any coding
    - Explicit confirmation that scope remains polish/list/filter over existing Stage-1 surface
  review_focus:
    - Scope limited to Stage-1 console hardening
    - No SB-D1/SB-D2/SB-D5 boundary violations
    - No bleed into NOT SELECTED candidates
  frozen_boundaries:
    - department-request-approver-console remains reserved/historical surface id, not this feature slug
      # Evidence: docs/governance/governance-log.md:15
    - F2 PARTIAL flip is a separate governance-log entry after L3 review — not authorized by this DRAFT
      # Evidence: docs/features/f-w07-04-candidate-catalog.md:13

definition_of_done:
  - Feature contract approved (L3 review gate)
  - Implementation lock approved for this feature only
  - Implementation completed within approved lock scope
  - Required tests added and passing
  - No frozen dual-model / shared-role boundaries violated
  - Feature closed without scope expansion into NOT SELECTED candidates

references:
  predecessor_artifacts:
    - docs/features/f-w07-04-candidate-catalog.md
    - docs/governance/governance-log.md (F-W07-04-D1)
    - docs/features/request/request-list-detail-navigation.feature-contract.yaml
    - specs/005-request-management/spec.md (OA-05-00)
    - tests/Feature/Modules/Request/Stage1ApproverConsoleActionsTest.php
    - routes/web.php
  architecture_authority:
    - .specify/ARCHITECTURE.md
    - docs/ai-ui/AI-UI-ENGINEERING-FRAMEWORK.md

open_questions: []
```
