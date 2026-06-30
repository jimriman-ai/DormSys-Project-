
# DormSys Governance Authority Model
model-version: 4.0.0
status: normative
tier: none (meta)
change-policy:
normative semantic changes: major version bump required
editorial changes (typo, formatting): no version change
minor semantic bumps: prohibited
owner: DormSys Architecture Team
last-updated: 1405/04/03 | 2026/06/24

---

## Preamble

This document is the **authority model anchor** for DormSys governance.

It is **non-tiered**.  
It does not participate in governance precedence resolution.

It defines the vocabulary, conceptual model, authority types, lifecycle, and invariants that all tiered governance documents MUST conform to.

A violation of this model by a tiered document is a **governance defect**, not a precedence conflict.  
Defects are corrected by fixing the violating document, not by precedence-based resolution.

### Relationship to the Constitution

The Constitution (`constitution.md`, Tier 0) defines the governing principles and non-negotiable constraints of DormSys — architectural principles (AP-*), technology stack, and domain language authority.

This anchor defines the governance authority model and vocabulary used to express and apply those constraints — authority types, record lifecycle, and invariants.

The two operate at different layers and cannot logically conflict.

If a hypothetical conflict appears, it indicates a defect in this model.  
The Constitution prevails, and this model MUST be corrected via a major version bump.

### Authority Scope of This Document

This document:

- Defines vocabulary that tiered documents MUST use consistently.
- Defines invariants that tiered documents MUST satisfy.
- Defines authorization-record lifecycle and structure (conceptual and normative for record shape).
- Does NOT determine governance decision authority ownership.
- Does NOT duplicate the Governance Decision Authority Map.
- Does NOT grant any operational authority.
- Does NOT override any tiered governance document.
- Does NOT participate in precedence resolution.

Governance decision authority ownership is defined only in:

`.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`

---

## §1 — Vocabulary (Normative)

The following terms have fixed meanings throughout DormSys governance.  
No governance document may use any of these terms with a different meaning.  
Doing so is a defect.

| Term | Means | Does NOT mean |
|---|---|---|
| **Decision** | A recorded choice with an ID and an owner document | A status line, header, or comment |
| **Policy** | A rule that governs how decisions are made | A decision itself |
| **Approval** | Confirmation that a review condition has been satisfied | Permission to implement, unless explicitly mapped to an authorization |
| **Authorization** | Explicit permission granted by an authoritative source, with declared scope and lifecycle | Design acceptance, progress status, or batch permission |
| **Permission** | Runtime allowance to perform a specific execution action under an active authorization | Permanent implementation authority |
| **Authorization Record** | A scoped, lifecycle-managed instance that grants implementation rights | A status field, confirmation, or approval note |
| **Scope** | The explicit, enumerated set of tasks, waves, or user stories covered by an authorization | An implicit inference from spec, plan, or task content |
| **Defect** | A tiered document statement that violates this model | A precedence conflict |
| **Conflict** | Two tiered documents at the same tier asserting contradictory authority over the same decision | A defect |
| **Nomination Record** | An evidence-only artifact documenting program-level spec selection after a governance transition boundary | An Authorization Record, Design Approval, Implementation Authorization, or map-backed authority artifact |
| **Next Spec Transition Nomination** | A non-operational governance decision class whose instance artifact is a Nomination Record | An operational authority type or authorization grant |

---

## §2 — Decision Classes vs Operational Authority Types

DormSys governance distinguishes two layers:

### Decision Classes (what kinds of choices exist)

- Architecture Decision (AP-*)
- Boundary Decision (CD-*)
- Operational Agreement (OA-*)
- Design Approval
- Implementation Authorization
- Batch Execution Permission

### Operational Authority Types (what controls execution)

Exactly **three** operational authority types exist.  
No fourth type, no subtype, no alias.

1. **Design Approval** — confirms design readiness for a specification.
2. **Implementation Authorization** — permits implementation execution under declared scope.
3. **Batch Execution Permission** — permits progression to the next eligible batch.

Architecture decisions, boundary decisions, and operational agreements are **not** operational authorities.  
They are constraints that all authorities must respect.

### Relationship Between Decision Classes and Authority Types

Decision Classes represent categories of recorded governance decisions.  
Operational Authority Types represent only those decisions that grant permission to proceed with an operational action.

Therefore:

- Every operational authority type is a decision class.
- Not every decision class is an operational authority type.

Specifically:

- AP-*, CD-*, and OA-* are decision classes but NOT operational authority types.  
They constrain authorities; they do not grant them.

