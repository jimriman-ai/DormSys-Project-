# Governance Enforcer

## Purpose

The Governance Enforcer is an automated validation layer responsible for ensuring that batch execution complies with defined governance rules.

Primary functions:

- monitor governance inputs,
- validate execution preconditions,
- gate invalid progression,
- halt on governance defects,
- report failures clearly,
- preserve auditability.

Enforcement is procedural only.

It does not:

- own governance decision authority,
- grant operational authority,
- restore revoked authority,
- replace canonical approval records,
- resolve ambiguity by inventing missing decisions.

---

## Authority Ownership

Canonical governance decision authority ownership is defined only in:

`.specify/docs/catalog-decisions.md`

See:

`## Governance Decision Authority Map`

This document does not redefine, transfer, or interpret that ownership beyond enforcement.

For governance document conflict resolution, refer to:

`.specify/governance/file-precedence.md`

---

## Validation Order

The Enforcer validates in the following order.

The documents listed below are validation inputs only unless they are explicitly identified as the canonical ownership source.

1. **Canonical Authority Check**  
   Load `.specify/docs/catalog-decisions.md` and resolve authority ownership from `## Governance Decision Authority Map`.  
   If ownership cannot be resolved unambiguously, execution must halt.

2. **Precedence Resolution**  
   Apply `.specify/governance/file-precedence.md` to resolve conflicts across loaded governance inputs.  
   Lower-tier documents cannot replace higher-tier governance ownership.

3. **Batch Integrity**  
   Verify that `tasks.md` and `.specify/governance/batches/<spec>.md` are internally consistent for the requested batch, including task range, batch identity, and sequencing expectations.

4. **Pre-Execution Preconditions**  
   Verify that required Design Approval and Implementation Authorization exist and are valid according to:
   - `.specify/docs/catalog-decisions.md`
   - `.specify/governance/execution-policy.md`

5. **Wave and Scope Gating**  
   Verify that execution is permitted for the current wave, scope, and sequencing state according to `.specify/governance/execution-policy.md`.

6. **Batch Progression Gate**  
   Confirm that:
   - the current batch is the next eligible batch,
   - required review outcomes exist,
   - prior gate conditions are satisfied.

7. **Execution Policy Compliance**  
   Enforce:
   - one-batch-at-a-time behavior,
   - halt conditions,
   - scope lock,
   - failure handling,
   - progression discipline.

8. **Coding Rule Compliance**  
   Enforce `.specify/governance/coding-rules.md` as implementation discipline only.  
   Coding rules constrain execution behavior; they do not grant authority.

---

## HARD RULE

Any governance violation results in immediate **HALT**.

A halt is mandatory when any of the following occurs:

- Canonical authority ownership cannot be resolved unambiguously from `.specify/docs/catalog-decisions.md` → `## Governance Decision Authority Map`
- A required precondition from the canonical map or execution policy is missing or not satisfied
- Design Approval is required but missing
- Implementation Authorization is missing, ambiguous, duplicated, inactive, revoked, or superseded
- A review gate required for batch progression has not been passed
- A lower-tier artifact attempts to imply, replace, or elevate higher-tier authority
- Two or more governance files use conflicting authority-ownership wording
- Execution readiness is inferred from task status, batch progress, review completion, or progress notes alone
- Required governance inputs are missing, unreadable, or contradictory
- A precedence conflict exists and has not been resolved through `.specify/governance/file-precedence.md`

Clarification:

- A precedence conflict is not resolved by guesswork.
- Missing authority is a governance defect, not an execution detail.
- The Enforcer must halt rather than infer.

---

## Enforcement Constraints

The Governance Enforcer is an enforcement mechanism only.

The following rules are mandatory:

- Enforcement does not grant authority.
- Design Approval is not Implementation Authorization.
- Review-gate approval is not Implementation Authorization.
- Review-gate approval controls batch progression only.
- Status text in `tasks.md`, reports, notes, or summaries is not execution authority.
- Execution readiness cannot be inferred from implementation progress alone.
- If authoritative governance data is unavailable, the only valid action is halt.

---

## Output Expectations

When the Enforcer halts execution, it must report:

- the exact failed rule,
- the blocking document or missing input,
- the relevant authority source,
- whether the defect is:
  - authority resolution,
  - precedence resolution,
  - precondition failure,
  - batch progression,
  - execution-policy violation,
  - coding-rule violation.

Reports must be explicit enough for remediation without redefining authority.

---

## Document Control

- Version: 1.2.0
- Last Updated: 1405/04/08
- Owner: DormSys Architecture Team

This ownership line is for document maintenance only.

It does not grant operational authority.
