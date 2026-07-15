# L9 PR Package — F2 employee-auth-ui chain closeout

**Suggested branch:** `release/f2-employee-auth-ui-l9`  
**Suggested base:** project default (`main` / `master` — confirm remote)  
**Note:** Working tree currently sits on `security/g-phase-dormitory-admin-ui` with mixed Phase G + F2 dirty files. Prefer a **dedicated F2-only branch** from the intended base for this L9 PR so dormitory-admin Phase G history does not dilute the decision boundary.

**Commit evidence:** As of prepare time, W-01…W-08 / SGAP DOC batch artifacts are largely **working-tree / untracked** — **no per-W commit SHAs** exist yet. Traceability below uses **work-breakdown + artifact paths only** (constraint: do not fabricate commit links).

---

## PR title

```
release(F2): close employee-auth-ui L9 chain (W-01…W-08 + Spec Completion Audit)
```

---

## PR description

```markdown
## Summary

Closes the **F2 `employee-auth-ui`** decision boundary: dual-guard employee login (`LoginUserAction` on default/`web` credentials → `EstablishApiSessionFromCredentialLoginAction` binds `api` + `identity`) with Shared Kernel `IdentityRoleGuard`, RateLimiter remediation (F-W07-01), security review (W-07), and verification suite (W-08), plus Spec Completion Audit DOC hygiene (SGAP dispositions). **No new UI feature slug** and **no Spec04 Auth / BO-parked packet work** are included.

## Traceability (W-01…W-08)

| ID | Status | Closing evidence |
|----|--------|------------------|
| W-01 | CLOSED | [work-breakdown.md](docs/features/employee-auth-ui/work-breakdown.md) · [l3-spec.md](docs/features/employee-auth-ui/l3-spec.md) §4 · `EmployeeLogin` + `employee.login` |
| W-02 | CLOSED | [work-breakdown.md](docs/features/employee-auth-ui/work-breakdown.md) · DGAP-07 Decision A · [open-decisions.md](docs/governance/open-decisions.md) |
| W-03 | CLOSED | [work-breakdown.md](docs/features/employee-auth-ui/work-breakdown.md) · [l3-spec.md](docs/features/employee-auth-ui/l3-spec.md) §2–§3 NO ACTION |
| W-04 | CLOSED | [work-breakdown.md](docs/features/employee-auth-ui/work-breakdown.md) · Option B L5 acceptance |
| W-05 | CLOSED | [work-breakdown.md](docs/features/employee-auth-ui/work-breakdown.md) · `app/Modules/Auth/Presentation/Livewire/EmployeeLogin.php` · views · route |
| W-06 | CLOSED | [work-breakdown.md](docs/features/employee-auth-ui/work-breakdown.md) · `app/Shared/Auth/IdentityRoleGuard.php` · BL-04 Delivered |
| W-07 | CLOSED | [work-breakdown.md](docs/features/employee-auth-ui/work-breakdown.md) · [w07-security-review-report.md](docs/features/employee-auth-ui/w07-security-review-report.md) |
| W-08 | CLOSED | [work-breakdown.md](docs/features/employee-auth-ui/work-breakdown.md) · [w08-scope.md](docs/features/employee-auth-ui/w08-scope.md) · `EmployeeLoginW08Test` + RateLimit + regression run |

**Commits:** _none attributable yet — land branch + commits before merge; do not invent SHAs._

## Security

| Finding | Disposition | Evidence |
|---------|-------------|----------|
| **F-W07-01** | FIXED + tested | `RateLimiter` in `EmployeeLogin::login` (key `employee-login:{ip}:{normalized-email}`, 5/60s); `tests/Feature/Auth/EmployeeLoginRateLimitTest.php` |
| **F-W07-02** | Risk accepted + W-08 verified | [risk-register.md](docs/governance/risk-register.md) (`F-W07-02`); W08-C-01/C-02 in `EmployeeLoginW08Test`; report [w07-security-review-report.md](docs/features/employee-auth-ui/w07-security-review-report.md) |

## Decision log

Canonical register only — **do not restate body here:**

→ [`docs/governance/open-decisions.md`](docs/governance/open-decisions.md)

Pointers for reviewers:

- **DGAP:** 07 DECIDED-A; 01/02/04/10 NOT-A-GAP; 09 FROZEN (BL-B1-01); 03/05/06/08 PARKED
- **SGAP:** 01/04/06/09 CLOSED; 02/03 ACCEPTED-MINIMAL; 05/07 PARKED; 08 DEFERRED

`.specify/governance/open-decisions.md` is **pointer-only** (non-canonical).

## Test evidence (W-08)

```text
php artisan test --filter=EmployeeLoginW08Test
# passed: 11

