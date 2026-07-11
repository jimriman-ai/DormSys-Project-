# Governance Patterns Index

This index lists reusable governance patterns under `.specify/governance/patterns/`.

Patterns constrain process and readiness. They do **not** grant Design Approval, Implementation Authorization, or Batch Execution Permission. Authority ownership remains only in `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`.

| Pattern | Path | Description |
| ------- | ---- | ----------- |
| Integration Readiness Gate | [`integration-readiness-gate.md`](integration-readiness-gate.md) | Mandatory readiness gate before cross-module Integration Implementation Authorization. |

## Usage

1. Locate the applicable pattern in this index.
2. Apply the canonical pattern file — do not duplicate its full content into specs or handoffs.
3. Record required artifact sections (for example `## Integration Readiness Gate`) in the relevant authorization template or handoff instance.

## Related reusable artifacts

- Template: `.specify/templates/integration-implementation-authorization-template.md`
- Issuance / pre-auth guidance: `.specify/governance/execution-policy.md` § Integration Implementation Authorization Issuance
- Implementation discipline: `.specify/governance/coding-rules.md`
- Review controls: `.specify/governance/review-checklist.md`
