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
   Per `.specify/governance/execution-policy.md` v1.4.0 § Nomination and Execution Policy and § HALT Classification (Authorization vs Transition vs Governance Precondition), **Case C MUST be evaluated before any operational authority checks.**

   **Nomination Record rule (strict):**  
   Nomination Records are **not** part of authorization validation. They are evidence-only, non-authorizing artifacts evaluated **only** for Case C precondition logic. A Nomination Record **cannot** satisfy Design Approval, Implementation Authorization, or Batch Execution Permission.

   **Case C — governance precondition (evaluate first; mandatory precedence):**  
   When execution or governance workflow initiates a **next-spec process** that `execution-policy.md` v1.4.0 requires to be preceded by a Nomination Record, and no valid Nomination Record exists for that specification (missing, duplicated, ambiguous, or superseded without a single active replacement), classify as **Case C — Governance precondition failure: transition nomination record required.**, **HALT immediately**, and report the exact HALT message from `execution-policy.md` v1.4.0:

   > `Governance precondition failure: transition nomination record required.`

   **Strict ordering:** If Case C is triggered, **HALT immediately**. Do **not** evaluate Case A or Case B.

   Case C is a **governance-precondition failure**, not an operational authority failure. A Nomination Record **does not** replace or satisfy Design Approval or Implementation Authorization.

   **Operational authority checks (only after Case C is ruled out or satisfied):**  
   Verify that required Design Approval and Implementation Authorization exist and are valid according to:
   - `.specify/docs/catalog-decisions.md`
   - `.specify/governance/execution-policy.md` v1.4.0  
   Authorization presence and validity MUST be resolved **only** from canonical authorization artifact classes and instance paths in `## Governance Decision Authority Map`.  
   Governance state / snapshot artifacts (per `catalog-decisions.md` § `Governance state / snapshot artifacts`), **Nomination Records**, and other artifacts outside the authorization record lifecycle (per `authority-model.md` § `Artifacts outside the authorization record lifecycle`) **cannot** satisfy these checks — regardless of `handoff/` placement, filename similarity, or descriptive status wording.

   Classify operational authorization failures as **Case A** or **Case B** only after Case C is ruled out or satisfied, per the detection procedure in `execution-policy.md` v1.4.0.

5. **Wave and Scope Gating**  
   Verify that execution is permitted for the current wave, scope, and sequencing state according to `.specify/governance/execution-policy.md`.

6. **Batch Progression Gate**  
   Confirm that:
   - the current batch is the next eligible batch,
   - required review outcomes exist,
   - prior gate conditions are satisfied.

7. **Execution Policy Compliance**  
   Enforce per `.specify/governance/execution-policy.md` v1.4.0:
   - one-batch-at-a-time behavior,
   - halt conditions and HALT classification (Case A, Case B, Case C),
   - scope lock,
   - failure handling,
   - progression discipline.

   **HALT classification precedence (mandatory; no alternative ordering):**

   > **Case C → Case A → Case B**

   When multiple HALT conditions could apply, precedence is **mandatory** during classification. **Case C always wins.** If Case C applies, do not classify as Case A or Case B.

   - **Case C** — missing or invalid required Nomination Record for the engaged next-spec process (governance-precondition failure).
   - **Case A** — implementation authorization defect for an implementation execution target (only after Case C is ruled out or satisfied).
   - **Case B** — program-boundary governance transition (only after Case C is ruled out or satisfied).

   Report the exact HALT message for the classified case per `execution-policy.md` v1.4.0.

8. **Coding Rule Compliance**  
   Enforce `.specify/governance/coding-rules.md` as implementation discipline only.  
   Coding rules constrain execution behavior; they do not grant authority.

---

## HARD RULE

Any governance violation results in immediate **HALT**.

A halt is mandatory when any of the following occurs:

