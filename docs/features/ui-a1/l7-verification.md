# L7 Verification — UI-A1 (Auth Layout / Identity Guard Integration)

| Field | Value |
|-------|--------|
| **Spec** | SPEC-UI-A1-L3-v2 |
| **L5** | PASS (prior gate) |
| **L6** | EXECUTED — FR-6 Option A; L6-R1 Amend |
| **Verifier role** | L7 Verification (read-only investigation + report) |
| **Date** | 1405/04/24 (2026-07-15) |
| **Code modified in this gate?** | **No** — verification only |
| **Commit?** | **No** |

---

## 1. Changed files vs approved L6 scope

### Approved L6 execute surface (plan)

| Path | L6 intent |
|------|-----------|
| `resources/views/components/layouts/dormitory-admin.blade.php` | Logout form + CSRF; auth helper only |
| `routes/web.php` | L6-R1 Amend logout middleware only (FR-6 Option A → dormitory-admin routes **unchanged**) |
| `tests/Feature/Modules/DormitoryAdmin/UiA1AuthLayoutTest.php` | New feature tests |
| Guard/bootstrap/middleware classes | VERIFY-ONLY (no change) |
| Migrations | NONE |

### Working-tree evidence (`git status` / `git diff --name-only`)

| File | Status | In L6 scope? |
|------|--------|----------------|
| `resources/views/components/layouts/dormitory-admin.blade.php` | Modified | **Yes** |
| `routes/web.php` | Modified | **Yes** (logout amend only — see §4) |
| `tests/Feature/Modules/DormitoryAdmin/UiA1AuthLayoutTest.php` | Untracked (new) | **Yes** |
| `docs/governance/roadmap.md` | Modified | **Docs status only** — UI-A1 → IN EXECUTION; not PHP/Livewire. Acceptable tracking artifact; **not** a code-scope violation. |

**Verdict §1:** **PASS** — no out-of-scope PHP/Livewire/migration churn for UI-A1 execute.

---

## 2. FR-3 / DEC-UIA1-G5 — no `UserModel` import in dormitory-admin layout

| Check | Result | Evidence |
|-------|--------|----------|
| FQCN import `use …UserModel` in layout | **Absent** | `resources/views/components/layouts/dormitory-admin.blade.php` — no `use` statements; principal via `auth('identity')` — L27–29 |
| Class/import in Livewire dashboards | **Absent** | `rg UserModel app/Modules/DormitoryAdmin` → no matches |
| Class/import in livewire dormitory-admin views | **Absent** | `rg` under `resources/views/livewire/dormitory-admin` → no matches |
| Comment mentioning “UserModel” | Present as **documentation only** (L25) — **not** an import | Allowed for AC-4 intent (no FQCN import) |

**Verdict §2:** **PASS**

---

## 3. Logout behavior — L6-R1 Amend

| Requirement | Result | Evidence |
|-------------|--------|----------|
| Web `logout` accepts `api` **or** `identity` | **PASS** | `routes/web.php:37-40` — `->middleware('auth:api,identity')` |
| Removed from `auth:api` + mutation/audit group | **PASS** | Diff: logout extracted before `auth:api` app-shell group (`web.php:42+`) |
| Controller still clears both guards | **PASS** (unchanged) | `AuthSessionController::destroy` logs out `api` then `identity` (prior evidence; not modified this gate) |
| Layout posts to named `logout` + CSRF | **PASS** | Layout L32–34 — `route('logout')`, `@csrf`, `data-testid="dormitory-admin-logout"` |

**Verdict §3:** **PASS**

---

## 4. FR-6 Option A — dormitory-admin routes unchanged

| Route | Middleware stack | Cite |
|-------|------------------|------|
| Group | `prefix('dormitory-admin')` + `auth:identity` + name `dormitory-admin.` | `web.php:24-26` |
| Manager | `identity.role:dormitory-manager` → `GET /` | `web.php:28-30` |
| Unit-manager | `identity.role:dormitory-unit-manager` → `GET /unit` | `web.php:32-34` |

`git diff` for `routes/web.php` shows **only** logout relocation/middleware amend — **no** hunk altering the dormitory-admin group (Option A / split roles preserved).

**Verdict §4:** **PASS**

---

## 5. Test results

Commands executed:

```text
php artisan test --filter=UiA1AuthLayoutTest
php artisan test tests/Feature/Modules/DormitoryAdmin/DormitoryManagerDashboardTest.php tests/Feature/Modules/DormitoryAdmin/DormitoryUnitManagerDashboardTest.php tests/Feature/Modules/DormitoryAdmin/DormitoryAdminSecurityRemediationTest.php
```

| Suite | Result | Counts |
|-------|--------|--------|
| `UiA1AuthLayoutTest` | **passed** | 3 passed, 11 assertions (~2.7s) |
| Manager + Unit + Security remediation | **passed** | 14 passed, 31 assertions (~11.1s) |
| **Combined exit** | **0** | — |

Covered by UiA1 tests: layout logout+CSRF; identity-only POST logout success; absolute guest logout redirect.

**Verdict §5:** **PASS**

---

## Overall L7 verdict

| Gate | Result |
|------|--------|
| Scope | PASS |
| FR-3 / UserModel import | PASS |
| L6-R1 Amend logout | PASS |
| FR-6 Option A | PASS |
| Tests | PASS |

### **L7 VERIFICATION: PASS — UI-A1 cleared for L8 (test/closure hygiene) / Lead closeout, pending Lead authorization.**

---

## STOP GATE

Verification complete. **No code changes** in this run. **No commit.**  
Next: Lead may authorize L8 / UI-A1 CLOSEOUT or request revisions.
