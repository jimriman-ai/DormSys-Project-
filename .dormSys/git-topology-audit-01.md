# GIT-TOPOLOGY-AUDIT-01 (READ-ONLY)

> Captured: 1405/05/01 (2026-07-23)  
> Mode: **read-only** — no commit / push / pull / merge / rebase / reset / branch delete / tag change.  
> Working tree left untouched by this wave (report + progress-log only).

---

## 0. Snapshot

| Item | Value | Evidence |
|------|-------|----------|
| Current branch | `release/f2-employee-auth-ui-l9` | `git rev-parse --abbrev-ref HEAD` |
| HEAD | `6e43c0f` — DOMAIN-GAP-DISCOVERY-02 بسته شد | `git log -1` |
| Upstream | `origin/release/f2-employee-auth-ui-l9` **ahead 2** | `git status -sb` |
| Default remote HEAD | `origin/main` | `git symbolic-ref refs/remotes/origin/HEAD` |
| Dirty working tree | **26** paths (A2 / Stage-1 / XMOD / baseline docs, etc.) | `git status --porcelain` count |
| Stashes | **5** entries | `git stash list` |
| Detached HEAD? | **No** | on named branch |

**Integration targets used for classification:** `main`, `develop`, `release/f2-employee-auth-ui-l9`.

**Count convention:** `git rev-list --left-right --count A...B` → left = only on A, right = only on B.

---

## 1. Topology (how lines relate)

```
develop / origin/develop @ 7f43cc9
    │
    ├─► release/f2… (local) @ 6e43c0f  (+5 commits vs develop; ahead 2 of origin/release)
    │         origin/release @ a41dc8d
    │
    └─► main (local) @ cb1ade7  (+5 different commits vs develop; ahead 2 of origin/main)
              origin/main @ e0bf22b
```

**Evidence — divergence (not merged into each other):**

| Compare | Commits only on left…right | Command |
|---------|----------------------------|---------|
| `main...release` | 5 … 5 | `git rev-list --left-right --count main...release/f2-employee-auth-ui-l9` |
| `develop..release` | 5 unique on release | `git log --oneline develop..release/f2-employee-auth-ui-l9` |
| `develop..main` | 5 unique on main | `git log --oneline develop..main` |
| `main..develop` | empty | `git log --oneline main..develop` |

`develop` **is** an ancestor of both `main` and `release` tips’ histories at fork point `7f43cc9`, but **`main` and `release` have diverged** with disjoint tip commits (registry/DOM-GAP work vs release DOM-GAP restores). Neither tip contains the other’s unique commits.

**Archive tags already present (preservation anchors):**  
`archive/release-f2-pre-develop-7f43cc9`, `archive/001-technical-foundation-fcf44d5`, `archive/011-phase-e-exit-2ee1d93`, `archive/spec02-baseline-1b31bce`, `archive/security-g-phase-local-9f5fe23`, `archive/security-g-phase-remote-f5fb7dc`, plus backup/spec tags.

---

## 2. Branch classification

### 2.1 Local branches

#### `release/f2-employee-auth-ui-l9` — **ACTIVE**

| Field | Value |
|-------|-------|
| Last commit | `6e43c0f` (2026-07-23) DOMAIN-GAP-DISCOVERY-02 بسته شد |
| Remote tracking | `origin/release/f2-employee-auth-ui-l9` (ahead **2**, behind 0) |
| Contains unique commits | **Yes** — 5 vs `develop` / vs `main` (fe34ce5…6e43c0f) |
| Relation to main/develop/release | Current work line; **not** ancestor of `main`; tip **is** release |

Evidence: `git branch -vv`; `git log --oneline develop..release/f2-employee-auth-ui-l9`.

**Do not delete.** Uncommitted A2/Stage-1/XMOD/baseline work sits here.

---

#### `main` — **ACTIVE** / **NEEDS_REVIEW** (integration)

| Field | Value |
|-------|-------|
| Last commit | `cb1ade7` — test suite / PHPStan not green (message truncated style) |
| Remote tracking | `origin/main` (ahead **2**) |
| Contains unique commits | **Yes** — 5 vs `develop` / vs `release` (10f9860…cb1ade7) |
| Relation | Default remote target; **diverged** from current release tip |

Evidence: `git log --oneline develop..main`; `git status -sb` on checkout would show ahead 2 (from `branch -vv`).

**Do not delete.** Decide how registry/`main`-only commits relate to `release` before any merge salad.

---

#### `develop` — **ACTIVE**

| Field | Value |
|-------|-------|
| Last commit | `7f43cc9` DECISION D-001 CLOSED |
| Remote tracking | `origin/develop` (synced 0/0) |
| Contains unique commits | **No** unique vs itself; tip **is** ancestor of both `main` and `release` histories at fork |
| Relation | Shared base for current fork |