- Design Approval, Implementation Authorization, and Batch Execution Permission are BOTH decision classes AND operational authority types.

### Non-Operational Governance Decision Classes

Some governance decision classes record program-level choices **without** granting operational authority. They are **not** operational authority types, **not** Authorization Records, and **not** entries in `## Governance Decision Authority Map`.

Exactly one non-operational governance decision class is defined at this time:

- **Next Spec Transition Nomination**

Additional non-operational classes may be added only via a **major version bump** of this document. They MUST NOT be added to the operational authority type list in §2 above.

#### Next Spec Transition Nomination (normative)

**Next Spec Transition Nomination** is a **non-operational** governance decision class.

It records **which specification is nominated as the program's next focus** after a governance transition boundary. It is selection evidence only.

Next Spec Transition Nomination:

- **MUST NOT** be classified as an operational authority type.
- **MUST NOT** grant Design Approval, Implementation Authorization, or Batch Execution Permission.
- **MUST NOT** be treated as an Authorization Record.
- **MUST NOT** appear as a decision node, authority row, or owner entry in `## Governance Decision Authority Map` (per `.specify/docs/catalog-decisions.md` § Nomination Record boundary).
- **MAY** be required as a **governance precondition** before certain next-spec processes per `.specify/governance/execution-policy.md` § Nomination and Execution Policy.

Governance decision authority **ownership** for operational types is defined only in the canonical map. This document defines **ontology and vocabulary** for Next Spec Transition Nomination only; it does **not** assign map ownership.

#### Nomination Record (normative)

A **Nomination Record** is an **evidence-only**, **non-authorizing** artifact instance of the Next Spec Transition Nomination decision class.

A Nomination Record:

- **MUST NOT** satisfy Design Approval, Implementation Authorization, Batch Execution Permission, or any pre-execution operational authority check.
- **MUST NOT** be part of the authorization record lifecycle (§4–§5).
- **MUST NOT** substitute for, imply, or elevate into operational authority.
- **MAY** be referenced by execution policy as evidence that a specification has been nominated as the program's next focus.

Presence of a valid Nomination Record **does not** clear HALT caused by missing operational authority.

---

## §3 — Authority Ownership Resolution (Pointer)

Governance decision authority **ownership** is defined only in:

`.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`

This section does not duplicate that map. It states conceptual constraints only:

- Each operational authority type has exactly one canonical ownership entry in that map.
- No tiered document may grant, imply, restore, or revoke operational authority except as defined there.
- Tier-2 and Tier-3 artifacts (`spec.md`, `plan.md`, `tasks.md`, batch files absent review outcome) MUST NOT grant operational authority.

To resolve who owns any operational authority type, consult the canonical map first. Instance record locations are identified through that map, not through this document.

---

## §4 — Authorization Record (Normative Structure)

An Authorization Record is the instance form of an Implementation Authorization.

### Required Fields

authorization-status: one of { active, partial, revoked, superseded }  
authorized-by: actor name (e.g., Governance Review)  
effective-date: ISO date  
supersedes: pointer to predecessor record, or "—"  
superseded-by: pointer to successor record, or "—"  
authorized-scope: explicit list of waves/tasks/user-stories  
blocked-scope: explicit list (required when status = partial)  
blocking-reason: reference to unresolved dependency (required when status = partial)  
authority-constraints: explicit list of what this record cannot do  
lifecycle-reference: pointer to authority-model.md §5

### Field Rules

- `authorized-scope` and `blocked-scope` are **verbatim enumerations**. No agent may infer scope from spec, plan, or task content.
- An authorization record MUST NOT mix an `active` status with inline "blocked" notes. Use `partial` instead.
- `authority-constraints` MUST explicitly state that the record cannot modify AP-*, override CD-*, expand spec scope, or bypass review gates.

### Artifacts outside the authorization record lifecycle

The following are **outside** the authorization record lifecycle defined in this section and §5:

- governance state snapshots
- transition state records
- checkpoint summaries
- audit/status documents
- **Nomination Records** (evidence-only instances of Next Spec Transition Nomination per §2)

They may serve as **evidence or context** for status interpretation, transition interpretation, or audit/history.

They **cannot** substitute for formal authorization records.

They **cannot** independently satisfy authorization requirements.

They are not Authorization Records and do not participate in the lifecycle states or operations in §5.

---

## §5 — Lifecycle (Normative State Machine)

### States