php artisan test tests/Feature/Auth/EmployeeLoginRateLimitTest.php \
  tests/Feature/Auth/LoginUserActionTest.php \
  tests/Feature/Auth/AuthContractRegressionTest.php \
  tests/Feature/Auth/AuthEdgeCaseTest.php \
  tests/Feature/Auth/ApiAuthSessionEntryTest.php \
  tests/Feature/Auth/LogoutUserActionTest.php \
  tests/Feature/Auth/ReleaseGateTest.php \
  tests/Feature/Auth/HRManagerEmployeeRecordsAuthTest.php \
  tests/Feature/Modules/DormitoryAdmin/DormitoryAdminSecurityRemediationTest.php
# passed: 47 (D + E run-only batch; see w08-scope.md audit clarification)
```

**Summary:** W08-A/B/C/D/E **ALL PASS**; blocking invariants W08-C-01/C-02 **PASS**. Lead Acceptance recorded; W-08 **CLOSED**.

## Out of scope (this PR)

- Parked **DGAP-03/05/06/08**, **SGAP-05/07** — gated on Business Owner / Spec04 Auth authority
- **BL-B1-01** assignment schema unfreeze
- **F-W07-04** / next **UI feature slug**, Feature Contract, Implementation Authorization
- **Spec011** audit (SGAP-08 DEFERRED)
- Phase G dormitory-admin remediation **as a separate decision boundary** (keep on its own PR if still open)

## Test plan

- [ ] Re-run W-08 commands above on CI / local
- [ ] Confirm `/employee/login` guest access + RateLimiter behavior smoke
- [ ] Confirm Establish-fail leaves `api`/`identity` unauthenticated
- [ ] Confirm no Spec04 Auth / parked DGAP code paths changed
```

---

## Reviewer checklist — files in this F2 chain

### Implementation (F2 auth)

- [ ] `app/Modules/Auth/Presentation/Livewire/EmployeeLogin.php` (+ Auth module tree as present)
- [ ] `app/Shared/Auth/IdentityRoleGuard.php`
- [ ] `app/Support/Auth/IdentityRoleGuard.php` (**deleted** — move to Shared)
- [ ] `app/Http/Middleware/EnsureIdentityRole.php` (Shared guard consumer)
- [ ] `app/Modules/DormitoryAdmin/DormitoryManagerDashboard.php`
- [ ] `app/Modules/DormitoryAdmin/DormitoryUnitManagerDashboard.php`
- [ ] `routes/web.php` (`employee.login`)
- [ ] `resources/views/livewire/auth/employee-login.blade.php`
- [ ] `resources/views/components/layouts/guest.blade.php`
- [ ] `tests/Feature/Auth/EmployeeLoginRateLimitTest.php`
- [ ] `tests/Feature/Auth/EmployeeLoginW08Test.php`
- [ ] `tests/Feature/Modules/DormitoryAdmin/DormitoryAdminSecurityRemediationTest.php` (guard path update)

### Feature / security / WBD docs

- [ ] `docs/features/employee-auth-ui/work-breakdown.md`
- [ ] `docs/features/employee-auth-ui/feature-brief.md`
- [ ] `docs/features/employee-auth-ui/l3-spec.md`
- [ ] `docs/features/employee-auth-ui/w07-security-review-scope.md`
- [ ] `docs/features/employee-auth-ui/w07-security-review-report.md`
- [ ] `docs/features/employee-auth-ui/w08-scope.md`
- [ ] `docs/product/product-authorization-employee-auth-ui.md`

### Governance / roadmap / decisions

- [ ] `docs/governance/open-decisions.md` (**canonical**)
- [ ] `.specify/governance/open-decisions.md` (**pointer-only**)
- [ ] `docs/governance/risk-register.md` (BL-04 Delivered; **F-W07-02** Accepted+verified)
- [ ] `docs/governance/roadmap.md` (F2 COMPLETE for boundary)
- [ ] `.specify/docs/discovery/domain-entity-relationship-map.md` (F2≠Spec04 Auth footnote)

### Spec Completion Audit DOC

- [ ] `specs/001-technical-foundation/spec.md` (Status header)
- [ ] `specs/008-external-accommodation/data-model.md`
- [ ] `specs/008-external-accommodation/contracts/*` (7 mirrors)
- [ ] `CLAUDE.md` / `AGENTS.md` (CheckIn wording)

### Scribe package

- [ ] `docs/features/employee-auth-ui/l9-pr-description.md` (this file)

---

## Pre-open STOP notes (honest)

| Issue | Status |
|-------|--------|
| Per-W **git commits** | **MISSING** — do not fabricate; commit on dedicated branch before PR open |
| F-W07-02 ↔ risk-register | **SYNCED** in this scribe pass → `docs/governance/risk-register.md` |
| Mixed branch `security/g-phase-dormitory-admin-ui` | **WARN** — split / dedicate `release/f2-employee-auth-ui-l9` before opening L9 |

**PR not opened via GitHub API** — description ready for Lead to authorize push + `gh pr create`.