Evidence: `git merge-base --is-ancestor develop main` → YES; same for release tip’s ancestors at `7f43cc9`.

---

#### `security/g-phase-dormitory-admin-ui` — **NEEDS_REVIEW** (merged tip + divergent remote)

| Field | Value |
|-------|-------|
| Last commit (local) | `9f5fe23` |
| Remote tracking | `origin/security/g-phase-dormitory-admin-ui` — **ahead 11, behind 5** (diverged) |
| Contains unique commits | Local tip **is ancestor of `main`** (reachable from main). Remote tip `f5fb7dc` is **not** ancestor of main. |
| Relation | Local tip content preserved on `main`; remote tip is parallel rewrite |

Evidence:

- `git merge-base --is-ancestor security/g-phase-dormitory-admin-ui main` → YES  
- `git merge-base --is-ancestor origin/security/g-phase-dormitory-admin-ui main` → NO  
- Same **patch-id** for `f5fb7dc` and `0feff0e` (remote vs local “DormitoryAdminSecurityRemediationTest” commits) → duplicated history via rewrite, not lost unique content for that patch.

Archive tags: `archive/security-g-phase-local-9f5fe23`, `archive/security-g-phase-remote-f5fb7dc`.

**Not auto MERGED_SAFE_TO_DELETE** until Lead confirms remote-only SHAs are disposable duplicates.

---

#### `011-reporting-projections` — **NEEDS_REVIEW**

| Field | Value |
|-------|-------|
| Last commit (local) | `2ee1d93` Phase E — Exit Readiness Cleanup: CLOSED |
| Remote tracking | `origin/011-reporting-projections` — local **ahead 1** |
| Contains unique commits | **Yes** — `2ee1d93` not on origin; origin tip `5a13365` **is** ancestor of `main` |
| Relation | Remote tip merged into main lineage; local tip is **extra** commit |

Evidence: `git log --oneline origin/011-reporting-projections..011-reporting-projections` → `2ee1d93`; `git merge-base --is-ancestor origin/011-reporting-projections main` → YES.

Tag: `archive/011-phase-e-exit-2ee1d93`.

---

#### `001-technical-foundation` — **NEEDS_REVIEW** (stale tip with unique commits)

| Field | Value |
|-------|-------|
| Last commit | `fcf44d5` |
| Remote tracking | synced with `origin/001-technical-foundation` |
| Contains unique commits | **Yes** — 5 commits not in `develop` |
| Relation | Not ancestor of main/develop/release |

Evidence: `git log --oneline develop..001-technical-foundation`.  
Tag: `archive/001-technical-foundation-fcf44d5`.

---

#### `spec02-baseline` — **NEEDS_REVIEW** (stale tip with unique commits)

| Field | Value |
|-------|-------|
| Last commit | `1b31bce` |
| Remote tracking | synced |
| Contains unique commits | **Yes** — 6 commits not in `develop` |
| Relation | Not ancestor of main/develop/release |

Evidence: `git log --oneline develop..spec02-baseline`.  
Tag: `archive/spec02-baseline-1b31bce`.

---

### 2.2 Remote-only branches (no local counterpart)

| Branch | Last commit | Status | Unique vs main? | Notes |
|--------|-------------|--------|-----------------|-------|
| `origin/docs/spec03-governance-alignment` | `579b3ee` | **MERGED_SAFE_TO_DELETE** (candidate) | Tip ancestor of main/develop/release | No local branch |
| `origin/feat/spec03-employee-mvp` | `6c213c8` | **MERGED_SAFE_TO_DELETE** (candidate) | Tip ancestor of main/develop/release | No local |
| `origin/fix/lottery-enroll-after-close-race` | `d8a833b` | **MERGED_SAFE_TO_DELETE** (candidate) | Tip ancestor of main/develop/release | No local |
| `origin/foundation/dormitory-admin-tables` | `60d9b6c` | **MERGED_SAFE_TO_DELETE** (candidate) | Tip ancestor of main/develop/release | Tip also appears in security local history |

Evidence pattern: `git merge-base --is-ancestor <remote> main` → YES; `git rev-list --left-right --count main...<remote>` → right = 0.

**“Safe to delete” here means tip commits are already reachable from `main` — not authorization to delete.** Lead must still approve remote prune.

---

## 3. Unreferenced / visually separated commits

### 3.1 Reflog-only (not on any branch)

| Commit | Message | Classification |
|--------|---------|----------------|
| `cf193ac` | chore(ledger): declare hash_scope=… | **UNREFERENCED_COMMIT** (reachable via reflog) |

