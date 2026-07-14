# DOMAIN_STRUCTURE_EVIDENCE_CONSOLIDATION_GATE_DECISION

## Status
Accepted

## Date
1405/04/22

## Context
`DEFERRED_ORIGIN_RECONCILIATION` completed with `TRIAGE_CONFIRMED` and established that many deferred items were intentional architecture, scope, or authority deferrals rather than incomplete implementation.

`DOMAIN_AUTHORITY_AND_ORGANIZATION_MODEL_DISCOVERY` was then completed to extract currently evidenced organizational and authority concepts from existing repository/spec/governance sources without introducing new design.

That discovery established the following:

### Solid evidence
- `Employee`
- `Department`
- `Dependent`
- `Request` with `employee_id`-based self-ownership evidence
- Current spatial/accommodation chain: `Dormitory -> Building -> Floor -> Room -> Bed`
- Approval-chain actor labels across four stages: `Dept -> HR -> Dorm -> Unit`

### Missing or ambiguous evidence
- `Organization` as a formally defined entity
- `Dormitory Unit` as a formally defined entity
- Binding between approval stages and organizational/site structure
- Formal business owner for product-surface authorization
- Approver visibility rules
- Clear distinction in some cases between entity, role, assignment, and workflow-stage label

The discovery outcome was:

- `DOMAIN_MODEL_DISCOVERY_STATUS: COMPLETED`
- `BUSINESS_OWNER_STATUS: NOT_DEFINED`
- `REQUEST_OWNERSHIP_STATUS: PARTIAL`
- `ORG_MODEL_STATUS: REQUIRES_HUMAN_CLARIFICATION`
- `RECOMMENDED_NEXT_GATE: HUMAN_DOMAIN_AUTHORITY_CLARIFICATION`

However, before human clarification and before any product-surface authorization decision is finalized, a more complete and consolidated evidence map is required so that clarification questions are minimal, precise, and grounded in a single canonical artifact.

## Problem
Going directly from initial domain/authority discovery to human clarification or product-surface authorization would introduce governance risk because the currently available evidence is still fragmented across documents/specs/repo findings.

Without a consolidated entity/relationship/actor/ownership map:

- human clarification questions may be incomplete or poorly framed
- authorization discussion may rest on implied rather than consolidated domain structure
- actor semantics may be misread as entity definitions
- ownership and visibility questions may be asked too early or with the wrong boundary assumptions
- later auth packet preparation may inherit ambiguity that could have been reduced first

## Decision
A mandatory intermediate governance gate is introduced:

`DOMAIN_STRUCTURE_AND_RELATIONSHIP_EVIDENCE_CONSOLIDATION`

This gate must occur after `DOMAIN_AUTHORITY_AND_ORGANIZATION_MODEL_DISCOVERY` and before:

- `HUMAN_DOMAIN_AUTHORITY_CLARIFICATION`
- `PRODUCT_SURFACE_AUTHORIZATION_DECISION`
- `SPEC04_AUTH_RESIDUAL_AUTH_PACKET_PREPARATION`

## Intent
The intent of this gate is to consolidate all currently evidenced domain structure from repository/spec/governance sources into a single canonical artifact that clearly distinguishes:

- entities
- relationships
- actor labels
- roles
- assignments
- ownership signals
- boundary signals
- unresolved ambiguities

This gate is discovery/evidence consolidation only. It does not authorize implementation, auth design, UI design, workflow activation, or role mapping.

## Required Output
A canonical discovery artifact must be created or updated at:

`.specify/docs/discovery/domain-entity-relationship-authority-map.md`

That artifact must consolidate evidence for:

- domain entities
- entity relationships
- actor/role/assignment semantics
- ownership indicators
- boundary indicators
- unresolved ambiguity register
- governance readiness for subsequent gates

## Required Minimum Sections
The artifact must include at least:

1. Discovery Baseline
2. Entity Inventory
3. Relationship Inventory
4. Actor / Role / Assignment Map
5. Ownership Map
6. Boundary Map
7. Gaps Requiring Human Clarification
8. Governance Readiness Assessment

## Constraints
This gate is evidence-consolidation only.

### Allowed
- reading repository/spec/governance evidence
- consolidating already evidenced concepts
- classifying evidence as explicit, implied, ambiguous, or not found
- identifying uncertainty
- preparing the basis for later human clarification

### Forbidden
- no implementation
- no auth design
- no role/permission design
- no UI design
- no workflow activation
- no schema invention
- no entity invention
- no relationship invention
- no business-owner assignment
- no reopening closed scope by implication
- no semantic filling of feature contracts
- no mutation of application/infrastructure/presentation code or tests

## Readiness Logic
This gate exists to improve the quality of the next human clarification step.

After this gate completes:

- if business owner, organization model, visibility basis, or actor semantics remain materially ambiguous, the next gate remains `HUMAN_DOMAIN_AUTHORITY_CLARIFICATION`
- if evidence unexpectedly becomes sufficient for a bounded next step, that sufficiency must be explicitly stated rather than inferred

This gate does not itself authorize product-surface selection, auth packet implementation, or role mapping.

## Sequencing
Revised recommended sequence:

1. `DEFERRED_ORIGIN_RECONCILIATION` ✅
2. `DOMAIN_AUTHORITY_AND_ORGANIZATION_MODEL_DISCOVERY` ✅
3. `DOMAIN_STRUCTURE_AND_RELATIONSHIP_EVIDENCE_CONSOLIDATION`
4. `HUMAN_DOMAIN_AUTHORITY_CLARIFICATION`
5. `PRODUCT_SURFACE_AUTHORIZATION_DECISION`
6. `SPEC04_AUTH_RESIDUAL_AUTH_PACKET_PREPARATION`

## Expected Final Status Model
The consolidation artifact should conclude with a structured readiness summary including:

- `DOMAIN_ENTITY_MAP_STATUS`
- `RELATIONSHIP_MAP_STATUS`
- `ACTOR_SEMANTICS_STATUS`
- `BUSINESS_OWNER_STATUS`
- `REQUEST_OWNERSHIP_STATUS`
- `VISIBILITY_MODEL_STATUS`
- `ORG_MODEL_STATUS`
- `AUTH_BASIS_STATUS`
- `RECOMMENDED_NEXT_GATE`

## Non-Modification Guarantee
No application, auth, UI, workflow, schema, role-mapping, policy, middleware, permission, route, controller, Livewire, Blade, seeder, migration, test, or implementation files are authorized to change as part of this decision.
