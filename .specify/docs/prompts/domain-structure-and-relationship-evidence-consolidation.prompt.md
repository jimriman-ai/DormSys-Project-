# PROMPT — DOMAIN_STRUCTURE_AND_RELATIONSHIP_EVIDENCE_CONSOLIDATION

**Gate:** `DOMAIN_STRUCTURE_AND_RELATIONSHIP_EVIDENCE_CONSOLIDATION`  
**Authority:** Decision `.specify/docs/decisions/domain-structure-evidence-consolidation-gate.md` (Accepted, 1405/04/22)  
**Mode:** Discovery / evidence consolidation only — non-authorizing

---

## Context

- `DEFERRED_ORIGIN_RECONCILIATION` completed; triage confirmed.
- `DOMAIN_AUTHORITY_AND_ORGANIZATION_MODEL_DISCOVERY` completed.
- Governance inserted this mandatory intermediate gate **before** `HUMAN_DOMAIN_AUTHORITY_CLARIFICATION` and **before** `PRODUCT_SURFACE_AUTHORIZATION_DECISION`.

Prior discovery solid evidence (do not invent beyond evidence):

- Employee, Department, Dependent
- Request with `employee_id`-based self-ownership evidence
- Spatial chain: Dormitory → Building → Floor → Room → Bed
- Approval-chain actor labels: Dept → HR → Dorm → Unit

Prior discovery gaps (preserve as unresolved unless new explicit evidence is found):

- Organization as formal entity
- Dormitory Unit as formal entity
- Stage-to-org/site binding
- Formal business owner
- Approver visibility rules
- Some entity / role / assignment / workflow-label distinctions

Upstream discovery (input, not substitute for this gate):

- `.specify/docs/discovery/domain-authority-and-organization-model-discovery.md`

---

## Objective

Consolidate **all currently evidenced** domain structure from repository / specs / governance into **one canonical artifact**, clearly distinguishing entities, relationships, actor labels, roles, assignments, ownership signals, boundary signals, and unresolved ambiguities.

This gate improves the quality of later human clarification. It does **not** authorize product surfaces, Auth packets, or role mapping.

---

## Required Output Artifact

Create or update:

`.specify/docs/discovery/domain-entity-relationship-authority-map.md`

### Required minimum sections

1. Discovery Baseline  
2. Entity Inventory  
3. Relationship Inventory  
4. Actor / Role / Assignment Map  
5. Ownership Map  
6. Boundary Map  
7. Gaps Requiring Human Clarification  
8. Governance Readiness Assessment  

---

## Required Method

Use only repository / spec / governance evidence.

For every concept, classify evidence as exactly one of:

- `Found` / `Explicit`
- `Implied`
- `Ambiguous`
- `Not Found`

For every actor / label, classify concept class as exactly one of:

- `Entity`
- `Role`
- `Assignment`
- `Workflow actor label`
- `Unknown`

Extract and consolidate:

- all evidenced domain entities
- all evidenced relationships
- ownership indicators (module ownership, request self-ownership, assignment vs physical, etc.)
- boundary indicators (CD-*, deferred Workflow, Spec02 freeze, Auth residual, etc.)
- unresolved ambiguity register suitable for precise later clarification questions

If evidence is insufficient, record uncertainty. Do **not** infer or invent.

---

## Constraints

### Allowed

- reading repository / spec / governance evidence
- consolidating already evidenced concepts
- classifying evidence and identifying uncertainty
- preparing the basis for later human clarification

### Forbidden (hard stops)

- no implementation
- no auth design
- no role / permission design
- no UI design
- no workflow activation
- no schema invention
- no entity invention
- no relationship invention
- no speculative business-owner assignment
- no reopening closed specs or expanding closed scope by implication
- no semantic filling of feature contracts
- no mutation of application / infrastructure / presentation code, tests, migrations, routes, policies, middleware, permissions, Livewire, Blade, or seeders

---

## Readiness Logic (for §8)

After consolidation:

- if business owner, organization model, visibility basis, or actor semantics remain materially ambiguous → recommend `HUMAN_DOMAIN_AUTHORITY_CLARIFICATION`
- if evidence unexpectedly becomes sufficient for a bounded next step → state that sufficiency **explicitly** (do not infer)
- do **not** claim Auth packet readiness unless evidence truly supports it

This gate does not authorize product-surface selection or Auth packet preparation.

---

## Required Final Status Lines

End the discovery artifact (and the agent response) exactly with:

```text
DOMAIN_ENTITY_MAP_STATUS: <COMPLETED or PARTIAL or INSUFFICIENT_EVIDENCE>
RELATIONSHIP_MAP_STATUS: <COMPLETED or PARTIAL or AMBIGUOUS>
ACTOR_SEMANTICS_STATUS: <CLEAR or PARTIAL or AMBIGUOUS>
BUSINESS_OWNER_STATUS: <DEFINED or NOT_DEFINED or AMBIGUOUS>
REQUEST_OWNERSHIP_STATUS: <DEFINED or PARTIAL or AMBIGUOUS>
VISIBILITY_MODEL_STATUS: <DEFINED or PARTIAL or AMBIGUOUS>
ORG_MODEL_STATUS: <SUFFICIENT_FOR_NEXT_GATE or REQUIRES_HUMAN_CLARIFICATION>
AUTH_BASIS_STATUS: <READY_FOR_PACKET_PREP or NOT_READY>
RECOMMENDED_NEXT_GATE: <HUMAN_DOMAIN_AUTHORITY_CLARIFICATION or PRODUCT_SURFACE_AUTHORIZATION_DECISION or DOMAIN_GAP_REVIEW>
No application, auth, UI, workflow, schema, role-mapping, policy, middleware, permission, route, controller, Livewire, Blade, seeder, migration, test, or implementation files were modified.
```
