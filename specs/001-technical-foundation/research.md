# Research: Spec01 Technical Foundation

**Branch**: `001-technical-foundation` | **Date**: 2026-06-22

This document resolves planning unknowns for the DormSys foundation setup. Decisions are derived from the Constitution v2.0.0, Spec01, and ADR-001 (`.specify/docs/ADR/dormsys-architecture-technical-stack-v1.md`). Items not explicitly mandated are marked as **planning assumptions**.

---

## R-01: PHP Runtime Version

- **Decision**: PHP **8.4** as the minimum runtime for local development and CI.
- **Rationale**: ADR-001 specifies PHP 8.4 with JIT and property hooks. Laravel 13supports PHP 8.2+; 8.4 is the ADR baseline and aligns with long-term support expectations.
- **Alternatives considered**: PHP 8.3 (broader hosting compatibility) — rejected for greenfield project where ADR already committed to 8.4.

---

## R-02: Local Development Container Strategy

- **Decision**: **Laravel Sail** as the exclusive local development orchestrator (per Spec01 FR-011).
- **Rationale**: Spec01 mandates Sail; Sail ships maintained PostgreSQL and Redis service definitions, integrates with Laravel tooling, and reduces custom Docker maintenance for foundation phase.
- **Alternatives considered**: Custom `docker-compose.yml` (ADR Section 7) — rejected for Spec01 because Spec01 explicitly requires Sail; custom compose may be revisited for production deployment (out of scope).

---

## R-03: Module Naming — Approval vs Workflow

- **Decision**: Scaffold the **`Approval`** module per Spec01 FR-004. Document constitutional alias: **`Approval` ≡ `Workflow`** bounded context.
- **Rationale**: Spec01 is the feature source of truth for foundation scaffolding. Constitution Section 11 names this module `workflow` with entities `RequestApproval`, `ApprovalStage`, `ApprovalHistory`. Foundation creates directory scaffold only; no business logic in Spec01.
- **Alternatives considered**: Use `Workflow` directory name — rejected because it violates Spec01 FR-004 and would confuse `/tasks` traceability.

---

## R-04: Module Naming — Voucher vs Lottery Ownership

- **Decision**: Scaffold a standalone **`Voucher`** module per Spec01 FR-004. Voucher generation logic remains constitutionally owned by lottery domain rules (BR-06.3, BR-07) but gets a dedicated bounded-context directory for external-dormitory voucher lifecycle.
- **Rationale**: Spec01 explicitly lists Voucher as a core foundation module. Separating scaffold now avoids restructuring when voucher presentation and read APIs are implemented.
- **Alternatives considered**: Fold Voucher under `Lottery/` per Constitution glossary — rejected for Spec01 scaffold compliance; cross-reference documented for future ADR if module boundary changes.

---

## R-05: Deferred Constitution Modules (CheckIn, Report)

- **Decision**: **Do not scaffold** `CheckIn` or `Report` modules in Spec01. They are constitution modules but outside Spec01's 10-module scope.
- **Rationale**: Spec01 FR-004 defines exactly 10 modules. Adding extra scaffolds would exceed scope and create unused structure.
- **Alternatives considered**: Scaffold all constitution modules — rejected as scope creep beyond Spec01.

---

## R-06: PostgreSQL Extensions

- **Decision**: Enable **`uuid-ossp`** and **`pgcrypto`** via dedicated foundation migration (Spec01 FR-015). Defer **`btree_gist`** until Allocation module implements exclusion constraints (Constitution BR-02).
- **Rationale**: Spec01 requires uuid-ossp and pgcrypto. Exclusion constraints need btree_gist but no allocation schema exists in foundation phase.
- **Alternatives considered**: Enable btree_gist preemptively — acceptable but not required; enable in Allocation spec to keep foundation migrations minimal.

---

## R-07: UUID Primary Key Strategy

- **Decision**: UUID v4 (or `gen_random_uuid()` via pgcrypto) as default primary key type for all future entity tables. Laravel models use `$keyType = 'string'`, `$incrementing = false`.
- **Rationale**: Constitution NFR-08 and Spec01 FR-013 mandate UUID PKs. pgcrypto provides `gen_random_uuid()` natively on PostgreSQL 13+.
- **Alternatives considered**: `uuid-ossp` `uuid_generate_v4()` — supported as fallback; pgcrypto preferred on PG 17.

---

## R-08: Redis Usage Baseline

- **Decision**: Redis **7.x** for `CACHE_STORE`, `QUEUE_CONNECTION`, and `SESSION_DRIVER` in local/CI environments.
- **Rationale**: Constitution AP-08/AP-09 and ADR-001 specify Redis 7 for cache, queue, session, and locking.
- **Alternatives considered**: Database cache/queue for foundation only — rejected; Spec01 FR-003 requires Redis backends.

---

## R-09: Frontend Stack Versions

- **Decision**:
  - Livewire **3.x** (Constitution AP-01, Spec01 FR-007)
  - Tailwind CSS **4.x** with RTL plugin (ADR-001, Spec01 FR-008) — **planning assumption** for 4.x; pin exact version at `composer install` / `npm install` time
  - Alpine.js **3.x** for micro-interactions only (Constitution AP-07) — **planning assumption**; bundled via Livewire
- **Rationale**: Constitution mandates Livewire 3 and Tailwind RTL. ADR specifies Tailwind 4 and Alpine 3.
- **Alternatives considered**: Tailwind 3.x — rejected where ADR is more specific and approved.

