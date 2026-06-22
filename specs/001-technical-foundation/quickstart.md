# Quickstart: Spec01 Foundation Validation

**Branch**: `001-technical-foundation` | **Date**: 2026-06-22

Runnable validation scenarios proving the foundation setup works end-to-end. See [data-model.md](./data-model.md) for abstractions and [contracts/](./contracts/) for interface contracts.

---

## Prerequisites

| Requirement | Version | Notes |
|-------------|---------|-------|
| Docker Desktop | Latest stable | Windows/macOS/Linux |
| Git | 2.x+ | Repository cloned |
| Composer | 2.x | Host-side for initial install, or via Sail |

**Planning assumptions**: PHP 8.4, Node.js 20+ (for Vite/Tailwind asset build).

---

## 1. Bootstrap & Dependencies

```bash
# From repository root
composer install
cp .env.example .env
php artisan key:generate
```

**Expected outcome**:
- Exit code 0
- `vendor/` populated with Laravel 12 and platform packages
- `.env` contains `DB_CONNECTION=pgsql`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`

**Maps to**: SC-001, FR-001, FR-016

---

## 2. Start Development Environment

```bash
./vendor/bin/sail up -d
# Or: sail up -d (after alias)
```

**Expected outcome**:
- PostgreSQL 17 container healthy
- Redis container healthy
- Application reachable at `http://localhost` (or configured `APP_URL`)
- Startup within ~30 seconds on typical hardware

**Maps to**: SC-002, FR-011, User Story 1

---

## 3. Database Foundation

```bash
sail artisan migrate
```

**Expected outcome**:
- Foundation migration enables `uuid-ossp` and `pgcrypto` extensions
- No migration errors
- Verify extensions:

```bash
sail psql -c "SELECT extname FROM pg_extension WHERE extname IN ('uuid-ossp', 'pgcrypto');"
```

Both extensions listed.

**Maps to**: SC-003, FR-002, FR-015, User Story 1 scenario 3

---

## 4. Redis Connectivity

```bash
sail artisan tinker --execute="Cache::put('foundation_test', 'ok', 60); echo Cache::get('foundation_test');"
```

**Expected outcome**: Prints `ok`

**Maps to**: SC-008, FR-003, User Story 1 scenario 4

---

## 5. Modular Structure Verification

```bash
# PowerShell
@('Identity','Employee','Request','Approval','Dormitory','Allocation','Lottery','Voucher','Notification','Audit') | ForEach-Object {
  $layers = @('Domain','Application','Infrastructure','Presentation')
  $layers | ForEach-Object { Test-Path "app/Modules/$($_)/$_" }
}
```

Or run the structure validation test (once implemented):

```bash
sail artisan test --filter=ModuleStructureTest
```

**Expected outcome**:
- All 10 modules exist with 4 layer subdirectories each
- `app/Shared/Domain/`, `app/Shared/Application/`, `app/Shared/Infrastructure/` exist
- Base classes present: `BaseEntity`, `BaseValueObject`, `BaseDomainEvent`, `BaseRepository`

**Maps to**: SC-005, SC-006, FR-004–FR-006, FR-012, User Story 2

---

## 6. Test Suite

```bash
sail artisan test
```

**Expected outcome**:
- All foundation tests pass (application boot, module structure, architecture rules, sample entity extension)
- Pest outputs green summary

**Maps to**: SC-004, FR-009, User Story 3

---

## 7. Code Quality Gates

```bash
sail composer run pint -- --test
sail composer run phpstan
```

**Expected outcome**:
- Pint: no formatting violations
- PHPStan: level 8, zero errors

**Maps to**: SC-010, FR-014, Constitution DoD

---

## 8. HTTP & Livewire Smoke Test

```bash
curl -s -o /dev/null -w "%{http_code}" http://localhost/up
```

**Expected outcome**: HTTP `200`

Optional: visit welcome page in browser; Livewire and Tailwind assets load without console errors.

**Maps to**: SC-009, FR-007, FR-008

---

## 9. CI Parity Check (Local)

Simulate CI before push:

```bash
sail composer run pint -- --test
sail composer run phpstan
sail artisan test
```

**Expected outcome**: Same as CI pipeline (see [contracts/ci-pipeline.md](./contracts/ci-pipeline.md))

**Maps to**: SC-007, User Story 4

---

## 10. Failure Scenarios (Edge Cases)

| Scenario | How to Simulate | Expected Behavior |
|----------|-----------------|-------------------|
| PostgreSQL down | `sail stop pgsql` then `sail artisan migrate` | Clear connection error; no silent failure |
| Redis down | `sail stop redis` then cache test | Graceful exception with Redis connection message |
| Missing UUID extension | Drop extension in test DB | Migration or health check reports descriptive failure |
| Incomplete module | Remove a layer directory | Structure validation test fails |

**Maps to**: Spec01 Edge Cases

---

## Acceptance Checklist

- [ ] `composer install` completes < 5 minutes
- [ ] `sail up` starts all services < 30 seconds
- [ ] `sail artisan migrate` succeeds with extensions enabled
- [ ] `sail artisan test` all green
- [ ] 10 modules with 4 layers each verified
- [ ] PHPStan level 8 passes
- [ ] Pint check passes
- [ ] Redis cache round-trip succeeds
- [ ] `/up` returns 200
- [ ] CI workflow green on push
