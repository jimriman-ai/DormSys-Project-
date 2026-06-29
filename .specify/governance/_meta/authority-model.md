
# DormSys Governance Authority Model
model-version: 1.0.0
status: normative
tier: none (meta)
change-policy:
normative semantic changes: major version bump required
editorial changes (typo, formatting): no version change
minor semantic bumps: prohibited
owner: DormSys Architecture Team
last-updated: 1405/04/06

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
- Does NOT grant any operational authority.
- Does NOT override any tiered governance document.
- Does NOT participate in precedence resolution.

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

---

## §3 — Authority Sources (Exclusive Mapping)

Each operational authority type has **exactly one** authoritative source class.  
No other file may grant, imply, restore, or revoke that authority.

| Authority Type | Authoritative Source Class |
|---|---|
| Design Approval | `.specify/docs/handoff/<spec>-design-approved.md` |
| Implementation Authorization | `.specify/docs/handoff/<spec>-implementation-authorization.md` |
| Batch Execution Permission | `.specify/governance/execution-policy.md` + `.specify/governance/batches/<spec>.md` + recorded human review outcome |

Tier-2 and Tier-3 artifacts (`spec.md`, `plan.md`, `tasks.md`, batch files absent review outcome) MUST NOT grant any of these authorities.

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
lifecycle-reference: pointer to file-precedence.md § lifecycle section

### Field Rules

- `authorized-scope` and `blocked-scope` are **verbatim enumerations**. No agent may infer scope from spec, plan, or task content.
- An authorization record MUST NOT mix an `active` status with inline "blocked" notes. Use `partial` instead.
- `authority-constraints` MUST explicitly state that the record cannot modify AP-*, override CD-*, expand spec scope, or bypass review gates.

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
| I3 | The Governance Decision Authority Map exists in exactly one file (`catalog-decisions.md`). All other references to authority mapping MUST be pointers, not redefinitions. |
| I4 | An agent answering "what authorizes implementation of spec X?" must reach exactly one file via exactly one pointer chain. |
| I5 | Each term in §1 has exactly one meaning across all governance documents. |
| I6 | No tiered document may redefine an authority type or introduce a new one. |
| I7 | An `authorization-status: partial` record MUST contain non-empty `authorized-scope`, `blocked-scope`, and `blocking-reason`. |

Violation of any invariant is a defect.  
Defects are corrected by fixing the violating document, not by precedence resolution.

---

## §7 — Verification Query (Conformance Test)

After any change to a tiered governance document, the following query MUST return a deterministic answer derivable from documents alone, without interpretation:

> For specification `<spec-id>`, identify:
> 1. The authoritative source of implementation authorization (one file path).
> 2. The current `authorization-status` (one value from §5).
> 3. The verbatim `authorized-scope`.
> 4. The verbatim `blocked-scope` (if status is `partial`).
> 5. The pointer chain from `tasks.md` to the authoritative record.

Expected answer shape:

- Exactly one file path.
- Exactly one state from §5.
- Two verbatim scope lists (or one, if status is `active`).
- A pointer chain of length ≤ 2.

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

- Model Version: 1.0.0
- Document Status: Normative
- Tier: None (Meta)
- Owner: DormSys Architecture Team
- Last Updated: 1405/04/06
- Supersedes: — (initial version)