- Canonical authority ownership cannot be resolved unambiguously from `.specify/docs/catalog-decisions.md` → `## Governance Decision Authority Map`
- A required Nomination Record is missing, duplicated, ambiguous, or superseded without replacement when `execution-policy.md` v1.4.0 requires one for the engaged next-spec process → **Case C** **HALT immediately** with message: `Governance precondition failure: transition nomination record required.` (**Case C is NOT an operational authority failure.**)
- A required precondition from the canonical map or execution policy is missing or not satisfied
- Design Approval is required but missing (evaluate only after Case C is ruled out or satisfied)
- Implementation Authorization is missing, ambiguous, duplicated, inactive, revoked, or superseded (evaluate only after Case C is ruled out or satisfied)
- A governance state / snapshot, **Nomination Record**, checkpoint summary, status report, or audit record is used in place of a canonical authorization artifact
- A review gate required for batch progression has not been passed
- A lower-tier artifact attempts to imply, replace, or elevate higher-tier authority
- Two or more governance files use conflicting authority-ownership wording
- Execution readiness is inferred from task status, batch progress, review completion, or progress notes alone
- Required governance inputs are missing, unreadable, or contradictory
- A precedence conflict exists and has not been resolved through `.specify/governance/file-precedence.md`

Clarification:

- A precedence conflict is not resolved by guesswork.
- Missing authority is a governance defect, not an execution detail.
- **HALT classification precedence is mandatory:** **Case C → Case A → Case B**. No alternative ordering is allowed. If Case C applies, do not evaluate or report Case A or Case B.
- Case C is a governance-precondition failure; it is **not** Case A (authorization defect) or Case B (governance transition).
- Case A and Case B apply **only** after Case C is ruled out or satisfied.
- The Enforcer must halt rather than infer.
- Exactly **three** operational authority types exist per `authority-model.md` §2: Design Approval, Implementation Authorization, and Batch Execution Permission. Case C does **not** introduce a fourth operational authority type. Next Spec Transition Nomination is non-operational; Nomination Records do not grant operational authority.
- Nomination Records **cannot** satisfy Design Approval, Implementation Authorization, or Batch Execution Permission.

---

## Enforcement Constraints

The Governance Enforcer is an enforcement mechanism only.

The following rules are mandatory:

- Enforcement does not grant authority.
- Design Approval is not Implementation Authorization.
- Review-gate approval is not Implementation Authorization.
- Review-gate approval controls batch progression only.
- Status text in `tasks.md`, reports, notes, or summaries is not execution authority.
- **Nomination Records** are evidence-only, non-authorizing artifacts per `execution-policy.md` v1.4.0 § Nomination and Execution Policy. They **cannot** satisfy authorization checks or substitute for missing Design Approval, Implementation Authorization, or Batch Execution Permission.
- Governance state / snapshot artifacts, checkpoint summaries, status reports, and audit records are evidence-only; they cannot satisfy authorization checks or substitute for missing Design Approval, Implementation Authorization, or Batch Execution Permission.
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
  - **governance-precondition failure (Case C)**,
  - batch progression,
  - execution-policy violation,
  - coding-rule violation.

**Case C reporting (mandatory):**  
When Case C is triggered, the defect MUST be classified as **governance-precondition failure (Case C)** — separately from authority resolution failures, batch progression failures, and execution-policy violations that are not Case C.

The report MUST include the exact Case C classification message:

> `Case C — Governance precondition failure: transition nomination record required.`

The report MUST also include the exact HALT message from `execution-policy.md` v1.4.0:

> `Governance precondition failure: transition nomination record required.`

Reports must be explicit enough for remediation without redefining authority.

---

## Document Control

- Version: 1.3.0
- Last Updated: 1405/04/03 | 2026/06/24
- Change: Case C governance-precondition HALT; Nomination Record exclusions; HALT precedence Case C → Case A → Case B; aligned with `execution-policy.md` v1.4.0
- Owner: DormSys Architecture Team

This ownership line is for document maintenance only.

It does not grant operational authority.
