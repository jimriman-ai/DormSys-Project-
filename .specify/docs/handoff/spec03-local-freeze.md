# spec03 Local Freeze — Handoff & Publication

**Frozen:** 2026-06-26  
**Phase:** **PUBLICATION** — remote live; PR topology correction in progress.  
**Remote:** `https://github.com/jimriman-ai/DormSys-Project-.git`  
**Status:** MVP complete (T001–T026a), stopped before US2.

> **Freeze scope:** Local engineering freeze (recoverable + scope lock). Not a catalog hard freeze.

---

## Backup tags

| Tag | Commit | Content |
|-----|--------|---------|
| `backup/spec03-governance` | `73f0291` | PR-1 docs-only |
| `backup/spec03-mvp` | `2dbe327` | PR-2 Employee MVP |

Recover: `git checkout backup/spec03-mvp` or `git checkout backup/spec03-governance`

---

## Branches (published)

| Branch | Commit | Role |
|--------|--------|------|
| `spec02-baseline` | `83c1771` | PR-1 base |
| `docs/spec03-governance-alignment` | `73f0291` | PR-1 head |
| `feat/spec03-employee-mvp` | `2dbe327` | PR-2 head (stacked) |
| `001-technical-foundation` | `47a84e5`+ | reference only — **not** in merge plan |

### Local coordination commits (not PR-1/PR-2 scope)

`0427182`, `d52922b`, `47a84e5` and later handoff edits on `001-technical-foundation` only.

---

## Canonical PR mapping (do not change)

| Role | GitHub | Base | Head |
|------|--------|------|------|
| **PR-1** | [#2](https://github.com/jimriman-ai/DormSys-Project-/pull/2) | `spec02-baseline` | `docs/spec03-governance-alignment` |
| **PR-2** | *open stacked PR* | `docs/spec03-governance-alignment` | `feat/spec03-employee-mvp` |

| PR | Title |
|----|-------|
| PR-1 | `docs: align governance for spec03 (ADR-006, Dependent ownership, agent docs)` |
| PR-2 | `feat(employee): spec03 MVP through T026a (CD-012 boundary)` |

### Invalid PRs — close

| PR | Why |
|----|-----|
| [#1](https://github.com/jimriman-ai/DormSys-Project-/pull/1) | `001-technical-foundation` — out of plan |
| [#3](https://github.com/jimriman-ai/DormSys-Project-/pull/3) | wrong base (`spec02-baseline` → feat) |

**Stacked PR compare link:**  
https://github.com/jimriman-ai/DormSys-Project-/compare/docs/spec03-governance-alignment...feat/spec03-employee-mvp

---

## Publication update (2026-06-26)

Published refs on `jimriman-ai/DormSys-Project-`:

- `spec02-baseline`, `docs/spec03-governance-alignment`, `feat/spec03-employee-mvp`
- `001-technical-foundation` (reference)
- `backup/spec03-governance`, `backup/spec03-mvp`

PR topology correction required:

- Close PR #1 and PR #3
- Open canonical stacked PR-2 (link above)
- Merge order: **PR #2 → stacked PR-2**

Constraints unchanged: **no T027+, no US2** until post-MVP authorization checkpoint.

---

## Roadmap

| Step | Status | Action |
|------|--------|--------|
| 1 | done | Local freeze + handoff |
| 2 | in progress | Publish + correct PR topology |
| 3 | pending | Merge PR-1 (#2) → merge PR-2 (stacked) |
| 4 | pending | **Post-MVP authorization checkpoint** |
| 5 | blocked | US2 / T027+ only with explicit go-ahead |

---

## Post-MVP authorization checkpoint (after PR-2 merge)

- [ ] Refresh `spec-catalog.md`: spec03 → MVP implemented (Wave 1A)
- [ ] Confirm local freeze ≠ catalog hard freeze
- [ ] US2 scope sanity (Department simple aggregate)
- [ ] Record explicit **US2 authorized** or **spec04 planning** decision

### Interim decision (recommended)

Pause at checkpoint after PR merges. Run validation/consistency review (handoff, docs, implementation). Defer T027+, US2, and forward scope until explicit authorization.

---

## PR-2 test plan

```bash
docker compose exec laravel.test php artisan test tests/Feature/Modules/Employee tests/Unit/Modules/Employee tests/Architecture/EmployeeSupplierBoundaryTest.php
docker compose exec laravel.test vendor/bin/phpstan analyse app/Modules/Employee
```

Boundary: BT-01–BT-05, `identity_id` no FK, no `Identity\Infrastructure` imports.

---

## Copy-paste: close PR #1

```markdown
Closing this PR because it is outside the current canonical publication / merge plan for spec03.

The planned review path is limited to:
1. governance alignment PR (#2)
2. stacked employee MVP PR

The `001-technical-foundation` branch remains published for reference but is not part of the PR-1 / PR-2 merge sequence.
```

---

## Copy-paste: close PR #3

```markdown
Closing this PR because it was opened against the wrong base branch.

Correct topology:
- PR-1: `spec02-baseline` <- `docs/spec03-governance-alignment`
- PR-2: `docs/spec03-governance-alignment` <- `feat/spec03-employee-mvp`

A corrected stacked PR replaces this one.
```

---

## Copy-paste: merge comment PR-1 (#2)

```markdown
Merging PR-1 as the canonical governance-alignment step for spec03.

This establishes the documentation/governance baseline before reviewing the stacked employee MVP PR.

Next: merge stacked PR-2, then post-MVP authorization checkpoint. No T027+ / US2 yet.
```

---

## Copy-paste: merge comment PR-2 (stacked)

```markdown
Merging PR-2 as the stacked spec03 employee MVP through T026a.

PR-1 handled governance; this PR completes the implementation layer only.

Next: post-MVP authorization checkpoint. No T027+ / US2 yet.
```

---

## Pre-merge checklists

### PR-1 (#2)

- [ ] Base `spec02-baseline`, head `docs/spec03-governance-alignment`
- [ ] Diff is governance/docs only (ADR-006, Dependent ownership, agents, FREEZE-LOG, boundary stub)
- [ ] No implementation / env runtime in diff

### PR-2 (stacked)

- [ ] Base `docs/spec03-governance-alignment`, head `feat/spec03-employee-mvp`
- [ ] MVP only (T001–T026a), CD-012 boundary
- [ ] No governance re-diff; no US2 / T027+

---

## HOLD rules (until checkpoint authorization)

- No feature work on spec03
- No US2 (`T027+`)
- No amend of `73f0291` or `2dbe327`
- No branch topology surgery