---

## R-10: Platform Packages — Foundation vs Deferred

| Package | Spec01 Required | Foundation Action |
|---------|-----------------|-------------------|
| `spatie/laravel-model-states` | FR-010 | Install + publish config stub |
| `spatie/laravel-activitylog` | FR-010 | Install + publish migration stub |
| `spatie/laravel-permission` | FR-010 | Install + publish migration stub |
| `morilog/jalali` | FR-010 | Install + service provider registration |
| `livewire/livewire` | FR-007 | Install + layout stub |
| `laravel/horizon` | Assumption | Install; configure Redis; no jobs in Spec01 |
| `laravel/telescope` | Assumption | Install dev-only; disabled in production |
| `laravel/fortify` | Not in Spec01 | **Defer** — no auth in foundation |
| `laravel/sanctum` | Not in Spec01 | **Defer** |
| `sentry/sentry-laravel` | Deferred per spec | **Defer** |
| `maatwebsite/excel` | Not in Spec01 | **Defer** |
| `barryvdh/laravel-dompdf` | Not in Spec01 | **Defer** |

- **Rationale**: Spec01 explicitly limits scope to foundation packages. ADR lists additional packages for later phases.

---

## R-11: Static Analysis Toolchain

- **Decision**: **PHPStan level 8** via **Larastan** (`nunomaduro/larastan` ^3.x) + **Laravel Pint** for formatting.
- **Rationale**: Constitution DoD (Section 15) and ADR Section 4-10 mandate PHPStan level 8 and Pint.
- **Alternatives considered**: Psalm — rejected; project governance standardizes on PHPStan.

---

## R-12: Testing Framework

- **Decision**: **Pest PHP 3.x** with `pest-plugin-laravel` for feature tests; PHPUnit compatible runner underneath.
- **Rationale**: Spec01 FR-009, Constitution NFR-04, ADR Section 4-10.
- **Alternatives considered**: PHPUnit-only — rejected; Pest is the project standard.

---

## R-13: Architecture Testing Strategy

- **Decision**: Use **Pest architecture tests** (`pestphp/pest-plugin-arch` or native `arch()` expectations) to enforce:
  - Domain layer does not depend on Infrastructure or Presentation
  - Modules do not import each other's Infrastructure namespaces
  - No `DB::` or Eloquent usage in Domain layer
- **Rationale**: Modular monolith boundaries are constitutionally critical (AP-03, AP-04). Automated enforcement from day one prevents erosion.
- **Alternatives considered**: Manual code review only — rejected; fails at scale.

---

## R-14: CI Platform

- **Decision**: **GitHub Actions** with service containers for PostgreSQL 17 and Redis 7.
- **Rationale**: Spec01 assumption and ADR Section 4-11. No application Docker build in foundation CI — run tests directly on `ubuntu-latest` with PHP 8.4.
- **Alternatives considered**: GitLab CI — rejected; spec assumes GitHub Actions.

---

## R-15: Logging Configuration

- **Decision**: Structured **JSON logging** in `production` environment; `stack`/`single` channel in `local`. Context fields: `request_id`, `user_id`, `module` (when available).
- **Rationale**: Spec01 FR-018 and Constitution NFR-05.
- **Alternatives considered**: Plain text in production — rejected per spec.

---

## R-16: Timezone and Localization Baseline

- **Decision**: `APP_TIMEZONE=UTC` for storage; `APP_LOCALE=fa` for UI; Jalali conversion at presentation layer via `morilog/jalali`.
- **Rationale**: Spec01 FR-019, Constitution NFR-07.
- **Alternatives considered**: `Asia/Tehran` storage timezone — rejected; constitution mandates UTC storage.

---

## R-17: Migration Organization

- **Decision**: Hybrid organization:
  - `database/migrations/foundation/` — cross-cutting (extensions, shared infrastructure)
  - `database/migrations/{module}/` — per-module migrations registered via module service providers
- **Rationale**: Constitution AP-04 requires per-module migration ownership while foundation needs shared extension bootstrap.
- **Alternatives considered**: Flat `database/migrations/` only — rejected; does not scale to 10+ modules.

---

## R-18: Service Provider Organization

- **Decision**: Each module has `{Module}ServiceProvider` extending `ModuleServiceProvider` base. Central `bootstrap/providers.php` registers all module providers. Shared infrastructure in `App\Shared\Infrastructure\SharedServiceProvider`.
- **Rationale**: Spec01 Key Entities include `ModuleServiceProvider`; Laravel 13uses `bootstrap/providers.php`.
- **Alternatives considered**: Auto-discovery via package — rejected; explicit registration aids architecture tests.

---

## R-19: Health Check Endpoint

- **Decision**: Implement `/up` (Laravel 12 built-in) plus optional `/api/health` returning JSON with DB and Redis connectivity status (foundation stub).
- **Rationale**: Constitution NFR-05 references `/api/health`. Laravel 13provides `/up` natively.
- **Alternatives considered**: Custom health only — use both; `/up` for load balancers, `/api/health` for detailed checks in later phases.

---

## R-20: Sail Services for Foundation

- **Decision**: Sail services: **laravel.test** (app), **pgsql** (PostgreSQL 17), **redis** (Redis 7). Optional: **mailpit** for future notification testing (not required for Spec01 acceptance).
- **Rationale**: Spec01 FR-011 minimum set. Mailpit is Sail default optional service.
- **Alternatives considered**: MinIO in Sail — deferred per Spec01 (storage out of scope).
