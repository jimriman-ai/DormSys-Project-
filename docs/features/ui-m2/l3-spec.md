<!-- STATUS: L3 ACCEPTED — SB-D6 -->
# L3 Spec — UI-M2 (Unit-Manager Dashboard — wire data)

**Layer:** L3 (Spec) artifact (requirements record).  
**Lifecycle status:** **L3 ACCEPTED (SB-D6) — L8-MAPPABLE** (implementation baseline already wired; this document records the L3 contract)  
**Catalog ID:** UI-M2 (F3 Sprint B)  
**Authority:** Lead **SB-D6** (ACCEPT); L3 authoring was SB-D3=A — L6+ **not** authorized by this document or by SB-D6  
**Canonical path:** `docs/features/ui-m2/l3-spec.md`  
**Pattern peer:** `docs/features/ui-m1/l3-spec.md`

---

## §0 Boundary statement

**UI-M2** is the identity-guard **dormitory-unit-manager** Livewire surface at `GET /dormitory-admin/unit` that renders **assignment-scoped** room occupancy summaries for the authenticated identity principal only.

---

## §1 Preconditions (RM-BL-B1 committed state)

All of the following are **preconditions**, not UI-M2 deliverables:

| Precondition | Evidence |
|--------------|----------|
| Assignment schema present | `dormitory_unit_manager_assignments` migration `database/migrations/modules/dormitory/2026_07_16_000002_create_dormitory_unit_manager_assignments_table.php` (`:17–28`); glossary `docs/governance/glossary.md:19` |
| `user_id` FK → `identity_users` with `restrictOnDelete()` | Same migration `:19–21`; Lead FK policy CONSTRAINED_IDENTITY |
| Dashboard query wiring landed | Component `app/Modules/DormitoryAdmin/DormitoryUnitManagerDashboard.php` (`:17–27` render; `:43–102` aggregate) |
| BL-B1-01 resolved (governance) | `docs/governance/risk-register.md:13` — **RESOLVED** |
| DGAP-09 RE-FROZEN | `docs/governance/open-decisions.md` DGAP-09 — no further assignment-schema work without new unfreeze |
| Catalog status | `docs/governance/roadmap.md` § F3 Catalog UI-M2 — **L3 ACCEPTED (SB-D6)**; L6+ NOT authorized — Implementation Lock required |
| UI-A1 auth/layout shell | Layout `resources/views/components/layouts/dormitory-admin.blade.php`; component `#[Layout('components.layouts.dormitory-admin')]` at `DormitoryUnitManagerDashboard.php:14` |

**Sequencing note:** Roadmap lists UI-M1 and UI-M2 as sibling catalog rows with the same BL-B1-01 dependency. UI-M2 does not require RESIDUAL-01/02 (SB-D1/D2) for L3 authoring.

---

## §2 Functional requirements (testable)

| ID | Requirement | Testable assertion |
|----|-------------|-------------------|
| **FR-1** | Route `GET /dormitory-admin/unit` is named `dormitory-admin.unit-manager` and served by `DormitoryUnitManagerDashboard` | `routes/web.php:32–34` |
| **FR-2** | Guest (no identity session) is redirected to login | Feature: redirects guests → `/login` (`DormitoryUnitManagerDashboardTest.php`) |
| **FR-3** | Authenticated identity user **without** Spatie role `dormitory-unit-manager` (`guard_name = identity`) receives **403** | Feature: forbids non-role / wrong-role identity users |
| **FR-4** | Authenticated `dormitory-unit-manager` with **zero** rows in `dormitory_unit_manager_assignments` for their UUID sees empty-state copy «اتاقی به شما اختصاص داده نشده است.» | AssertSee `اتاقی به شما اختصاص داده نشده است.` (`data-testid="unit-manager-empty"`) — Blade `:8–10` |
| **FR-5** | Authenticated `dormitory-unit-manager` sees **only** rooms joined via `dormitory_unit_manager_assignments.user_id = auth('identity')->id()` | AssertSee assigned room; AssertDontSee unassigned — component join `:46–49` |
| **FR-6** | For each assigned room card, the view receives: `id`, `room_label`, `floor_label`, `building_name`, `dormitory_name`, `bed_total`, `bed_occupied`, `bed_reserved`, `bed_vacant` | Blade fields + `data-testid` bed-* — Blade `:19–46`; component return shape `:88–98` |
| **FR-7** | Bed aggregates count non-deleted beds under the assigned room | `leftJoin` beds with `deleted_at` null — `:62–65` |
| **FR-8** | Occupancy ratio display is presentational: `round((bed_occupied / bed_total) * 100)` when `bed_total > 0`, else `0` | Blade `:15–17`, `:48–55` |
| **FR-9** | Query-derived room list is **not** a public Livewire property; built in `render()` and passed via `->with()` (SEC-G-03) | `DormitoryUnitManagerDashboard.php:24–27` |
| **FR-10** | Residents section remains **out-of-band** placeholder copy | AssertSee `خارج از محدوده — Stage 3` (`data-testid="residents-oob"`) — Blade `:63–67` |

