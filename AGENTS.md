# Repository Guidelines

## Project Overview

DormSys is a greenfield Laravel 12 enterprise application for employee dormitory request, allocation, lottery, and lifecycle management. It follows a **Modular Monolith + Clean Architecture + DDD Lite** pattern. The authoritative governance document is `.specify/memory/constitution.md`.

## Project Structure & Module Organization

All business logic lives under `app/Modules/{Module}/`. Each module has four layers:

```
app/Modules/{Module}/
├── Domain/          # Entities, Value Objects, Domain Services, Domain Events
├── Application/     # Use Cases (Actions/Commands), DTOs, Application Services
├── Infrastructure/  # Eloquent Models, Repositories, Jobs
└── Presentation/    # Livewire Components, Controllers, Blade Views, Form Requests
```

**Core modules:** `Identity`, `Employee`, `Request`, `Workflow`, `Dormitory`, `Allocation`, `Lottery`, `CheckIn`, `Notification`, `Audit`, `Report`.

**Shared abstractions** (`BaseEntity`, `BaseValueObject`, `BaseDomainEvent`, `BaseRepository`) live in `app/Shared/`.

Feature specs are organized under `specs/{###-feature-name}/` with `spec.md`, `plan.md`, and `tasks.md`.

## Build, Test & Development Commands

| Command | Purpose |
|---|---|
| `sail up` | Start Docker environment (Laravel, PostgreSQL 17, Redis) |
| `sail artisan test` | Run full Pest PHP test suite |
| `sail artisan test --filter=ClassName` | Run tests matching a filter |
| `sail artisan migrate` | Run database migrations |
| `sail composer run phpstan` | Static analysis (PHPStan level 8) |
| `sail composer run pint` | Code formatting (Laravel Pint) |
| `sail artisan horizon` | Start queue worker |

## Coding Style & Naming Conventions

- **Formatter:** Laravel Pint. Run before every commit.
- **Static analysis:** PHPStan level 8 — zero errors required.
- **Primary keys:** UUID for all entities.
- **Eloquent models** belong in `Infrastructure/`, never in `Domain/`.
- **State machines** (`spatie/laravel-model-states`) own all lifecycle transitions — never scatter transition logic in Livewire components or controllers.
- **Alpine.js** is restricted to micro-interactions (dropdowns, modals). All primary interactivity uses Livewire.
- **Timestamps:** Store in UTC; convert to Jalali at the presentation layer using `morilog/jalali`.
- **UI language:** Persian (Farsi), RTL layout via Tailwind CSS.

## Architecture Rules

1. **Layer dependency:** Domain ← Application ← Infrastructure/Presentation. Domain must never import from outer layers.
2. **No cross-module Eloquent queries.** Use Application Services for cross-module reads. Cross-module foreign keys are prohibited — store UUIDs as value references.
3. **Audit log is append-only.** Never `UPDATE` or `DELETE` from `audit_logs`. All state transitions must emit entries via `AuditService`.

## Testing Guidelines

- **Framework:** Pest PHP (unit + feature), PHPUnit compatible. Optional Laravel Dusk for E2E.
- **Coverage target:** ≥ 80% for Domain and Application layers.
- **Run a single test:** `sail artisan test tests/Unit/Modules/Lottery/LotteryScoringEngineTest.php`
- **Test location:** Mirror the module structure under `tests/Unit/Modules/` and `tests/Feature/Modules/`.

## Commit & Pull Request Guidelines

- Write concise, descriptive commit messages (e.g., `feat: add lottery scoring engine`).
- Reference the relevant spec or feature number when applicable.
- Pull requests should include a description of changes, linked spec/issue, and confirmation that `phpstan` and `pint` pass with zero errors.

## Definition of Done

A task is complete when:

1. PHPStan level 8 passes with zero errors.
2. Laravel Pint formatting applied.
3. Domain/Application layer test coverage ≥ 80%.
4. All state transitions emit audit log entries via `AuditService`.
5. Database migrations include a rollback.
