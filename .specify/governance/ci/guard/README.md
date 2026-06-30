# Governance Guard

Read-only CI validator for DormSys core governance file consistency.

**Spec:** `.specify/governance/ci/governance-guard-layer-spec.md`  
**Rule catalog:** `.specify/governance/tests/governance-consistency-test-spec.md`

The guard detects ontology and enforcement semantic drift. It does **not** grant authority, modify governance files, or auto-remediate findings.

## Run locally

From repository root:

```bash
python .specify/governance/ci/guard/run_guard.py
```

Outputs:

- `.specify/governance/ci/guard/output/governance-drift-report.json`
- `.specify/governance/ci/guard/output/governance-drift-summary.md`

### Environment variables

| Variable | Default | Effect |
| --- | --- | --- |
| `HARD_GUARD_MODE` | `true` | CRITICAL and MAJOR findings fail the run |
| `GOVERNANCE_GUARD_STRICT_LITERALS` | `true` | Enforce documented cross-file literals |
| `ALLOW_MAJOR` | `false` | Only when `HARD_GUARD_MODE=false` |
| `ALLOW_MINOR` | `false` | Only when `HARD_GUARD_MODE=false` |

### Exit codes

| Code | Meaning |
| --- | --- |
| `0` | PASS — no blocking drift |
| `1` | CRITICAL or MAJOR governance drift detected |
| `2` | Guard execution failure (missing/unreadable inputs) |

## Tests

```bash
python -m unittest discover -s .specify/governance/ci/guard/tests -v
```

## CI

GitHub Actions workflow: `.github/workflows/governance-guard.yml`  
Job name: `governance-guard`
