# L3 Spec — UI-M1 (Manager Dashboard — wire data)

**Layer:** L3 (Spec) artifact (requirements record).  
**Lifecycle status:** **L9-pending (L8-COMPLETE)**  
**Catalog ID:** UI-M1 (F3 Sprint A)  
**Authority:** Lead `AUTHORIZE L3-SPEC: UI-M1` (2026-07-16)  
**Canonical path:** `docs/features/ui-m1/l3-spec.md`  
**Pattern peer:** `docs/features/employee-auth-ui/l3-spec.md`

---

## §0 Boundary statement

**UI-M1** is the identity-guard **dormitory-manager** Livewire surface at `GET /dormitory-admin` that renders **assignment-scoped** dormitory occupancy summaries for the authenticated identity principal only.

---

## §1 Preconditions (RM-BL-B1 committed state)

All of the following are **preconditions**, not UI-M1 deliverables:

| Precondition | Evidence |
|--------------|----------|
| Assignment schema present | `dormitory_manager_assignments` migration `database/migrations/modules/dormitory/2026_07_16_000001_create_dormitory_manager_assignments_table.php`; glossary `docs/governance/glossary.md:19` |
| `user_id` FK → `identity_users` with `restrictOnDelete()` | Same migration; Lead FK policy CONSTRAINED_IDENTITY |
| Dashboard query wiring landed | Commit `369a106` — `feat(dormitory-admin): resolve BL-B1-01 — assignment schema + dashboard scoping (RM-BL-B1)`; component `app/Modules/DormitoryAdmin/DormitoryManagerDashboard.php` |
| BL-B1-01 resolved (governance) | `docs/governance/risk-register.md:13` — **RESOLVED (commit 369a106)** |
| DGAP-09 RE-FROZEN | `docs/governance/open-decisions.md:29`, `:177–185` — no further assignment-schema work without new unfreeze |
| Catalog status | `docs/governance/roadmap.md:39` — **L9-pending (L8-COMPLETE)** |
| UI-A1 auth/layout shell | `docs/governance/roadmap.md:41`; layout `resources/views/components/layouts/dormitory-admin.blade.php` |

**Sequencing note (Evidence):** Roadmap lists UI-M1 and UI-M2 as **sibling** catalog rows with the **same** dependency (BL-B1-01). No roadmap line states UI-M1 as a prerequisite of UI-M2 (`docs/governance/roadmap.md:39–40`). Lead sequencing preference (UI-M1 first) does **not** contradict roadmap.

---

## §2 Functional requirements (testable)

| ID | Requirement | Testable assertion |
|----|-------------|-------------------|
| **FR-1** | Route `GET /dormitory-admin` is named `dormitory-admin.manager` and served by `DormitoryManagerDashboard` | Route registration `routes/web.php:28–30` |
| **FR-2** | Guest (no identity session) is redirected to login | Feature: redirects guests → `/login` |
| **FR-3** | Authenticated identity user **without** Spatie role `dormitory-manager` (`guard_name = identity`) receives **403** | Feature: forbids non-role identity users |
| **FR-4** | Authenticated `dormitory-manager` with **zero** rows in `dormitory_manager_assignments` for their UUID sees empty-state copy | AssertSee `خوابگاهی به شما اختصاص داده نشده است.` (`data-testid="dormitory-manager-empty"`) |
| **FR-5** | Authenticated `dormitory-manager` sees **only** dormitories joined via `dormitory_manager_assignments.user_id = auth('identity')->id()` | AssertSee assigned dorm name; AssertDontSee unassigned dorm name |
| **FR-6** | For each assigned dormitory card, the view receives: `id`, `name`, `unit_count`, `bed_total`, `bed_occupied`, `bed_available` | Blade fields + `data-testid` unit-count / bed-* |
| **FR-7** | `unit_count` = distinct rooms under the dormitory hierarchy (building→floor→room, non-deleted) | Fixture with 2 rooms → `unit-count">2` |
| **FR-8** | `bed_total` / `bed_occupied` aggregate non-deleted beds; `bed_available = bed_total - bed_occupied` (occupied marker only; reserved/vacant both count toward “available” remainder) | Counts fixture assertions in `DormitoryManagerDashboardTest` |
| **FR-9** | Occupancy ratio display is presentational: `round((bed_occupied / bed_total) * 100)` when `bed_total > 0`, else `0` | Blade `dormitory-manager-dashboard.blade.php` (ratio block) |
| **FR-10** | Query-derived dormitory list is **not** a public Livewire property; built in `render()` and passed via `->with()` (SEC-G-03) | Code inspection / security remediation tests |
| **FR-11** | Stage-3 “pending requests” section remains **out-of-band** placeholder copy | AssertSee `خارج از محدوده — Stage 3` (`data-testid="pending-requests-oob"`) |

---

## §3 Auth requirements

| Concern | Spec |
|---------|------|
| **Route middleware** | Group `auth:identity` + route `identity.role:dormitory-manager` — `routes/web.php:24–30` |
| **Role string** | Canonical `dormitory-manager` — `docs/governance/glossary.md:15`; seeder `IdentityRoleSeeder::ROLE_DORMITORY_MANAGER` |
| **Render re-assert** | First statement in `render()`: `IdentityRoleGuard::assertIdentityRole(...)` — `DormitoryManagerDashboard.php:19` (SEC-G-02) |
| **Guard pin** | `IdentityRoleGuard` filters Spatie roles with `guard_name = identity` — `app/Shared/Auth/IdentityRoleGuard.php:27–29`, `:40–47` |
| **Principal resolution** | `auth('identity')->user()` then `getAuthIdentifier()` — **no** `UserModel` FQCN import in Livewire/Blade (DEC-UIA1-G5) — `DormitoryManagerDashboard.php:21–24` |

