# spec03 Local Freeze — GitHub Handoff (do not push until ready)

**Frozen:** 2026-06-26  
**Phase:** **HOLD** — state valid; no feature work until publish or explicit US2 authorization.  
**Status:** MVP complete (T001–T026a), stopped before US2. No remote configured.

> **Freeze scope:** This is a **local engineering freeze** (recoverable state + scope lock). It is **not** a catalog hard freeze or global governance lifecycle closure.

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
| `001-technical-foundation` | `0427182`+ | local integration branch (includes handoff commits only) |

### `0427182` — local coordination only

Commit `0427182` (`docs: add spec03 local freeze handoff`) and any follow-up handoff edits are **local coordination / publication helpers**. They are **not** part of PR-1 or PR-2 product scope.

On publish:
- **Omit** from PR-1 and PR-2 diffs (PR tips remain `73f0291` and `2dbe327`)
- **Or** include in a separate internal/docs PR after MVP merges
- **Do not** rebase PR branches onto handoff commits

## Canonical PR mapping (do not change)

| PR | Base | Head |
|----|------|------|
| PR-1 | `spec02-baseline` (`83c1771`) | `docs/spec03-governance-alignment` (`73f0291`) |
| PR-2 | `docs/spec03-governance-alignment` (`73f0291`) | `feat/spec03-employee-mvp` (`2dbe327`) |

| PR | Title |
|----|-------|
| PR-1 | `docs: align governance for spec03 (ADR-006, Dependent ownership, agent docs)` |
| PR-2 | `feat(employee): spec03 MVP through T026a (CD-012 boundary)` |

## Roadmap (single path)

| Step | When | Action |
|------|------|--------|
| **1** | Now (done) | Handoff + canonical mapping + `0427182` clarification |
| **2** | Remote ready | Publish per push order below; PR-1 → merge → PR-2 → merge |
| **3** | After PR-2 merge | **Post-MVP authorization checkpoint** (before T027) |
| **4** | Explicit go-ahead only | US2 (`T027+`) via `/speckit-implement` |

### Step 3 — Post-MVP authorization checkpoint (before T027)

- [ ] Refresh `spec-catalog.md`: spec03 → MVP implemented (Wave 1A), not hard freeze
- [ ] Confirm distinction: local freeze ≠ catalog hard freeze
- [ ] US2 scope sanity: Department stays simple aggregate (no eligibility/read-contract creep)
- [ ] Record explicit **US2 authorized** decision

## When ready to publish (Step 2)

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

## HOLD rules (until Step 2 or Step 4 authorization)

- No feature work on spec03
- No US2 (`T027+`)
- No push / rebase / squash / branch topology changes
- No amend of `73f0291` or `2dbe327` (use new commits for fixups)
- Keep backup tags and this handoff as canonical truth
