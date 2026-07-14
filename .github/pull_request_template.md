## Summary

<!-- Brief description of changes -->

## Test plan

- [ ] Tests pass (`sail artisan test` or `php artisan test`)
- [ ] Architecture decay prevention passes (`composer run arch`)
- [ ] PHPStan level 8 passes (`composer run phpstan`)
- [ ] Pint check passes (`composer run pint -- --test`; apply with `composer run pint` if needed)

---

## Architecture boundary attestation

Complete when the PR touches `app/Modules/`, `app/Integrations/`, `app/Providers/`, or `bootstrap/providers.php`.  
Otherwise write **N/A** and skip this section.

**Reference:** [docs/architecture/pr-review-checklist.md](../docs/architecture/pr-review-checklist.md)

### CI (merge-blocking)

- [ ] `composer run arch` passes locally
- [ ] No new undocumented entries in `tests/Architecture/architecture.php` registries

### What changed (check all that apply)

- [ ] **None** — no architecture-boundary files touched
- [ ] New or changed **cross-module bridge** (`app/Integrations/`)
- [ ] New or changed **Application contract/port** consumed across modules
- [ ] **Composition root** change (`IntegrationServiceProvider`, module `*ServiceProvider`, `bootstrap/providers.php`)
- [ ] **Module inventory** change (`architectureModuleNames()` or `architectureMatrixExcludedActiveModules()`)
- [ ] **Legacy exception registry** change (`architectureLegacyCrossModuleAdapterPaths()` or related)
- [ ] Within-module only (no new cross-module edges)

### Author confirmations

- [ ] New cross-module edges use `app/Integrations/` + `IntegrationServiceProvider::register()` — **not** legacy adapter locations ([integration-layer-policy.md](../docs/architecture/integration-layer-policy.md))
- [ ] Did **not** copy Lottery `RequestReadAdapter`, Reporting↔Audit adapters, or Identity `SpatieAuditPermissionReadAdapter` patterns
- [ ] Did **not** add foreign **Domain** or **Infrastructure** imports to Application (matrix modules)
- [ ] Did **not** bind integration ports (`ApprovedRequestReadPort`, `PendingRequestReadPort`, etc.) in module providers

### Exception request (if any)

If this PR needs a **new tolerated legacy exception**, stop and get architecture approval **before** merge.

- [ ] **No exception requested**
- [ ] **Exception requested** — link approval (issue/ADR/PR comment) and list registry function updated:

<!-- e.g. architectureLegacyCrossModuleAdapterPaths() -->

**Approver / ticket:**

---

## Architecture review (reviewer)

<!-- Reviewer completes — see docs/architecture/pr-review-checklist.md -->

- [ ] Verdict: **Approve** / **Request changes** / **Architecture approval required**
- [ ] `composer run arch` green in CI
- [ ] No new legacy-pattern copy-paste
- [ ] Exception request valid (if present)

**Reviewer notes:**

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