| State | Meaning |
|---|---|
| `active` | Implementation permitted for full declared scope |
| `partial` | Implementation permitted only for `authorized-scope`; `blocked-scope` halts execution |
| `revoked` | Implementation no longer permitted; terminal state for this record |
| `superseded` | Replaced by a successor record; terminal state for this record |

### State Transitions

The following transitions are the **complete and exclusive** set of permitted state changes.  
Any transition not listed is forbidden.

| From | To | Conditions |
|---|---|---|
| (none) | active | Creation under §5 lifecycle operations, full scope authorized |
| (none) | partial | Creation under §5 lifecycle operations, scope partially blocked |
| active | partial | Scope reduction recorded via lifecycle update |
| partial | active | ALL of: blocking_reason resolved AND blocked_scope becomes empty AND lifecycle update recorded |
| active | revoked | Revocation operation under §5 |
| partial | revoked | Revocation operation under §5 |
| active | superseded | Supersession operation under §5 |
| partial | superseded | Supersession operation under §5 |

### Terminal States

`revoked` and `superseded` are **terminal**.

No transition out of these states is permitted.

A revoked record cannot be reactivated; a new record must be created.  
A superseded record cannot be edited; the successor record is authoritative.

### Lifecycle Operations

Exactly three operations exist:

`create`, `revoke`, `supersede`.

Each operation requires:

1. A Change Log entry in `catalog-decisions.md` with a version bump.
2. A governance checkpoint reference (e.g., `<spec>-planning-review`).
3. `Governance Review` recorded as actor.

---

## §6 — Invariants (MUST hold at all times)

| ID | Invariant |
|---|---|
| I1 | Exactly one record per spec is in `active` or `partial` state at any moment. |
| I2 | Status text in Tier-2 or Tier-3 artifacts is descriptive only, never authoritative. |
| I3 | The Governance Decision Authority Map exists in exactly one file (`.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`). All other references to authority mapping MUST be pointers, not redefinitions. |
| I4 | An agent answering "what authorizes implementation of spec X?" must start from `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`, then reach exactly one instance record via exactly one pointer chain. |
| I5 | Each term in §1 has exactly one meaning across all governance documents. |
| I6 | No tiered document may redefine an authority type or introduce a new one. |
| I7 | An `authorization-status: partial` record MUST contain non-empty `authorized-scope`, `blocked-scope`, and `blocking-reason`. |
| I8 | **Next Spec Transition Nomination** and **Nomination Record** are **non-operational**. They MUST NOT be classified as operational authority types, Authorization Records, or map-backed authority classes. |
| I9 | A Nomination Record MUST NOT satisfy Design Approval, Implementation Authorization, Batch Execution Permission, or authorization lifecycle checks in §4–§5. |

Violation of any invariant is a defect.  
Defects are corrected by fixing the violating document, not by precedence resolution.

---

## §7 — Verification Query (Conformance Test)

After any change to a tiered governance document, the following query MUST return a deterministic answer derivable from documents alone, without interpretation:

> For specification `<spec-id>`, identify:
> 1. The canonical ownership entry from `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`.
> 2. The instance file path for implementation authorization (one file path), as identified through that map.
> 3. The current `authorization-status` (one value from §5).
> 4. The verbatim `authorized-scope`.
> 5. The verbatim `blocked-scope` (if status is `partial`).
> 6. The pointer chain from `tasks.md` to the canonical map and then to the instance record.

Expected answer shape:

- One canonical map reference (`.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`).
- Exactly one instance file path.
- Exactly one state from §5.
- Two verbatim scope lists (or one, if status is `active`).
- A pointer chain of length ≤ 2 from operational artifacts to the instance record, starting from the canonical map.

If any of these is ambiguous, multi-valued, or requires interpretation, a defect exists.

---

## §8 — Change Policy

This document follows strict change rules:

- **Major version bump** is the only allowed change type for normative content.
- A major bump invalidates conformance of all tiered governance documents.
- All tiered documents MUST be re-verified against the new model before any execution is permitted.
- **Minor version bumps are prohibited.** There is no "small correction" to a normative model.
- Editorial changes (typos, formatting) are not version-bumped and are not considered changes to the model.

---

## Document Control

- Model Version: 4.0.0
- Document Status: Normative
- Tier: None (Meta)
- Owner: DormSys Architecture Team
- Last Updated: 1405/04/03 | 2026/06/24
- Change: §2 Non-Operational Governance Decision Classes; Next Spec Transition Nomination; Nomination Record; §4 lifecycle boundary; invariants I8–I9
- Supersedes: model-version 3.0.0
