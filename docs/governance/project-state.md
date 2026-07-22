# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only. Not a decision ledger. Does not supersede `docs/governance/open-decisions.md`.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/31 | 2026-07-22 | Fixed registry DRAFT/RATIFIED conflict; recorded REGISTRY-RATIFY-02 in project-state_

**Authority note:** OBSERVED. `.dormSys` registries show Lead ratification (`REGISTRY-RATIFY-02`, 2026-07-22T11:31:02Z). Body headers aligned to RATIFIED. Scope limits unchanged (blockers / `.specify/**` / `docs/governance/**` not cleared). pending Lead commit.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Registry body authority | `.dormSys/database-map.md` | DRAFT / await `RATIFY REGISTRIES` → **RATIFIED** (`REGISTRY-RATIFY-02`) + scope limit | frontmatter already `ratified: true`; body aligned |
| Registry body authority | `.dormSys/open-decisions.md` | same DRAFT conflict → **RATIFIED** + scope limit; DR-REG-04 “this draft” → “this registry” | frontmatter + body |
| Registry body authority | `.dormSys/spec-catalog.md` | same DRAFT conflict → **RATIFIED** + scope limit | frontmatter + body |
| Snapshot hashes (DR-REG-07) | three registries | recomputed after body fix | `snapshot_sha256` match verified |
| Governance status mirror | `docs/governance/project-state.md` | suite-only session → records REGISTRY-RATIFY-02 | this file |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🧑‍⚖️ **Human Decision** — Lead commit of registry authority-header alignment + prior suite fixes; open DRs remain ACCEPTED-OPEN (DR-REG-03/04/05) without blocking Discovery

Options for Lead:
1. Commit registry header/hash + prior green-suite fixes together
2. Split: registry governance commit vs code/test commit
3. Hold until COMMIT-SEQ-01 branch decision

---

## 5. Open Decisions (mirror only)

| ID | Summary |
|----|---------|
| REGISTRY-RATIFY-02 | **OBSERVED DONE** — `.dormSys` registries ratified (accuracy only; scope_note limits) |
| DR-REG-03 / 04 / 05 | **OPEN** (ACCEPTED-OPEN at ratify; deferred, does not block Discovery) |
| DR-REG-07 | **ACCEPTED** — exclusion hash convention applied |
| COMMIT-SEQ-01 branch | Lead: allow on `main` vs checkout release branch |

Canonical: `.dormSys/open-decisions.md` (RATIFIED registry; DR-REG-04 still open vs `docs/governance/open-decisions.md`)

---

## 6. Lifecycle Matrix (affected)

| Item | Status |
|------|--------|
| REGISTRY-RATIFY-02 (`.dormSys` trio) | ✅ OBSERVED RATIFIED; body headers aligned this prompt |
| Registry DRAFT/RATIFIED conflict | ✅ CLOSED (headers + hashes) |
| Full suite / PHPStan gate | ✅ OBSERVED GREEN (prior prompt; 2006 / PHPStan 0) |
| DR-REG-03/04/05 | ⏳ OPEN ACCEPTED-OPEN |
| COMMIT-SEQ-01 | ❌ BLOCKED T0.5 |

---

## 7. Next Step

**Action:** Lead commit authority-header alignment (and optionally prior suite/architecture fixes).  
**Owner:** Lead  
**Gate:** none beyond Lead commit / COMMIT-SEQ-01  
**Target files:** `.dormSys/database-map.md`, `.dormSys/open-decisions.md`, `.dormSys/spec-catalog.md`, `docs/governance/project-state.md`  
**Done when:** Lead commits; developers read RATIFIED body + frontmatter without conflict  
**Blocker:** none for this ambiguity fix  
**Suggested user prompt:**
> Commit the .dormSys registry authority-header alignment and project-state update

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| Registry DRAFT vs ratified:true | CLOSED | body aligned to REGISTRY-RATIFY-02; hashes refreshed |
| project-state missing ratify mirror | CLOSED | §0/§5/§6/§7 updated this prompt |
| DR-REG-03/04/05 | OPEN | ACCEPTED-OPEN; deferred post-ratify |
| Host `php artisan test` + DB_HOST=pgsql | OPEN | run via Sail/`docker compose exec laravel.test` |
| COMMIT-SEQ-01 T0.5 | OPEN | branch mismatch |
| Results→registrations FK | BLOCKED | frozen parent table |
