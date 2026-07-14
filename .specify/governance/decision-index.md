# Decision Index

## Purpose

This index maps every governance decision identifier to its defining location. The AI agent must consult this index before citing or applying a decision. **Never guess the location of a decision ID.**

---

## Architectural Principles (AP-*)

| ID | Title | Owner File | Section |
|---|---|---|---|
| AP-01 | Technology Stack | constitution.md | § 2.1 |
| AP-02 | Modular Monolith | constitution.md | § 2.2 |
| AP-03 | Clean Architecture with DDD-Lite | constitution.md | § 2.3 |
| AP-04 | Shared Database with Bounded Module Ownership | constitution.md | § 2.4 |
| AP-05 | Explicit State Machines | constitution.md | § 2.5 |

---

## Catalog Decisions (CD-*)

| ID | Title | Owner File | Section |
|---|---|---|---|
| CD-009 | Dependent Entity Ownership | `.specify/docs/catalog-decisions.md` | Decision Index + § CD-009 |
| CD-010 | Approval State vs Transition Rules | `.specify/docs/catalog-decisions.md` | Decision Index + § CD-010 |
| CD-011 | Lottery Domain Centralization | `.specify/docs/catalog-decisions.md` | Decision Index + § CD-011 |
| CD-012 | Employee ↔ Identity Attachment Mechanism | `.specify/docs/catalog-decisions.md` | Decision Index + § CD-012 |
| CD-013 | Eligibility Invariant Ownership | `.specify/docs/catalog-decisions.md` | Decision Index + § CD-013 |
| CD-014 | Allocation ↔ Occupancy Ownership Split | `.specify/docs/catalog-decisions.md` | Decision Index + § CD-014 |
| CD-015 | CheckIn/CheckOut Module Boundary | `.specify/docs/catalog-decisions.md` | Decision Index + § CD-015 |
| CD-016 | Voucher Eligibility Ownership | `.specify/docs/catalog-decisions.md` | Decision Index + § CD-016 |
| CD-017 | Reporting Projection Boundary | `.specify/docs/catalog-decisions.md` | Decision Index + § CD-017 |

---

## Operational Agreements (OA-05-*)

**Note**: OA identifiers are spec-specific. The prefix `OA-05-*` indicates they belong to `spec05` (request-management).

| ID | Title | Owner File | Section |
|---|---|---|---|
| OA-05-01 | *(not yet extracted — location TBD)* | `specs/spec05/spec.md` or `plan.md` | *(pending line-read confirmation)* |
| OA-05-03 | *(not yet extracted — location TBD)* | `specs/spec05/spec.md` or `plan.md` | *(pending line-read confirmation)* |
| OA-05-09 | *(not yet extracted — location TBD)* | `specs/spec05/spec.md` or `plan.md` | *(pending line-read confirmation)* |

**Action Required**: Line-read `spec.md` and `plan.md` to extract the exact section headings and titles for OA-05-01, OA-05-03, OA-05-09. Until then, treat them as valid identifiers but cite their file names only.

---
## Operational Agreements (OA-*)


Pending extraction from specification documents.

No OA entry shall appear here until its defining location has
been verified from the source document.
## Playbook Rules

| ID | Title | Owner File | Section |
|---|---|---|---|
| Rule 1 | Evidence Sources (Layered) | specification-playbook.md | § II |
| Rule 2 | Mandatory Citation | specification-playbook.md | § II |
| Rule 3 | Separation of Concept from Implementation | specification-playbook.md | § II |
| Rule 4 | Evidence Freeze | specification-playbook.md | § II |
| Rule 5 | Tagging | specification-playbook.md | § III |
| Rule 6 | Resolution Process | specification-playbook.md | § III |
| Rule 7 | Shared Database, Isolated Ownership | specification-playbook.md | § IV |

---

## Governance Patterns

Reusable process patterns (not decision IDs; not operational authority types):

| Pattern | Owner File | Description |
| --- | --- | --- |
| Integration Readiness Gate | `.specify/governance/patterns/integration-readiness-gate.md` | Mandatory readiness gate before cross-module Integration Implementation Authorization. |

Pattern index: `.specify/governance/patterns/README.md`

Reusable authorization template: `.specify/templates/integration-implementation-authorization-template.md`

These patterns do **not** grant Design Approval, Implementation Authorization, or Batch Execution Permission. They constrain readiness before Integration Implementation Authorization may be requested or issued.

---

## Core Completion Wave Gates (governance sequencing — not CD-*)

Non-operational gate decisions / discovery artifacts. Consult before citing wave next-gate recommendations.

