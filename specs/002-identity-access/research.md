# Research: Identity & Access (spec02)

**Date**: 2026-06-26 | **Plan**: [plan.md](./plan.md)

---

## R-01 — UUID primary key version

**Decision:** UUID **v7** via spec01 `HasUuid` (`Ramsey\Uuid\Uuid::uuid7()`).

**Rationale:** Foundation kernel already standardized on v7; time-ordered IDs aid PostgreSQL index locality. spec02 MUST NOT introduce v4 (`Str::uuid()`).

**Alternatives considered:** UUID v4 — rejected (inconsistent with kernel).

---

## R-02 — Authentication / session (OA-02-01)

**Decision:** **Defer** login, session, Fortify, Sanctum to a later spec when entry flows exist.

**Rationale:** spec-catalog open question resolved as documented non-blocking uncertainty; Wave 1A focuses on account records + RBAC + supplier API.

**Alternatives considered:** Minimal login in spec02 — rejected (scope creep, blocks on UX).

---

## R-03 — RBAC package

**Decision:** `spatie/laravel-permission` (already in constitution tech stack).

**Rationale:** Constitution permission matrix; mature Laravel integration; tables stay in Identity module migrations.

**Alternatives considered:** Custom RBAC — rejected (reinventing, audit risk).

---

## R-04 — Cross-context read pattern (FR-008)

**Decision:** Application-level **`IdentityUserReadContract`** registered in DI; single implementation inside Identity module.

**Rationale:** AP-04 forbids cross-module persistence access; CD-012 requires supplier read without Identity knowing consumers. Interface is the narrowest stable surface.

**Alternatives considered:**
- Shared database views — rejected (violates module ownership)
- Direct repository injection — rejected (leaks Infrastructure)
- Domain events only for reads — rejected (insufficient for synchronous validation at consumer create)

---

## R-05 — User table naming

**Decision:** Table `identity_users` (module-prefixed) in `database/migrations/modules/identity/`.

**Rationale:** Avoid collision with Laravel default `users` if auth added later; clarifies module ownership.

**Alternatives considered:** `users` — deferred until auth package choice; may alias later with view/model name `UserModel`.

---

## R-06 — User status modeling

**Decision:** Enum `UserStatus: Active | Disabled` on domain + string/enum cast on persistence.

**Rationale:** Matches spec FR-003/004; simple for Wave 1A without state machine package for User.

**Alternatives considered:** `spatie/laravel-model-states` — deferred (only two states, no complex transitions in Wave 1A).

---

## R-07 — Event transport

**Decision:** Laravel synchronous domain events on create/disable in Wave 1A; queue dispatch optional later.

**Rationale:** Audit and tests need reliable emission; async not required for OA-02-01 deferral.

**Alternatives considered:** Outbox table — deferred to operational hardening.

---

## R-08 — Admin UI scope

**Decision:** Livewire admin components in Identity `Presentation/` — **Phase E** (tail of Wave 1A or early spec02 tasks).

**Rationale:** Supplier contract + domain can be tested without UI; aligns AP-07.

**Alternatives considered:** API-only — insufficient for administrator user stories in spec.

---

## R-09 — Support vs Shared path

**Decision:** Use `app/Support/` as implemented in spec01 (not `app/Shared/` from older plan drafts).

**Rationale:** Codebase truth; `BaseModel`, `HasUuid` live under Support.

---

## R-10 — Downstream consumer stub

**Decision:** spec03 gets pointer-only contract file; no Employee implementation in spec02 plan.

**Rationale:** CD-012 coupling direction; Identity plan must not depend on spec03 artifacts.
