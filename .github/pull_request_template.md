## Summary

<!-- Brief description of changes -->

## Test plan

- [ ] Tests pass (`sail artisan test`)
- [ ] Architecture decay prevention passes (`composer run arch`)
- [ ] PHPStan level 8 passes
- [ ] Pint formatting applied

---

## Architecture boundary check

Complete when the PR touches `app/Modules/`, `app/Integrations/`, `app/Providers/`, or `bootstrap/providers.php`.

- [ ] `composer run arch` passes (mandatory)
- [ ] New cross-module edges use `app/Integrations/` + `IntegrationServiceProvider` (not legacy adapter locations)
- [ ] No new entries needed in `architectureLegacyCrossModuleAdapterPaths()` unless explicitly approved
- [ ] If adding an active module: updated `architectureModuleNames()` or `architectureMatrixExcludedActiveModules()` in `tests/Architecture/architecture.php`

If no architecture files are changed, write **N/A**.

---

## Governance / Authority Drift Check

Complete this section **only if** this PR modifies any governance-related file, including:

- `.specify/docs/catalog-decisions.md`
- `.specify/governance/_meta/authority-model.md`
- `.specify/governance/execution-policy.md`
- `.specify/governance/governance-enforcer.md`
- `.specify/governance/file-precedence.md`
- `.specify/governance/batch-strategy.md`
- `.specify/governance/coding-rules.md`
- `.specify/governance/review-checklist.md`
- `.specify/governance/decision-index.md`

If no governance files are changed, write **N/A** and skip the items below.

- [ ] `Authority Drift Prevention (MANDATORY)` from `.specify/governance/review-checklist.md` completed
- [ ] No new authority ownership map introduced outside `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`

### Final control question (required for governance PRs)

> If `catalog-decisions.md` disappeared, could this change make any other document able to determine who owns authority?

**Required answer: NO**

- [ ] Answer: **NO**

If the answer is anything other than **NO**, this PR **MUST NOT** be merged.