| Gate / Decision ID | Title | Owner File |
|---|---|---|
| DEFERRED_ORIGIN_RECONCILIATION | Deferred origin recovery | `.specify/docs/decisions/deferred-origin-reconciliation.md` |
| PRODUCT_AUTHORIZATION_GAP_TRIAGE | Product authorization gap triage | `.specify/docs/decisions/product-authorization-gap-triage.md` |
| DOMAIN_AUTHORITY_AND_ORGANIZATION_MODEL_DISCOVERY | Domain authority / org model discovery | `.specify/docs/discovery/domain-authority-and-organization-model-discovery.md` |
| DOMAIN_STRUCTURE_EVIDENCE_CONSOLIDATION_GATE_DECISION | Intermediate consolidation gate (Accepted) | `.specify/docs/decisions/domain-structure-evidence-consolidation-gate.md` |
| DOMAIN_STRUCTURE_AND_RELATIONSHIP_EVIDENCE_CONSOLIDATION | Evidence consolidation (next) | Prompt: `.specify/docs/prompts/domain-structure-and-relationship-evidence-consolidation.prompt.md`; Output: `.specify/docs/discovery/domain-entity-relationship-authority-map.md` |
| BUSINESS_OWNER_FORMALIZATION_AND_ARCHITECTURE_REVIEW | Proposed Business Owner wording review («واحد اداری / منابع انسانی») | `.specify/docs/decisions/business-owner-formalization-review.md` |
| PRODUCT_SURFACE_AUTHORIZATION_DECISION | Named surface `employee-request-self-service` (owner UNRESOLVED) | `.specify/docs/decisions/product-surface-authorization-decision.md` |
| PRODUCT_SURFACE_OWNER_AUTHORITY_CLARIFICATION | Owner/authority sufficiency for self-service surface | `.specify/docs/decisions/product-surface-owner-authority-clarification.md` |
| RELATIONSHIP_EVIDENCE_CONSOLIDATION | Scoped domain relationship evidence map | `.specify/docs/discovery/domain-entity-relationship-map.md` |
| HUMAN_DOMAIN_AUTHORITY_CLARIFICATION_PACKET | Gate B1 human clarification questions (HDAC-01–06) | `.specify/docs/clarifications/human-domain-authority-clarification.md` |
| HUMAN_AUTHORITY_RESPONSE_RESOLUTION | HDAC-01..06 response intake / resolution | `.specify/docs/decisions/human-authority-response-resolution.md` |
| HDAC_05_BUSINESS_OWNER_REQUERY | Formal Business Owner re-query | `.specify/docs/clarifications/hdac-05-business-owner-requery.md` |
| HDAC_06_MANAGER_APPROVAL_SCOPE_CR | Manager approve/reject scope change request | `.specify/docs/decisions/hdac-06-manager-approval-scope-change-request.md` |

---

## Authority Ownership

This document is an index only.

Canonical Governance Decision Authority ownership is defined only in:

`.specify/docs/catalog-decisions.md`

See:

`## Governance Decision Authority Map`

If any wording in this document conflicts with the canonical map,
the canonical map prevails.

### Governance Transition (cross-reference — not an authority owner)

Operational state when authorized work is complete and no next specification or batch has been authorized. Not a decision class in the authority map.

| Concept | Defining location | Section |
| --- | --- | --- |
| Governance Transition state | `.specify/governance/execution-policy.md` | § Governance Transition State |
| HALT Case A / Case B / Case C | `.specify/governance/execution-policy.md` | § HALT Classification (Authorization vs Transition vs Governance Precondition) |
| Nomination and Execution Policy | `.specify/governance/execution-policy.md` | § Nomination and Execution Policy |
| Descriptive note (no owner assigned) | `.specify/docs/catalog-decisions.md` | § Governance Transition (state — not an authority owner) |
| Case C / Nomination Record boundaries | `.specify/docs/catalog-decisions.md` | § Case C — governance precondition classification; § Nomination Record boundary |
| Non-operational nomination ontology | `.specify/governance/_meta/authority-model.md` | §2 — Non-Operational Governance Decision Classes |

#### Next Spec Transition Nomination — conceptual handoff chain (documentation only)

When governance resolves **which specification is the program's next focus** after a transition boundary, the conceptual sequence is:

```
Next Spec Transition Nomination (non-operational decision class)
        |
        v
Nomination Record (evidence-only artifact instance)
        |
        v
Future Design Approval initiation (operational — separate artifact)
        |
        v
Future Implementation Authorization (operational — separate artifact, when applicable)
```

**Normative constraints (no enforcement logic defined here):**

- **Next Spec Transition Nomination** records program-level spec selection; it is **not** an operational authority type and has **no** entry in `## Governance Decision Authority Map`.
- A **Nomination Record** is the evidence-only instance of that decision class. It **does not** grant Design Approval, Implementation Authorization, or Batch Execution Permission.
- **Future operational decisions** still require their own operational authority records per the canonical map (Design Approval at `.specify/docs/handoff/<spec>-design-approved.md`; Implementation Authorization at `.specify/docs/handoff/<spec>-implementation-authorization.md`).
- A valid Nomination Record may be a **governance precondition** before initiating Design Approval for a next spec (Case C when missing); it does **not** satisfy operational authority checks.

This chain is **descriptive documentation** for operators. HALT classification, detection procedure, and enforcement behavior remain defined only in `execution-policy.md` and `governance-enforcer.md`.

## Maintenance Protocol

When a new decision ID is introduced:
1. Add it to this index immediately
2. Document the owner file and section
3. Run a grep check to confirm the ID appears exactly once in the owner file
4. If the ID is referenced in multiple files, mark the *defining* file as the owner and list the other files as "references"

**Never allow an orphaned decision ID to enter the codebase or reports.**

---

**Document Control**
- Version: 1.4.2
- Last Updated: 1405/04/22 | 2026/07/13
- Change: Added Core Completion Wave Gates index entries including DOMAIN_STRUCTURE_EVIDENCE_CONSOLIDATION_GATE_DECISION
- Owner: DormSys Architecture Team
