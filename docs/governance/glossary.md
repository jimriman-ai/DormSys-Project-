# Glossary — DormSys (DG-05)

**Authority:** DG-05 Option C (Lead, 1405/04/24)  
**Canonical path:** `docs/governance/glossary.md`  
**Rule:** One canonical term per boundary in code/schema. UI/Persian may use listed aliases. Never mix aliases into code identifiers.

## Canonical mapping

| Boundary / concept | Canonical (code / schema) | Alias (UI / Farsi) | Notes |
|--------------------|---------------------------|--------------------|-------|
| Person in dormitory/HR domain | `Employee` | Student (دانشجو / دانش‌پژوه in UI copy only) | Schema/code stay `employee_*`; do not rename columns for UI |
| Auth session guard for dormitory-admin UI | `identity` | — | `config/auth.php` guard `identity`; provider `identity` → `UserModel` |
| Dual-guard RBAC model | `UserModel.$guard_name = ['web', 'identity']` | — | D-G-12 locked; removing `web` = BL-02 |
| Spec02 / historical Spatie roles & permissions | Auth guard `web` | — | Roles like `HRMgr`, `DormMgr`; permissions seeded on `web` |
| Dormitory manager role (admin UI) | `dormitory-manager` | مدیر خوابگاه | **Must** exist as Spatie role with `guard_name = identity` |
| Dormitory unit (room) manager role | `dormitory-unit-manager` | مدیر واحد خوابگاه | **Must** exist as Spatie role with `guard_name = identity` |
| Identity primary keys | UUID (`identity_users.id`) | — | Opaque string UUID / UUIDv7 in tests |
| Physical bed occupancy enum | `vacant` \| `reserved` \| `occupied` | خالی / رزرو / اشغال | Column `physical_occupancy_state` |
| Assignment schema (BL-B1-01) | `dormitory_manager_assignments` / `dormitory_unit_manager_assignments` | تخصیص‌ها | Restored 2026-07-16 (RM-BL-B1); `user_id` → `identity_users` CONSTRAINED_IDENTITY + restrictOnDelete |

## Product / UI terms (non-code)

| Term | Meaning |
|------|---------|
| dormitory-admin-ui | Phase G boundary: manager + unit manager Livewire dashboards under `/dormitory-admin` |
| Stage 3 | Out-of-band pending-request UI (explicitly OOB on dashboards) |

## Anti-patterns

- Using Spatie `role:dormitory-manager` without identity guard filter (SEC-G-01).
- Assigning `dormitory-manager` on `web` and expecting identity routes to allow it.
- Mixing `Student` into migration/column/class names for the employee record domain.
