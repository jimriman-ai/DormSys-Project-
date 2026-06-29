## GOVERNANCE ENFORCEMENT PROTOCOL

The Governance Enforcer is an automated agent responsible for validating that all batch executions adhere to the defined governance rules. Its primary function is to:

- **Monitor**: Track the status and progress of all active batches.
- **Validate**: Check compliance against `.specify/governance/file-precedence.md`, `.specify/governance/coding-rules.md`, `.specify/governance/execution-policy.md`, and the active spec's `tasks.md` and `spec.md`.
- **Differentiate Decisions**: Treat Design Approval, Implementation Authorization, and Batch Execution Permission as distinct governance decisions.
- **Halt**: Immediately stop execution if any governance rule is violated or if execution authority is missing, ambiguous, duplicated, or revoked.
- **Report**: Generate a detailed report of the violation, citing the specific rule and its location.
- **Audit**: Maintain an immutable log of all validations and violations.


---

## VALIDATION ORDER

The Enforcer validates rules in a predefined order to ensure efficient and logically safe checks:

1. **Authority Resolution**: Determine the governing source using `.specify/governance/file-precedence.md`.
2. **Batch Integrity**: Verify `tasks.md` and batch map consistency.
3. **Design Approval Status**: Confirm the active spec's `spec.md` or designated design-status artifact shows design approval.
4. **Implementation Authorization**: Check `execution-policy.md` and the recognized authorization record for explicit implementation authority for the current spec and wave.
5. **Batch Execution Permission**: Confirm that the current batch is the next eligible batch and that required human review approval exists.
6. **Execution Policy Compliance**: Check sequencing, halt conditions, wave constraints, and review-gate requirements.
7. **Coding Standards**: Enforce `coding-rules.md`.

---
## HARD RULE

Any governance violation results in immediate **HALT** of execution.

A halt is mandatory when any of the following occurs:
- The authoritative source cannot be resolved unambiguously.
- Design approval is missing where required.
- Implementation authorization is missing, duplicated, ambiguous, revoked, expired, or not active for the current wave.
- A review gate has not been passed for the next batch.
- A lower-tier artifact attempts to imply or replace higher-tier authority.

## DECISION INTERPRETATION RULES

The Enforcer must apply the following interpretation rules without exception:

1. Design Approval confirms design readiness only.
2. Implementation Authorization permits implementation to start or continue only when active and valid.
3. Batch Execution Permission permits progression to the next eligible batch only.
4. Design Approval must not be interpreted as Implementation Authorization.
5. Review-gate approval must not be interpreted as Implementation Authorization.
6. Status text in `tasks.md`, reports, or progress notes must not be treated as execution authority unless a higher-authority governance artifact explicitly assigns that role.
