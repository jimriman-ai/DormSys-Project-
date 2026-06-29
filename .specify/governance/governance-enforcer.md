## GOVERNANCE ENFORCEMENT PROTOCOL

The Governance Enforcer is an automated agent responsible for validating that all batch executions adhere to defined governance rules. Enforcement does not grant authority.

Primary functions:

- **Monitor**: Track the status and progress of all active batches.
- **Validate**: Check compliance against governance inputs required by `.specify/governance/execution-policy.md`, including precedence, coding rules, batch maps, task consistency, and authority preconditions resolved via the canonical map.
- **Gate**: Block execution when preconditions, wave gates, or review gates are not satisfied.
- **Halt**: Immediately stop execution on any governance violation or unresolved authority conflict.
- **Report**: Generate a detailed violation report citing the specific rule and its location.
- **Audit**: Maintain an immutable log of all validations and violations.

---

## Authority Ownership

Canonical Governance Decision Authority ownership is defined only in:

`.specify/docs/catalog-decisions.md`

See:

`## Governance Decision Authority Map`

This document does not redefine that ownership.

---

## VALIDATION ORDER

The Enforcer validates in this order:

1. **Canonical Authority Pointer**: Load `.specify/docs/catalog-decisions.md` § Governance Decision Authority Map. HALT if ownership cannot be resolved unambiguously from that section.
2. **Precedence Resolution**: Apply `.specify/governance/file-precedence.md` for tier conflicts among loaded documents.
3. **Batch Integrity**: Verify `tasks.md` and `.specify/governance/batches/<spec>.md` consistency for the requested batch.
4. **Pre-Execution Preconditions**: Verify Design Approval and Implementation Authorization are satisfied per the canonical map and `.specify/governance/execution-policy.md` Pre-Execution Requirements.
5. **Wave Gating**: Verify wave and scope constraints per `.specify/governance/execution-policy.md` Wave Gating.
6. **Batch Progression Gate**: Confirm the current batch is the next eligible batch and that required human review approval exists per `.specify/governance/execution-policy.md` Review Gate.
7. **Execution Policy Compliance**: Check sequencing, halt conditions, scope lock, and failure-policy requirements.
8. **Coding Standards**: Enforce `.specify/governance/coding-rules.md`.

---

## HARD RULE

Any governance violation results in immediate **HALT** of execution.

A halt is mandatory when any of the following occurs:

- Canonical authority ownership cannot be resolved unambiguously from `.specify/docs/catalog-decisions.md` § Governance Decision Authority Map.
- A required precondition from the canonical map or execution policy is not satisfied.
- Design Approval is missing where required.
- Implementation authorization is missing, duplicated, ambiguous, `revoked`, `superseded`, or not active for the current wave or scope.
- A review gate has not been passed for the next batch.
- A lower-tier artifact attempts to imply or replace higher-tier authority.
- Required governance inputs are missing or unreadable.

---

## Enforcement Constraints

- Enforcement does not grant authority.
- Design Approval is not Implementation Authorization.
- Review-gate approval is not Implementation Authorization.
- Review-gate approval controls batch progression only.
- Status text in `tasks.md`, reports, or progress notes is not execution authority.

---

## Failure Handling and Escalation

On validation failure:

1. **HALT** immediately.
2. Report the violation with document path, section, and rule cited.
3. Do not continue batch execution or start the next batch.
4. If the same violation recurs after correction attempt, escalate to human review before retry.

---

## Conflict Reporting

When sources conflict:

1. **HALT** immediately.
2. Identify conflicting documents and their tiers using `.specify/governance/file-precedence.md`.
3. Report both sources with references.
4. If tier precedence does not resolve the conflict, wait for human decision.

If the conflict involves authority ownership, the canonical map in `.specify/docs/catalog-decisions.md` § Governance Decision Authority Map prevails over any local wording in other documents.