---

## §4 Dual-Principal Boundary

| Point | Principal | Model / table | Notes |
|-------|-----------|---------------|-------|
| Session for dormitory-admin | Guard **`identity`** | Provider `identity` → `UserModel` / `identity_users` (UUID PK) | `config/auth.php` identity guard; DGAP-10 CLOSED — NOT-A-GAP |
| Credential / legacy web | Guard **`web`** | `App\Models\User` / `users` (bigint) | **Not** used for UI-M1 route access |
| Assignment FK `user_id` | Identity UUID | FK → `identity_users.id`, `restrictOnDelete()` | Must match `auth('identity')->id()` |
| Spatie role check | Identity-guard roles only | `guard_name = identity` | SEC-G-01 |

**Forbidden in UI-M1 presentation code:** importing `App\Modules\Identity\Infrastructure\Persistence\Models\UserModel` in Livewire or Blade.

---

## §5 Data constraints

| Constraint | Rule |
|------------|------|
| Identity IDs | UUID strings (`identity_users.id`) |
| Assignment table | `dormitory_manager_assignments` only for UI-M1 scoping (not unit-manager table) |
| FK delete | `restrictOnDelete()` on `user_id` and `dormitory_id` — no cascade |
| Soft deletes | Hierarchy joins exclude soft-deleted buildings/floors/rooms/beds (`deleted_at` null) |
| Schema changes | **Prohibited** under this L3 — DGAP-09 **RE-FROZEN** (`open-decisions.md:185`) |

---

## §6 Acceptance criteria (L8-mappable)

L8 COMPLETE — criteria demonstrated:

1. **AC-1** Guest → redirect `/login` on `GET /dormitory-admin`.
2. **AC-2** Identity user without `dormitory-manager` → **403**.
3. **AC-3** Role holder, no assignments → empty state Persian copy + Stage-3 OOB visible.
4. **AC-4** Role holder assigned to dormitory A only → sees A, not B.
5. **AC-5** Assigned dormitory shows correct `unit_count`, `bed_total`, `bed_occupied`, `bed_available` for a fixed fixture.
6. **AC-6** Static/grep: no `UserModel` FQCN in `app/Modules/DormitoryAdmin/DormitoryManagerDashboard.php` or manager Blade.
7. **AC-7** `IdentityRoleGuard::assertIdentityRole` remains first authz gate inside `render()`.

**Baseline evidence already present (RM-07):** `tests/Feature/Modules/DormitoryAdmin/DormitoryManagerDashboardTest.php` covers AC-1…AC-5 shape.

---

## §7 Out of scope (explicit)

- **UI-M2** (unit-manager dashboard / `dormitory_unit_manager_assignments` / `/dormitory-admin/unit`)
- **UI-A1** residual auth/layout work (COMPLETE per roadmap)
- **UI-A2** assignment-management UI (creating/editing assignments) — catalog note `roadmap.md:43`
- **Stage 3** pending-requests product behavior (placeholder only)
- New migrations / schema / Eloquent assignment models (**RM-03 deferred**; DGAP-09 frozen)
- Changes to `config/auth.php` guard topology
- Cross-guard use of `web` / `App\Models\User` for this surface
- Allocation/Check-in/Request mutation flows
- Residual product scope outside UI-M1 (see catalog / out-of-scope rows above)

---

## §8 PENDING LEAD DECISION

**None blocking this L3 document.**

Informational (non-blocking) — W2 hygiene 2026-07-18 closed prior doc-lag notes:

| Note | Detail |
|------|--------|
| ~~Doc lag~~ | `risk-register.md` / roadmap BL-B1-01 wording → **RESOLVED** (W2 HIGH; commit `369a106`) |
| ~~Stale closeout~~ | `ui-a1/l8-closeout.md` UI-M1/M2 rows → **synced** (W2 HIGH) |
| Remaining | Lead merge SHA for UI-M1 L9 (GAP-GOV-02) — not an L3 decision |

---

## §9 Authorization reference

- Catalog: `docs/governance/roadmap.md` § F3 — UI-M1  
- Risk: `docs/governance/risk-register.md` — BL-B1-01, SEC-G-01…04  
- Decisions: `docs/governance/open-decisions.md` — DGAP-09 RE-FROZEN; DGAP-10 Dual Principal; DGAP-11 catalog  
- Role glossary: `docs/governance/glossary.md:15`  
- Shared guard: `app/Shared/Auth/IdentityRoleGuard.php`  
- Prior F2 L3 pattern: `docs/features/employee-auth-ui/l3-spec.md`

---

## Document control

| Field | Value |
|-------|--------|
| Status | **L9-pending (L8-COMPLETE)** |
| Implementation baseline | RM-BL-B1 / commit `369a106` (wires FR-1…FR-11 contract) |
| Next gate | Lead merge SHA (GAP-GOV-02) — L9 |
| VCS | No commit by this L3 docs task |
