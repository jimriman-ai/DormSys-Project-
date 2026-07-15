# L5 Authorization Record — Phase G: dormitory-admin-ui

**Record ID:** L5-G-CANONICAL-01  
**Boundary:** dormitory-admin-ui (Phase G — UI/Impl + Security Remediation)  
**Status:** AUTHORIZED — pending L9-R sign-off for merge (per D-G-14)  
**Decision Authority:** Lead (Human-only)  
**Date:** 1405/04/24 (2026-07-15)  
**STATUS:** CANONICAL

---

## 1. Scope of Authorization

این record مرجع canonical برای تصمیمات بسته‌شدهٔ فاز G است.
یک boundary = یک canonical record. هر تغییر در این تصمیمات نیازمند
supersede صریح توسط Lead است.

## 2. Canonical Decisions

| ID | Boundary | Decision | Rationale (خلاصه) |
|----|----------|----------|-------------------|
| D-G-10 | Scope SEC-G-01 | **(a)** رفع guard-confusion فقط در این PR برای dormitory-admin-ui؛ F2 → backlog جدا | جلوگیری از scope drift؛ F2 مالک مستقل دارد |
| D-G-11 | Ownership IdentityRoleGuard | **(a)** helper متعلق به ماژول dormitory | promotion به shared kernel نیازمند boundary جدید |
| D-G-12 | Dual-guard UserModel | **(a)** dual-guard `['web','identity']` باقی می‌ماند + rule صریح role-assignment؛ impact analysis حذف `web` → boundary جدا | Evidence: `UserModel.php` dual-guard; `config/auth.php` identity guard |
| D-G-13 | Owner ریسک SEC-G-04 | **Tech Lead**؛ بازبینی هر ۶ ماه یا ابتدای فاز مهم | Accepted Risk با owner و review cadence مشخص |
| D-G-14 | Merge Sign-off | **(b)** merge فقط با تأیید صریح Lead | Human-only Decision Authority |
| D-G-15 | Security Docs | **(a)** canonical = `docs/security/dormitory-admin-ui.md`؛ register سراسری → فاز بعد | Artifact-minimalism |
| D-G-16 | Closure Phase G | **(a)** merge این PR = بسته شدن Phase G؛ موارد باز → backlog | تعریف صریح Definition of Done فاز |

## 3. Authorized Implementation Surface (G-E)

- **H-01:** middleware سفارشی `EnsureIdentityRole` (چک صریح guard `identity`)؛ alias در `bootstrap/app.php`؛ حذف Spatie `role:` از مسیرهای dormitory-admin
- **H-02:** فراخوانی `IdentityRoleGuard::assertIdentityRole()` در ابتدای `render()` هر دو کامپوننت Livewire
- **H-03:** حذف public collections (`$dormitories`, `$rooms`) → متغیر محلی در `render()`
- **H-04:** Accepted Risk (SEC-G-04) — بدون تغییر کد؛ ثبت در security doc

## 4. Backlog (خارج از scope این PR — طبق D-G-10/D-G-12/D-G-15/D-G-16)

- **BL-01:** بررسی F2 guard-confusion (سراسری)
- **BL-02:** Impact analysis حذف guard `web` از `UserModel.$guard_name`
- **BL-03:** Global security register
- **BL-04:** ارزیابی promotion `IdentityRoleGuard` به shared kernel

## 5. Invariants (غیرقابل نقض در Impl)

1. FK convention: `restrictOnDelete()` — بدون cascade
2. Role separation سخت: `dormitory-manager@identity` / `dormitory-unit-manager@identity`
3. Identity IDs: UUID
4. دسترسی داده در dashboardها: فقط DB query builder aggregation
5. مقادیر canonical `physical_occupancy_state`: `vacant | reserved | occupied`