Evidence: `git branch -a --contains cf193ac` → empty; `git log -1 cf193ac` exists.  
Reflog: `HEAD@{5}: reset: moving to HEAD~1` then later `b2f16d1` with similar subject.  
**patch-id of `cf193ac` ≠ `b2f16d1`** → not a byte-identical duplicate; content may differ — **do not assume safe to drop** without inspecting diff.

### 3.2 Stash objects

Stash refs hold WIP; example graph node `refs/stash` / `9efe651` (“On main: wip: untracked ci.yml…”).  
`git branch -a --contains` empty for sample stash commits → **not lost**, held by stash. **NEEDS_REVIEW** before `stash drop`.

### 3.3 `git fsck --unreachable --no-reflogs`

Many unreachable trees/blobs/commits listed (sample truncated). These are **not proof of lost product work**; many are byproducts of resets/amends. **No cleanup recommended in this wave.**

### 3.4 Lost vs visually separated?

| Case | Verdict |
|------|---------|
| `main` vs `release` tips | **Visually separated (diverged)** — both tips alive on named branches; nothing “lost” |
| security remote vs local | **Rewrite duplicate** (matching patch-id on remediation test commit); tip SHAs differ; archive tags retain both |
| `cf193ac` | **Reflog-only** — not on branch; not proven identical to `b2f16d1` |
| Dirty WT (26 files) | **Uncommitted** — not in any commit; highest loss risk if reset/clean |

---

## 4. Recommendation

### Current

- Active checkout: **`release/f2-employee-auth-ui-l9`**, ahead of origin by 2 commits, **dirty WT with multi-wave uncommitted work**.
- **`main` and `release` diverge** from shared `develop@7f43cc9` with two different 5-commit stacks.
- Several remote feature tips are fully contained in `main` (merged candidates).
- Security / 011 show **squash/rebase-style duplicate SHAs** or local-only tips; archive tags already exist for several tips.
- Stashes and at least one reflog-only ledger commit exist.

### Recommended (safe cleanup plan — **authorize separately; not executed**)

1. **First:** commit or explicitly stash **current dirty tree** on `release` (Lead-directed commit wave) — before any branch prune.  
2. **Do not** merge/rebase `main`↔`release` until Lead picks integration authority.  
3. **Remote prune candidates** (after Lead OK):  
   `docs/spec03-governance-alignment`, `feat/spec03-employee-mvp`, `fix/lottery-enroll-after-close-race`, `foundation/dormitory-admin-tables`.  
4. **Local NEEDS_REVIEW** (inspect unique commits, then optional delete **only if** archive tag + Lead OK):  
   `001-technical-foundation`, `spec02-baseline`, `011-reporting-projections` (preserve `2ee1d93` first), `security/g-phase-dormitory-admin-ui` (after confirming remote duplicates).  
5. **Leave alone:** `main`, `develop`, `release/f2-employee-auth-ui-l9`, all `archive/*` tags, stashes until inventoried.  
6. **Optional later:** inspect `cf193ac` vs `b2f16d1` diff; only then consider reflog expiry.

### Reason

Dirty multi-wave tree + diverged `main`/`release` means “cleanup now” risks salad history or losing uncommitted A2/Stage-1 work. Topology audit first; commit current work second; prune only tips already reachable from `main` with tags as seatbelts.

### Risks

- Deleting `001` / `spec02` / local `011` tip without review → lose unique commits.  
- Force-aligning security remote/local → confuse PRs still pointing at old SHAs.  
- Merging `main` into `release` (or reverse) without decision → duplicate registry/DOM-GAP narratives.  
- `git clean` / hard reset on dirty WT → **irrecoverable** uncommitted waves.

### Trade-offs

| Cleanup | Preservation |
|---------|----------------|
| Fewer remote branches, clearer graph | Keeps archaeology; archive tags already help |
| Faster clone/fetch | Diverged main/release remains until intentional integrate |
| Dropping reflog/unreachable | May discard unrecovered experiments (`cf193ac`) |

---

## 5. Commands run (read-only)

```
git rev-parse --abbrev-ref HEAD
git status -sb
git remote -v
git branch -vv
git branch -r
git log --oneline --decorate -15 HEAD
git log --oneline --decorate --graph --all -40
git symbolic-ref refs/remotes/origin/HEAD
git show-ref --heads --tags
git stash list
git reflog -20
git rev-list --left-right --count <A>...<B>
git merge-base --is-ancestor <commit> <branch>
git log --oneline <A>..<B>
git branch -a --contains <commit>
git show … | git patch-id
git fsck --unreachable --no-reflogs   # sample only
git tag --points-at <commit>
```

No mutating Git commands were executed.

---

## 6. Failure block (not applicable)

Audit completed; not BLOCKED.
