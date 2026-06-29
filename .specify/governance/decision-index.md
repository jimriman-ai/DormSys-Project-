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
| CD-009 | Dependent Entity Ownership | catalog-decisions.md | Decision Index + § CD-009 |
| CD-010 | Approval State vs Transition Rules | catalog-decisions.md | Decision Index + § CD-010 |
| CD-011 | Lottery Domain Centralization | catalog-decisions.md | Decision Index + § CD-011 |
| CD-012 | Employee ↔ Identity Attachment Mechanism | catalog-decisions.md | Decision Index + § CD-012 |
| CD-013 | Eligibility Invariant Ownership | catalog-decisions.md | Decision Index + § CD-013 |
| CD-014 | Allocation ↔ Occupancy Ownership Split | catalog-decisions.md | Decision Index + § CD-014 |

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
## Governance Decision Authority Map

| Decision | Authoritative Source | Owner | Constraint |
| --- | --- | --- | --- |
| Design Approval | specification status artifact for the active spec | Spec Governance | Confirms design readiness only |
| Implementation Authorization | `handoff/*-implementation-authorization.md` recognized by governance precedence rules | Governance Review | Authoritative only for implementation execution state |
| Batch Execution Permission | `execution-policy.md` + human approval at review gate | Governance Review | Permits only next-batch progression |
| Scope Definition | `spec.md` / `plan.md` | Spec Owner | Does not grant execution authority |
| Task Definition | `tasks.md` | Spec Owner | Does not grant execution authority |
| Architecture Principles | `constitution.md` | Architecture Governance | Cannot be overridden by execution artifacts |

Rules:
- Each decision type must have one authoritative source class.
- If multiple active sources claim the same decision authority, HALT until resolved.
- Status summaries, task headers, and progress notes must not be treated as execution authority unless governance explicitly assigns that role.

## Maintenance Protocol

When a new decision ID is introduced:
1. Add it to this index immediately
2. Document the owner file and section
3. Run a grep check to confirm the ID appears exactly once in the owner file
4. If the ID is referenced in multiple files, mark the *defining* file as the owner and list the other files as "references"

**Never allow an orphaned decision ID to enter the codebase or reports.**

---

**Document Control**
- Version: 1.0.0
- Last Updated: 1405/04/06
- Owner: DormSys Architecture Team
