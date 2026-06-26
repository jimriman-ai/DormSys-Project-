# spec03 Local Freeze — GitHub Handoff (do not push until ready)

**Frozen:** 2026-06-26  
**Status:** MVP complete (T001–T026a), stopped before US2. No remote configured.

## Backup tags

| Tag | Commit | Content |
|-----|--------|---------|
| `backup/spec03-governance` | `73f0291` | PR-1 docs-only |
| `backup/spec03-mvp` | `2dbe327` | PR-2 Employee MVP |

Recover: `git checkout backup/spec03-mvp` or `git checkout backup/spec03-governance`

## Branches

| Branch | Commit | Role |
|--------|--------|------|
| `spec02-baseline` | `83c1771` | PR-1 base (spec02 complete) |
| `docs/spec03-governance-alignment` | `73f0291` | PR-1 head |
| `feat/spec03-employee-mvp` | `2dbe327` | PR-2 head (stacked on PR-1) |
| `001-technical-foundation` | `2dbe327` | integration branch (local) |

## When ready to publish

### 1. Add remote (replace URL)

```bash
git remote add origin <GITHUB_REPO_URL>
```

### 2. Push order

```bash
git push -u origin spec02-baseline
git push -u origin docs/spec03-governance-alignment
git push -u origin feat/spec03-employee-mvp
```

Optional: `git push -u origin 001-technical-foundation` after PRs merge.

### 3. Pull requests (stacked)

| PR | Base | Head | Title |
|----|------|------|-------|
| PR-1 | `spec02-baseline` | `docs/spec03-governance-alignment` | `docs: align governance for spec03 (ADR-006, Dependent ownership, agent docs)` |
| PR-2 | `docs/spec03-governance-alignment` | `feat/spec03-employee-mvp` | `feat(employee): spec03 MVP through T026a (CD-012 boundary)` |

### PR-1 summary

- Constitution: Dependent ownership → Employee
- ADR-006: `{module}_{entity}` table naming
- Agent docs: Laravel 13, `app/Support/`, CheckIn candidate
- FREEZE-LOG: RequestApproval → CD-010
- spec03 boundary stub: active obligations

### PR-2 body

> Governance alignment was completed in a prior docs-only PR. This PR implements spec03 MVP strictly within CD-012 boundary constraints and stops at the approved MVP Gate (T026a), before US2.

### PR-2 test plan

```bash
docker compose exec laravel.test php artisan test tests/Feature/Modules/Employee tests/Unit/Modules/Employee tests/Architecture/EmployeeSupplierBoundaryTest.php
docker compose exec laravel.test vendor/bin/phpstan analyse app/Modules/Employee
```

Boundary checks: BT-01–BT-05, `identity_id` no FK, no `Identity\Infrastructure` imports.

## Do not (until PR-2 merged)

- Start US2 (T027+)
- Push without remote URL confirmed
- Amend `73f0291` or `2dbe327` (use new commits for fixups)