---

## §3 Auth requirements

| Concern | Spec |
|---------|------|
| **Route middleware** | Group `auth:identity` + route `identity.role:dormitory-unit-manager` — `routes/web.php:24–26`, `:32–34` |
| **Role string** | Canonical `dormitory-unit-manager` — `docs/governance/glossary.md:16`; seeder `IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER` |
| **Render re-assert** | First statement in `render()`: `IdentityRoleGuard::assertIdentityRole(IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER)` — `DormitoryUnitManagerDashboard.php:19` (SEC-G-02) |
| **Guard pin** | `IdentityRoleGuard` filters Spatie roles with `guard_name = identity` — `app/Shared/Auth/IdentityRoleGuard.php` |
| **Principal resolution** | `auth('identity')->user()` then `getAuthIdentifier()` — **no** `UserModel` FQCN import in Livewire/Blade — `DormitoryUnitManagerDashboard.php:21–24` |

---

## §4 Dual-Principal Boundary

| Point | Principal | Model / table | Notes |
|-------|-----------|---------------|-------|
| Session for dormitory-admin | Guard **`identity`** | Provider `identity` → `UserModel` / `identity_users` (UUID PK) | Same as UI-M1 |
| Credential / legacy web | Guard **`web`** | `App\Models\User` / `users` | **Not** used for UI-M2 route access |
| Assignment FK `user_id` | Identity UUID | FK → `identity_users.id`, `restrictOnDelete()` | Must match `auth('identity')->id()` |
| Spatie role check | Identity-guard roles only | `guard_name = identity` | SEC-G-01 |

**Forbidden in UI-M2 presentation code:** importing `App\Modules\Identity\Infrastructure\Persistence\Models\UserModel` in Livewire or Blade.

---

## §5 Data constraints

| Constraint | Rule |
|------------|------|
| Identity IDs | UUID strings (`identity_users.id`) |
| Assignment table | `dormitory_unit_manager_assignments` only for UI-M2 scoping (not manager table) |
| FK delete | `restrictOnDelete()` on `user_id` and `room_id` — no cascade — migration `:19–24` |
| Soft deletes | Hierarchy joins exclude soft-deleted floors/buildings/dormitories/rooms/beds |
| Schema changes | **Prohibited** under this L3 — DGAP-09 **RE-FROZEN** |

---

## §6 Acceptance criteria (L8-mappable)

1. **AC-1** Guest → redirect `/login` on `GET /dormitory-admin/unit`.
2. **AC-2** Identity user without `dormitory-unit-manager` → **403**.
3. **AC-3** Role holder, no assignments → empty state Persian copy + residents OOB visible.
4. **AC-4** Role holder assigned to room A only → sees A, not B.
5. **AC-5** Assigned room shows correct bed aggregates for a fixed fixture.
6. **AC-6** Static/grep: no `UserModel` FQCN in `DormitoryUnitManagerDashboard.php` or unit-manager Blade.
7. **AC-7** `IdentityRoleGuard::assertIdentityRole` remains first authz gate inside `render()`.

**Baseline evidence already present:** `tests/Feature/Modules/DormitoryAdmin/DormitoryUnitManagerDashboardTest.php`.

---

## §7 Out of scope (explicit)

- **UI-M1** manager dashboard (`dormitory-manager` / `/dormitory-admin`)
- **UI-A2** assignment-management UI
- Residents product behavior beyond Stage-3 placeholder
- New migrations / schema / Eloquent assignment models
- Changes to `config/auth.php` guard topology
- Request / Stage-1 / Allocation mutation flows
- L6+ implementation work (requires separate IA after L3 review)

---

## §8 PENDING LEAD DECISION

| Note | Detail |
|------|--------|
| L3 review gate | **CLOSED** — ACCEPTED (**SB-D6**, WP-01 rev-4) |
| L6+ | **NOT** authorized (SB-D3=A / SB-D6 unchanged on this point); Implementation Lock required |

---

## §9 Authorization reference

- Catalog: `docs/governance/roadmap.md` § F3 — UI-M2; Sprint B backlog  
- Decision: **SB-D6** (L3 ACCEPT); prior authoring **SB-D3=A** (L3-only)  
- Risk: `docs/governance/risk-register.md` — BL-B1-01, SEC-G-01…04  
- Role glossary: `docs/governance/glossary.md:16`  
- Shared guard: `app/Shared/Auth/IdentityRoleGuard.php`  
- Pattern peer: `docs/features/ui-m1/l3-spec.md`

---

## Document control

| Field | Value |
|-------|--------|
| Status | **L3 ACCEPTED (SB-D6) — L8-MAPPABLE** |
| Implementation baseline | RM-BL-B1 unit-manager wiring (component + route + tests already present) |
| Next gate | Implementation Lock (PA-03); L6+ **not** authorized until then |
| VCS | No commit by this L3 docs task — Lead commits manually |
